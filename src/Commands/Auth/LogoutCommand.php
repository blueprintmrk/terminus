<?php
// src/Commands/Auth/LogoutCommand.php

namespace Terminus\Commands\Auth;

use Terminus\Commands\TerminusCommand;

/**
 * Log yourself out and remove the secret session key.
 */
class LogoutCommand extends TerminusCommand {

  public function configure() {
    $this->setName('auth:logout')
      ->setDescription('Log yourself out and remove the secret session key.');
    parent::configure();
  }

  public function do($args, $assoc_args) {
    $this->log()->info('Logging out of Pantheon.');
    $this->cache()->remove('session');
  }

}