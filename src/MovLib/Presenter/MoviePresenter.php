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

use \MovLib\Entity\Language;
use \MovLib\View\HTML\ErrorView;
use \MovLib\Exception\DatabaseException;
use \MovLib\Model\MovieModel;
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
  
  /**
   * An array containing the full movie information.
   * @var array 
   */
  private $movie;
  
  /**
   * An array containing all releases of a movie.
   * @var array 
   */
  private $movieReleases;
  

  /**
   * The movie model instance that is associated with the requested movie ID and this presenter.
   *
   * @var \MovLib\Model\MovieModel
   */
  private $movieModel;

  /**
   * The releases model instance that is associated with the requested movie ID and this presenter.
   *
   * @var \MovLib\Model\ReleasesModel
   */
  private $releasesModel;

  /**
   * {@inheritdoc}
   */
  protected function init() {
    try {
      switch ($_SERVER["REQUEST_METHOD"]) {
        case "DELETE":
          break;
        case "POST":
          break;
        case "PUT":
          break;
        default:
          
          $this->movieModel = new MovieModel($this->language->getCode());
          $this->releasesModel = new ReleasesModel($this->language->getCode());
          
          $this->movie = $this->movieModel->getMovieFull($_SERVER["MOVIE_ID"]);
          $this->movieReleases = $this->releasesModel->getReleasesForMovie($_SERVER["MOVIE_ID"]);
      
          $this->output = (new MovieShowView($this))->getRenderedView();
          return $this;        
      }
      
    } catch (Exception $e) {
      var_dump($e);
    }
  }
  
  public function getTitle() {
    return $this->movie["display_title"];
  }
  
  public function getYear() {
    return $this->movie["year"];
  }

}
