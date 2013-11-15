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
namespace MovLib\Presentation\Movies;

use \MovLib\Data\Movie\Movies;

/**
 * The listing for the latest movie additions.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\Movies\TraitMovies;

  /**
   * Numeric array containing the <code>\MovLib\Data\Movie</code> objects to display.
   *
   * @var array
   */
  protected $movies;

  /**
   * Instantiate new latest movies presentation
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->init($i18n->t("Movies"));
    $this->movies = (new Movies())->getMoviesByCreated();
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
//    $list = new Ordered(
//      $this->movies,
//      new Alert($i18n->t("No movies match your criteria."), null, Alert::SEVERITY_INFO),
//      [ "id" => "latest-additions" ]
//    );
//    $list->closure = function (&$movie) {
//      global $i18n;
//      $title = $movie->getDisplayTitle();
//      $poster = $movie->getDisplayPoster();
//      $titleSuffix = new GlueSeparated(array_column($movie->getCountries(), "code"));
//      if ($movie->year) {
//        $titleSuffix->listItems[] = $movie->year;
//      }
//      $titleSuffix->listBefore = " (";
//      $titleSuffix->listAfter  = ")";
//      $movie = $this->a(
//        $i18n->r("/movie/{0}", [ $movie-id ]),
//        "<article>" .
//          "<div class='latest-additions__image'>{$this->getImage($poster, MovieImage::IMAGESTYLE_SPAN_1, [
//            "alt" => $i18n->t("{0} movie poster{1}.", [ $title, $poster->country ? $i18n->t(" for {0}", [ $poster->country["name"] ]) : "" ]),
//          ])}</div>" .
//          "<div class='latest-additions__info clear-fix'>" .
//            "<h2>{$title}{$titleSuffix}</h2>" .
//            "<p>{$i18n->t("“{0}” (<em>original title</em>)", [ $movie->originalTitle ])}</p>" .
//          "</div>" .
//        "</article>",
//        [ "tabindex" => $this->getTabindex() ]
//      );
//    };
    return "<pre>" . print_r($this->movies, true) . "</pre>";
  }

}