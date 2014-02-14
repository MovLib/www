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
namespace MovLib\Presentation\Email\User;

use \MovLib\Data\Temporary;

/**
 * This email template is used if a user requests account deactivation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Deletion extends \MovLib\Presentation\Email\AbstractEmail {


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
   * @param \MovLib\Data\Full $user
   *   The user who requested deletion.
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
   * Initialize email properties.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  public function init() {
    global $i18n, $kernel;
    $this->recipient = $this->user->email;
    $this->subject   = $i18n->t("Reqeusted Deletion");
    $token           = (new Temporary())->set([
      "user_id"  => $this->user->id,
      "deletion" => true,
    ]);
    $this->link      = "{$kernel->scheme}://{$kernel->hostname}{$kernel->requestURI}?{$i18n->r("token")}={$token}";
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getHTML() {
    global $i18n;
    return
      "<p>{$i18n->t("Hi {0}!", [ $this->user->name ])}</p>" .
      "<p>{$i18n->t("You (or someone else) requested to delete your account.")} {$i18n->t("You may now confirm this action by {0}clicking this link{1}.", [
        "<a href='{$this->link}'>",
        "</a>"
      ])}</p>" .
      "<p>{$i18n->t("This link can only be used once within the next 24 hours.")} {$i18n->t(
        "Once you click the link above your account will be deleted and all your personal data will be purged. " .
        "Please note that you won’t be able to sign in anymore and there is no possibility to reclaim your account " .
        "later on."
      )}<br>" .
      "{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}</p>"
    ;
  }

  /**
   * @inheritdoc
   */
  public function getPlainText() {
    global $i18n;
    return <<<EOT
{$i18n->t("Hi {0}!", [ $this->user->name ])}

{$i18n->t("You (or someone else) requested to deactivate your account.")} {$i18n->t("You may now confirm this action by clicking the following link or copying and pasting it to your browser:")}

{$this->link}

{$i18n->t("This link can only be used once within the next 24 hours.")} {$i18n->t(
  "Once you click the link above your account will be deleted and all your personal data will be purged. " .
  "Please note that you won’t be able to sign in anymore and there is no possibility to reclaim your account " .
  "later on."
)}
{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
