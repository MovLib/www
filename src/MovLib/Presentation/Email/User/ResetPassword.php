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
namespace MovLib\Presentation\Email\User;

use \MovLib\Data\UserExtended;
use \MovLib\Exception\MailerException;
use \MovLib\Exception\UserException;

/**
 * Description of ResetPassword
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ResetPassword extends \MovLib\Presentation\Email\AbstractEmail {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user who requested the password reset.
   *
   * @var \MovLib\Data\UserExtended
   */
  private $user;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user reset password email.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $email
   *   The user submitted email address.
   */
  public function __construct($email) {
    global $i18n;
    parent::__construct($email, $i18n->t("Requested Password Reset"));
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize email properties.
   *
   * @return this
   */
  public function init() {
    try {
      $this->user = new UserExtended(UserExtended::FROM_EMAIL, $this->recipient);
      $this->user->setAuthenticationToken()->prepareTemporaryData("d", [ "id" ], [ $this->user->id ]);
    }
    catch (UserException $e) {
      throw new MailerException("User with user for email {$this->recipient}.", $e);
    }
    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function getHtmlBody() {
    global $i18n;
    return
      "<p>{$i18n->t("Hi {0}!", [ $this->user->name ])}</p>" .
      "<p>{$i18n->t("You (or someone else) requested to reset your password.")} {$i18n->t("You may now confirm this action by {0}clicking this link{1}.", [
        "<a href='{$_SERVER["SERVER"]}{$i18n->r("/user/password-settings")}?{$i18n->t("token")}={$this->user->authenticationToken}'>", "</a>"
      ])}</p>" .
      "<p>{$i18n->t("This link can only be used once within the next 24 hours.")} {$i18n->t("Once you click the link above, you won’t be able to sign in with your old password.")}<br/>" .
      "{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}</p>"
    ;
  }

  /**
   * @inheritdoc
   */
  protected function getPlainBody() {
    global $i18n;
    return <<<EOT
{$i18n->t("Hi {0}!", [ $this->user->name ])}

{$i18n->t("You (or someone else) requested to reset your password.")} {$i18n->t("You may now confirm this action by clicking the following link or copying and pasting it to your browser:")}

{$_SERVER["SERVER"]}{$i18n->r("/user/password-settings")}?{$i18n->t("token")}={$this->user->authenticationToken}

{$i18n->t("This link can only be used once within the next 24 hours.")} {$i18n->t("Once you click the link above, you won’t be able to sign in with your old password.")}
{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
