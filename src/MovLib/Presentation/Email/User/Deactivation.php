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

/**
 * This email template is used if a user requests account deactivation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Deactivation extends \MovLib\Presentation\Email\AbstractEmail {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user who requested deactivation.
   *
   * @var \MovLib\Data\Full
   */
  private $user;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user deactivation email.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Full $user
   *   The user who requested deactivation.
   */
  public function __construct($user) {
    global $i18n;
    parent::__construct($user->email, $i18n->t("Requested Deactivation"));
    $this->user = $user;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize email properties.
   *
   * @return this
   */
  public function init() {
    $this->user->setAuthenticationToken()->prepareTemporaryData("d", [ "id" ], [ $this->user->id ]);
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getHTML() {
    global $i18n;
    return
      "<p>{$i18n->t("Hi {0}!", [ $this->user->name ])}</p>" .
      "<p>{$i18n->t("You (or someone else) requested to deactivate your account.")} {$i18n->t("You may now confirm this action by {0}clicking this link{1}.", [
        "<a href='{$_SERVER["SERVER"]}{$i18n->r("/user/danger-zone-settings")}?{$i18n->t("token")}={$this->user->authenticationToken}'>",
        "</a>"
      ])}</p>" .
      "<p>{$i18n->t("This link can only be used once within the next 24 hours.")} {$i18n->t("Once you click the link above your account will be deactivated and all your personal data will be purged.")}<br>" .
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

{$_SERVER["SERVER"]}{$i18n->r("/user/danger-zone-settings")}?{$i18n->t("token")}={$this->user->authenticationToken}

{$i18n->t("This link can only be used once within the next 24 hours.")} {$i18n->t("Once you click the link above your account will be deactivated and all your personal data will be purged.")}
{$i18n->t("If it wasn’t you who requested this action simply ignore this message.")}
EOT;
  }

}
