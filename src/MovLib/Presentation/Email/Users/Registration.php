<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
 * Description of RegistrationEmail
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
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
   * The user's base64 encoded email address.
   *
   * @var string
   */
  protected $token;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create registration email for activation of account.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $username
   *   The user's name.
   * @param string $email
   *   The user's email address.
   */
  public function __construct($username, $email) {
    global $i18n;
    parent::__construct($email, $i18n->t("Welcome to {0}!", [ "MovLib" ]));
    $this->username = $username;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize properties for email.
   *
   * @return this
   */
  public function init() {
    // We base64 encode the email address because it looks akward having your own email address as part of a URL.
    $this->token = rawurlencode(base64_encode($this->recipient));
    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function getHtmlBody() {
    global $i18n;
    return
      "<p>{$i18n->t("Hi {0}!", [ $this->username ])}</p>" .
      "<p>{$i18n->t("Thank you for registering at {0}. You may now sign in and activate your new account by {1}clicking this link{2}.", [
        "MovLib",
        "<a href='{$_SERVER["SERVER"]}{$i18n->r("/users/registration")}?token={$this->token}'>",
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
   */
  protected function getPlainBody() {
    global $i18n;
    return <<<EOT
{$i18n->t("Hi {0}!", [ $this->username ])}

{$i18n->t("Thank your for registering at {0}. You may now sign in and activate your new account by clicking the following link or copying and pasting it to your browser:", [ "MovLib" ])}

{$_SERVER["SERVER"]}{$i18n->r("/users/registration")}?token={$this->token}

{$i18n->t("This link can only be used once within the next 24 hours.")}
{$i18n->t("You will be able to sign in with the following data:")}

{$i18n->t("Email Address")}:  '{$this->recipient}'
{$i18n->t("Password")}:       '{$i18n->t("Your secret password")}'

{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
