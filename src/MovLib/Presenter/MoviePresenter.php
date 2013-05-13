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

use \MovLib\Exception\DatabaseException;
use \MovLib\Model\MovieModel;
use \MovLib\View\HTML\Movie\ShowView;

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
class MoviePresenter extends AbstractPresenter{

  /**
   * The movie model instance that is associated with the requested movie ID and this presenter.
   *
   * @var \MovLib\Model\MovieModel
   */
  private $model;

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->model = new MovieModel();
    $view = "";
    try {
      $movie = $this->model->getMovieById($_SERVER["MOVIE_ID"]);
      $view = new ShowView($this->language, $movie);
    } catch (DatabaseException $e) {
      $view = new \MovLib\View\HTML\ErrorView($this->language, $e);
    }
    $this->output = $view->getRenderedView();
  }

}
