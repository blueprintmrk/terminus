<?php
// src/Commands/TerminusCommand.php

namespace Terminus\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Terminus\Caches\FileCache;

abstract class TerminusCommand extends Command {

  /**
   * @var FileCache
   */
  private $cache;
  /**
   * @var InputInterface
   */
  private $input;
  /**
   * @var LoggerInterface
   */
  private $logger;
  /**
   * @var OutputInterface
   */
  private $output;

  /**
   * @inheritdoc
   */
  public function __construct($name = null) {
    $this->cache = new FileCache();
    parent::__construct($name);
  }

  public function log() {
    return $this->logger;
  }

  public function output() {
    return $this->output;
  }

  public function cache() {
    return $this->cache;
  }

  protected function configure() {
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->input  = $input;
    $this->output = $output;
    $this->logger = new ConsoleLogger($output);
    $this->do($input->getArguments(), $input->getOptions());
  }

  protected function failure($message, $context) {
    $this->log()->error($message, $context);
  }

}
