<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Data;

use \MovLib\Data\Delayed\Logger;

/**
 * Represents a single movie's image (e.g. lobby card).
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieImage extends \MovLib\Data\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Table properties


  /**
   * The movie's ID this image belongs to.
   *
   * @var int
   */
  public $id;

  /**
   * The ID of the image within the movie's images.
   *
   * @var int
   */
  public $sectionId;

  /**
   * The image's unique user ID.
   *
   * @var int
   */
  public $userId;

  /**
   * The image's license ID.
   *
   * @var int
   */
  public $licenseId;

  /**
   * The file size of the image in bytes.
   *
   * @var int
   */
  public $size;

  /**
   * The timestamp this image was initially uploaded.
   *
   * @var int
   */
  public $created;

  /**
   * The timestamp this image was last modified.
   *
   * @var int
   */
  public $changed;

  /**
   * The overall count of upvotes for this image.
   *
   * @var int
   */
  public $rating;

  /**
   * The image's description.
   *
   * @var string
   */
  public $description;

  /**
   * The image's type (e.g. "lobby-card").
   *
   * @var string
   */
  public $type;

  /**
   * The image's source.
   *
   * @var string
   */
  public $source;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Construct a new movie image model. If the image ID is not specified, an empty model is created.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param int $movieId
   *   The movie ID this image belongs to.
   * @param string $type
   *   The type of the image (e.g. "lobby-card").
   * @param int $imageId
   *   The ID of the image within the movie images.
   */
  public function __construct($movieId, $type, $imageId = null) {
    global $i18n;
    $this->id = $movieId;
    $this->type = $type;
    $this->imageDirectory = "movie/{$type}s/{$movieId}";
    if ($imageId) {
      $result = $this->select(
        "SELECT
          `movie_id` AS `id`,
          `section_id` AS `sectionId`,
          `user_id` AS `userId`,
          `license_id` AS `licenseId`,
          `filename` AS `imageName`,
          `width` AS `imageWidth`,
          `height` AS `imageHeight`,
          `size`,
          `ext` AS `imageExtension`,
          UNIX_TIMESTAMP(`created`) AS `created`,
          UNIX_TIMESTAMP(`changed`) AS `changed`,
          `rating`,
          COLUMN_GET(`dyn_descriptions`, 'en' AS BINARY) AS `description_en`,
          COLUMN_GET(`dyn_descriptions`, '{$i18n->languageCode}' AS BINARY) AS `description_localized`,
          `hash` AS `imageHash`,
          `source`
        FROM `movies_images`
        WHERE `movie_id` = ?
          AND `section_id` = ?
        LIMIT 1",
        "dd",
        [ $movieId, $imageId ]
      );
      if (empty($result)) {
        Logger::log("Could not retrieve image (movie id: {$movieId}, image id: {$imageId})!", E_NOTICE);
      }
      else {
        $result["description"] = $result["description_localized"] ?: $result["description_en"];
        unset($result["description_localized"]);
        unset($result["description_en"]);
        foreach ($result as $k => $v) {
          $this->{$k} = $v;
        }
        $this->initImage($this->imageName, [
          new ResizeImageStyle(self::IMAGESTYLE_GALLERY),
          new ResizeImageStyle(self::IMAGESTYLE_DETAILS),
          new ResizeCropCenterImageStyle(self::IMAGESTYLE_DETAILS_STREAM)
        ]);
      }
    }
  }

}
