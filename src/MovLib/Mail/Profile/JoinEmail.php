<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
 *
 * MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with MovLib.
 * If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Mail\Profile;

use \MovLib\Data\TemporaryStorage;

/**
 * Email template that is sent to clients after successfully joining MovLib.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class JoinEmail extends \MovLib\Mail\AbstractEmail {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user.
   *
   * @var \MovLib\Data\User\User
   */
  protected $user;

  /**
   * The user's unique link to activate the account.
   *
   * @var string
   */
  protected $link;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create join email for activation of account.
   *
   * @param \MovLib\Data\User\User $user
   *   The user instance.
   */
  public function __construct(\MovLib\Data\User\User $user) {
    $this->user = $user;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP) {
    parent::init($diContainerHTTP);

    $this->recipient = $this->user->email;
    $this->subject = $this->intl->t("Welcome to {0}!", [ $diContainerHTTP->config->sitename ]);

    // The token is the user's email address, but because it's kind a ackward to see your own email address as part of
    // a URL we hex encode it. Not that a hex encoded string might be longer than a base64 encoded string, but it only
    // contains characters that are valid within a URL and that's actually what we need here.
    $token = bin2hex($this->user->email);
    $this->link = $this->presenter->url($this->intl->r("/profile/join"), [ "token" => $token ], null, true);

    // Build the data that we're going to store for this join attempt. No need to store the email address, the key
    // actually is the email address.
    $data = (object) [
      "name" => $this->user->name,
      "passwordHash" => password_hash($this->user->passwordHash, $this->config->passwordAlgorithm, $this->config->passwordOptions),
    ];

    // Check if we already have some data stored for this email address.
    $tmp = new TemporaryStorage($diContainerHTTP);
    $user = $tmp->get($token);

    // We have no data stored for this email address, create a new record.
    if ($user === false) {
      $tmp->set($data, $token);
    }
    else {
      $tmp->update($data, $token);
    }

    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function getHTML() {
    return
      "<p>{$this->intl->t("Hi {username}!", [ "username" => $this->user->name ])}</p>" .
      "<p>{$this->intl->t("Thank you for joining {sitename}. You may now sign in and activate your new account by {0}clicking this link{1}.", [
        "sitename" => $this->config->sitename,
        "<a href='{$this->link}'>",
        "</a>",
      ])}</p>" .
      "<p>{$this->intl->t("This link can only be used once within the next 24 hours.")}<br>" .
      "{$this->intl->t("You will be able to sign in with the following data:")}</p>" .
      "<table>" .
        "<tr><td>{$this->intl->t("Email Address")}:</td><td>{$this->user->email}</td><tr>" .
        "<tr><td>{$this->intl->t("Password")}:</td><td><em>{$this->intl->t("Your secret password")}</em></td></tr>" .
      "</table>" .
      "<p>{$this->intl->t("If it wasn’t you who requested this action simply ignore this message.")}</p>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlainText() {
    return <<<EOT
{$this->intl->t("Hi {username}!", [ "username" => $this->user->name ])}

{$this->intl->t("Thank your for joining {sitename}. You may now sign in and activate your new account by clicking the following link or copying and pasting it to your browser:", [ "sitename" => $this->config->sitename ])}

{$this->link}

{$this->intl->t("This link can only be used once within the next 24 hours.")}
{$this->intl->t("You will be able to sign in with the following data:")}

{$this->intl->t("Email Address")}:  '{$this->user->email}'
{$this->intl->t("Password")}:       '{$this->intl->t("Your secret password")}'

{$this->intl->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
