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

use \MovLib\Partial\Date;

/**
 * Add various movie formatting functions to presentation.
 *
 * @property \MovLib\Presentation\AbstractPresenter $this
 * @property \MovLib\Core\Intl $intl
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait MovieTrait {

  /**
   * Get the movie's genres formatted as labels.
   *
   * @param array|null $genres
   *   The genres to format as labels.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to <var>$tag</var>, defaults to an empty array.
   * @param string $tag [optional]
   *   The tag used to enclose the labels, defaults to <code>"small"</code>.
   * @param string $labelTag [optional]
   *   The HTML tag that should be used for the label, defaults to <code>"h3"</code>.
   * @param boolean $hideLabel [optional]
   *   Whether to hide the label or not, defaults to <code>TRUE</code> (hide the label).
   * @return string
   *   The movie's genres formatted as labels, or <code>NULL</code> if there were no genres to format.
   */
  final protected function getGenreLabels($genres, array $attributes = null, $tag = "section", $labelTag = "h3", $hideLabel = true) {
    if ($genres) {
      $formatted = null;
      /* @var $genre \MovLib\Data\Genre\Genre */
      foreach ($genres as $genre) {
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

  /**
   * {@inheritdoc}
   */
  protected function getSidebarItems() {
    $items = [];
    if ($this->entity->deleted) {
      return $items;
    }
    foreach ([
      [ "person", "cast", $this->intl->t("Cast"), null ],
      [ "company", "crew", $this->intl->t("Crew"), null ],
      [ "release", "releases", $this->intl->t("Releases"), $this->entity->countReleases ],
      [ "award separator", "awards", $this->intl->t("Awards"), $this->entity->countAwards ],
    ] as list($icon, $plural, $title, $count)) {
      if (isset($count)) {
        $count =  "<span class='fr'>{$this->intl->format("{0,number}", $count)}</span>";
      }
      $items[] = [
        $this->intl->r("/movie/{0}/{$plural}", $this->entity->id),
        "{$title} {$count}",
        [ "class" => "ico ico-{$icon}" ]
      ];
    }
    return $items;
  }

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
  final protected function getStructuredDisplayTitle(\MovLib\Data\Movie\Movie $movie, $linkTitle = true, $linkYear = false) {
    $property = ($movie->displayTitle == $movie->originalTitle) ? "name" : "alternateName";
    $title    = "<span{$this->lang($movie->displayTitleLanguageCode)} property='{$property}'>{$movie->displayTitle}</span>";
    if ($movie->year) {
      $title = $this->intl->t("{0} ({1})", [ $title, (new Date($this->intl, $this))->formatYear(
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
  final protected function getStructuredOriginalTitle(\MovLib\Data\Movie\Movie $movie, $wrap = null, array $wrapAttributes = null) {
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

  /**
   * Get the movie's tagline enhanced with structured data.
   *
   * @param \MovLib\Data\Movie\Movie $movie
   *   The movie to get the tagline for.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the tagline.
   * @param string $tag [optional]
   *   The HTML tag used to wrap the tagline.
   * @return string
   *   The movie's tagline enhanced with structured data, <code>NULL</code> if there is no tagline to format.
   */
  final protected function getStructuredTagline(\MovLib\Data\Movie\Movie $movie, array $attributes = [], $tag = "blockquote") {
    if ($movie->tagline) {
      $attributes["lang"]     = $movie->taglineLanguageCode;
      $attributes["property"] = "headline";
      return "<{$tag}{$this->expandTagAttributes($attributes)}>{$movie->tagline}</{$tag}>";
    }
  }

}
