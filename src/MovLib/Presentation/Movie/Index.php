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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Movie\Movie;
use \MovLib\Presentation\Partial\Listing\MovieListing as MoviesPartial;

/**
 * The listing for the latest movie additions.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\Page {
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
      [ $i18n->rp("/movies"), $i18n->t("Movies"), [ "class" => "ico ico-movie" ] ],
      [ $i18n->rp("/movies/charts"), $i18n->t("Charts") ],
      [ $i18n->r("/movie/random"), $i18n->t("Random") ],
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

    return new MoviesPartial(Movie::getMovies($this->paginationOffset, $this->paginationLimit));
  }

}
