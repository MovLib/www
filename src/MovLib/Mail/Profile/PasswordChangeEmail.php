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

/**
 * This email template is used if a user requests a password change.
 *
 * @see \MovLib\Presentation\User\PasswordSettings
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PasswordChangeEmail extends \MovLib\Mail\AbstractEmail {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user who requested the password change.
   *
   * @var \MovLib\Data\User\FullUser
   */
  protected $user;

  /**
   * The user's new unhashed password.
   *
   * @var string
   */
  protected $rawPassword;

  /**
   * The user's unique link to confirm the password change.
   *
   * @var string
   */
  protected $link;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user password change email.
   *
   * @param \MovLib\Data\User\User $user
   *   The user who requested the password change.
   * @param string $rawPassword
   *   The new unhashed password.
   */
  public function __construct(\MovLib\Data\User\User $user, $rawPassword) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($rawPassword), "\$rawPassword cannot be empty.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->user        = $user;
    $this->rawPassword = $rawPassword;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP) {
    parent::init($diContainerHTTP);
    $this->recipient = $this->user->email;
    $this->subject = $this->intl->t("Requested Password Change");
    $token = (new TemporaryStorage($diContainerHTTP))->set((object) [
      "userId"      => $this->user->id,
      "newPassword" => $this->rawPassword,
    ]);
    $this->link = $this->presenter->url($this->request->path, [ $this->intl->r("token") => $token ]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getHTML() {
    return
      "<p>{$this->intl->t("Hi {0}!", [ $this->user->name ])}</p>" .
      "<p>{$this->intl->t("You (or someone else) requested to change your account’s password.")} {$this->intl->t("You may now confirm this action by {0}clicking this link{1}.", [
        "<a href='{$this->link}'>",
        "</a>"
      ])}</p>" .
      "<p>{$this->intl->t("This link can only be used once within the next 24 hours.")} {$this->intl->t("Once you click the link above, you won’t be able to sign in with your old password.")}<br>" .
      "{$this->intl->t("If it wasn’t you who requested this action simply ignore this message.")}</p>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlainText() {
    return <<<EOT
{$this->intl->t("Hi {0}!", [ $this->user->name ])}

{$this->intl->t("You (or someone else) requested to change your account’s password.")} {$this->intl->t("You may now confirm this action by clicking the following link or copying and pasting it to your browser:")}

{$this->link}

{$this->intl->t("This link can only be used once within the next 24 hours.")} {$this->intl->t("Once you click the link above, you won’t be able to sign in with your old password.")}
{$this->intl->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
