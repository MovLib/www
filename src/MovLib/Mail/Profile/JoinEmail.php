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
   * Initialize properties for email.
   *
   * @internal
   *   We base64 encode the email address because it looks akward having your own email address as part of a URL.
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   The HTTP dependency injection container.
   * @return this
   */
  public function init(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP) {
    parent::init($diContainerHTTP);

    $this->recipient = $this->user->email;
    $this->subject   = $this->intl->t("Welcome to {0}!", [ $diContainerHTTP->config->sitename ]);
    $this->link      = $this->url($this->intl->r("/profile/join"), [ "token" => base64_encode($this->user->email) ]);
    $key             = "jointoken{$this->user->email}";
    $tmp             = new TemporaryStorage($diContainerHTTP);
    $user            = $tmp->get($key);

    // Make sure we won't store an unhashed password in our temporary table.
    $this->user->passwordHash = password_hash($this->user->passwordHash, $this->config->passwordAlgorithm, $this->config->passwordOptions);

    // Check if we already have a temporary record for this user, create new if we don't and overwrite if we do.
    if ($user === false) {
      $tmp->set($this->user, $key);
    }
    elseif ($user != $this->user) {
      $tmp->update($this->user, $key);
    }

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getHTML() {
    return
      "<p>{$this->intl->t("Hi {username}!", [ "username" => $this->user->name ])}</p>" .
      "<p>{$this->intl->t("Thank you for joining {0}. You may now sign in and activate your new account by {1}clicking this link{2}.", [
        $this->config->sitename,
        "<a href='{$this->link}'>",
        "</a>"
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
   * @inheritdoc
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
