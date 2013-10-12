<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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

use \MovLib\Data\Movie;
use \MovLib\Exception\MovieException;
use \MovLib\Exception\Client\ErrorNotFoundException;
use \MovLib\Presentation\Partial\Alert;

/**
 * Provides secondary breadcrumb, menu points and stylesheets for movie presentations.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractMoviePage extends \MovLib\Presentation\AbstractSecondaryNavigationPage {

  /**
   * The movie to display.
   *
   * @var \MovLib\Data\Movie
   */
  protected $model;

  /**
   * @inheritdoc
   */
  protected function getBreadcrumbs() {
    global $i18n;
    return [ [ $i18n->r("/movies"), $i18n->t("Movies") ] ];
  }

  /**
   * @inheritdoc
   */
  protected function init($title) {
    $this->stylesheets[] = "modules/movie.css";
    return parent::init($title);
  }

  /**
   * Initialize the movie model and the title.
   *
   * @throws ErrorNotFoundException
   */
  protected function initMovie() {
    try {
      $this->model = new Movie($_SERVER["MOVIE_ID"]);
      $this->title = $this->model->getDisplayTitle();
      if (isset($this->model->year)) {
        $this->title .= " ({$this->model->year})";
      }
    } catch (MovieException $e) {
      throw new ErrorNotFoundException($e);
    }
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
    // Status code for "Gone".
    http_response_code(410);
    $gone = new Alert(
      "<p>{$i18n->t("The deletion message is provided below for reference.")}</p>" .
      /** @todo Provide commit message with history implementation. */
      "<p>" .
        $i18n->t(
          "The movie and all its content has been deleted. A look at the edit {0}history{2} or {1}discussion{2} " .
          "will explain why that is the case. Please discuss with the person responsible for this deletion before " .
          "you restore this entry from its {0}history{2}.",
          [
            "<a href='{$i18n->r("/movie/{0}/history", [ $this->model->id ])}'>",
            "<a href='{$i18n->r("/movie/{0}/discussion", [ $this->model->id ])}'>",
            "</a>",
          ]
        ) .
      "</p>" .
      "<p>{$i18n->t("{0}Please note{1}: The images for this movie have been permanently deleted and cannot be restored.", [ "<strong>", "</strong>" ])}</p>"
    );
    $gone->title = $i18n->t("This Movie has been deleted.");
    $gone->severity = Alert::SEVERITY_ERROR;
    return $gone;
  }

  /**
   * @inheritdoc
   */
  public function getSecondaryNavigationMenuItems() {
    global $i18n;
    return [
      [ $i18n->r("/movie/{0}", [ $this->model->id ]), "<i class='icon icon--eye'></i>{$i18n->t("View")}", [
        "accesskey" => "v",
        "title"     => $i18n->t("View the {0}.", [ $i18n->t("movie") ]),
      ]],
      [ $i18n->r("/movie/{0}/discussion", [ $this->model->id ]), "<i class='icon icon--comment'></i>{$i18n->t("Discuss")}", [
        "accesskey" => "d",
        "title"     => $i18n->t("Discussion about the {0}.", [ $i18n->t("movie") ])
      ]],
      [ $i18n->r("/movie/{0}/edit", [ $this->model->id ]), "<i class='icon icon--pencil'></i>{$i18n->t("Edit")}", [
        "accesskey" => "e",
        "title"     => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]),
      ]],
      [ $i18n->r("/movie/{0}/history", [ $this->model->id ]), "<i class='icon icon--history'></i>{$i18n->t("History")}", [
        "accesskey" => "h",
        "class"     => "separator",
        "title"     => $i18n->t("Past versions of this {0}.", [ $i18n->t("movie") ]),
      ]]
    ];
  }

}
