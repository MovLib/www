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
use \MovLib\Model\ReleasesModel;


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

  /**
   * Associative array containing the complete data of this movie.
   *
   * @var array
   */
  private $movie;

  /**
   * {@inheritdoc}
   */
  protected function init() {
    return $this
      ->{__FUNCTION__ . $this->getMethod()}()
      ->setOutput()
    ;
  }

  /**
   * Render the movie's page.
   *
   * @return $this
   */
  protected function initGet() {
    $languageCode = $this->language->getCode();
    try {
      $this->movie = (new MovieModel($languageCode))->getMovieFull($_SERVER["MOVIE_ID"]);
      if ($this->movie["display"] === false) {
        return $this->setOutput("Error\\GoneMovie");
      }
      $this->movie["releases"] = (new ReleasesModel($languageCode))->getReleasesForMovie($_SERVER["MOVIE_ID"]);
      return $this->setOutput("Movie\\MovieShow");
    } catch (MovieException $e) {
      return $this->setOutput("Error\\NotFound");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBreadcrumb() {
    return [[ "href" => route("movies"), "text" => __("Movies") ]];
  }

  /**
   * Get the display title and year appended in brackets (if the movie has a year).
   *
   * @return string
   */
  public function getMovieDisplayTitleAndYear() {
    if ($this->movie["year"] !== "0000") {
      return "{$this->movie["display_title"]} ({$this->movie["year"]})";
    }
    return $this->movie["display_title"];
  }

  /**
   * Get the full path to the poster art.
   *
   * @param string $style
   *   The desired image style.
   * @return string
   *   Absolute path to the poster art for the desired image style.
   */
  public function getMoviePoster($style) {
    if ($this->movie["poster"]) {
      return "/uploads/poster/{$this->movie["id"]}/{$style}/{$this->movie["poster"]["file_name"]}.{$this->movie["poster"]["file_id"]}.{$this->movie["poster"]["extension"]}";
    }
  }

}
