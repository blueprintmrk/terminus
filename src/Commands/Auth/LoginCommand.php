<?php
// src/Commands/Auth/LoginCommand.php

namespace Terminus\Commands\Auth;

use Terminus\Commands\TerminusCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Terminus\Session;

/**
 * Log in as a user
 *
 * ## OPTIONS
 *
 * <email>
 * : Email address to log in as.
 *
 * --password=<value>
 * : Log in non-interactively with this password. Useful for automation.
 *
 * --machine-token=<value>
 * : Authenticates using a machine token from your dashboard. Stores the
 *   token for future use.
 */
class LoginCommand extends TerminusCommand {

  public function configure() {
    $this->setName('auth:login')
      ->setDescription('Log in as a user')
      ->addArgument('email', InputArgument::OPTIONAL, 'If set, it will access a saved machine token to log you in unless the password argument is also supplied, in which case a user/pass login will be conducted.')
      ->addOption('password', 'p', InputArgument::OPTIONAL, 'If set, Terminus will conduct a user/pass login.')
      ->addOption('machine-token', 'mt', InputArgument::OPTIONAL, 'If set, Terminus will authenticate with this machine token and stores the token for future use.');
    parent::configure();
  }

  public function do($args, $assoc_args) {
    if (!empty($args)) {
      $email = array_shift($args);
    }
    if (isset($assoc_args['machine-token'])
      && ($assoc_args['machine-token'] !== true)
    ) {
      // Try to log in using a machine token, if provided.
      $token_data = ['token' => $assoc_args['machine-token']];
      $this->auth->logInViaMachineToken($token_data);
    } elseif (isset($email) && !isset($assoc_args['password'])
      && $this->auth->tokenExistsForEmail($email)
    ) {
      // Try to log in using a machine token, if the account email was provided.
      $this->auth->logInViaMachineToken(compact('email'));
    } elseif (empty($args) && isset($_SERVER['TERMINUS_MACHINE_TOKEN'])) {
      // Try to log in using a machine token, if it's in the $_SERVER.
      $token_data = ['token' => $_SERVER['TERMINUS_MACHINE_TOKEN']];
      $this->auth->logInViaMachineToken($token_data);
    } elseif (isset($_SERVER['TERMINUS_USER'])
      && !isset($assoc_args['password'])
      && $this->auth->tokenExistsForEmail($_SERVER['TERMINUS_USER'])
    ) {
      // Try to log in using a machine token, if $_SERVER provides account email.
      $this->auth->logInViaMachineToken(['email' => $_SERVER['TERMINUS_USER']]);
    } elseif (!isset($email)
      && $only_token = $this->auth->getOnlySavedToken()
    ) {
      // Try to log in using a machine token, if there is only one saved token.
      $this->auth->logInViaMachineToken(['email' => $only_token['email']]);
    } else if (isset($email) && isset($assoc_args['password'])) {
      $password = $assoc_args['password'];
      $this->auth->logInViaUsernameAndPassword(
        $email,
        $assoc_args['password']
      );
    } else {
      $this->log()->info(
        "Please visit the Dashboard to generate a machine token:\n{url}",
        ['url' => $this->auth->getMachineTokenCreationUrl()]
      );
      exit(1);
    }
  }

}
