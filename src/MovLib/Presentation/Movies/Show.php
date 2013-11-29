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

use \MovLib\Data\Image\MoviePoster;
use \MovLib\Data\Movie\Movie;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Lists\Images;

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
    $this->movies = Movie::getMovies();
  }

  /**
   * Format a single movie.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Movie\Movie $movie
   * @return string
   */
  public function formatMovie($movie) {
    global $i18n;
    $image = $this->getImage($movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_01), false, [ "itemprop" => "image" ]);
    if ($movie->year) {
      $title = $i18n->t("{movie_title} ({movie_year})", [
        "movie_title" => "<span itemprop='name'>{$movie->displayTitle}</span>",
        "movie_year"  => "<span itemprop='datePublished'>{$movie->year}</span>",
      ]);
    }
    else {
      $title = "<span itemprop='name'>{$movie->displayTitle}</span>";
    }
    return "<a class='img' href='{$i18n->r("/movie/{0}", [ $movie->id ])}' itemprop='url'>{$image}{$title}</a>{$movie->created}";
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    $movies = new Images(
      $this->movies,
      new Alert($i18n->t("No movies match your search criteria."), null, Alert::SEVERITY_INFO),
      null,
      [ "itemscope", "itemtype" => "http://schema.org/Movie" ]
    );
    $movies->closure = [ $this, "formatMovie" ];
    return $movies;
  }

  /**
   * @inheritdoc
   */
  public function getSecondaryNavigationMenuItems() {
    global $i18n;
    return [
      [
        $i18n->r("/movies"),
        "<i class='icon icon--film'></i> {$i18n->t("Movies")}",
        [ "title" => $i18n->t("View the latest {0} additions to the database.", [ $i18n->t("movie") ]) ]
      ],
      [
        $i18n->r("/persons"),
        "<i class='icon icon--user'></i> {$i18n->t("Persons")}",
        [ "title" => $i18n->t("View the latest {0} additions to the database.", [ $i18n->t("person") ]) ]
      ],
      [
        $i18n->r("/help"),
        "<i class='icon icon--help-circled'></i> {$i18n->t("Help")}",
        [ "title" => $i18n->t("View the latest {0} additions to the database.", [ $i18n->t("help") ]) ]
      ]
    ];
  }

}