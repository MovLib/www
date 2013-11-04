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
namespace MovLib\Presentation\Email\Users;

/**
 * Email template that is sent to clients after successfull registration.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Registration extends \MovLib\Presentation\Email\AbstractEmail {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's name.
   *
   * @var string
   */
  protected $username;

  /**
   * The user's unique link to activate the account.
   *
   * @var string
   */
  protected $link;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create registration email for activation of account.
   *
   * @param string $username
   *   The user's name.
   * @param string $email
   *   The user's email address.
   */
  public function __construct($username, $email) {
    $this->recipient = $email;
    $this->username  = $username;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize properties for email.
   *
   * @internal
   *   We base64 encode the email address because it looks akward having your own email address as part of a URL.
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  public function init() {
    global $i18n, $kernel;
    $this->subject = $i18n->t("Welcome to {0}!", [ $kernel->siteName ]);
    $this->link    = "{$kernel->scheme}://{$kernel->hostname}{$i18n->r("/users/registration")}?token=" . rawurlencode(base64_encode($this->recipient));
    return $this;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function getHTML() {
    global $i18n, $kernel;
    return
      "<p>{$i18n->t("Hi {0}!", [ $this->username ])}</p>" .
      "<p>{$i18n->t("Thank you for registering at {0}. You may now sign in and activate your new account by {1}clicking this link{2}.", [
        $kernel->siteName,
        "<a href='{$this->link}'>",
        "</a>"
      ])}</p>" .
      "<p>{$i18n->t("This link can only be used once within the next 24 hours.")}<br>" .
      "{$i18n->t("You will be able to sign in with the following data:")}</p>" .
      "<table>" .
        "<tr><td>{$i18n->t("Email Address")}:</td><td>{$this->recipient}</td><tr>" .
        "<tr><td>{$i18n->t("Password")}:</td><td><em>{$i18n->t("Your secret password")}</em></td></tr>" .
      "</table>" .
      "<p>{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}</p>"
    ;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function getPlainText() {
    global $i18n, $kernel;
    return <<<EOT
{$i18n->t("Hi {0}!", [ $this->username ])}

{$i18n->t("Thank your for registering at {0}. You may now sign in and activate your new account by clicking the following link or copying and pasting it to your browser:", [ $kernel->siteName ])}

{$this->link}

{$i18n->t("This link can only be used once within the next 24 hours.")}
{$i18n->t("You will be able to sign in with the following data:")}

{$i18n->t("Email Address")}:  '{$this->recipient}'
{$i18n->t("Password")}:       '{$i18n->t("Your secret password")}'

{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
