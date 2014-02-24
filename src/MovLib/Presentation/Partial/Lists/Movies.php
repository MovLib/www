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

use \MovLib\Data\User\User;
use \MovLib\Data\Image\MoviePoster;
use \MovLib\Presentation\Partial\Time;

/**
 * Special images list for movie instances.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movies extends \MovLib\Presentation\Partial\Lists\AbstractList {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The span size for a single person's description.
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
   * @global \MovLib\Kernel $kernel
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
    $this->addClass("hover-list no-list r", $this->attributes);
    $this->listItemsAttributes = $listItemsAttributes;
    $this->addClass("r s s{$spanSize}", $this->listItemsAttributes);
    $this->descriptionSpan                 = --$spanSize;
    $this->listItemsAttributes[]           = "itemscope";
    $this->listItemsAttributes["itemtype"] = "http://schema.org/Movie";
    $this->showRating                      = $showRating;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n;

    if ($this->showRating === true) {
      $this->descriptionSpan--;
    }

    try {
      $list   = null;
      /* @var $movie \MovLib\Data\Movie\FullMovie */
      while ($movie = $this->listItems->fetch_object("\\MovLib\\Data\\Movie\\FullMovie")) {
        // We have to use different micro-data if display and original title differ.
        if ($movie->displayTitle != $movie->originalTitle) {
          $displayTitleItemprop = "alternateName";
          $movie->originalTitle = "<br><span class='small'>{$i18n->t("{0} ({1})", [
            "<span itemprop='name'{$this->lang($movie->originalTitleLanguageCode)}>{$movie->originalTitle}</span>",
            "<i>{$i18n->t("original title")}</i>",
          ])}</span>";
        }
        // Simplay clear the original title if it's the same as the display title.
        else {
          $displayTitleItemprop = "name";
          $movie->originalTitle = null;
        }
        $movie->displayTitle = "<span class='link-color' itemprop='{$displayTitleItemprop}'{$this->lang($movie->displayTitleLanguageCode)}>{$movie->displayTitle}</span>";

        // Append year enclosed in micro-data to display title if available.
        if (isset($movie->year)) {
          $movie->displayTitle = $i18n->t("{0} ({1})", [ $movie->displayTitle, "<span itemprop='datePublished'>{$movie->year}</span>" ]);
        }

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
            $genres .= ", ";
          }
          $genres      .= $row["name"];
        }
        if ($genres) {
          $genres = "<p class='small'>{$genres}</p>";
        }

        // Put the movie list entry together.
        $list .=
          "<li{$this->expandTagAttributes($this->listItemsAttributes)}>" .
            "<a class='img li r' href='{$movie->route}' itemprop='url'>" .
              "<div class='s s1 tac'>" .
                $this->getImage($movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_01), false, [ "itemprop" => "image" ]) .
              "</div>" .
              $ratingInfo .
              "<span class='s s{$this->descriptionSpan}'><p>{$movie->displayTitle}{$movie->originalTitle}</p>{$genres}</span>" .
            "</a>" .
          "</li>";
      }

      // Put it all together and we're done.
      return "<ol{$this->expandTagAttributes($this->attributes)}>{$list}</ol>";
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }
  }

}
