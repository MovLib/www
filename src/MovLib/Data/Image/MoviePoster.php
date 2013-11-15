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

use \MovLib\Data\Image\Style;

/**
 * @todo Description of MoviePoster
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MoviePoster extends \MovLib\Data\Image\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  const IMAGE_STYLE_SPAN_08 = \MovLib\Data\Image\SPAN_08;


  // ------------------------------------------------------------------------------------------------------------------- Properties

  protected $alternativeText;

  protected $imageDirectory = "movie";

  public $id;

  /**
   * The movie this poster belongs to.
   *
   * @var \MovLib\Data\Movie\Movie
   */
  public $movie;

  public $route;

  private $type = 0;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Movie\Movie $movie
   * @param type $imageId
   */
  public function __construct($movie, $imageId = null) {
    global $i18n;
    if ($imageId) {

    }
    $alt = $movie->displayTitle;
    if ($movie->year) {
      $alt .= " ({$movie->year})";
    }
    $this->alternativeText = $i18n->t("Poster for {0}.", [ $alt ]);
    $this->imageDirectory .= "/{$movie->id}/poster";
    $this->imageExists     = (boolean) $this->imageExists;
    $this->movie           = $movie->id;
    if ($this->id) {
      $this->route = $i18n->r("/movie/{0}/poster/{1}", [ $this->movie->id, $this->id ]);
    }
    else {
      $this->route = $i18n->t("/movie/{0}/posters/upload");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  public function commit() {

  }

  protected function generateImageStyles($source) {
    $span08 = $this->convertImage($source, self::IMAGE_STYLE_SPAN_08);
    $span02 = $this->convertImage($span08, self::IMAGE_STYLE_SPAN_02);
    $this->convertImage($span02, self::IMAGE_STYLE_SPAN_01);
    return $this;
  }

  public function getImageStyle($style = self::IMAGE_STYLE_SPAN_02) {
    if (!isset($this->imageStyles[$style])) {
      $this->imageStyles = unserialize($this->imageStyles);
    }
    if (!isset($this->imageStylesCache[$style])) {
      $this->imageStylesCache[$style] = new Style(
        $this->alternativeText,
        $this->getImageURL($style),
        $this->imageStyles[$style]["width"],
        $this->imageStyles[$style]["height"],
        $this->route
      );
    }
    return $this->imageStylesCache[$style];
  }

}
