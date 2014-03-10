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
namespace MovLib\Presentation\Partial\Listing\Movie;

/**
 * Base class for movie listings.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractListing extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The MySQLi result containing the full movies.
   *
   * @var \mysqli_result
   */
  protected $result;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie listing.
   *
   * @param \mysqli_result $result
   *   The MySQLi result containing the movies.
   */
  public function __construct($result) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($result instanceof \mysqli_result)) {
      throw new \InvalidArgumentException("\$result must be of type \\mysqli_result");
    }
    // @devEnd
    // @codeCoverageIgnoreEnd
    $this->result = $result;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format the given full movie's list entry.
   *
   * @param \MovLib\Data\Movie\FullMovie $movie
   *   The full movie to format.
   * @param mixed $list
   *   The list, depends on the concrete implementation.
   * @return $this
   */
  abstract protected function formatListItem($movie, &$list);

  /**
   * Get the full movie's formatted genres.
   *
   * @param \MovLib\Data\Movie\FullMovie $movie
   *   The full movie for which the genres should be formatted.
   * @return string
   *   The full movie's formatted genres.
   */
  protected function formatGenres($movie) {
    $result = $movie->getGenres();
    $genres = null;
    while ($genre = $result->fetch_assoc()) {
      if ($genres) {
        $genres .= " ";
      }
      $genres .= "<span class='label' property='genre'>{$genre["name"]}</span>";
    }
    if ($genres) {
      return "<small>{$genres}</small>";
    }
  }

  /**
   * Get the full movie's formatted title.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Movie\FullMovie $movie
   *   The full movie for which the title should be formatted.
   * @param array $attributes [optional]
   *   The full movie title's attributes array.
   * @param string $tag [optional]
   *   The HTML tag with which the formatted movie's title should be enclosed.
   * @return string
   *   The full movie's formatted title.
   */
  protected function formatTitle($movie, array $attributes = [], $tag = "p") {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($movie instanceof \MovLib\Data\Movie\FullMovie)) {
      throw new \InvalidArgumentException("\$movie must be of type \\MovLib\\Data\\Movie\\FullMovie");
    }
    if (empty($tag)) {
      throw new \InvalidArgumentException("\$tag must be a valid HTML tag (e.g. 'p')");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // We have to use different structured data if display and original title differ.
    if ($movie->displayTitle != $movie->originalTitle) {
      $property      = "alternateName";
      $originalTitle = "<br><span class='small'>{$i18n->t("{0} ({1})", [
        "<span property='name'{$this->lang($movie->originalTitleLanguageCode)}>{$movie->originalTitle}</span>",
        "<i>{$i18n->t("original title")}</i>",
      ])}</small>";
    }
    // Simply clear the original title if it's the same as the display title.
    else {
      $property      = "name";
      $originalTitle = null;
    }

    // Put the title together.
    $title = "<a href='{$movie->route}' property='url'><span property='{$property}'{$this->lang($movie->displayTitleLanguageCode)}>{$movie->displayTitle}</span>{0}</a>";

    // Append year with structured data if available.
    if ($movie->year) {
      $title = str_replace("{0}", " (<span property='datePublished'>{$movie->year}</span>)", $title);
    }
    else {
      $title = str_replace("{0}", "", $title);
    }

    // Put it all together and we're done. Note that we always need the <span> element around the title, even if the
    // original title is the same as the display title and no year is present. This is because the class 'link-color'
    // is applied and this has to be an inline tag.
    return "<{$tag}{$this->expandTagAttributes($attributes)}>{$title}{$originalTitle}</{$tag}>";
  }

  /**
   * Get the formatted movie listing.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The formatted movie listing.
   */
  public function getListing() {
    $list = null;
    while ($movie = $this->result->fetch_object("\\MovLib\\Data\\Movie\\FullMovie")) {
      $this->formatListItem($movie, $list);
    }
    if ($list) {
      return $list;
    }
  }

}
