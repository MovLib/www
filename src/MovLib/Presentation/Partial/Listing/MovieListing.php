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
namespace MovLib\Presentation\Partial\Listing;

use \MovLib\Data\Image\MoviePoster;
use \MovLib\Presentation\Partial\Alert;

/**
 * Special images list for movie instances.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieListing extends \MovLib\Presentation\AbstractBase {
  use \MovLib\Presentation\TraitMovie;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The list items to display.
   *
   * @var mixed
   */
  protected $listItems;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new special movies listing.
   *
   * @param mixed $listItems
   *   The items to build the movie listing.
   */
  public function __construct($listItems) {
    $this->listItems = $listItems;
  }

  /**
   * Get the string representation of the listing.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The string representation of the listing.
   */
  public function __toString() {
    global $i18n;

    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $list = null;
      /* @var $movie \MovLib\Data\Movie\FullMovie */
      while ($movie = $this->listItems->fetch_object("\\MovLib\\Data\\Movie\\FullMovie")) {
        $list .= $this->formatListItem($movie);
      }

      if ($list) {
        return "<ol class='hover-list no-list'>{$list}</ol>";
      }

      return (string) new Alert(
        $i18n->t("No movies match your search criteria."),
        $i18n->t("No Movies"),
        Alert::SEVERITY_INFO
      );
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
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Movie\FullMovie $movie
   *   The movie to format.
   * @param mixed $listItem [optional]
   *   The current list item if different from $movie.
   * @return string
   *   The formatted movie list item.
   */
  final protected function formatListItem($movie, $listItem = null) {
    global $i18n;

    // Format the genres.
    $genres     = null;
    $genreRoute = $i18n->r("/genre/{0}");
    $result     = $movie->getGenres();
    while ($row = $result->fetch_assoc()) {
      if ($genres) {
        $genres .= " ";
      }
      $route   = str_replace("{0}", $row["id"], $genreRoute);
      $genres .= "<a class='label' href='{$route}'>{$row["name"]}</a>";
    }
    if ($genres) {
      $genres = "<small><span class='vh'>{$i18n->t("Genres: ")}</span>{$genres}</small>";
    }

    // Put the movie list entry together.
    return
      "<li class='hover-item r'>" .
        $this->getImage(
          $movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_01),
          $movie->route,
          null,
          [ "class" => "s s1 tac" ]
        ) .
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
  protected function getAdditionalContent($movie) {
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
    global $i18n;
    $rating = \NumberFormatter::create($i18n->locale, \NumberFormatter::DECIMAL)->format($movie->ratingMean);
    return "<span class='s s1 rating-mean tac'>{$rating}</span>";
  }

}
