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
namespace MovLib\Partial\Helper;

use \MovLib\Partial\Date;

/**
 * Movie Helper Methodes.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class MovieHelper extends \MovLib\Core\Presentation\DependencyInjectionBase {

  /**
   * Get the movie's display title enhanced with structured data.
   *
   * @param \MovLib\Data\Movie\Movie $movie
   *   The movie to get the display title for.
   * @param boolean $linkTitle [optional]
   *   Whether to link the movie to its show or not, defaults to <code>TRUE</code>.
   * @param boolean $linkYear [optional]
   *   Whether to link the year to it's movie index or not, defaults to <code>FALSE</code>.
   * @return string
   *   The movie's display title enhanced with structured data.
   */
  final public function getStructuredDisplayTitle(\MovLib\Data\Movie\Movie $movie, $linkTitle = true, $linkYear = false) {
    $property = ($movie->displayTitle == $movie->originalTitle) ? "name" : "alternateName";
    $title    = "<span{$this->lang($movie->displayTitleLanguageCode)} property='{$property}'>{$movie->displayTitle}</span>";
    if ($movie->year) {
      $title = $this->intl->t("{0} ({1})", [ $title, (new Date($this->intl, $this->presenter))->formatYear(
        $movie->year,
        [ "property" => "datePublished" ],
        $linkYear ? [ "href" => $this->intl->r("/year/{0}/movies", $movie->year->year) ] : null
      ) ]);
    }
    if ($linkTitle) {
      return "<a href='{$movie->route}' property='url'>{$title}</a>";
    }
    return $title;
  }

  /**
   * Get the movie's original title enhanced with structured data.
   *
   * @param \MovLib\Data\Movie\Movie $movie
   *   The movie to get the original title for.
   * @param null|string $wrap [optional]
   *   Optional wrapper tag to enclose the original title, defaults to <code>NULL</code> (don't wrap).
   * @param null|array $wrapAttributes [optional]
   *   Additional attributes the should be applied to the wrapper, defaults to <code>NULL</code>.
   * @return null|string
   *   Get the movie's original title enhanced with structured data, <code>NULL</code> if display and original title are
   *   equal.
   */
  final public function getStructuredOriginalTitle(\MovLib\Data\Movie\Movie $movie, $wrap = null, array $wrapAttributes = null) {
    if ($movie->displayTitle != $movie->originalTitle) {
      $title = $this->intl->t(
        "{0} ({1})",
        [
          "<span{$this->lang($movie->originalTitleLanguageCode)} property='name'>{$movie->originalTitle}</span>",
          "<i>{$this->intl->t("original title")}</i>",
        ]
      );
      if ($wrap) {
        return "<{$wrap}{$this->expandTagAttributes($wrapAttributes)}>{$title}</{$wrap}>";
      }
      return $title;
    }
  }

}
