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
namespace MovLib\Partial\Listing;

use \MovLib\Data\Image\MoviePoster;
use \MovLib\Presentation\Partial\Alert;

/**
 * Images list for movie instances.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieListing {
  use \MovLib\Presentation\TraitMovie;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The dependency injection container.
   *
   * @var \MovLib\Core\HTTP\DIContainerHTTP
   */
  protected $diContainerHTTP;

  /**
   * The active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The list items to display.
   *
   * @var mixed
   */
  protected $listItems;

  /**
   * The text to display if there are no items.
   *
   * @var mixed
   */
  protected $noItemsText;

  /**
   * The presenting presenter.
   *
   * @var \MovLib\Presentation\AbstractPresenter
   */
  protected $presenter;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie listing.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   The dependency injection container.
   * @param mixed $listItems
   *   The items to build the movie listing.
   * @param mixed $noItemsText [optional]
   *   The text to display if there are no items, defaults to a generic {@see \MovLib\Presentation\Partial\Alert}.
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, $listItems, $noItemsText = null) {
    $this->diContainerHTTP = $diContainerHTTP;
    $this->intl            = $this->diContainerHTTP->intl;
    $this->presenter       = $this->diContainerHTTP->presenter;
    // @devStart
    // @codeCoverageIgnoreStart
    if (isset($noItemsText) && (empty($noItemsText) || !method_exists($noItemsText, "__toString"))) {
      throw new \InvalidArgumentException(
        "\$noItemsText must be a non-empty string or convertable to string when given"
      );
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->listItems   = $listItems;
    $this->noItemsText = $noItemsText;
  }

  /**
   * Get the string representation of the listing.
   *
   * @return string
   *   The string representation of the listing.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $list = null;
      /* @var $movie \MovLib\Data\Movie\FullMovie */
      while ($movie = $this->listItems->fetch_object("\\MovLib\\Data\\Movie\\FullMovie", [ $this->diContainerHTTP ])) {
        $list .= $this->formatListItem($movie->initFetchObject());
      }

      if ($list) {
        return "<ol class='hover-list no-list'>{$list}</ol>";
      }

      if (!$this->noItemsText) {
        $this->noItemsText = (string) new Alert(
          $this->intl->t("No movies match your search criteria."),
          $this->intl->t("No Movies"),
          Alert::SEVERITY_INFO
        );
      }

      return (string) $this->noItemsText;
    // @devStart
    // @codeCoverageIgnoreStart
    } catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", "Error Rendering Movie List", Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format a movie list item.
   *
   * @param \MovLib\Data\Movie\FullMovie $movie
   *   The movie to format.
   * @param mixed $listItem [optional]
   *   The current list item if different from $movie.
   * @return string
   *   The formatted movie list item.
   */
  final protected function formatListItem($movie, $listItem = null) {
    // Format the genres.
    $genres     = null;
    $genreRoute = $this->intl->r("/genre/{0}");
    $result     = $movie->getGenres();
    while ($row = $result->fetch_assoc()) {
      if ($genres) {
        $genres .= " ";
      }
      $route   = str_replace("{0}", $row["id"], $genreRoute);
      $genres .= "<a class='label' href='{$route}'>{$row["name"]}</a>";
    }
    if ($genres) {
      $genres = "<small><span class='vh'>{$this->intl->t("Genres")}</span>{$genres}</small>";
    }

    // Put the movie list entry together.
    // @todo: Implement new image retrieval!
    return
      "<li class='hover-item r' typeof='Movie'>" .
        "<a class='no-link s s1 tac' href='{$movie->route}'><img alt='' height='60' src='{$this->presenter->getExternalURL("asset://img/logo/vector.svg")}' width='60'></a>" .
        "<div class='s s8'>{$this->getTitleInfo($movie)}{$genres}{$this->getAdditionalContent($movie, $listItem)}</div>" .
        $this->getRatingContent($movie) .
      "</li>"
    ;
  }

  /**
   * Get additional content to display on a movie list item.
   *
   * @param \MovLib\Data\Movie\FullMovie $movie
   *   The movie providing the information.
   * @return string
   *   The formatted additional content.
   */
  protected function getAdditionalContent($movie, $listItem) {
    // The default implementation returns no additional content.
  }

  /**
   * Get rating content to display on a movie list item.
   *
   * @param \MovLib\Data\Movie\FullMovie $movie
   *   The movie providing the information.
   * @return string
   *   The formatted rating content.
   */
  protected function getRatingContent($movie) {
    $rating = \NumberFormatter::create($this->intl->locale, \NumberFormatter::DECIMAL)->format($movie->ratingMean);
    return "<span class='s s1 rating-mean tac'>{$rating}</span>";
  }

}
