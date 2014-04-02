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

use \MovLib\Data\Movie\MovieSet;

/**
 * Defines the movie index presentation.
 *
 * @link http://schema.org/Movie
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/movies
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/movies
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/movies
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/movies
 *
 * @property \MovLib\Data\Movie\MovieSet $set
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {
  use \MovLib\Presentation\Movie\MovieTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this->initIndex(new MovieSet($this->diContainerHTTP), $this->intl->t("Create New Movie"));
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Movie\Movie $movie {@inheritdoc}
   */
  protected function formatListingItem(\MovLib\Data\EntityInterface $movie, $id) {
    return
      "<li class='hover-item r'>" .
        "<article typeof='Movie'>" .
          "<a class='no-link s s1' href='{$movie->getRoute()}'>" .
            "<img alt='' src='{$this->getExternalURL("asset://img/logo/vector.svg")}' width='60' height='60'>" .
          "</a>" .
          "<div class='s s8'>" .
            "<h2 class='para'>{$this->getStructuredDisplayTitle($movie)}</h2>" .
            $this->getStructuredOriginalTitle($movie, "small") .
            $this->getStructuredTagline($movie, [ "class" => "small" ]) .
            $this->getGenreLabels($movie->genres, [ "class" => "small" ]) .
          "</div>" .
          "<div class='s s1 rating-mean tac'>{$this->intl->format("{0,number}", $movie->meanRating)}</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return new Alert(
      "<p>{$this->intl->t("We couldn’t find any movies matching your filter criteria, or there simply aren’t any movies available.")}</p>" .
      "<p>{$this->intl->t("Would you like to {0}create a movie{1}?", [ "<a href='{$this->intl->r("/movie/create")}'>", "</a>" ])}</p>",
      $this->intl->t("No Movies")
    );
  }

}
