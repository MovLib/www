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
namespace MovLib\Presenter;

use \MovLib\Model\MoviesModel;
use \MovLib\View\HTML\MoviesView;

/**
 * Present listings of movies.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MoviesPresenter extends AbstractPresenter {

  /**
   * The MoviesModel containing all the movies needed for the listing.
   *
   * @var \MovLib\Model\MoviesModel
   */
  public $moviesModel;

  /**
   * Initialize the MoviesPresenter.
   *
   * @global \MovLib\Model\I18nModel $i18n
   */
  public function __construct() {
    $this->moviesModel = new MoviesModel();
    new MoviesView($this);
  }

  public function getBreadcrumb() {

  }

  /**
   * Get secondary navigation points.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return array
   *   Points for the secondary navigation.
   */
  public function getSecondaryNavigation() {
    global $i18n;
    return [
      [ $i18n->r("/movies"), $i18n->t("Movies"), [
        "title" => $i18n->t("View the latest {0} additions to the database.", [ $i18n->t("movie") ])
      ]],
      [ $i18n->r("/persons"), $i18n->t("Persons"), [
        "title" => $i18n->t("View the latest {0} additions to the database.", [ $i18n->t("person") ])
      ]],
      [ $i18n->r("/help"), $i18n->t("Help"), [
        "title" => $i18n->t("View the latest {0} additions to the database.", [ $i18n->t("help") ])
      ]]
    ];
  }

}