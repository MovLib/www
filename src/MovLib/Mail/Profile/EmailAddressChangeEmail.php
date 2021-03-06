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
 * This email template is used if a user requests an email change.
 *
 * @see \MovLib\Presentation\User\EmailSettings
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class EmailAddressChangeEmail extends \MovLib\Mail\AbstractEmail {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "EmailAddressChangeEmail";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's who requested the email change.
   *
   * @var \MovLib\Data\User\FullUser
   */
  private $user;

  /**
   * The user's unique link to confirm the email change.
   *
   * @var string
   */
  protected $link;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user email change email.
   *
   * @todo Should we send an email to the old address as well?
   * @param \MovLib\Data\Full $user
   *   The user who requested an email change.
   * @param string $newEmail
   *   The already validated new email address. The email will be sent to this address. We cannot send the email to the
   *   old email address, because people tend to loose their passwords and stuff, therefor it would be very difficult
   *   for them to update their email address.
   */
  public function __construct(\MovLib\Data\User\User $user, $newEmail) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($newEmail), "\$newEmail cannot be empty.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->user      = $user;
    $this->recipient = $newEmail;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init(\MovLib\Core\HTTP\Container $container) {
    parent::init($container);

    $this->subject = $this->intl->t("Requested Email Change");
    $token = (new TemporaryStorage($this->container))->set((object) [
      "userId"   => $this->user->id,
      "newEmail" => $this->recipient,
    ]);
    $this->link = $this->presenter->url($this->request->path, [ "token" => $token ], null, true);

    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function getHTML() {
    return
      "<p>{$this->intl->t("Hi {0}!", [ $this->user->name ])}</p>" .
      "<p>{$this->intl->t("You (or someone else) requested to change your account’s email address.")} {$this->intl->t(
        "You may now confirm this action by {0}clicking this link{1}.",
        [ "<a href='{$this->link}'>", "</a>" ]
      )}</p>" .
      "<p>{$this->intl->t("This link can only be used once within the next 24 hours.")} {$this->intl->t(
        "Once you click the link above, you won’t be able to sign in with your old email address."
      )}</p>" .
      "<p>{$this->intl->t("If it wasn’t you who requested this action simply ignore this message.")}</p>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlainText() {
    return <<<EOT
{$this->intl->t("Hi {0}!", [ $this->user->name ])}

{$this->intl->t("You (or someone else) requested to change your account’s email address.")} {$this->intl->t("You may now confirm this action by clicking the following link or copying and pasting it to your browser:")}

{$this->link}

{$this->intl->t("This link can only be used once within the next 24 hours.")} {$this->intl->t("Once you click the link above, you won’t be able to sign in with your old email address.")}

{$this->intl->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }
}
