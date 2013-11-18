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

  public $countryId;

  public $description;

  protected $imageDirectory = "movie";

  public $id;

  public $license;

  protected $movieId;

  public $route;

  public $source;

  protected $type = 0;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   *
   * @global \MovLib\Data\I18n $i18n
   * @param integer $movieId
   * @param string $displayTitleWithYear
   * @param type $imageId
   */
  public function __construct($movieId, $displayTitleWithYear, $imageId = null) {
    global $i18n;
    if ($imageId) {

    }
    $this->alternativeText = $i18n->t("Poster for {0}.", [ $displayTitleWithYear ]);
    $this->imageDirectory .= "/{$movieId}/poster";
    $this->imageExists     = (boolean) $this->imageExists;
    $this->movieId         = $movieId;
    if ($this->id) {
      $this->route = $i18n->r("/movie/{0}/poster/{1}", [ $movieId, $this->id ]);
    }
    else {
      $this->route = $i18n->t("/movie/{0}/posters/upload");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  public function commit() {
    global $i18n;
    if ($this->imageExists === true) {
      $this->query(
        "UPDATE `movies_images` SET
          `license_id`       = ?,
          `country_id`       = ?,
          `width`            = ?,
          `height`           = ?,
          `extension`        = ?,
          `changed`          = CURRENT_TIMESTAMP,
          `dyn_descriptions` = COLUMN_ADD(`dyn_descriptions`, ?, ?),
          `source`           = ?,
          `styles`           = ?
        WHERE `id` = ? AND `movie_id` = ?",
        "iiiiisssssdd",
        [
          $this->license,
          $this->countryId,
          $this->imageWidth,
          $this->imageHeight,
          //$this->imageSize,
          $this->imageExtension,
          $i18n->languageCode,
          $this->description,
          $this->source,
          serialize($this->imageStyles),
          $this->id,
          $this->movieId,
        ]
      );
    }
    else {
      $this->query(
        "INSERT INTO `movies_images` (
          `id`,
          `movie_id`,
          `type_id`,
          `user_id`,
          `license_id`,
          `country_id`,
          `width`,
          `height`,
          `size`,
          `extension`,
          `changed`,
          `created`,
          `dyn_descriptions`,
          `source`,
          `styles`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, ?)",
        "ddidiiiiissss",
        [
          
        ]
      );
    }
    return $this;
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
