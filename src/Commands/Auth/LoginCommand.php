<?php
// src/Commands/Auth/LoginCommand.php

namespace Terminus\Commands\Auth;

use Terminus\Commands\TerminusCommand;
use Terminus\Models\Auth;
use Symfony\Component\Console\Input\InputArgument;
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
    $auth = new Auth();
    if (isset($args['email'])) {
      $email = $args['email'];
    }
    if (isset($assoc_args['machine-token'])
        && ($assoc_args['machine-token'] !== true)
    ) {
      // Try to log in using a machine token, if provided.
      $token_data = ['token' => $assoc_args['machine-token']];
      $auth->logInViaMachineToken($token_data);
      $this->log()->info('Logging in via machine token');
    } elseif (isset($email) && !isset($assoc_args['password'])
              && $auth->tokenExistsForEmail($email)
    ) {
      // Try to log in using a machine token, if the account email was provided.
      $this->log()->info(
        'Found a machine token for "{email}".',
        ['email' => $args['email'],]
      );
      $auth->logInViaMachineToken(compact('email'));
      $this->log()->info('Logging in via machine token');
    } elseif (empty($args) && isset($_SERVER['TERMINUS_MACHINE_TOKEN'])) {
      // Try to log in using a machine token, if it's in the $_SERVER.
      $token_data = ['token' => $_SERVER['TERMINUS_MACHINE_TOKEN']];
      $auth->logInViaMachineToken($token_data);
      $this->log()->info('Logging in via machine token');
    } elseif (isset($_SERVER['TERMINUS_USER'])
              && !isset($assoc_args['password'])
              && $auth->tokenExistsForEmail($_SERVER['TERMINUS_USER'])
    ) {
      // Try to log in using a machine token, if $_SERVER provides account email.
      $this->log()->info(
        'Found a machine token for "{email}".',
        ['email' => $_SERVER['TERMINUS_USER'],]
      );
      $auth->logInViaMachineToken(['email' => $_SERVER['TERMINUS_USER']]);
      $this->log()->info('Logging in via machine token');
    } elseif (!isset($email)
              && $only_token = $auth->getOnlySavedToken()
    ) {
      // Try to log in using a machine token, if there is only one saved token.
      $this->log()->info(
        'Found a machine token for "{email}".',
        ['email' => $only_token['email'],]
      );
      $auth->logInViaMachineToken($only_token);
      $this->log()->info('Logging in via machine token');
    } else if (isset($email) && isset($assoc_args['password'])) {
      $password = $assoc_args['password'];
      $auth->logInViaUsernameAndPassword(
        $email,
        $assoc_args['password']
      );
    } else {
      $this->failure(
        "Please visit the Dashboard to generate a machine token:\n{url}",
        ['url' => $auth->getMachineTokenCreationUrl()]
      );
    }
    if (!isset($email)) {
      $user = Session::getUser();
      $user->fetch();
      $user_data = $user->serialize();
      $email     = $user_data['email'];
    }
    $this->log()->info('Logged in as {email}.', compact('email'));

    $this->log()->debug(print_r(get_defined_vars(), true));
    //$this->helpers->launch->launchSelf(
    //  ['command' => 'art', 'args' => ['fist']]
    //);
  }

}