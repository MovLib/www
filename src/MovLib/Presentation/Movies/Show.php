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
class Show extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;
  use \MovLib\Presentation\TraitPagination;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Numeric array containing the <code>\MovLib\Data\Movie</code> objects to display.
   *
   * @var array
   */
  protected $movies;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new latest movies presentation
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->init($i18n->t("Movies"));
    $this->initSidebar([
      [ $i18n->r("/movies"), $i18n->t("Movies"), [ "class" => "ico ico-movie" ] ],
      [ $i18n->r("/releases"), $i18n->t("Releases"), [ "class" => "ico ico-release" ] ],
      [ $i18n->r("/persons"), $i18n->t("Persons"), [ "class" => "ico ico-person" ] ],
      [ $i18n->r("/series"), $i18n->t("Series"), [ "class" => "ico ico-series" ] ],
      [ $i18n->r("/help"), $i18n->t("Help"), [ "class" => "ico ico-help" ] ],
    ]);
    $this->initPagination(Movie::getUndeletedMoviesCount());
    $this->movies        = Movie::getMovies($this->resultsOffset, $this->resultsPerPage);
    $this->headingBefore = "<a class='button button--large button--success pull-right' href='{$i18n->r("/movie/create")}'>{$i18n->t("Create New Movie")}</a>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format a single movie.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Movie\Movie $movie
   * @return string
   */
  public function formatMovie($movie) {
    global $i18n;

    // Format the poster for the list.
    $image = $this->getImage($movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_01), false, [ "itemprop" => "image" ]);

    // Format the display title for the list.
    if ($movie->year) {
      $title = $i18n->t("{movie_title} ({movie_year})", [
        "movie_title" => "<span itemprop='name'>{$movie->displayTitle}</span>",
        "movie_year"  => "<span itemprop='datePublished'>{$movie->year}</span>",
      ]);
    }
    else {
      $title = "<span itemprop='name'>{$movie->displayTitle}</span>";
    }

    // Append the original title to the output if it differs from the localized title.
    if ($movie->displayTitle != $movie->originalTitle) {
      $title .= "<br><span class='small'>{$i18n->t("Original title: “{original_title}”", [
        "original_title" => "<span itemprop='alternateName'>{$movie->originalTitle}</span>",
      ])}</span>";
    }

    // Put it all together.
    return
      "<a class='img row' href='{$i18n->r("/movie/{0}", [ $movie->id ])}' itemprop='url' tabindex='{$this->getTabindex()}'>" .
        "<span class='span span--1'>{$image}</span>" .
        "<span class='span span--9'>{$title}</span>" .
      "</a>"
    ;
  }

  /**
   * Get the presentation's page content.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The presentation's page content.
   */
  protected function getPageContent() {
    global $i18n;

    // Instantiate the list that makes up all the content.
    $movies = new Images(
      $this->movies,
      new Alert($i18n->t("No movies match your search criteria."), null, Alert::SEVERITY_INFO),
      [ "itemscope", "itemtype" => "http://schema.org/Movie" ]
    );
    $movies->closure = [ $this, "formatMovie" ];

    // Add the filter interface to the current presentation.
    return "<div id='filter'>filter filter filter</div>{$movies}";
  }

}