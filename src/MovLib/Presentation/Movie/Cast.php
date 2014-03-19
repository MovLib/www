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

use \MovLib\Data\Movie\FullMovie;
use \MovLib\Presentation\Partial\Listing\PersonListing;
use \MovLib\Presentation\Partial\Listing\PersonCastListing;

/**
 * Presentation of single movie's cast.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Cast extends \MovLib\Presentation\Movie\AbstractBase {

  /**
   * Instantiate new movie cast presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->movie = new FullMovie($_SERVER["MOVIE_ID"]);
    $this->initPage($i18n->t("Cast"));
    $this->initBreadcrumb();
    $this->pageTitle = $i18n->t(
      "Cast of {0}",
      [ "<a href='{$this->movie->route}' property='url'><span property='name'>{$this->movie->displayTitleWithYear}</span></a>" ]
    );
    $this->initLanguageLinks("/movie/{0}/cast", [ $this->movie->id ]);
    $this->initSidebar();
  }

  /**
   * @inheritdoc
   *
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n;
    $this->schemaType = "Movie";
    $content = null;

    // Build the directors section.
    $this->sidebarNavigation->menuitems[] = [ "#directors", $i18n->t("Directors") ];
    $directors = new PersonListing($this->movie->getDirectors(), "director", $i18n->t(
      "No directors assigned yet, {0}add directors{1}?",
      [ "<a href='{$i18n->r("/movie{0}/edit", [ $this->movie->id ])}'>", "</a>" ]
    ));
    $content .= "<div id='directors'><h2>{$i18n->t("Directors")}</h2>{$directors}</div>";

    // Build the cast section.
    $this->sidebarNavigation->menuitems[] = [ "#cast", $i18n->t("Cast") ];
    $cast                                 = new PersonCastListing($this->movie->getCast(), "actor");
    $content .= "<div id='cast'><h2>{$i18n->t("Cast")}</h2>{$cast}</div>";

    return $content;
  }

}
