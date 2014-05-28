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
namespace MovLib\Partial;

/**
 * Defines generic listings with structured data.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Genre extends \MovLib\Core\Presentation\DependencyInjectionBase {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Genre";
  // @codingStandardsIgnoreEnd

  /**
   * Format an array of genres as comma separated list.
   *
   * @param array $genres
   *   Array containing the genres to format.
   * @return string
   *   The genres formatted as comma separated list.
   */
  public function getList(array $genres) {
    $list = null;

    /// The "," is used to separate list items, please note the space after the comma!
    $comma = $this->intl->t(", ");

    /* @var $genre \MovLib\Data\Genre\Genre */
    foreach ($genres as $genre) {
      if ($list) {
        $list .= $comma;
      }
      $list .= "<a href='{$genre->route}' property='genre'>{$genre->name}</a>";
    }

    return $list;
  }

  /**
   * Get the movie's genres formatted as labels.
   *
   * @param \MovLib\Data\Genre\GenreSet $genreSet
   *   The genres to format as labels, can be <code>NULL</code> in which case this method returns <code>NULL</code>.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to <var>$tag</var>, defaults to <code>NULL</code>.
   * @param string $tag [optional]
   *   The tag used to enclose the labels, defaults to <code>"small"</code>.
   * @param string $labelTag [optional]
   *   The HTML tag that should be used for the label, defaults to <code>"h3"</code>.
   * @param boolean $hideLabel [optional]
   *   Whether to hide the label or not, defaults to <code>TRUE</code> (hide the label).
   * @return null|string
   *   The movie's genres formatted as labels, or <code>NULL</code> if there were no genres to format.
   */
  public function getLabels(\MovLib\Data\Genre\GenreSet $genreSet = null, array $attributes = null, $tag = "section", $labelTag = "h3", $hideLabel = true) {
    if ($genreSet) {
      $formatted = null;
      /* @var $genre \MovLib\Data\Genre\Genre */
      foreach ($genreSet as $genre) {
        if ($formatted) {
          $formatted .= " ";
        }
        $formatted .= "<a class='label' href='{$genre->route}' property='genre'>{$genre->name}</a>";
      }
      if ($formatted) {
        $hideLabel = $hideLabel ? " class='vh'" : null;
        return "<{$tag}{$this->expandTagAttributes($attributes)}><{$labelTag}{$hideLabel}>{$this->intl->t("{0}:", $this->intl->t("Genres"))}</{$labelTag}> {$formatted}</{$tag}>";
      }
    }
  }

}
