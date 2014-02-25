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
namespace MovLib\Presentation\Partial\Lists;

use \MovLib\Data\Image\MoviePoster;

/**
 * Special images list for movie instances.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movies extends \MovLib\Presentation\Partial\Lists\AbstractMovieList {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Display global rating, user rating or no rating at all.
   *
   * @var boolean|integer
   */
  protected $showRating;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new special movies listing.
   *
   * @param \mysqli_result $listItems
   *   The mysqli result object containing the movies.
   * @param stdClass $entity
   * {@inheritdoc}
   * @param string $noItemsText [optional]
   *   {@inheritdoc}
   * @param array $listItemsAttributes [optional]
   *   {@inheritdoc}
   * @param array $attributes [optional]
   *   {@inheritdoc}
   * @param integer $spanSize [optional]
   *   The span size the list items should reserve, defaults to <code>10</code>
   * @param boolean $showRating [optional]
   *   Show rating information, defaults to <code>FALSE</code>.
   *   Possible values: <ul>
   *     <li><code>FALSE</code>: no rating</li>
   *     <li><code>TRUE</code>: global (average) rating</li>
   *     <li><code>USER_ID</code>: rating of a specific user</li>
   *   </ul>
   */
  public function __construct($listItems, $entity, $noItemsText = "", array $listItemsAttributes = null, array $attributes = null, $spanSize = 10, $showRating = false) {
    parent::__construct($listItems, null, $noItemsText, $listItemsAttributes, $attributes, $spanSize);
    $this->showRating                      = $showRating;
    if ($showRating === true) {
      $this->descriptionSpan--;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function formatItem($movie, $entity) {
    global $i18n;

    // Display average rating information according to parameter.
    $ratingInfo = null;
    if ($this->showRating !== false && $movie->ratingMean > 0) {
      $rating = \NumberFormatter::create($i18n->locale, \NumberFormatter::DECIMAL)->format($movie->ratingMean);
      $ratingInfo = "<span class='rating-mean tac'>{$rating}</span>";
    }

    $genres = null;
    $result = $movie->getGenres();
    while ($row = $result->fetch_assoc()) {
      if ($genres) {
        $genres .= "&nbsp;";
      }
      $genres      .= "<span class='label'>{$row["name"]}</span>";
    }
    if ($genres) {
      $genres = "<p class='small'>{$genres}</p>";
    }

    // Put the movie list entry together.
    return
      "<li{$this->expandTagAttributes($this->listItemsAttributes)}>" .
        "<a class='img li r' href='{$movie->route}' itemprop='url'>" .
          "<div class='s s1 tac'>" .
            $this->getImage($movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_01), false, [ "itemprop" => "image" ]) .
          "</div>" .
          $ratingInfo .
          "<div class='s s{$this->descriptionSpan}'>{$this->getTitleInfo($movie)}{$genres}</div>" .
        "</a>" .
      "</li>";

  }

}
