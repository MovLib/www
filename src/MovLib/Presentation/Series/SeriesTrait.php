<?php

/* !
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
namespace MovLib\Presentation\Series;

use \MovLib\Data\Series\Series;

/**
 * Provides properties and methods that are used by several series presenters.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait SeriesTrait {


  /**
   * {@inheritdoc}
   */
  protected function getSidebarItems() {
    $items = [];
    if ($this->entity->deleted) {
      return $items;
    }
    foreach ([
      [ "award", "awards", $this->intl->t("Awards"), $this->entity->awardCount ],
      [ "season", "seasons", $this->intl->t("Seasons"), $this->entity->seasonCount ],
      [ "release separator", "releases", $this->intl->t("Releases"), $this->entity->releaseCount ],
    ] as list($icon, $plural, $title, $count)) {
      $items[] = [
        $this->intl->r("/series/{0}/{$plural}", $this->entity->id),
        "{$title} <span class='fr'>{$this->intl->format("{0,number}", $count)}</span>",
        [ "class" => "ico ico-{$icon}" ]
      ];
    }
    return $items;
  }

  /**
   * Get the series's status.
   *
   * @return string|null
   *   The series's translated status or null.
   */
  final protected function getStatus() {
    $status = $this->getStatusArray();
    if (isset($this->entity->status) && isset($status[$this->entity->status])) {
      return $status[$this->entity->status];
    }
  }

    /**
   * Get the series's status array.
   *
   * @return array
   *   Associative array with series status codes.
   */
  final protected function getStatusArray() {
    return [
      Series::STATUS_UNKNOWN   => $this->intl->t("Unknown"),
      Series::STATUS_NEW       => $this->intl->t("New"),
      Series::STATUS_RETURNING => $this->intl->t("Returning"),
      Series::STATUS_ENDED     => $this->intl->t("Ended"),
      Series::STATUS_CANCELLED => $this->intl->t("Cancelled"),
    ];
  }

}
