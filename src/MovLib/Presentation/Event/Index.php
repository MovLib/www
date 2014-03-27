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

use \MovLib\Data\Event;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Listing\AwardEventListing;

/**
 * The latest Award Events.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitSidebar;
  use \MovLib\Presentation\TraitPagination;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new latest award events presentation.
   *
   */
  public function __construct() {
    $this->initPage($this->intl->t("Events"));
    $this->initBreadcrumb();
    $this->initLanguageLinks("/events", null, true);
    $this->paginationInit(Event::getTotalCount());
    $this->sidebarInit([
      [ $kernel->requestPath, $this->title, [ "class" => "ico ico-event" ] ],
      [ $this->intl->r("/event/random"), $this->intl->t("Random") ],
    ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    $this->headingBefore =
      "<a class='btn btn-large btn-success fr' href='{$this->intl->r("/event/create")}'>{$this->intl->t("Create New Event")}</a>"
    ;

    $result      = Event::getEvents($this->paginationOffset, $this->paginationLimit);
    $noItemText  = new Alert(
      $this->intl->t(
        "We couldn’t find any events matching your filter criteria, or there simply aren’t any events available."
      ), $this->intl->t("No Events"), Alert::SEVERITY_INFO
    );
    $noItemText .=
      $this->intl->t("<p>Would you like to {0}create a new entry{1}?</p>", [ "<a href='{$this->intl->r("/award/create")}'>", "</a>" ]);

    $moviesRoute = $this->intl->rp("/event/{0}/movies", [ "{{ id }}" ]);
    $seriesRoute = $this->intl->rp("/event/{0}/series", [ "{{ id }}" ]);

    return new AwardEventListing($result, $noItemText, "Event", $moviesRoute, $seriesRoute);
  }

}
