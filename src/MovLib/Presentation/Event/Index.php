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

/**
 * The latest Award Events.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {

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
  protected function formatListingItem($event) {

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
