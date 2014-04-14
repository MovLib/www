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

/**
 * Abstract base class for all movie presentations.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase extends \MovLib\Presentation\AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie we are currently working with.
   *
   * @var \MovLib\Data\Movie\FullMovie
   */
  protected $movie;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize the page's breadcrumb.
   *
   * @param array $breadcrumbs [optional]
   *   Numeric array containing additional breadcrumbs to put between home and the current page.
   * @return this
   */
  protected function initBreadcrumb(array $breadcrumbs = []) {
    array_unshift($breadcrumbs, [ $this->movie->route, $this->movie->displayTitle ]);
    array_unshift($breadcrumbs, [ $this->intl->rp("/movies"), $this->intl->t("Movies") ]);
    return parent::initBreadcrumb($breadcrumbs);
  }

  /**
   * Initialize the movie sidebar.
   *
   * @return this
   */
  protected function initSidebar() {
    $routeArgs = [ $this->movie->id ];
    return $this->traitSidebarInit([
      [ $this->movie->route, $this->intl->t("View"), [ "class" => "ico ico-view" ] ],
      [ $this->intl->r("/movie/{0}/discussion", $routeArgs), $this->intl->t("Discuss"), [ "class" => "ico ico-discussion", "property" => "discussionUrl" ] ],
      [ $this->intl->r("/movie/{0}/edit", $routeArgs), $this->intl->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $this->intl->r("/movie/{0}/history", $routeArgs), $this->intl->t("History"), [ "class" => "ico ico-history" ] ],
      [ $this->intl->r("/movie/{0}/delete", $routeArgs), $this->intl->t("Delete"), [ "class" => "ico ico-delete separator" ] ],

      [ $this->intl->r("/movie/{0}/cast", $routeArgs), $this->intl->t("Cast"), [ "class" => "ico ico-person" ] ],
      [ $this->intl->r("/movie/{0}/crew", $routeArgs), $this->intl->t("Crew"), [ "class" => "ico ico-company separator" ] ],
    ]);
  }

  /**
   * Get the gone content for movie pages.
   *
   * Please note, that this method will also set the HTTP status code 410 (Gone).
   *
   * @return \MovLib\Presentation\Partial\Alert
   */
  protected function getGoneContent() {
    http_response_code(410);
    return new Alert(
      "<p>{$this->intl->t("The deletion message is provided below for reference.")}</p>" .
      /** @todo Provide commit message with history implementation. */
      "<p>{$this->intl->t(
        "The movie and all its content has been deleted. A look at the edit {0}history{2} or {1}discussion{2} " .
        "will explain why that is the case. Please discuss with the person responsible for this deletion before " .
        "you restore this entry from its {0}history{2}.",
        [ "<a href='{$this->routeHistory}'>", "<a href='{$this->routeDiscussion}'>", "</a>" ]
      )}</p><p>{$this->intl->t(
        "{0}Please note{1}: The images for this movie have been permanently deleted and cannot be restored.",
        [ "<strong>", "</strong>" ]
      )}</p>",
      $this->intl->t("This Movie has been deleted."),
      Alert::SEVERITY_ERROR
    );
  }

}
