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
namespace MovLib\Partial;

use \MovLib\Exception\ClientException\NoItemsException;
use \MovLib\Partial\Navigation;

/**
 * Add pagination support to presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait PaginationTrait {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The pagination's current page.
   *
   * @var integer
   */
  protected $paginationCurrentPage = 1;

  /**
   * The pagination's limit.
   *
   * This property determines how many results should be displayed per page.
   *
   * @var integer
   */
  public $paginationLimit = 25;

  /**
   * The pagination's offset.
   *
   * This property determines from which offset the results that are displayed should start.
   *
   * @var integer
   */
  public $paginationOffset = 0;

  /**
   * The pagination's total page count.
   *
   * @var integer
   */
  protected $paginationTotalPages = 1;

  /**
   * The pagination's total result count.
   *
   * @var integer
   */
  protected $paginationTotalResults = 0;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Called if there are no items to display.
   *
   * This method should return an alert that describes why there are no items and provide a linkt to create a new
   * entity.
   *
   * <i>Why do we have to re-implement this in each class?</i><br>
   * The problem are the translations, we can't simply use placeholder tokens for entities (e.g.
   * <code>"No {entity}"</code>) because we have no clue how this has to be translated in other languages. Translators
   * need highest flexibility because languages are so complex.
   *
   * <b>EXAMPLE</b><br>
   * <code><?php
   *
   * protected function paginationNoItems() {
   *   return new Alert(
   *     "<p>{$this->intl->t(
   *       "We couldn’t find any entities matching your filter criteria, or there simply aren’t any entities available."
   *     )}</p><p>{$this->intl->t(
   *       "Would you like to {0}create a new entity{1}?",
   *       [ "<a href='{$this->intl->r("/entity/create")}'>", "</a>" ]
   *     )}</p>",
   *     $this->intl->t("No Entities")
   *   );
   * }
   *
   * ?></code>
   *
   * @return string
   *   No items text.
   */
  abstract public function getNoItemsContent();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize the pagination.
   *
   * @param \MovLib\Data\SetInterface $set
   *   The set that is going to be displayed.
   * @return this
   */
  final protected function paginationInit(\MovLib\Data\SetInterface $set) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this instanceof \MovLib\Presentation\AbstractPresenter)) {
      throw new \LogicException("You can only use the pagination trait within a presenter.");
    }
    if (empty($this->title)) {
      throw new \LogicException("You have to initialize the page before you initialize the pagination trait.");
    }
    if (empty($this->breadcrumb)) {
      throw new \LogicException("You have to initialize the breadcrumb before you initialize the pagination trait.");
    }
    if (!empty($this->contentAfter)) {
      throw new \LogicException("The \$contentAfter variable will be overwritten by the pagination trait.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->paginationTotalResults = $set->getCount();
    if ($this->paginationTotalResults === 1) {
      return $this;
    }
    elseif ($this->paginationTotalResults < 1) {
      throw new NoItemsException();
    }

    // Include the pagination stylesheet and let the complete design know that a pagination is present.
    $this->bodyClasses .= " pagination";

    // Validate the user submitted page query string.
    $this->paginationCurrentPage = $this->request->filterInput(INPUT_GET, $this->intl->r("page"), FILTER_VALIDATE_INT, [
      "options" => [ "default" => 1, "min_range" => 1 ]
    ]);

    // Calculate how many pages we have to display.
    $this->paginationTotalPages = (integer) ceil($this->paginationTotalResults / $this->paginationLimit);

    // Extend the page's breadcrumb and title if this isn't the first page.
    if ($this->paginationCurrentPage > 1) {
      // Calculate the pagination offset within the results for this page.
      $this->paginationOffset = ($this->paginationCurrentPage - 1) * $this->paginationLimit;

      // Extend the page's breadcrumb and title with information about the current pagination page.
      $title = $this->intl->t("Page {0, number, integer}", [ $this->paginationCurrentPage ]);
      $this->breadcrumb->menuitems[] = [ $this->request->uri, $title ];
      $this->title .= " {$title}";
    }

    // @todo Better documentation of the code below and we should get rid of all the magic numbers in there!

    // Only create a pagination navigation if we have at least two pages.
    $pagination = null;
    $to         = $this->paginationTotalResults;
    if ($this->paginationTotalResults > $this->paginationLimit) {
      // Calculate the maximum amount of results that we can show on this page.
      $max = $this->paginationOffset + $this->paginationLimit;
      // If the current total count isn't smaller then the maximum, use the maximum as to (see bottom of method).
      if ($this->paginationTotalResults > $max) {
        $to = $max;
      }

      // Create the complete route string with the translated page query once. Initialize the page array and substract
      // one from the current page's index.
      $route = "{$this->request->path}?{$this->intl->r("page")}=";
      $pages = [];
      $x     = $this->paginationCurrentPage - 1;

      // Generate the previous link if it isn't the first page.
      if ($x >= 1) {
        // Only include the query string if we aren't linking to the very first page.
        $pages[] = [
          ($x > 1 ? "{$route}{$x}" : $this->request->path),
          "<span class='ico ico-chevron-left small'></span> {$this->intl->t("previous")}",
          [ "class" => "pager", "rel" => "previous" ],
        ];
      }
      // We totally mute this pagination item for screen readers and alike because it has no value anymore for them. But
      // we keep it on normal screens to ensure that the pagination navigation always looks the same on all pages.
      else {
        $pages[] = "<span class='mute pager' aria-hidden='true'><span class='ico ico-chevron-left small'></span> {$this->intl->t("previous")}</span>";
      }

      // Always add the first page to the pagination for fast jumps to the beginning.
      $pages[] = [ $this->request->path, "1", [ "rel" => "first" ] ];
      if ($x <= 1) {
        $x = 2;
      }

      // The second pagination item is special and if we have a pagination it always exists, see above if.
      if ($x < 5) {
        $pages[] = [ "{$route}2", "2" ];
        $x = 3;
      }
      else {
        $pages[] = "<span class='mute pager'>{$this->intl->t("…")}</span>";
        $x--;
      }

      $y = $this->paginationTotalPages - 6;
      if ($y > 2) {
        if ($x > $y) {
          $x = $y;
        }

        // We can generate the next points in a loop, as they always have the same formatting.
        $secondLast = $this->paginationTotalPages - 1;
        for ($i = 0; $i < 5 && $x < $secondLast; ++$i, ++$x) {
          $pages[] = [ "{$route}{$x}", $x ];
        }

        // The second last pagination item is special again.
        if ($x === $secondLast) {
          $pages[] = [ "{$route}{$secondLast}", $secondLast ];
        }
        else {
          $pages[] = "<span class='mute pager'>{$this->intl->t("…")}</span>";
        }

        // Always add the last page to the pagination for fast traveling.
        $pages[] = [ "{$route}{$this->paginationTotalPages}", $this->paginationTotalPages, [ "class" => "pager", "rel" => "last" ] ];
      }

      // Check if we have a next page and perform the same logic as we used for the previous link.
      if ($this->paginationCurrentPage < $this->paginationTotalPages) {
        $next    = $this->paginationCurrentPage + 1;
        $pages[] = [ "{$route}{$next}", "{$this->intl->t("next")} <span class='ico ico-chevron-right small'></span>", [ "class" => "pager", "rel" => "next" ] ];
      }
      else {
        $pages[] = "<span class='mute pager' aria-hidden='true'>{$this->intl->t("next")} <span class='ico ico-chevron-right small'></span></span>";
      }

      $pagination = new Navigation($this, $this->intl->t("Pagination"), $pages, [ "id" => "pagination-nav" ]);
    }

    // Tampering with the actual navigation partial of the pagination isn't allowed, the concrete class only has the
    // string representation to output it in its content area.
    $this->contentAfter = "<div class='c'><div class='r'><div class='s s10 o2'>{$pagination}<small class='tac'>{$this->intl->t(
      "Results from {from,number,integer} to {to,number,integer} of {total,number,integer} results.", [
        "from"  => $this->paginationOffset + 1,
        "to"    => $to,
        "total" => $this->paginationTotalResults,
      ]
    )}</small></div></div></div>";

    return $this;
  }

}
