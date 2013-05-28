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
namespace MovLib\View\HTML\Movie;

use \MovLib\View\HTML\AbstractView;

/**
 * Description of MovieView
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieShowView extends AbstractView {

  /**
   * @var \MovLib\Presenter\MoviePresenter
   */
  protected $presenter;

  /**
   * The Constructor for the movie show view.
   *
   * @param \MovLib\Presenter\MoviePresenter $presenter
   *  The MoviePresenter to be used.
   */
  public function __construct($presenter) {
    parent::__construct($presenter, SITENAME);
    $this->title = $presenter->getMovieTitle();
    $this->title .= $presenter->getMovieYear() == "0000" ? "" : " ({$presenter->getMovieYear()})";
    $this->addStylesheet("/assets/css/modules/movie.css");
  }


  public function getRenderedContent() {
    $posterFilePath = "/assets/img/poster_w300_default.jpg";
    $posterAlt = __("No poster available.");
    $posterFileName = $this->presenter->getDisplayPosterFileName();

    if ($posterFileName !== false) {
      $posterFilePath = "/uploads/posters/{$this->presenter->geMovietId()}/w300/{$posterFileName}";
      $posterAlt = sprintf(__("%s poster."), $this->title);
    }

    return
      "<div class='row'>" .
      "<div class='page-header span span--0'><h1>{$this->title}</h1></div>" .
        "<figure id='movie-poster-box' class='pull-left span span--4 text-center'>" .
          $this->a("#", "<img src='{$posterFilePath}' alt='{$posterAlt}'>" ) .
          "<ul id='movie-poster-box__more-posters'>" .
            "<li class='more-posters__thumbnail pull-left'></li>" .
            "<li class='more-posters__thumbnail pull-left'></li>" .
            "<li class='more-posters__thumbnail pull-left'></li>" .
            "<li class='more-posters__thumbnail pull-left'></li>" .
            "<li class='more-posters__thumbnail pull-left'></li>" .
          "</ul>" .
        "</figure>" .
        "<div class='span span--4c'>" .
        (empty($this->presenter->getMovieSynopsis()) ? __("No synopsis has been added yet. Please click Edit to do so.") : $this->presenter->getMovieSynopsis()) .
        "</div>" .
      "</div>"
    ;
  }

}