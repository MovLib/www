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

use \MovLib\Data\Temporary;

/**
 * Email template that is sent to clients after successfully joining MovLib.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Join extends \MovLib\Presentation\Email\AbstractEmail {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's <b>raw</b> password.
   *
   * @var string
   */
  protected $rawPassword;

  /**
   * The user's name.
   *
   * @var \MovLib\Data\User\Full
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
   * @param \MovLib\Data\User\Full $user
   *   The user instance.
   */
  public function __construct($user) {
    $this->user = $user;
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
    $this->recipient = $this->user->email;
    $this->subject   = $i18n->t("Welcome to {0}!", [ $kernel->siteName ]);
    $this->link      = "{$kernel->scheme}://{$kernel->hostname}{$i18n->r("/profile/join")}?{$i18n->r("token")}=" . rawurlencode(base64_encode($this->recipient));
    $key             = "jointoken{$this->user->email}";
    $tmp             = new Temporary();
    $user            = $tmp->get($key);
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
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function getHTML() {
    global $i18n, $kernel;
    return
      "<p>{$i18n->t("Hi {username}!", [ "username" => $this->user->name ])}</p>" .
      "<p>{$i18n->t("Thank you for joining {sitename}. You may now sign in and activate your new account by {1}clicking this link{2}.", [
        "sitename" => $kernel->siteName,
        "<a href='{$this->link}'>",
        "</a>"
      ])}</p>" .
      "<p>{$i18n->t("This link can only be used once within the next 24 hours.")}<br>" .
      "{$i18n->t("You will be able to sign in with the following data:")}</p>" .
      "<table>" .
        "<tr><td>{$i18n->t("Email Address")}:</td><td>{$this->user->email}</td><tr>" .
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
{$i18n->t("Hi {username}!", [ "username" => $this->user->name ])}

{$i18n->t("Thank your for joining {sitename}. You may now sign in and activate your new account by clicking the following link or copying and pasting it to your browser:", [ "sitename" => $kernel->siteName ])}

{$this->link}

{$i18n->t("This link can only be used once within the next 24 hours.")}
{$i18n->t("You will be able to sign in with the following data:")}

{$i18n->t("Email Address")}:  '{$this->user->email}'
{$i18n->t("Password")}:       '{$i18n->t("Your secret password")}'

{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
