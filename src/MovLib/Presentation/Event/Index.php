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
namespace MovLib\Presentation\Event;

use \MovLib\Data\Event\EventSet;
use \MovLib\Partial\Alert;

/**
 * Defines the event index presentation.
 *
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/events
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/events
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/events
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/events
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {
  use \MovLib\Partial\EventTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this->initIndex(
      new EventSet($this->diContainerHTTP),
      $this->intl->t("Create New Event"),
      $this->intl->t("Events"),
      "events",
      "event"
    );
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Event\Event $event {@inheritdoc}
   */
  protected function formatListingItem(\MovLib\Data\AbstractEntity $event, $delta) {
    return
      "<li class='hover-item r'>" .
        "<article>" .
          "<a class='no-link s s1' href='{$event->route}'>" .
            "<img alt='{$event->name}' src='{$this->getExternalURL("asset://img/logo/vector.svg")}' width='60' height='60'>" .
          "</a>" .
          "<div class='s s9'>" .
            "<div class='fr'>" .
              "<a class='ico ico-movie label' href='{$this->intl->rp("/event/{0}/movies", $event->id)}' title='{$this->intl->t("Movies")}'>{$event->movieCount}</a>" .
              "<a class='ico ico-series label' href='{$this->intl->rp("/event/{0}/series", $event->id)}' title='{$this->intl->t("Series")}'>{$event->seriesCount}</a>" .
            "</div>" .
            "<h2 class='para'><a href='{$event->route}' property='url'><span property='name'>{$event->name}</span></a></h2>" .
            "<small>{$this->getEventDates($event)} {$this->getEventPlace($event)}</small>" .
          "</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return new Alert(
      "<p>{$this->intl->t("We couldn’t find any events matching your filter criteria, or there simply aren’t any events available.")}</p>" .
      "<p>{$this->intl->t("Would you like to {0}create an event{1}?", [ "<a href='{$this->intl->r("/event/create")}'>", "</a>" ])}</p>",
      $this->intl->t("No Events")
    );
  }

}
