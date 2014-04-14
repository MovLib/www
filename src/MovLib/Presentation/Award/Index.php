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

use \MovLib\Data\Award\AwardSet;
use \MovLib\Partial\Date;

/**
 * Defines the award index presentation.
 *
 * @link http://schema.org/Organization
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/awards
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/awards
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/awards
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/awards
 *
 * @property \MovLib\Data\Award\AwardSet $set
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
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
      new AwardSet($this->diContainerHTTP),
      $this->intl->t("Awards"),
      $this->intl->t("Create New Award")
    );
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Award\Award $award {@inheritdoc}
   */
  public function formatListingItem(\MovLib\Data\AbstractEntity $award, $delta) {
    $awardDates = (new Date($this->intl, $this))->formatFromTo(
      $award->firstEventYear,
      $award->lastEventYear,
      [ "title" => $this->intl->t("First Event") ],
      [ "title" => $this->intl->t("Last Event") ],
      true
    );
    if ($awardDates) {
      $awardDates = "<small>{$awardDates}</small>";
    }
    $route = $award->route;
    return
      "<li class='hover-item r'>" .
        "<article typeof='Organization'>" .
          "<a class='no-link s s1' href='{$route}'>" .
            "<img alt='{$award->name}' src='{$this->fs->getExternalURL("asset://img/logo/vector.svg")}' width='60' height='60'>" .
          "</a>" .
          "<div class='s s9'>" .
            "<div class='fr'>" .
              "<a class='ico ico-movie label' href='{$this->intl->rp("/award/{0}/movies", $award->id)}' title='{$this->intl->t("Movies")}'>{$award->movieCount}</a>" .
              "<a class='ico ico-series label' href='{$this->intl->rp("/award/{0}/series", $award->id)}' title='{$this->intl->t("Series")}'>{$award->seriesCount}</a>" .
              "<a class='ico ico-person label' href='{$this->intl->rp("/award/{0}/persons", $award->id)}' title='{$this->intl->t("Persons")}'>{$award->personCount}</a>" .
              "<a class='ico ico-company label' href='{$this->intl->rp("/award/{0}/companies", $award->id)}' title='{$this->intl->t("Companies")}'>{$award->companyCount}</a>" .
              "<a class='ico ico-category label' href='{$this->intl->rp("/award/{0}/categories", $award->id)}' title='{$this->intl->t("Categories")}'>{$award->categoryCount}</a>" .
              "<a class='ico ico-event label' href='{$this->intl->rp("/award/{0}/events", $award->id)}' title='{$this->intl->t("Events")}'>{$award->eventCount}</a>" .
            "</div>" .
            "<h2 class='para'><a href='{$route}' property='url'><span property='name'>{$award->name}</span></a></h2>" .
            $awardDates .
          "</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return $this->callout(
      "<p>{$this->intl->t("We couldn’t find any awards matching your filter criteria, or there simply aren’t any awards available.")}</p>" .
      "<p>{$this->intl->t("Would you like to {0}create an award{1}?", [ "<a href='{$this->intl->r("/award/create")}'>", "</a>" ])}</p>",
      $this->intl->t("No Awards"),
      "info"
    );
  }

}
