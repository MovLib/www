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
 * Abstract base class for all movie presentations.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar {
    sidebarInit as traitSidebarInit;
  }


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
   * @global \MovLib\Data\I18n $i18n
   * @param array $breadcrumbs [optional]
   *   Numeric array containing additional breadcrumbs to put between home and the current page.
   * @return this
   */
  protected function initBreadcrumb(array $breadcrumbs = []) {
    global $i18n;
    array_unshift($breadcrumbs, [ $this->movie->route, $this->movie->displayTitleWithYear ]);
    array_unshift($breadcrumbs, [ $i18n->rp("/movies"), $i18n->t("Movies") ]);
    return parent::initBreadcrumb($breadcrumbs);
  }

  /**
   * Initialize the movie sidebar.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   */
  protected function initSidebar() {
    global $i18n;
    $routeArgs = [ $this->movie->id ];
    return $this->traitSidebarInit([
      [ $this->movie->route, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
      [ $i18n->r("/movie/{0}/discussion", $routeArgs), $i18n->t("Discuss"), [ "class" => "ico ico-discussion", "property" => "discussionUrl" ] ],
      [ $i18n->r("/movie/{0}/edit", $routeArgs), $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $i18n->r("/movie/{0}/history", $routeArgs), $i18n->t("History"), [ "class" => "ico ico-history" ] ],
      [ $i18n->r("/movie/{0}/delete", $routeArgs), $i18n->t("Delete"), [ "class" => "ico ico-delete separator" ] ],

      [ $i18n->r("/movie/{0}/cast", $routeArgs), $i18n->t("Cast"), [ "class" => "ico ico-person" ] ],
      [ $i18n->r("/movie/{0}/crew", $routeArgs), $i18n->t("Crew"), [ "class" => "ico ico-company separator" ] ],
    ]);
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
