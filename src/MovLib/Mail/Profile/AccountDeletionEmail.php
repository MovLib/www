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
 * Defines the email that is sent if a user wishes to delete the account.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class AccountDeletionEmail extends \MovLib\Mail\AbstractEmail {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AccountDeletionEmail";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user who requested deletion.
   *
   * @var \MovLib\Data\User\FullUser
   */
  protected $user;

  /**
   * The user's unique link to confirm the deletion.
   *
   * @var string
   */
  protected $link;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user deletion email.
   *
   * @param \MovLib\Data\User\User $user
   *   The user who requested deletion.
   */
  public function __construct(\MovLib\Data\User\User $user) {
    $this->user = $user;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init(\MovLib\Core\HTTP\Container $container) {
    parent::init($container);
    $this->recipient = $this->user->email;
    $this->subject   = $this->intl->t("Requested Deletion");
    $token           = (new TemporaryStorage($this->container))->set($this->user->id);
    $this->link      = $this->presenter->url($this->request->path, [ "token" => $token ], null, true);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function getHTML() {
    return
      "<p>{$this->intl->t("Hi {0}!", [ $this->user->name ])}</p>" .
      "<p>{$this->intl->t("You (or someone else) requested to delete your account.")} {$this->intl->t(
        "You may now confirm this action by {0}clicking this link{1}.",
        [ "<a href='{$this->link}'>", "</a>" ]
      )}</p>" .
      "<p>{$this->intl->t("This link can only be used once within the next 24 hours.")} {$this->intl->t(
        "Once you click the link above your account will be deleted and all your personal data will be purged. " .
        "Please note that you won’t be able to sign in anymore and there is no possibility to reclaim your account " .
        "later on."
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

{$this->intl->t("You (or someone else) requested to deactivate your account.")} {$this->intl->t("You may now confirm this action by clicking the following link or copying and pasting it to your browser:")}

{$this->link}

{$this->intl->t("This link can only be used once within the next 24 hours.")} {$this->intl->t(
  "Once you click the link above your account will be deleted and all your personal data will be purged. " .
  "Please note that you won’t be able to sign in anymore and there is no possibility to reclaim your account " .
  "later on."
)}

{$this->intl->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
