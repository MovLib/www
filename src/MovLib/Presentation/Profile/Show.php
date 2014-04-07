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
namespace MovLib\Presentation\Profile;

use \MovLib\Partial\DateTime;

/**
 * Defines the profile show presenter.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Show extends \MovLib\Presentation\Profile\AbstractProfilePresenter {

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this->initProfilePresentation(
      $this->intl->t("You must be signed in to view your profile."),
      $this->intl->t("My Profile"),
      "/profile",
      true
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $dateTime = new DateTime($this->intl, $this, $this->user->timezone);
    return
      "<h2>{$this->intl->t("Your Account Summary")}</h2>" .
      "<div class='r'>" .
        "<dl class='dl--horizontal s s7'>" .
          "<dt>{$this->intl->t("Username")}</dt><dd>{$this->user->name}</dd>" .
          "<dt>{$this->intl->t("User ID")}</dt><dd>{$this->user->id}</dd>" .
          "<dt>{$this->intl->t("Edits")}</dt><dd>{$this->user->edits}</dd>" .
          "<dt>{$this->intl->t("Reputation")}</dt><dd>{$this->user->reputation}</dd>" .
          "<dt>{$this->intl->t("Email Address")}</dt><dd>{$this->user->email}</dd>" .
          "<dt>{$this->intl->t("Joined")}</dt><dd>{$dateTime->format($this->user->created)}</dd>" .
          "<dt>{$this->intl->t("Last visit")}</dt><dd>{$dateTime->format($this->user->access)}</dd>" .
        "</dl>" .
        "<div class='s s2'>{$this->img($this->user->imageGetStyle())}</div>" .
      "</div>"
    ;
  }

}
