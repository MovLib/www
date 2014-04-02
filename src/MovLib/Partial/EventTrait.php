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
namespace MovLib\Partial;

use \MovLib\Partial\DateTime;
use \MovLib\Partial\Place;

/**
 * @todo Description of EventTrait
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait EventTrait {

  /**
   * Get the event's first and last awarding years.
   *
   * @param \MovLib\Data\Event\Event $event
   *   The event to get the dates from.
   * @return string
   *   The formatted start and end dates or <code>NULL</code> if none were present.
   */
  protected function getEventDates(\MovLib\Data\Event\Event $event) {
    $dates = null;
    if (($event->startDate && $event->endDate) && ($event->startDate != $event->endDate)) {
      $dates = "{$this->intl->t("from {0} to {1}", [
        new DateTime($this->diContainerHTTP, $event->startDate),
        new DateTime($this->diContainerHTTP, $event->endDate)
      ])} ";
    }
    else if ($event->startDate) {
      $dates = "{$this->intl->t("on {0}", [ new DateTime($this->diContainerHTTP, $event->startDate) ])} ";
    }
    return $dates;
  }

  /**
   * Get the event's place.
   *
   * @param \MovLib\Data\Event\Event $event
   *   The event to get the place from.
   * @return string
   *   The formatted place or <code>NULL</code> if there was none.
   */
  protected function getEventPlace(\MovLib\Data\Event\Event $event) {
    $place = null;
    if ($event->place) {
      $place = $this->intl->t("in {0}", [ new Place($this->diContainerHTTP->presenter, $this->diContainerHTTP->intl, $event->place) ]);
    }
    return $place;
  }

}
