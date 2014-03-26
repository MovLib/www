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
namespace MovLib\Presentation\Award;

/**
 * Base presenation of all award pages.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitGone {
    goneGetContent as private traitGetGoneContent;
  }
  use \MovLib\Presentation\TraitSidebar {
    sidebarInit as traitSidebarInit;
  }


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award to present.
   *
   * @var \MovLib\Data\Award
   */
  protected $award;


  // ------------------------------------------------------------------------------------------------------------------- Methods


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
    // @codeCoverageIgnoreEnd
    // @devEnd

    $routeArgs = [ $this->award->id ];

    $this->goneAlertMessage = $i18n->t(
        "The award and all its content have been deleted. Take a look at the {0}history{2} or {1}discussion{2} page " .
        "for further information. Please discuss with the person responsible for this deletion before " .
        "you restore this entry from its {0}history{2}.",
        [
          "<a href='{$i18n->r("/award/{0}/history", $routeArgs)}'>",
          "<a href='{$i18n->r("/award/{0}/discussion", $routeArgs)}'>",
          "</a>"
        ]
      );
    return $this->traitGetGoneContent();
  }

  /**
   * Init award breadcrumb.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   * @throws \LogicException
   */
  protected function initAwardBreadcrumb() {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this->award instanceof \MovLib\Data\Award)) {
      throw new \LogicException($i18n->t("\$this->award has to be a valid award object!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    return $this->initBreadcrumb([
      [ $i18n->rp("/awards"), $i18n->t("Awards") ],
      [ $this->award->route, $this->award->name ]
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
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Compile array once.
    $routeArgs = [ $this->award->id ];

    // Reduce the sidebar if the award was deleted.
    if ($this->award->deleted === true) {
      return $this->traitSidebarInit([
        [ $this->award->route, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
        [ $i18n->r("/award/{0}/discussion", $routeArgs), $i18n->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
        [ $i18n->r("/award/{0}/history", $routeArgs), $i18n->t("History"), [ "class" => "ico ico-history" ] ]
      ]);
    }

    return $this->traitSidebarInit([
      [ $this->award->route, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
      [ $i18n->r("/award/{0}/discussion", $routeArgs), $i18n->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
      [ $i18n->r("/award/{0}/edit", $routeArgs), $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $i18n->r("/award/{0}/history", $routeArgs), $i18n->t("History"), [ "class" => "ico ico-history" ] ],
      [ $i18n->r("/award/{0}/delete", $routeArgs), $i18n->t("Delete"), [ "class" => "ico ico-delete separator" ] ],

      [ $i18n->rp("/award/{0}/events", $routeArgs), "{$i18n->t("Events")} <span class='fr'>{$i18n->format("{0,number}", [ $this->award->getEventsCount() ])}</span>", [ "class" => "ico ico-event" ] ],
      [ $i18n->rp("/award/{0}/categories", $routeArgs), "{$i18n->t("Categories")} <span class='fr'>{$i18n->format("{0,number}", [ $this->award->getCategoriesCount() ])}</span>", [ "class" => "ico ico-category separator" ] ],

      [ $i18n->rp("/award/{0}/movies", $routeArgs), "{$i18n->t("Movies")} <span class='fr'>{$i18n->format("{0,number}", [ $this->award->getMoviesCount() ])}</span>", [ "class" => "ico ico-movie" ] ],
      [ $i18n->rp("/award/{0}/series", $routeArgs), "{$i18n->t("Series")} <span class='fr'>{$i18n->format("{0,number}", [ $this->award->getSeriesCount() ])}</span>", [ "class" => "ico ico-series separator" ] ],
    ]);
  }

}
