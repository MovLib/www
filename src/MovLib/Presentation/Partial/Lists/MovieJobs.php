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
namespace MovLib\Presentation\Partial\Lists;

use \MovLib\Data\Image\MoviePoster;

/**
 * Listing of a person's movie jobs.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieJobs extends \MovLib\Presentation\Partial\Lists\AbstractMovieList {

  /**
   * @inheritdoc
   */
  protected function formatItem($movie, $entity) {
    global $i18n;
    $genres = null;
    $result = $movie->getGenres();
    while ($row = $result->fetch_assoc()) {
      if ($genres) {
        $genres .= "&nbsp;";
      }
      $genres      .= "<a class='label' href='{$i18n->r("/genre/{0}", [ $row["id"] ])}'>{$row["name"]}</a>";
    }
    if ($genres) {
      $genres = "<p class='small'>{$genres}</p>";
    }

    $job = null;
    if (isset($movie->jobTitle)) {
      if ($movie->jobTitle === "Actor" && isset($this->person->sex)) {
        if ($this->person->sex === 1) {
          $movie->jobTitle = $i18n->t("Actor");
        }
        elseif ($this->person->sex === 2) {
          $movie->jobTitle = $i18n->t("Actress");
        }
        else {
          $movie->jobTitle = $i18n->t("Actor/Actress");
        }
        if (!empty($movie->role)) {
          $movie->jobTitle = $i18n->t("{job} ({role})", [ "job" => $movie->jobTitle, "role" => $movie->role ]);
        }
      }
      else {
        $movie->jobTitle = $i18n->t($movie->jobTitle);
      }
      $job = "<p class='small'>{$movie->jobTitle}</p>";
    }


    // Put the movie list entry together.
    return
      "<li{$this->expandTagAttributes($this->listItemsAttributes)}>" .
        "<div class='li r'>" .
          "<a class='img s s1 tac' href='{$movie->route}' itemprop='url'>" .
            $this->getImage($movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_01), false, [ "itemprop" => "image" ]) .
          "</a>" .
          "<span class='s s{$this->descriptionSpan}'><p>{$this->getTitleInfo($movie, [ "href" => $movie->route, "itemprop" => "url" ], "a")}<p>{$genres}{$job}</span>" .
        "</a>" .
      "</li>";
  }

}
