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

use \MovLib\Data\User\User;

/**
 * This email template is used if someone tries to register an email address that is already taken by another user. We
 * inform the owner of this email address about the registration attempt.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class RegistrationEmailExists extends \MovLib\Presentation\Email\AbstractEmail {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The name of the user the email belongs to.
   *
   * @var string
   */
  private $name;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new registration email exists email.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $email
   *   The valid email address of the registered user.
   */
  public function __construct($email) {
    global $i18n;
    parent::__construct($email, $i18n->t("Forgot Your Password?"));
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize email properties.
   *
   * @return this
   */
  public function init() {
    $this->name = (new User(User::FROM_EMAIL, $this->recipient))->name;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getHTML() {
    global $i18n;
    return
      "<p>{$i18n->t("Hi {0}!", [ $this->name ])}</p>" .
      "<p>{$i18n->t("You (or someone else) tried to sign up a new account with this email address. If you forgot your password go to the {0}reset password{1} page to request a new one.", [
        "<a href='{$_SERVER["SERVER"]}{$i18n->r("/user/reset-password")}'>", "</a>"
      ])}</p>" .
      "<p>{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}</p>"
    ;
  }

  /**
   * @inheritdoc
   */
  public function getPlainText() {
    global $i18n;
    return <<<EOT
{$i18n->t("Hi {0}!", [ $this->name ])}

{$i18n->t("You (or someone else) tried to sign up a new account with this email address. If you forgot your password go to the reset password page to request a new one.")}

{$_SERVER["SERVER"]}{$i18n->r("/user/reset-password")}

{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }
}
