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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new latest movies presentation
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->initPage($i18n->t("Movies"));
    $this->initBreadcrumb();
    $this->initLanguageLinks("/movies", null, true);
    $this->initSidebar([
      [ $this->languageLinks[$i18n->languageCode], $i18n->t("Movies"), [ "class" => "ico ico-movie" ] ],
      [ $i18n->rp("/serials"), $i18n->t("Serials"), [ "class" => "ico ico-series" ] ],
      [ $i18n->rp("/releases"), $i18n->t("Releases"), [ "class" => "ico ico-release" ] ],
      [ $i18n->rp("/persons"), $i18n->t("Persons"), [ "class" => "ico ico-person" ] ],
      [ $i18n->rp("/help"), $i18n->t("Help"), [ "class" => "ico ico-help" ] ],
    ]);
    $this->initPagination(Movie::getMoviesCount());
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * Get the presentation's page content.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The presentation's page content.
   */
  protected function getPageContent() {
    global $i18n;

    // Ensure it's easy for users to find the page where the can create new movies.
    $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$i18n->r("/movie/create")}'>{$i18n->t("Create New Movie")}</a>";

    // Nothing to display.
    if ($this->resultsTotalCount < 1) {
      return new Alert($i18n->t("No movies match your search criteria."), null, Alert::SEVERITY_INFO);
    }

    // Fetch all movies matching the current pagination offset.
    $list   = null;
    $movies = Movie::getMovies($this->resultsOffset, $this->resultsPerPage);
    /* @var $movie \MovLib\Data\Movie\Movie */
    while ($movie = $movies->fetch_object("\\MovLib\\Data\\Movie\\Movie")) {
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

      // Put the movie list entry together.
      $list .= "<li itemscope itemtype='http://schema.org/Movie'><a class='img li r' href='{$movie->route}' itemprop='url'>" .
        "<span class='s s1'>{$this->getImage($movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_01), false, [ "itemprop" => "image" ])}</span>" .
        "<span class='s s9'>{$movie->displayTitle}{$movie->originalTitle}</span>" .
      "</a></li>";
    }
    $movies->free();

    // Put it all together and we're done.
    return "<div id='filter' class='tar'>Filter</div><ol class='hover-list no-list'>{$list}</ol>";
  }

}