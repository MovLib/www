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
use \MovLib\Partial\Date;

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
   * Format a single listing's item.
   *
   * @param \MovLib\Data\Series\Series $series
   *   The movie to format.
   * @param integer $delta
   *   The current loops delta.
   * @return string
   *   A formated list item.
   */
  public function formatListingItem(\MovLib\Data\AbstractEntity $series, $delta) {
    return
      "<li class='hover-item r'>" .
        "<article>" .
          "<div class='s s10'>" .
            "<div class='fr'>" .
              "<a class='ico ico-award label' href='{$series->r("/awards", [ $series->id ])}' title='{$this->intl->t("Awards")}'>{$series->awardCount}</a>" .
              "<a class='ico ico-season label' href='{$series->r("/seasons", [ $series->id ])}' title='{$this->intl->t("Seasons")}'>{$series->seasonCount}</a>" .
              "<a class='ico ico-release label' href='{$series->r("/releases", [ $series->id ])}' title='{$this->intl->t("Releases")}'>{$series->releaseCount}</a>" .
            "</div>" .
            "<h2 class='para'>{$this->getStructuredDisplayTitle($series)}</h2>" .
            $this->getStructuredOriginalTitle($series, "small") .
          "</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSidebarItems() {
    $items = [];
    if ($this->entity->deleted) {
      return $items;
    }
    $navItems = [
      [ "award", "awards", $this->intl->t("Awards"), $this->entity->awardCount ],
      [ "season", "seasons", $this->intl->t("Seasons"), $this->entity->seasonCount ],
      [ "release separator", "releases", $this->intl->t("Releases"), $this->entity->releaseCount ],
    ];
    foreach ($navItems as list($icon, $plural, $title, $count)) {
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

  /**
   * Get the series's display title enhanced with structured data.
   *
   * @param \MovLib\Data\Series\Series $series
   *   The series to get the display title for.
   * @param boolean $linkTitle [optional]
   *   Whether to link the series to its show or not, defaults to <code>TRUE</code>.
   * @param boolean $linkYears [optional]
   *   Whether to link the years to it's series index or not, defaults to <code>FALSE</code>.
   * @return string
   *   The series's display title enhanced with structured data.
   */
  final public function getStructuredDisplayTitle(\MovLib\Data\Series\Series $series, $linkTitle = true, $linkYears = false) {
    $property = ($series->displayTitle == $series->originalTitle) ? "name" : "alternateName";
    $title    = "<span{$this->lang($series->displayTitleLanguageCode)} property='{$property}'>{$series->displayTitle}</span>";
    if (isset($series->startYear) && isset($series->endYear)) {
      $title = $this->intl->t("{0} ({1}–{2})", [
        $title,
        (new Date($this->intl, isset($this->presenter) ? $this->presenter : $this))->formatYear(
          $series->startYear,
          [ "property" => "startDate" ],
          $linkYears ? [ "href" => $this->intl->r("/year/{0}/series", $series->startYear->year) ] : null
        ),
        (new Date($this->intl, isset($this->presenter) ? $this->presenter : $this))->formatYear(
          $series->endYear,
          [ "property" => "startDate" ],
          $linkYears ? [ "href" => $this->intl->r("/year/{0}/series", $series->endYear->year) ] : null
        )
      ]);
    }
    else if (isset($series->startYear)) {
      $title = $this->intl->t("{0} ({1})", [ $title, (new Date($this->intl, isset($this->presenter) ? $this->presenter : $this))->formatYear(
        $series->startYear,
        [ "property" => "startDate" ],
        $linkYears ? [ "href" => $this->intl->r("/year/{0}/series", $series->startYear->year) ] : null
      ) ]);
    }
    if ($linkTitle) {
      return "<a href='{$series->route}' property='url'>{$title}</a>";
    }
    return $title;
  }

  /**
   * Get the series's original title enhanced with structured data.
   *
   * @param \MovLib\Data\Series\Series $series
   *   The series to get the original title for.
   * @param null|string $wrap [optional]
   *   Optional wrapper tag to enclose the original title, defaults to <code>NULL</code> (don't wrap).
   * @param null|array $wrapAttributes [optional]
   *   Additional attributes the should be applied to the wrapper, defaults to <code>NULL</code>.
   * @return null|string
   *   Get the series's original title enhanced with structured data, <code>NULL</code> if display and original title are
   *   equal.
   */
  final public function getStructuredOriginalTitle(\MovLib\Data\Series\Series $series, $wrap = null, array $wrapAttributes = null) {
    if ($series->displayTitle != $series->originalTitle) {
      $title = $this->intl->t(
        "{0} ({1})",
        [
          "<span{$this->lang($series->originalTitleLanguageCode)} property='name'>{$series->originalTitle}</span>",
          "<i>{$this->intl->t("original title")}</i>",
        ]
      );
      if ($wrap) {
        return "<{$wrap}{$this->expandTagAttributes($wrapAttributes)}>{$title}</{$wrap}>";
      }
      return $title;
    }
  }

}
