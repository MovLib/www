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
namespace MovLib\Mail\Users;

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
class Join extends \MovLib\Mail\AbstractEmail {


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
   * @var \MovLib\Data\User\FullUser
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
   * @param \MovLib\Data\User\FullUser $user
   *   The user instance.
   */
  public function __construct($user) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($user instanceof \MovLib\Data\User\FullUser)) {
      throw new \InvalidArgumentException("\$user must be instance of \\MovLib\\Data\\User\\FullUser");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->user = $user;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize properties for email.
   *
   * @internal
   *   We base64 encode the email address because it looks akward having your own email address as part of a URL.
   * @return this
   */
  public function init() {
    $this->recipient = $this->user->email;
    $this->subject   = $this->intl->t("Welcome to {0}!", [ $kernel->sitename ]);
    $this->link      = "{$kernel->scheme}://{$kernel->hostname}{$this->intl->r("/profile/join")}?{$this->intl->r("token")}=" . rawurlencode(base64_encode($this->recipient));
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
   */
  public function getHTML() {
      "<p>{$this->intl->t("Hi {username}!", [ "username" => $this->user->name ])}</p>" .
      "<p>{$this->intl->t("Thank you for joining {0}. You may now sign in and activate your new account by {1}clicking this link{2}.", [
        $kernel->sitename,
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

{$this->intl->t("Thank your for joining {sitename}. You may now sign in and activate your new account by clicking the following link or copying and pasting it to your browser:", [ "sitename" => $kernel->sitename ])}

{$this->link}

{$this->intl->t("This link can only be used once within the next 24 hours.")}
{$this->intl->t("You will be able to sign in with the following data:")}

{$this->intl->t("Email Address")}:  '{$this->user->email}'
{$this->intl->t("Password")}:       '{$this->intl->t("Your secret password")}'

{$this->intl->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
