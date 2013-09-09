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
 * Template for mails that ask the user to confirm the mail change.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserMailChangeMail extends AbstractMail {

  /**
   * The user's authentication token.
   *
   * @var string
   */
  private $hash;

  /**
   * The user's name.
   *
   * @var string
   */
  private $name;

  public function __construct($hash, $name, $newMail) {
    global $i18n;
    parent::__construct($newMail, $i18n->t("Requested Mail Change"));
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
      "<p>{$i18n->t("You (or someone else) requested a mail change for your account. You may now confirm your new email address by {0}clicking this link{1}.", [
        "<a href='{$i18n->r("/user/mail-settings")}?{$i18n->t("token")}={$this->hash}'>",
        "</a>"
      ])}</p>" .
      "<p>{$i18n->t("This link can only be used once within the next 24 hours to confirm the change. Once you click the link above, you won’t be able to sign in with your old email address.")}</p>" .
      "<p>{$i18n->t("If it wasn’t you who requested the mail change ignore this message.")}</p>"
    ;
  }

  /**
   * @inheritdoc
   */
  protected function getPlainBody() {
    global $i18n;
    return <<<EOT
{$i18n->t("Hi {0}!", [ $this->name ])}

{$i18n->t("You (or someone else) requested a mail change for your account. You may now confirm your new email address by clicking the following link or copying and pasting it to your browser:")}

{$i18n->r("/user/mail-settings")}?{$i18n->t("token")}={$this->hash}

{$i18n->t("This link can only be used once within the next 24 hours to confirm the change. Once you click the link above, you won’t be able to sign in with your old email address.")}

{$i18n->t("If it wasn’t you who requested the mail change ignore this message.")}
EOT;
  }

}
