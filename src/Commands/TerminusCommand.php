<?php
// src/Commands/TerminusCommand.php

namespace Terminus\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Terminus\FileLoader;

abstract class TerminusCommand extends Command {

  /**
   * @var InputInterface
   */
  private $input;
  /**
   * @var OutputInterface
   */
  private $output;

  public function input($key) {}

  public function output() {
    return $this->output;
  }

  protected function configure() {
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->input  = $input;
    $this->output = $output;
    $this->do($input->getArguments(), $input->getOptions());
  }

}
