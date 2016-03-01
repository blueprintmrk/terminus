<?php
// src/Terminus.php

use Symfony\Component\ClassLoader\Psr4ClassLoader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class Terminus extends Application {

  /*
   * @var string[]
   */
  private $command_directories = [__DIR__ . '/Commands',];
  /**
   * @var string
   */
  private $helper_directory = __DIR__ . '/Helpers';

  /**
   * @inheritdoc
   */
  public function __construct() {
    $this->setConstants();
    $this->setAutoloader();
    parent::__construct('Terminus', TERMINUS_VERSION);
    $this->setCommands();
  }

  /**
   * Runs Terminus as a command-line application
   *
   * @throws Exception
   */
  public function __invoke () {
    $this->run();
  }

  /**
   * @inheritdoc
   */
  protected function getDefaultHelperSet() {
    $helper_set = parent::getDefaultHelperSet();
    $helpers    = $this->getClassInstances(
      [
        'directory' => $this->helper_directory,
        'filename'  => '/^(?!\.).*Helper.php$/',
        'classname' => '/class (.*Helper)( extends)/',
      ]
    );
    foreach ($helpers as $helper) {
      $helper_set->set($helper);
    }
    return $helper_set;
  }

  /**
   * @inheritdoc
   */
  protected function getDefaultInputDefinition() {
    $definition = parent::getDefaultInputDefinition();
    $definition->addOptions(
      [
        new InputOption(
          '--yes',
          '-y',
          InputOption::VALUE_NONE,
          'Answer yes to all prompts'
        ),
      ]
    );
    return $definition;
  }

  /**
   * Retrieves instances of all matching classes within a directory
   *
   * @param string[] $arg_options Elements as follow:
   *   string directory Directory to begin search from
   *   string filename  Regex to match valid file names
   *   string classname Regex to match valid class names
   *   string namespace Regex to match valid namespaces
   *   bool   recursive Retrieve classes from within directories
   * @return object[]
   */
  private function getClassInstances(array $arg_options = []) {
    $default_options = [
      'directory' => __DIR__, 
      'filename'  => '/^(?!\.).*.php$/',
      'classname' => '/class (.*) /',
      'namespace' => '/namespace (.*);/',
      'recursive' => true,
    ];
    $options         = array_merge($default_options, $arg_options);
    $classes         = [];
    $iterator        = new DirectoryIterator($options['directory']);
    foreach ($iterator as $file) {
      if (!$file->isDot()) {
        if ($file->isDir()) {
          if ($options['recursive']) {
            $dir_options = array_merge($options, ['directory' => $file->getPathname(),]);
            $classes     = array_merge($classes, $this->getClassInstances($dir_options)); 
          }
        } elseif (!empty(preg_grep($options['filename'], [$file->getFilename(),]))) {
          $contents = file_get_contents($file->getPathname());
          preg_match($options['namespace'], $contents, $namespace_matches);
          preg_match($options['classname'], $contents, $class_matches);
          if (!empty($namespace_matches) && !empty($class_matches) && !strpos($contents, 'abstract class')) {
            $class_name = $namespace_matches[1] . '\\' . $class_matches[1];
            $classes[]  = new $class_name();
          }
        }
      }
    }
    return $classes; 
  }

  /**
   * Sets the autoloader up to retrieve files as necessary
   *
   * @return void
   */
  private function setAutoloader() {
    $loader = new Psr4ClassLoader();
    $loader->addPrefix('Terminus\\', __DIR__);
    $loader->register();
  }

  /**
   * Loads all Terminus and plugin commands
   *
   * @return void
   */
  private function setCommands() {
    foreach ($this->command_directories as $directory) {
      $commands = $this->getClassInstances(
        [
          'directory' => $directory,
          'filename'  => '/^(?!\.).*Command.php$/',
          'classname' => '/class (.*Command)( extends)/',
        ]
      );
      foreach($commands as $command) {
        $this->add($command);
      }
    }
  }

  /**
   * Loads constants from file
   *
   * @return void
   */
  private function setConstants() {
    $constants = Yaml::parse(
      file_get_contents(__DIR__ . '/../config/constants.yml')
    );
    foreach($constants as $name => $value) {
      if (isset($_SERVER[$name])) {
        $env = $_SERVER[$name];
      } elseif ($env = getenv($name)) {
        $value = $env;
      }
      define($name, $value);
    }
  }

}
