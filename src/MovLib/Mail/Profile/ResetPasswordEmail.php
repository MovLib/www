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
namespace MovLib\Mail\Profile;

use \MovLib\Data\TemporaryStorage;
use \MovLib\Data\User\User;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * @todo Description of ResetPassword
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ResetPasswordEmail extends \MovLib\Mail\AbstractEmail {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "ResetPasswordEmail";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user who requested the password reset.
   *
   * @var \MovLib\Data\User\User
   */
  protected $user;

  /**
   * The user's unique link to reset the password.
   *
   * @var string
   */
  protected $link;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user reset password email.
   *
   * @param string $email
   *   The user submitted email address.
   */
  public function __construct($email) {
    $this->recipient = $email;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init(\MovLib\Core\HTTP\Container $container) {
    parent::init($container);
    try {
      $this->user    = new User($container, $this->recipient, User::FROM_EMAIL);
      $this->subject = $this->intl->t("Requested password reset");
      $token         = (new TemporaryStorage($container))->set($this->user->id);
      $this->link    = $this->presenter->url($this->intl->r("/profile/reset-password"), [ "token" => $token ], null, true);
    }
    catch (NotFoundException $e) {
      $this->log->warning("Password reset request for unknown email address.");
      return false;
    }
    return true;
  }

  /**
   * @inheritdoc
   */
  public function getHTML() {
    return
      "<p>{$this->intl->t("Hi {0}!", [ $this->user->name ])}</p>" .
      "<p>{$this->intl->t("You (or someone else) requested to reset your password.")} {$this->intl->t("You may now confirm this action by {0}clicking this link{1}.", [
        "<a href='{$this->link}'>", "</a>"
      ])}</p>" .
      "<p>{$this->intl->t("This link can only be used once within the next 24 hours.")} {$this->intl->t("Once you click the link above, you won’t be able to sign in with your old password.")}<br/>" .
      "{$this->intl->t("If it wasn’t you who requested this action simply ignore this message.")}</p>"
    ;
  }

  /**
   * @inheritdoc
   */
  public function getPlainText() {
    return <<<EOT
{$this->intl->t("Hi {0}!", [ $this->user->name ])}

{$this->intl->t("You (or someone else) requested to reset your password.")} {$this->intl->t("You may now confirm this action by clicking the following link or copying and pasting it to your browser:")}

{$this->link}

{$this->intl->t("This link can only be used once within the next 24 hours.")} {$this->intl->t("Once you click the link above, you won’t be able to sign in with your old password.")}
{$this->intl->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
