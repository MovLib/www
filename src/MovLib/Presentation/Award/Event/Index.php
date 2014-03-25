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
namespace MovLib\Presentation\Award\Event;

use \MovLib\Data\Award;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Listing\AwardEventListing;

/**
 * A event of a certain award.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\Award\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award events presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->award = new Award((integer) $_SERVER["AWARD_ID"]);
    $this->initPage($i18n->t("Events of {0}", [ $this->award->name ]));
    $this->pageTitle       = $i18n->t("Events of {0}", [ "<a href='{$this->award->route}'>{$this->award->name}</a>" ]);
    $this->breadcrumbTitle = $i18n->t("Events");
    $this->initLanguageLinks("/award/{0}/events", [ $this->award->id ], true);
    $this->initAwardBreadcrumb();
    $this->sidebarInit();

    $kernel->stylesheets[] = "award";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @return \MovLib\Presentation\Partial\Listing\EntityIndexListing
   */
  protected function getPageContent() {
    global $i18n;
    $this->headingBefore =
      "<a class='btn btn-large btn-success fr' href='{$i18n->r("/event/create")}?a={$this->award->id}'>" .
        $i18n->t("Create New Event") .
      "</a>"
    ;

    $result      = $this->award->getEventsResult();
    $noItemText  = new Alert(
      $i18n->t(
        "We couldn’t find any event matching your filter criteria, or there simply aren’t any events available."
      ), $i18n->t("No Event"), Alert::SEVERITY_INFO
    );
    $noItemText .=
      $i18n->t("<p>Would you like to {0}create a new entry{1}?</p>", [
        "<a href='{$i18n->r("/event/creat")}?a={$this->award->id}'>",
        "</a>"
      ]);

    $moviesRoute = $i18n->rp("/event/{0}/movies", [ "{{ id }}" ]);
    $seriesRoute = $i18n->rp("/event/{0}/series", [ "{{ id }}" ]);

    return new AwardEventListing($result, $noItemText, "Event", $moviesRoute, $seriesRoute);
  }

}
