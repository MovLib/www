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

use \MovLib\Data\Award;
use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\Place;

/**
 * Base presenation of all event pages.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitGone {
    goneGetContent as private traitGetGoneContent;
  }
  use \MovLib\Presentation\TraitSidebar {
    sidebarInit as traitSidebarInit;
  }


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award the event belongs to.
   *
   * @var \MovLib\Data\Award
   */
  protected $award;

  /**
   * The event to present.
   *
   * @var \MovLib\Data\Event
   */
  protected $event;


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * Build the Header of an Event
   *
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   */
  protected function getEventHeader() {
    global $i18n;

    // Enhance the page title with microdata.
    $this->schemaType = "Intangible";
    $this->pageTitle  = "<span property='name'>{$this->event->name}</span>";

    if ($this->event->deleted === true) {
      return $this->goneGetContent();
    }

    // Put the event information together.
    $info = null;
    if (($this->event->startDate && $this->event->endDate) && ($this->event->startDate != $this->event->endDate)) {
      $info .= "{$i18n->t("from {0} to {1}", [
        (new Date($this->event->startDate))->format(),
        (new Date($this->event->endDate))->format()
      ])} ";
    }
    else if ($this->event->startDate) {
      $info .= "{$i18n->t("on {0}", [ (new Date($this->event->startDate))->format() ])} ";
    }
    if ($this->event->place) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= $i18n->t("in {0}", [ new Place($this->event->place) ]);
    }
    $info   .= "<br>{$i18n->t("Award")}: <a href='{$this->award->route}'>{$this->award->name}</a>";

    // Construct the wikipedia link.
    if ($this->event->wikipedia) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= "<span class='ico ico-wikipedia'></span><a href='{$this->event->wikipedia}' itemprop='sameAs' target='_blank'>{$i18n->t("Wikipedia Article")}</a>";
    }

    $headerImage = $this->getImage($this->award->getStyle(Award::STYLE_SPAN_02), $this->award->route, [ "itemprop" => "image" ]);
    $this->headingBefore = "<div class='r'><div class='s s10'>";
    $this->headingAfter = "<p>{$info}</p></div><div id='award-logo' class='s s2'>{$headerImage}</div></div>";
  }

  /**
   * Build content for gone page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   * @throws \LogicException
   */
  protected function goneGetContent() {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this->award instanceof \MovLib\Data\Award)) {
      throw new \LogicException($i18n->t("\$this->award has to be a valid award object!"));
    }
    if (!($this->event instanceof \MovLib\Data\Event)) {
      throw new \LogicException($i18n->t("\$this->awardEvent has to be a valid award event object!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $routeArgs = [ $this->event->id ];

    $this->goneAlertMessage = $i18n->t(
        "The event and all its content have been deleted. Take a look at the {0}history{2} or {1}discussion{2} page " .
        "for further information. Please discuss with the person responsible for this deletion before " .
        "you restore this entry from its {0}history{2}.",
        [
          "<a href='{$i18n->r("/event/{0}/history", $routeArgs)}'>",
          "<a href='{$i18n->r("/event/{0}/discussion", $routeArgs)}'>",
          "</a>"
        ]
      );
    return $this->traitGetGoneContent();
  }

  /**
   * Init event breadcrumb.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   * @throws \LogicException
   */
  protected function initEventBreadcrumb() {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this->award instanceof \MovLib\Data\Award)) {
      throw new \LogicException($i18n->t("\$this->award has to be a valid award object!"));
    }
    if (!($this->event instanceof \MovLib\Data\Event)) {
      throw new \LogicException($i18n->t("\$this->awardEvent has to be a valid award event object!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    return $this->initBreadcrumb([
      [ $i18n->rp("/events"), $i18n->t("Events") ],
      [ $this->event->route, $this->event->name ],
    ]);
  }

  /**
   * Init award sidebar.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   * @throws \LogicException
   */
  protected function sidebarInit() {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this->award instanceof \MovLib\Data\Award)) {
      throw new \LogicException($i18n->t("\$this->award has to be a valid award object!"));
    }
    if (!($this->event instanceof \MovLib\Data\Event)) {
      throw new \LogicException($i18n->t("\$this->awardEvent has to be a valid award event object!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Compile array once.
    $routeArgs = [ $this->event->id ];

    // Reduce the sidebar if the event was deleted.
    if ($this->event->deleted === true) {
      return $this->traitSidebarInit([
        [ $this->event->route, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
        [ $i18n->r("/event/{0}/discussion", $routeArgs), $i18n->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
        [ $i18n->r("/event/{0}/history", $routeArgs), $i18n->t("History"), [ "class" => "ico ico-history" ] ]
      ]);
    }

    return $this->traitSidebarInit([
      [ $this->event->route, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
      [ $i18n->r("/event/{0}/discussion", $routeArgs), $i18n->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
      [ $i18n->r("/event/{0}/edit", $routeArgs), $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $i18n->r("/event/{0}/history", $routeArgs), $i18n->t("History"), [ "class" => "ico ico-history" ] ],
      [ $i18n->r("/event/{0}/delete", $routeArgs), $i18n->t("Delete"), [ "class" => "ico ico-delete separator" ] ],

      [ $i18n->rp("/event/{0}/movies", $routeArgs), "{$i18n->t("Movies")} <span class='fr'>{$i18n->format("{0,number}", [ $this->award->getMoviesCount() ])}</span>", [ "class" => "ico ico-movie" ] ],
      [ $i18n->rp("/event/{0}/series", $routeArgs), "{$i18n->t("Series")} <span class='fr'>{$i18n->format("{0,number}", [ $this->award->getSeriesCount() ])}</span>", [ "class" => "ico ico-series separator" ] ],
    ]);
  }

}
