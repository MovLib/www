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
   * @var \MovLib\Model\RatingModel
   */
  public $ratingModel;
  /**
   * The display title of the movie to display.
   * @var string
   */
  public $displayTitle;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __construct() {
    return $this
      ->{__FUNCTION__ . $this->getMethod()}()
      ->setPresentation()
    ;
  }

  /**
   * Render the movie's page.
   *
   * @return this
   */
  protected function __constructGet() {
    try {
      $this->movieModel = new MovieModel($_SERVER["MOVIE_ID"]);
      if ($this->movieModel->deleted === true) {
        return $this->setPresentation("Error\\GoneMovie");
      }
      $this->ratingModel = new RatingModel();
      $this->releasesModel = (new ReleasesModel())->__constructFromMovieId($this->movieModel->id);
      if (!empty($this->movieModel->getTitleDisplay())) {
        $this->displayTitle = $this->movieModel->getTitleDisplay()["title"];
      }
      else {
        $this->displayTitle = $this->movieModel->originalTitle;
      }
      $this->view = new MovieShowView($this);
      return $this;
    } catch (MovieException $e) {
      return $this->setPresentation("Error\\NotFound");
    }
  }

  /**
   * Handle form submissions.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
   *   The global user model instance.
   * @return this
   */
  protected function __constructPost() {
    global $i18n, $user;
    try {
      $this->movieModel = new MovieModel($_SERVER["MOVIE_ID"]);
      if ($this->movieModel->deleted === true) {
        return $this->setPresentation("Error\\GoneMovie");
      }
      $this->releasesModel = (new ReleasesModel())->__constructFromMovieId($this->movieModel->id);
      // Construct the title of the page from the movie's display title or the original title if no display title exists.
      $languages = $i18n->getLanguages();
      $titles = $this->movieModel->getTitles();
      $count = count($titles);
      $this->displayTitle = $this->movieModel->originalTitle;
      for ($i = 0; $i < $count; ++$i) {
        if ($titles[$i]["isDisplayTitle"] === true && $languages[ $titles[$i]["languageId"] ]["code"] === $i18n->languageCode) {
          $this->displayTitle = $titles[$i]["title"];
          break;
        }
      }
      $this->view = new MovieShowView($this);
      if ($user->isLoggedIn) {
        /** @todo Rate the movie for the current user. */
      } else {
        $this->view->setAlert($i18n->t("You have to be logged in to rate this movie."));
      }
      return $this;
    } catch (MovieException $e) {
      return $this->setPresentation("Error\\NotFound");
    }
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * {@inheritdoc}
   */
  public function getBreadcrumb() {
    global $i18n;
    return [[ $i18n->r("/movies"), $i18n->t("Movies") ]];
  }

  /**
   * Get the full path to the poster art.
   *
   * @param string $style
   *   The desired image style.
   *   @todo Examples
   * @return string
   *   Absolute path to the poster art for the desired image style.
   */
  public function getMoviePoster($style) {
    if ($this->movieModel["poster"]) {
      return "/uploads/poster/{$this->movieModel["id"]}/{$style}/{$this->movieModel["poster"]["file_name"]}.{$this->movieModel["poster"]["file_id"]}.{$this->movieModel["poster"]["extension"]}";
    }
  }

}
