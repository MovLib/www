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
namespace MovLib\Data\Image;

/**
 * Represents a single poster of a movie.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MoviePoster extends \MovLib\Data\Image\MovieImage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The movie image's type identifier.
   *
   * @var integer
   */
  const TYPE_ID = 2;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * @inheritdoc
   */
  protected $placeholder = "poster";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie poster.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param integer $movieId
   *   The unique movie's identifier this image belongs to.
   * @param string $movieTitle
   *   The display title (with year) of the movie this image belongs to.
   * @param null|integer $id [optional]
   *   The identifier of the movie image that should be loaded from the database. If none is passed (default) an empty
   *   movie image is created, ready for creating a new movie image.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Preentation\Error\NotFound
   */
  public function __construct($movieId, $movieTitle, $id = null) {
    global $i18n;
    $this->init($movieId, $id, "poster", $i18n->t("Poster for {title}", [ "title" => $movieTitle ]));
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the <var>$style</var> for this movie poster.
   *
   * The movie poster placeholder has the US one sheet dimensions, unlike most other placeholder images which are square.
   *
   * @param mixed $style
   *   The desired style, use the objects <var>STYLE_*</var> class constants. Defaults to <var>STYLE_SPAN_02</var>.
   * @return \MovLib\Data\Image\Style
   *   The image's desired style object.
   */
  public function getStyle($style = self::STYLE_SPAN_02) {
    if ($this->imageExists === false && !isset($this->styles[$style])) {
      if (!is_array($this->styles)) {
        $this->styles = [];
      }
      $this->styles[$style] = [ "width" => $style, "height" => ceil(($style / 27) * 40) ];
    }
    return parent::getStyle($style);
  }

}
