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

use \MovLib\Model\AbstractImageModel;
use \MovLib\Utility\DelayedLogger;
use \MovLib\View\ImageStyle\ResizeCropCenterImageStyle;
use \MovLib\View\ImageStyle\ResizeImageStyle;

/**
 * Retrieve movie poster data from the database.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MoviePosterModel extends AbstractImageModel {



  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Small image style (e.g. for movie listings).
   *
   * @var int
   */
  const IMAGESTYLE_SMALL = "75x75";

  /**
   * Large image style with fixed width (e.g. for movie page).
   *
   * @var int
   */
  const IMAGESTYLE_LARGE_FIXED_WIDTH = "220x";

  /**
   * Huge image style with fixed width (e.g. for the poster page).
   *
   * @var int
   */
  const IMAGESTYLE_HUGE_FIXED_WIDTH = "700x";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie ID this poster belongs to.
   *
   * @var int
   */
  public $id;

  /**
   * The ID of the poster within the movie posters.
   *
   * @var int
   */
  public $sectionId;

  /**
   * The ID of the user who has uploaded/changed the poster.
   *
   * @var int
   */
  public $userId;

  /**
   * The ID of the license this poster has.
   *
   * @var int
   */
  public $licenseId;

  /**
   * The country this poster belongs to as an associative array.
   *
   * @var array
   */
  public $country;

  /**
   * The timestamp this poster was initially uploaded.
   *
   * @var int
   */
  public $created;

  /**
   * The timestamp this poster was last modified.
   *
   * @var int
   */
  public $changed;

  /**
   * The overall count of upvotes for this poster.
   *
   * @var int
   */
  public $rating;

  /**
   * The poster's description.
   *
   * @var string
   */
  public $description;

  /**
   * The image's source.
   *
   * @var string
   */
  public $source;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate a new poster model.
   *
   * If the poster ID is not specified, an empty model is created.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param int $movieId
   *   The movie ID this poster belongs to.
   * @param int $posterId
   *   The ID of the poster within the movie posters.
   */
  public function __construct($movieId, $posterId = null) {
    global $i18n;
    $this->id = $movieId;
    $this->imageDirectory = "movie/posters/{$movieId}";
    if ($posterId) {
      $result = $this->select(
        "SELECT
          `movie_id` AS `id`,
          `section_id` AS `sectionId`,
          `user_id` AS `userId`,
          `license_id` AS `licenseId`,
          `country_id` AS `country`,
          `filename` AS `imageName`,
          `width` AS `imageWidth`,
          `height` AS `imageHeight`,
          `size` AS `imageSize`,
          `ext` AS `imageExtension`,
          UNIX_TIMESTAMP(`created`) AS `created`,
          UNIX_TIMESTAMP(`changed`) AS `changed`,
          `rating`,
          COLUMN_GET(`dyn_descriptions`, 'en' AS BINARY) AS `description_en`,
          COLUMN_GET(`dyn_descriptions`, '{$i18n->languageCode}' AS BINARY) AS `description_localized`,
          `hash` AS `imageHash`,
          `source`
        FROM `posters`
        WHERE `movie_id` = ?
          AND `section_id` = ?
        LIMIT 1",
        "dd",
        [ $movieId, $posterId ]
      );
      if (empty($result[0])) {
        DelayedLogger::log("Could not retrieve poster (movie id: {$movieId}, poster id: {$posterId})!", E_NOTICE);
      }
      else {
        // Convenience
        $result = $result[0];
        // Get the description for this image (fallback to English).
        foreach ([ "en", "localized" ] as $v) {
          if (isset($result["description_{$v}"])) {
            $result["description"] = $result["description_{$v}"];
            unset($result["description_{$v}"]);
          }
        }
        foreach ($result as $k => $v) {
          $this->{$k} = $v;
        }
        if ($this->country) {
          $this->country = $i18n->getCountries()[$this->country];
        }
        $this->initImage($this->imageName, [
          new ResizeImageStyle(self::IMAGESTYLE_SMALL),
          new ResizeImageStyle(self::IMAGESTYLE_LARGE_FIXED_WIDTH),
          new ResizeImageStyle(self::IMAGESTYLE_HUGE_FIXED_WIDTH),
          new ResizeImageStyle(self::IMAGESTYLE_GALLERY),
          new ResizeImageStyle(self::IMAGESTYLE_DETAILS),
          new ResizeCropCenterImageStyle(self::IMAGESTYLE_DETAILS_STREAM)
        ]);
      }
    }
  }

  /**
   * Get all image details.
   *
   * Retrieve all the relevant image details including license and user information. Override to provide country
   * information.
   *
   * @return array
   *   Associative array containing the image details.
   */
  public function getImageDetails() {
    parent::getImageDetails();
    $this->details["country"] = $this->country;
    return $this->details;
  }

  /**
   * Get the position of the current poster within all posters sorted by creation datetime and the total poster count.
   *
   * <b>TIP:</b> Use <code>list($p, $c) = $this->getPosterPositionAndTotalCount();</code>.
   *
   * @return array
   *   Numeric array, first offset is the position and second offset the total count.
   */
  public function getPosterPositionAndTotalCount() {
    $position = 0;
    $posters = $this->select("SELECT `section_id` FROM `posters` WHERE `movie_id` = ? ORDER BY `created` DESC", "d", [ $this->id ]);
    $c = count($posters);
    for ($i = 0; $i < $c; ++$i) {
      if ($posters[$i]["section_id"] === $this->sectionId) {
        $position = ++$i;
        break;
      }
    }
    return [ $position, $c ];
  }

}
