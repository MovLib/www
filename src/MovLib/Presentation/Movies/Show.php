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
use \MovLib\Presentation\Partial\Lists\Movies as MoviesPartial;

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
    $this->sidebarInit([
      [ $this->languageLinks[$i18n->languageCode], $i18n->t("Movies"), [ "class" => "ico ico-movie" ] ],
      [ $i18n->rp("/serials"), $i18n->t("Serials"), [ "class" => "ico ico-series" ] ],
      [ $i18n->rp("/releases"), $i18n->t("Releases"), [ "class" => "ico ico-release" ] ],
      [ $i18n->rp("/persons"), $i18n->t("Persons"), [ "class" => "ico ico-person" ] ],
      [ $i18n->rp("/companies"), $i18n->t("Companies"), [ "class" => "ico ico-comoany" ] ],
      [ $i18n->rp("/help"), $i18n->t("Help"), [ "class" => "ico ico-help" ] ],
    ]);
    $this->paginationInit(Movie::getMoviesCount());
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

    return "<div id='filter' class='tar'>Filter</div>" .
            new MoviesPartial(
              Movie::getMovies($this->paginationOffset, $this->paginationLimit),
              null,
              new Alert($i18n->t("No movies match your search criteria."), null, Alert::SEVERITY_INFO),
              null,
              null,
              10,
              true
            )
    ;
  }

}