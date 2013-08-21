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
namespace MovLib\Model;

/**
 * Description of LobbyCardModel
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieImageModel extends AbstractImageModel {

  // ------------------------------------------------------------------------------------------------------------------- Table properties
  // The following properties were inherited from AbstractImageModel:
  // filename, width, height, ext and hash.

  /**
   * The movie ID this image belongs to.
   * @var int
   */
  public $movieId;

  /**
   * The ID of the image within the movie images.
   * @var int
   */
  public $sectionId;

  /**
   * The ID of the user who has uploaded/changed the image.
   * @var int
   */
  public $userId;

  /**
   * The country this image belongs to as an associative array.
   * @var array
   */
  public $country;

  /**
   * The file size of the image in bytes.
   * @var int
   */
  public $size;

  /**
   * The timestamp this image was initially uploaded.
   * @var int
   */
  public $created;

  /**
   * The timestamp this image was last modified.
   * @var int
   */
  public $changed;

  /**
   * The overall count of upvotes for this image.
   * @var int
   */
  public $rating;

  /**
   * The image's description.
   * @var string
   */
  public $description;

  // ------------------------------------------------------------------------------------------------------------------- Image styles

  /**
   * @todo Add image styles
   */

  /**
   * Construct a new movie image model. If the IDs are not specified, an empty model is created.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param int $movieId
   *   The movie ID this image belongs to.
   * @param int $imageId
   *   The ID of the image within the movie images.
   * @param string $type
   *   The type of the image (e.g. "lobby-card").
   */
  public function __construct($movieId = null, $imageId = null, $type = null) {
    global $i18n;
    if ($movieId && $imageId && $type) {
      try {
        $this->imageDirectory = "movie/{$type}s/{$movieId}";
        $result = $this->select(
          "SELECT
            `movie_id` AS `movieId`,
            `section_id` AS `sectionId`,
            `user_id` AS `userId`,
            `country_id` AS `country`,
            `filename` AS `imageName`,
            `width` AS `imageWidth`,
            `height` AS `imageHeight`,
            `size`,
            `ext` AS `imageExtension`,
            `created`,
            `changed`,
            `rating`,
            COLUMN_GET(`dyn_descriptions`, 'en' AS BINARY) AS `description_en`,
            COLUMN_GET(`dyn_descriptions`, '{$i18n->languageCode}' AS BINARY) AS `description_localized`,
            `hash` AS `imageHash`
          FROM `movies_images`
          WHERE `movie_id` = ?
            AND `section_id` = ?
          LIMIT 1",
          "dd",
          [ $movieId, $imageId ]
        )[0];
        $result["description"] = $result["description_localized"] ?: $result["description_en"];
        unset($result["description_localized"]);
        unset($result["description_en"]);
        foreach ($result as $property => $value) {
          $this->{$property} = $value;
        }
        $this->initImage($this->imageName, [
          AbstractImageModel::IMAGESTYLE_GALLERY
        ]);
      } catch (ErrorException $e) {
        throw new DatabaseException("Could not retrieve image (movie id: {$movieId}, image id: {$imageId})!", $e);
      }
    }
  }

}
