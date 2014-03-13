<?php

/* !
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
namespace MovLib\Presentation\Partial\Listing\Movie;

use \MovLib\Data\Image\MoviePoster;

/**
 * List all movie's a person has participated in.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MoviePersonListing extends \MovLib\Presentation\Partial\Listing\Movie\AbstractListing {

  /**
   * Format the given full movie's list entry.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Movie\FullMovie $movie
   *   The full movie to format.
   * @param array $list
   *   Numeric array containing the formatted movies where the key is the movie's unique identifier.
   * @return $this
   */
  protected function formatListItem($movie, &$list) {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($movie instanceof \MovLib\Data\Movie\FullMovie)) {
      throw new \InvalidArgumentException("\$movie must be of type \\MovLib\\Data\\Movie\\FullMovie");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Format the movie.
    $list[$movie->id]["#movie"] =
      "{$this->getImage(
          $movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_01),
          $movie->route,
          null,
          [ "class" => "s s1 tac" ]
        )}<div class='s s5'>{$this->formatTitle($movie)}{$this->formatGenres($movie)}</div>"
    ;

    // Prepare jobs array.
    $list[$movie->id]["#jobs"] = null;

    // Directly add the director if this person directed this movie (this can't happen multiple times).
    if (isset($movie->director)) {
      $list[$movie->id]["#jobs"] .=
        "<li>" .
          "<a href='{$i18n->r("/job/{0}", [ $movie->director ])}'>{$i18n->t("Director")}</a>" .
        "</li>"
      ;
    }

    // Directly add the job if jobTitle and jobId are set.
    if (isset($movie->jobTitle) && isset($movie->jobId)) {
      $list[$movie->id]["#jobs"] .=
        "<li>" .
          "<a href='{$i18n->r("/job/{0}", [ $movie->jobId ])}'>{$movie->jobTitle}</a>" .
        "</li>"
      ;
    }

    return $this;
  }

}
