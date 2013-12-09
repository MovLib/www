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
namespace MovLib\Presentation;

use \MovLib\Presentation\Partial\Navigation;

/**
 * Pagination trait.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitPagination {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The current page index.
   *
   * @var integer
   */
  protected $page = 1;

  /**
   * The total amount of pages.
   *
   * @var integer
   */
  protected $pageCount = 1;

  /**
   * The offset from which the results start.
   *
   * @var integer
   */
  protected $resultsOffset = 0;

  /**
   * How many results to display per page.
   *
   * @var integer
   */
  protected $resultsPerPage = 25;

  /**
   * The total result count.
   *
   * @var integer
   */
  protected $resultsTotalCount = 0;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize the pagination.
   *
   * @param integer $resultsTotalCount
   *   The total results count.
   * @return this
   */
  protected function initPagination($resultsTotalCount) {
    global $i18n, $kernel;
    $this->resultsTotalCount = $resultsTotalCount;

    // Include the pagination stylesheet and let the complete design know that a pagination is present.
    $kernel->stylesheets[] = "pagination";
    $this->addBodyClass("pagination");

    // Initial calculations.
    $this->page = filter_input(INPUT_GET, $i18n->r("page"), FILTER_VALIDATE_INT, [
      "options" => [ "default" => 1, "min_range" => 1 ]
    ]);
    $this->pageCount = (integer) ceil($this->resultsTotalCount / $this->resultsPerPage);
    if ($this->page > 1) {
      $this->resultsOffset           = ($this->page - 1) * $this->resultsPerPage;
      $title                         = $i18n->t("Page {0, number, integer}", [ $this->page ]);
      $this->breadcrumb->menuitems[] = [ $kernel->requestURI, $title ];
      $this->title                  .= " {$title}";
    }

    // Only create a pagination navigation if we have at least two pages.
    $pagination = null;
    $to         = $this->resultsTotalCount;
    if ($this->resultsTotalCount > $this->resultsPerPage) {
      $to    = $this->resultsOffset + $this->resultsPerPage;
      $route = "{$kernel->requestPath}?{$i18n->r("page")}=";
      $pages = [];
      $x     = $this->page - 1;

      // Generate the previous link if it isn't the first page.
      if ($x >= 1) {
        $pages[] = [ "{$route}{$x}", $i18n->t("« previous"), [ "rel" => "previous prerender" ] ];
      }
      // We totally mute this pagination item for screen readers and alike because it has no value anymore for them. But
      // we keep it on normal screens to ensure that the pagination navigation always looks the same on all pages.
      else {
        $pages[] = "<span class='mute' aria-hidden='true'>{$i18n->t("« previous")}</span>";
      }

      // Always add the first page to the pagination for fast jumps to the beginning.
      $pages[] = [ $kernel->requestPath, "1", [ "rel" => "first prerender" ] ];
      if ($x <= 1) {
        $x = 2;
      }

      // The second pagination item is special and if we have a pagination it always exists, see above if.
      if ($x < 5) {
        $pages[] = [ "{$route}2", "2", [ "rel" => "prerender" ] ];
        $x = 3;
      }
      else {
        $pages[] = "<span class='mute'>{$i18n->t("…")}</span>";
        $x--;
      }

      $y = $this->pageCount - 6;
      if ($x > $y) {
        $x = $y;
      }

      // We can generate the next points in a loop, as they always have the same formatting.
      $secondLast = $this->pageCount - 1;
      for ($i = 0; $i < 5 && $x < $secondLast; ++$i, ++$x) {
        $pages[] = [ "{$route}{$x}", $x ];
      }

      // The second last pagination item is special again.
      if ($x === $secondLast) {
        $pages[] = [ "{$route}{$secondLast}", $secondLast ];
      }
      else {
        $pages[] = "<span class='mute'>{$i18n->t("…")}</span>";
      }

      // Always add the last page to the pagination for fast traveling.
      $pages[] = [ "{$route}{$this->pageCount}", $this->pageCount, [ "rel" => "last prerender" ] ];

      // Check if we have a next page and perform the same logic as we used for the previous link.
      if ($this->page < $this->pageCount) {
        $next    = $this->page + 1;
        $pages[] = [ "{$route}{$next}", $i18n->t("next »"), [ "rel" => "next prerender" ] ];
      }
      else {
        $pages[] = "<span class='mute' aria-hidden='true'>{$i18n->t("next »")}</span>";
      }

      $pagination = new Navigation($i18n->t("Pagination"), $pages, [ "id" => "pagination-nav" ]);
    }

    // Tampering with the actual navigation partial of the pagination isn't allowed, the concrete class only has the
    // string representation to output it in its content area.
    $this->contentAfter = "{$pagination}<small class='tac'>{$i18n->t(
      "Results from {from, number, integer} to {to, number, integer} of {total, number, integer} results.",
      [ "from" => $this->resultsOffset + 1, "to" => $to, "total" => $this->resultsTotalCount ]
    )}</small>";

    return $this;
  }

}
