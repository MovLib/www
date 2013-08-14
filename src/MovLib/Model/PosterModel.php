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

use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\ErrorException;

/**
 * Retrieve movie poster data from the database.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class PosterModel extends AbstractImageModel {

  // ------------------------------------------------------------------------------------------------------------------- Table properties
  // The following properties were inherited from AbstractImageModel:
  // filename, width, height, ext and hash.

  /**
   * The movie ID this poster belongs to.
   * @var int
   */
  public $movieId;

  /**
   * The ID of the poster within the movie posters.
   * @var int
   */
  public $posterId;

  /**
   * The ID of the user who has uploaded/changed the poster.
   * @var int
   */
  public $userId;

  /**
   * The country this poster belongs to as an associative array.
   * @var array
   */
  public $country;

  /**
   * The file size of the poster in bytes.
   * @var int
   */
  public $size;

  /**
   * The timestamp this poster was initially uploaded.
   * @var int
   */
  public $created;

  /**
   * The timestamp this poster was last modified.
   * @var int
   */
  public $changed;

  /**
   * The overall count of upvotes for this poster.
   * @var int
   */
  public $rating;

  /**
   * The poster's description.
   * @var string
   */
  public $description;

  // ------------------------------------------------------------------------------------------------------------------- Image styles

  /**
   * Small image style (e.g. for movie listings).
   * @var int
   */
  const IMAGESTYLE_SMALL = "75x75>";
  /**
   * Large image style (e.g. for the gallery).
   * @var int
   */
  const IMAGESTYLE_LARGE = "220x220>";
  /**
   * Large image style with fixed width (e.g. for movie page).
   * @var int
   */
  const IMAGESTYLE_LARGE_FIXED_WIDTH = "220x>";
  /**
   * Huge image style with fixed width (e.g. for the poster page).
   * @var int
   */
  const IMAGESTYLE_HUGE_FIXED_WIDTH = "700x>";

  /**
   * Construct a new poster model. If the IDs are not specified, an empty model is created.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param int $movieId
   *   The movie ID this poster belongs to.
   * @param int $posterId
   *   The ID of the poster within the movie posters.
   */
  public function __construct($movieId = null, $posterId = null) {
    global $i18n;
    if ($movieId && $posterId) {
      try {
        $this->imageDirectory = "posters/{$movieId}";
        $posterResult = $this->select(
          "SELECT
            `movie_id` AS `movieId`,
            `poster_id` AS `posterId`,
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
          FROM `posters`
          WHERE `movie_id` = ?
            AND `poster_id` = ?
          LIMIT 1",
          "dd",
          [ $movieId, $posterId ]
        )[0];
        $posterResult["description"] = $posterResult["description_localized"] ?: $posterResult["description_en"];
        unset($posterResult["description_localized"]);
        unset($posterResult["description_en"]);
        foreach ($posterResult as $property => $value) {
          $this->{$property} = $value;
        }
        $this->initImage($this->imageName, [
          self::IMAGESTYLE_SMALL,
          self::IMAGESTYLE_LARGE,
          self::IMAGESTYLE_LARGE_FIXED_WIDTH,
          self::IMAGESTYLE_HUGE_FIXED_WIDTH
        ]);
      } catch (ErrorException $e) {
        throw new DatabaseException("Could not retrieve poster (movie id: {$movieId}, poster id: {$posterId})!", $e);
      }
    }
  }

}