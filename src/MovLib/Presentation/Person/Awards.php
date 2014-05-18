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
namespace MovLib\Presentation\Person;

use \MovLib\Partial\Date;

/**
 * Presentation of a person's awards.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright ©  MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Awards extends \MovLib\Presentation\Person\AbstractPersonPresenter {

  /**
   * Initialize person releases presentation.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function init() {
    $this->initPersonPresentation(
      $this->intl->t("Awards of {0}"),
      $this->intl->t("Awards of {0}"),
      $this->intl->t("Awards")
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $listing = null;
    $awards  = $this->entity->getAwards();
    $date = new Date($this->intl, $this);
    /* @var $award \MovLib\Data\Award\Award */
    foreach ($awards as $awardId => $award) {
      $listing .= "<h2><a href='{$award->route}'>{$award->name}</a></h2><table class='table table-striped'><thead><tr>" .
          "<th>{$this->intl->t("Year")}</th>" .
          "<th>{$this->intl->t("Event")}</th>" .
          "<th>{$this->intl->t("Category")}</th>" .
          "<th>{$this->intl->t("Result")}</th>" .
          "<th>{$this->intl->t("Associated Work")}</th>" .
        "</tr></thead><tbody>"
      ;

      /* @var $event \MovLib\Data\Event\Event */
      foreach ($award->events as $eventId => $event) {
        $rowSpan = count($event->categories);
        /* @var $category \MovLib\Data\Award\Category */
        foreach ($event->categories as $categoryId => $category) {
          $listing .= "<tr>";
          if ($rowSpan) {
            $listing .= "<td rowspan='{$rowSpan}'>{$date->formatYear($event->startDate)}</td>";
            $listing .= "<td rowspan='{$rowSpan}'><a href='{$event->route}'>{$event->name}</a></td>";
            $rowSpan = false;
          }
          $listing .= "<td><a href='{$category->route}'>{$category->name}</a></td>";
          if ($category->won === 0) {
            $category->won = $this->intl->t("Nominated");
          }
          // @todo: Fix this when we hava a working nomination/win system.
          else {
            $category->won = $this->intl->t("Won");
          }
          $listing .= "<td>{$category->won}</td>";
          if (isset($category->movie)) {
            $work = "<a href='{$category->movie->route}'>{$category->movie->displayTitleAndYear}</a>";
          }
          elseif (isset($category->series)) {
            $work = "<a href='{$category->series->route}'>{$category->series->displayTitle}</a>";
          }
          else {
            $work = $this->intl->t("–");
          }
          $listing .= "<td>{$work}</td>";
          $listing .= "</tr>";
        }
      }

      $listing .= "</tbody></table>";
    }

    if ($listing) {
      return $listing;
    }

    return $this->calloutInfo(
      $this->intl->t("{sitename} doesn’t have any award details for this person.", [ "sitename" => $this->config->sitename ]),
      $this->intl->t("No Awards")
    );
  }

}
