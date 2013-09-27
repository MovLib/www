<?php

/* !
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
   * The new user.
   *
   * @var \MovLib\Data\User
   */
  private $user;

  /**
   * The base64 encoded authentication token for this registration.
   *
   * @var string
   */
  private $token;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create registration email for activation of account.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\User $user
   *   The user to which we should send the activation mail.
   */
  public function __construct($user) {
    global $i18n;
    parent::__construct($user->email, $i18n->t("Welcome to {0}!", [ "MovLib" ]));
    $this->user = $user;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize email properties.
   *
   * @return this
   */
  public function init() {
    $this->token = base64_encode($this->user->prepareRegistration());
    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function getHtmlBody() {
    global $i18n;
    return
      "<p>{$i18n->t("Hi {0}!", [ $this->user->name ])}</p>" .
      "<p>{$i18n->t("Thank you for registering at {0}. You may now sign in by {1}clicking this link{2}.", [
        "MovLib",
        "<a href='{$_SERVER["SERVER"]}{$i18n->r("/user/registration")}?{$i18n->t("token")}={$this->user->authenticationToken}'>",
        "</a>"
      ])}</p>" .
      "<p>{$i18n->t("This link can only be used once within the next 24 hours and will lead you to a page where you can view (and change) your secret password.")}<br>" .
      "{$i18n->t("After setting your password, you will be able to sign in at MovLib in the future using:")}</p>" .
      "<table>" .
        "<tr><td>{$i18n->t("Email Address")}:</td><td>{$this->recipient}</td><tr>" .
        "<tr><td>{$i18n->t("Password")}:</td><td><em>{$i18n->t("Your Secret Password")}</em></td></tr>" .
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
{$i18n->t("Hi {0}!", [ $this->user->name ])}

{$i18n->t("Thank your for registering at {0}. You may now sign in by clicking the following link or copying and pasting it to your browser:", [ "MovLib" ])}

{$_SERVER["SERVER"]}{$i18n->r("/user/registration")}?{$i18n->t("token")}={$this->user->authenticationToken}

{$i18n->t("This link can only be used once within the next 24 hours and will lead you to a page where you can view (and change) your secret password.")}

{$i18n->t("After setting your password, you will be able to sign in at MovLib in the future using:")}
{$i18n->t("Email Address")}:  '{$i18n->t("Your Email Address")}'
{$i18n->t("Password")}:       '{$i18n->t("Your Secret Password")}'

{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
