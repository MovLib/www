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
class Movies extends \MovLib\Presentation\Partial\Listing\AbstractListing {
  use \MovLib\Presentation\TraitMovie;


  // ------------------------------------------------------------------------------------------------------------------- Properties

  /**
   * The span size for a single movie's description.
   *
   * @var integer
   */
  protected $descriptionSpan;

  /**
   * The attributes of the list's items.
   *
   * @var array
   */
  public $listItemsAttributes;

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
  public function __construct($listItems, $noItemsText = "", array $listItemsAttributes = null, array $attributes = null, $spanSize = 10, $showRating = false) {
    global $kernel;

    parent::__construct($listItems, $noItemsText, $attributes);
    $kernel->stylesheets[] = "movie";
    $this->addClass("hover-list no-list", $this->attributes);
    $this->listItemsAttributes = $listItemsAttributes;
    $this->addClass("hover-item r", $this->listItemsAttributes);
    $this->descriptionSpan                 = --$spanSize;
    $this->listItemsAttributes["typeof"] = "Movie";
    $this->showRating = $showRating;
    if ($showRating === true) {
      $this->descriptionSpan--;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n;

    $list   = null;
    /* @var $movie \MovLib\Data\Movie\FullMovie */
    while ($movie = $this->listItems->fetch_object("\\MovLib\\Data\\Movie\\FullMovie")) {
      // Display average rating information according to parameter.
      $ratingInfo = null;
      if ($this->showRating !== false && $movie->ratingMean > 0) {
        $rating = \NumberFormatter::create($i18n->locale, \NumberFormatter::DECIMAL)->format($movie->ratingMean);
        $ratingInfo = "<span class='s s1 rating-mean tac'>{$rating}</span>";
      }

      $genres = null;
      $genreRoute = $i18n->r("/genre/{0}");
      $result = $movie->getGenres();
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
      $list .=
        "<li{$this->expandTagAttributes($this->listItemsAttributes)}>" .
          $this->getImage(
            $movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_01),
            $movie->route,
            null,
            [ "class" => "s s1 tac" ]
          ) .
          "<div class='s s{$this->descriptionSpan}'>{$this->getTitleInfo($movie)}{$genres}</div>" .
          $ratingInfo .
        "</li>";
    }

    if (!$list) {
      return (string) new Alert($this->noItemsText, null, Alert::SEVERITY_INFO);
    }

    // Put it all together and we're done.
    return "<ol{$this->expandTagAttributes($this->attributes)}>{$list}</ol>";
  }

}
