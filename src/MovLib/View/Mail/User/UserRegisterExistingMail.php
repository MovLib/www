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

use \MovLib\Model\UserModel;
use \MovLib\View\Mail\AbstractMail;

/**
 * This mail is sent to user if somebody tries to register a new account with an existing mail.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserRegisterExistingMail extends AbstractMail {

  /**
   * The model of the user to which we'll send the mail.
   *
   * @var \MovLib\Model\UserModel
   */
  private $user;

  /**
   * Instantiate new mail.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param string $mail
   *   The valid mail.
   */
  public function __construct($mail) {
    global $i18n;
    parent::__construct($mail, $i18n->t("Forgot your password?"));
  }

  /**
   * Initialize properties of this mail.
   */
  public function init() {
    $this->user = new UserModel(UserModel::FROM_MAIL, $this->recipient);
  }

  /**
   * @inheritdoc
   */
  protected function getHtmlBody() {
    global $i18n;
    return
      "<p>{$i18n->t("Hi {0}!", [ $this->user->name ])}</p>" .
      "<p>{$i18n->t("You (or someone else) requested a new account with this email address. If you forgot your password visit the {0}reset password{1} page.", [
        "<a href='{$i18n->r("/user/reset-password")}'>",
        "</a>"
      ])}</p>" .
      "<p>{$i18n->t("If it wasn’t you who requested a new account ignore this message.")}</p>"
    ;
  }

  /**
   * @inheritdoc
   */
  protected function getPlainBody() {
    global $i18n;
    return <<<EOT
{$i18n->t("Hi {0}!", [ $this->user->name ])}

{$i18n->t("You (or someone else) requested a new account with this email address. If you forgot your password visit the “reset password” page:")}

{$i18n->r("/user/reset-password")}

{$i18n->t("If it wasn’t you who requested a new account ignore this message.")}
EOT;
  }

}
