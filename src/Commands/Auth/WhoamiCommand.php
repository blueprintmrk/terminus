<?php
// src/Commands/Auth/LogoutCommand.php

namespace Terminus\Commands\Auth;

use Terminus\Commands\TerminusCommand;
use Terminus\Session;

/**
 * Find out what user you are logged in as.
 */
class WhoamiCommand extends TerminusCommand {

  public function configure() {
    $this->setName('auth:whoami')
      ->setDescription('Find out what user you are logged in as.');
    parent::configure();
  }

  public function do($args, $assoc_args) {
    if (Session::getValue('user_uuid')) {
      $user = Session::getUser();
      $user->fetch();

      $data = $user->serialize();
      $formatter       = $this->getHelper('formatter');
      $formatted_block = $formatter->formatBlock($data, 'info');
      $this->output()->writeln($formatted_block);
    } else {
      $this->output()->writeln('You are not logged in.');
    }
  }

}