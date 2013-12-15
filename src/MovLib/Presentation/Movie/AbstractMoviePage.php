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

use \MovLib\Presentation\Partial\Alert;

/**
 * Provides secondary breadcrumb, menu points and stylesheets for movie presentations.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractMoviePage extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie we are currently working with.
   *
   * @var \MovLib\Data\Movie\Full
   */
  protected $movie;

  /**
   * The movie's translated discussion route.
   *
   * @var string
   */
  protected $routeDiscussion;

  /**
   * The movie's translated edit route.
   *
   * @var string
   */
  protected $routeEdit;

  /**
   * The movie's translated history route.
   *
   * @var string
   */
  protected $routeHistory;

  /**
   * The movie's translated route.
   *
   * @var string
   */
  protected $routeMovie;

  /**
   * The translted route to the movies page.
   *
   * @var string
   */
  protected $routeMovies;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function init($title, $breadcrumbTitle = null) {
    global $i18n;

    // Substitue all routes for this movie once and for all (this has nothing to do with caching, we just don't want to
    // keep repeating us as these routes are needed A LOT).
    $this->routeDiscussion = $i18n->r("/movie/{0}/discussion", [ $_SERVER["MOVIE_ID"] ]);
    $this->routeEdit       = $i18n->r("/movie/{0}/edit", [ $_SERVER["MOVIE_ID"] ]);
    $this->routeHistory    = $i18n->r("/movie/{0}/history", [ $_SERVER["MOVIE_ID"] ]);
    $this->routeMovie      = $i18n->r("/movie/{0}", [ $_SERVER["MOVIE_ID"] ]);

    // Initialize the presentation.
    parent::init($title, $breadcrumbTitle);

    // Initialize the sidebar navigation.
    $this->initSidebar([
      [ $this->routeMovie, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
      [ $this->routeDiscussion, $i18n->t("Discuss"), [ "class" => "ico ico-discussion", "itemprop" => "discussionUrl" ] ],
      [ $this->routeEdit, $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $this->routeHistory, $i18n->t("History"), [ "class" => "ico ico-history separator" ] ],
    ]);

    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function getBreadcrumbs() {
    global $i18n;
    return [[ $i18n->rp("/movies"), $i18n->t("Movies") ]];
  }

  /**
   * Get the gone content for movie pages.
   *
   * Please note, that this method will also set the HTTP status code 410 (Gone).
   *
   * @global \MovLib\Data\I18n $i18n
   * @return \MovLib\Presentation\Partial\Alert
   */
  protected function getGoneContent() {
    global $i18n;
    http_response_code(410);
    return new Alert(
      "<p>{$i18n->t("The deletion message is provided below for reference.")}</p>" .
      /** @todo Provide commit message with history implementation. */
      "<p>{$i18n->t(
        "The movie and all its content has been deleted. A look at the edit {0}history{2} or {1}discussion{2} " .
        "will explain why that is the case. Please discuss with the person responsible for this deletion before " .
        "you restore this entry from its {0}history{2}.",
        [ "<a href='{$this->routeHistory}'>", "<a href='{$this->routeDiscussion}'>", "</a>" ]
      )}</p><p>{$i18n->t(
        "{0}Please note{1}: The images for this movie have been permanently deleted and cannot be restored.",
        [ "<strong>", "</strong>" ]
      )}</p>",
      $i18n->t("This Movie has been deleted."),
      Alert::SEVERITY_ERROR
    );
  }

}
