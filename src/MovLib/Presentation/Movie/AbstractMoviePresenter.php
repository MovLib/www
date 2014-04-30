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

/**
 * Base class for most movie presentations.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractMoviePresenter extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;
  use \MovLib\Presentation\Movie\MovieTrait;

  /**
   * The movie this presentation is for.
   *
   * @var \MovLib\Data\Movie\Movie
   */
  protected $entity;

  /**
   * Initialize the movie presentation.
   *
   * @param string $title [optional]
   *   The presentation's translated title with the placeholder for the movie's display title "{title}",
   *   defaults to the movie's display title with year.
   * @param string $pageTitle [optional]
   *   The presentation's translated page title with the placeholder (only has to be supplied if different from the title).
   *
   *   The movie's name will be linked automatically and will also contain correct structured markup.
   * @param string $breadcrumbTitle
   *   The presentation's translated breadcrumb title with the placeholder (only has to be supplied if different from the title).
   */
  final protected function initMoviePresenation($title = null, $pageTitle = null, $breadcrumbTitle = null) {
    if (!$this->entity) {
      $this->entity = new Movie($this->diContainerHTTP, (integer) $_SERVER["MOVIE_ID"]);
    }
    $this->schemaType            = "Movie";
    $this->headingSchemaProperty = null;

    $title =
      $title
        ? str_replace("{title}", $this->entity->displayTitleAndYear, $title)
        : $this->entity->displayTitleAndYear
    ;

    $pageTitle = $pageTitle
      ? str_replace("{title}", $this->getStructuredDisplayTitle($this->entity), $pageTitle)
      : $this->getStructuredDisplayTitle($this->entity, false)
    ;
    $breadcrumbTitle && $breadcrumbTitle = str_replace("{title}", $this->entity->displayTitleAndYear, $breadcrumbTitle);

    $this->initPage($title, $pageTitle, $breadcrumbTitle);

    // Construct the breadcrumbs and route key.
    $this->breadcrumb->addCrumb($this->intl->r("/movies"), $this->intl->tp(-1, "Movies", "Movie"));
    $routeKey = $this->entity->routeKey;
    if (($shortName = strtolower($this->shortName())) != "show") {
      $routeKey .= "/{$shortName}";
      $this->breadcrumb->addCrumb($this->entity->route, $this->entity->displayTitleAndYear);
    }

    // There's no single subpage requiring another placeholder, construct language links immediately.
    $this->initLanguageLinks($routeKey, [ $this->entity->id ]);

    $this->sidebarInitToolbox($this->entity, $this->getSidebarItems());

    $this->stylesheets[] = "movie";
  }

}
