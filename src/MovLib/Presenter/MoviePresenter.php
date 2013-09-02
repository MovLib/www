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

use \MovLib\Exception\MovieException;
use \MovLib\Model\MovieModel;
use \MovLib\Model\RatingModel;
use \MovLib\Model\ReleasesModel;
use \MovLib\View\HTML\Movie\MovieShowView;
use \MovLib\View\HTML\Error\GoneView;


/**
 * Description of MoviePresenter
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MoviePresenter extends AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie model containing all the data of this movie.
   *
   * @var \MovLib\Model\MovieModel
   */
  public $movieModel;

  /**
   * The rating model to retrieve the movie's rating data.
   *
   * @var \MovLib\Model\RatingModel
   */
  public $ratingModel;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __construct() {
    global $i18n, $user;
    try {
      $this->movieModel = new MovieModel($_SERVER["MOVIE_ID"]);
      if ($this->movieModel->deleted === true) {
        $this->view = new GoneView($this);
        return;
      }
      $this->ratingModel = new RatingModel();
      $this->releasesModel = (new ReleasesModel())->__constructFromMovieId($this->movieModel->id);
      $this->view = new MovieShowView($this);
      if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if ($user->isLoggedIn) {
          /** @todo Rate the movie for the current user. */
        } else {
          $this->view->setAlert($i18n->t("You have to be logged in to rate this movie."));
        }
      }
    } catch (MovieException $e) {
      $this->view = new NotFoundView($this);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * @inheritdoc
   */
  public function getBreadcrumb() {
    global $i18n;
    return [[ $i18n->r("/movies"), $i18n->t("Movies") ]];
  }

  /**
   * Get the secondary navigation array for the movie page.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return array
   *   The secondary navigation point array for the movie page.
   */
  public function getSecondaryNavigation() {
    global $i18n;
    return [
      [ $i18n->r("/movie/{0}", [ $this->movieModel->id ]), "<i class='icon icon--eye'></i>{$i18n->t("View")}", [
        "accesskey" => "v",
        "title"     => $i18n->t("View the {0}.", [ $i18n->t("movie") ]),
      ]],
      [ $i18n->r("/movie/{0}/discussion", [ $this->movieModel->id ]), "<i class='icon icon--comment'></i>{$i18n->t("Discuss")}", [
        "accesskey" => "d",
        "title"     => $i18n->t("Discussion about the {0}.", [ $i18n->t("movie") ])
      ]],
      [ $i18n->r("/movie/{0}/edit", [ $this->movieModel->id ]), "<i class='icon icon--pencil'></i>{$i18n->t("Edit")}", [
        "accesskey" => "e",
        "title"     => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]),
      ]],
      [ $i18n->r("/movie/{0}/history", [ $this->movieModel->id ]), "<i class='icon icon--history'></i>{$i18n->t("History")}", [
        "accesskey" => "h",
        "class"     => "menuitem--separator",
        "title"     => $i18n->t("Past versions of this {0}.", [ $i18n->t("movie") ]),
      ]]
    ];
  }

}
