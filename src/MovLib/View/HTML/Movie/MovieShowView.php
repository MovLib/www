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
 * Shows a single movie.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieShowView extends AbstractView {

  /**
   * The movie presenter currently controlling this view.
   *
   * @var \MovLib\Presenter\MoviePresenter
   */
  protected $presenter;

  /**
   * Construct new movie show view.
   *
   * @param \MovLib\Presenter\MoviePresenter $presenter
   *  The movie presenter currently controlling this view.
   */
  public function __construct($presenter) {
    parent::__construct($presenter, $presenter->getMovieDisplayTitleAndYear(), [ "/assets/css/modules/movie.css" ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
//    if ($movie["poster"]) {
//      $posterFilePath = $this->presenter->getMoviePoster();
//      $posterAlt = __("@movieTitle poster.", [ "@movieTitle" => $this->title ]);
//    }
//    else {
//      $posterFilePath = "/assets/img/poster_w300_default.jpg";
//      $posterAlt = __("No poster art available.");
//    }
//
//    if (!($movie["synopsis"])) {
//      $synopsis = __("No synopsis available, do you want to !create it?", [ "!create" => route("/movie/%s/edit/synopsis", $movie["id"]) ]);
//    }

    return
      "<div class='row'>" .
      "<div class='page-header span span--0'><h1>{$this->title}</h1></div>" .
        "<figure id='movie-poster-box' class='pull-left span span--4 text-center'>" .
//          $this->a("#", "<img src='{$posterFilePath}' alt='{$posterAlt}'>" ) .
          "<ul id='movie-poster-box__more-posters'>" .
            "<li class='more-posters__thumbnail pull-left'></li>" .
            "<li class='more-posters__thumbnail pull-left'></li>" .
            "<li class='more-posters__thumbnail pull-left'></li>" .
            "<li class='more-posters__thumbnail pull-left'></li>" .
            "<li class='more-posters__thumbnail pull-left'></li>" .
          "</ul>" .
        "</figure>" .
        "<div class='span span--4c'>" .
//        (empty($this->presenter->getMovieSynopsis()) ? __("No synopsis has been added yet. Please click Edit to do so.") : $this->presenter->getMovieSynopsis()) .
        "</div>" .
      "</div>"
    ;
  }

}