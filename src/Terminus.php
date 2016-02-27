#!/usr/bin/env php
<?php
// src/Terminus.php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\ClassLoader\Psr4ClassLoader;
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;

$terminus = new Terminus;
$terminus();

class Terminus {

  /*
   * @var Application
   */
  private $application;
  /*
   * @var string
   */
  private $command_directories = [__DIR__ . '/Commands',];

  /**
   * Terminus constructor.
   *
   * @return Terminus
   */
  public function __construct() {
    $this->setAutoloader();
    $this->loadConstants();
    $this->application = new Application('Terminus', TERMINUS_VERSION);
    $this->loadCommands();
  }

  /**
   * Runs Terminus as a command-line application
   *
   * @throws Exception
   */
  public function __invoke () {
    $this->application->run();
  }

  /**
   * Loads all commands from a directory
   *
   * @param string $directory Name of the directory to load commands from
   * @return void
   */
  private function loadCommandDirectory($directory) {
    $iterator = new \DirectoryIterator($directory);
    foreach ($iterator as $file) {
      if (!$file->isDot()) {
        if ($file->isDir()) {
          $this->loadCommandDirectory($file->getPathname()); 
        } elseif (!empty(preg_grep('/^(?!\.).*Command.php$/', [$file->getFilename(),]))) {
          $contents = file_get_contents($file->getPathname());
          preg_match("/namespace (.*);/", $contents, $namespace_matches);
          preg_match("/class (.*Command)( extends)/", $contents, $class_matches);
          if (!empty($namespace_matches) && !empty($class_matches) && !strpos($contents, 'abstract class')) {
            include_once($file->getPathname());
            $class_name = $namespace_matches[1] . '\\' . $class_matches[1];
            $this->application->add(new $class_name());
          }
        }
      }
    }
  }

  /**
   * Sets the application and loads commands
   *
   * @return void
   */
  private function loadCommands() {
    foreach($this->command_directories as $directory) {
      $this->loadCommandDirectory($directory);
    }
  }

  /**
   * Loads constants from file
   *
   * @return void
   */
  private function loadConstants() {
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

}
