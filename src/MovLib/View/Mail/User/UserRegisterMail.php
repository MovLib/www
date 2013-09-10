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
namespace MovLib\View\Mail\User;

use \MovLib\View\Mail\AbstractMail;

/**
 * This mail is sent to user's after registering a new account at MovLib.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserRegisterMail extends AbstractMail {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The activation hash of the user.
   *
   * @var string
   */
  private $hash;

  /**
   * The valid name of the user.
   *
   * @var string
   */
  private $name;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new mail.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param string $hash
   *   The activation hash of the user.
   * @param string $name
   *   The valid name of the user.
   * @param string $mail
   *   The valid mail of the user.
   */
  public function __construct($hash, $name, $mail) {
    global $i18n;
    parent::__construct($mail, $i18n->t("Welcome to MovLib!"));
    $this->hash = $hash;
    $this->name = $name;
  }

  /**
   * @inheritdoc
   */
  protected function getHtmlBody() {
    global $i18n;
    return
      "<p>{$i18n->t("Hi {0}!", [ $this->name ])}</p>" .
      "<p>{$i18n->t("Thank you for registering at MovLib. You may now sign in by {0}clicking this link{1}.", [
        "<a href='{$i18n->r("/user/register")}?{$i18n->t("token")}={$this->hash}'>",
        "</a>"
      ])}</p>" .
      "<p>{$i18n->t("This link can only be used once within the next 24 hours and will lead you to a page where you can set your secret password.")}</p>" .
      "<p>{$i18n->t("After setting your password, you will be able to sign in at MovLib in the future using:")}</p>" .
      "<table>" .
        "<tr><td>{$i18n->t("Email Address")}:</td><td>{$this->recipient}</td><tr>" .
        "<tr><td>{$i18n->t("Password")}:</td><td><em>{$i18n->t("Your Secret Password")}</em></td></tr>" .
      "</table>"
    ;
  }

  /**
   * @inheritdoc
   */
  protected function getPlainBody() {
    global $i18n;
    return <<<EOT
{$i18n->t("Hi {0}!", [ $this->name ])}

{$i18n->t("Thank your for registering at MovLib. You may now sign in by clicking the following link or copying and pasting it to your browser:")}

{$i18n->r("/user/register")}?{$i18n->t("token")}={$this->hash}

{$i18n->t("This link can only be used once within the next 24 hours and will lead you to a page where you can set your secret password.")}

{$i18n->t("After setting your password, you will be able to sign in at MovLib in the future using:")}
{$i18n->t("Email Address")}:  '{$i18n->t("Your Email Address")}'
{$i18n->t("Password")}:       '{$i18n->t("Your Secret Password")}'
EOT;
  }

}
