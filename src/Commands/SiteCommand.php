<?php
// src/Command/SiteCommand.php

namespace Terminus\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Terminus\Commands\TerminusCommand;

class SiteCommand extends TerminusCommand {

  protected function configure() {
    $this->setName('site')
      ->setDescription('Site manipulation command')
      ->addArgument('subcommand', InputArgument::OPTIONAL)
      ->addOption(
       'yell',
       null,
       InputOption::VALUE_NONE,
       'If set, the task will yell in uppercase letters'
    );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $name = $input->getArgument('subcommand');
    if ($name) {
      $text = 'Hello '.$name;
    } else {
      $text = 'Hello';
    }

    if ($input->getOption('yell')) {
      $text = strtoupper($text);
    }

    $output->writeln($text);
  }

}
