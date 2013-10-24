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

use \MovLib\Data\Delayed\Logger;
use \MovLib\View\ImageStyle\ResizeCropCenterImageStyle;
use \MovLib\View\ImageStyle\ResizeImageStyle;

/**
 * Represents a single movie's image (e.g. lobby card).
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movie extends \MovLib\Data\Image\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Image style for span 1 elements.
   *
   * @var int
   */
  const IMAGESTYLE_SPAN_1 = 70;

  /**
   * Image style for span 2 elements.
   *
   * @var int
   */
  const IMAGESTYLE_SPAN_2 = 140;

  /**
   * Image style for span 3 elements.
   *
   * @var int
   */
  const IMAGESTYLE_SPAN_3 = 220;

  /**
   * Image style for span 9 elements.
   *
   * @var int
   */
  const IMAGESTYLE_SPAN_9 = 700;

  /**
   * The image type for posters.
   *
   * @var int
   */
  const IMAGETYPE_POSTER = 0;

  /**
   * The image type for lobby cards.
   *
   * @var int
   */
  const IMAGETYPE_LOBBYCARD = 1;

  /**
   * The image type for photos.
   *
   * @var int
   */
  const IMAGETYPE_PHOTO = 2;


  // ------------------------------------------------------------------------------------------------------------------- Table properties


  /**
   * The timestamp this image was last modified.
   *
   * @var int
   */
  public $changed;

  /**
   * The country this poster belongs to as an associative array.
   *
   * @var array
   */
  public $country;

  /**
   * The timestamp this image was initially uploaded.
   *
   * @var int
   */
  public $created;

  /**
   * The image's description.
   *
   * @var string
   */
  public $description;

  /**
   * The movie's ID this image belongs to.
   *
   * @var int
   */
  public $id;

  /**
   * Associative array to resolve image directories with the type constants.
   *
   * @var array
   */
  public static $imageDirectories = [
    self::IMAGETYPE_POSTER    => "posters",
    self::IMAGETYPE_LOBBYCARD => "lobby-cards",
    self::IMAGETYPE_PHOTO     => "photos",
  ];

  /**
   * The ID of the image within the movie's images.
   *
   * @var int
   */
  public $imageId;

  /**
   * The image's license ID.
   *
   * @var int
   */
  public $licenseId;

  /**
   * The image's source.
   *
   * @var string
   */
  public $source;

  /**
   * The image's type (one of the <code>IMAGETYPE_*</code> constants).
   *
   * @var int
   */
  public $type;

  /**
   * The overall count of upvotes for this image.
   *
   * @var int
   */
  public $upvotes;

  /**
   * The image's unique user ID.
   *
   * @var int
   */
  public $userId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Construct a new movie image model. If the image ID is not specified, an empty model is created.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param int $movieId
   *   The movie ID this image belongs to.
   * @param int $type
   *   The type of the image (one of the <code>IMAGETYPE_*</code> constants).
   * @param int $imageId [optional]
   *   The ID of the image within the movie images.
   * @param string $movieTitle [optional]
   *   The movie title for the alt attribute.
   */
  public function __construct($movieId, $type, $imageId = null, $movieTitle = "") {
    global $i18n;
    $this->id             = $movieId;
    $this->type           = $type;
    $imgDir               = self::$imageDirectories[$type];
    $this->imageDirectory = "movie/{$imgDir}/{$movieId}";

    if ($imageId) {
      $result = $this->select(
        "SELECT
          `movie_id` AS `id`,
          `image_id` AS `imageId`,
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
          `upvotes`,
          COLUMN_GET(`dyn_descriptions`, '{$i18n->languageCode}' AS BINARY) AS `description`,
          `hash` AS `imageHash`,
          `source`
        FROM `movies_images`
        WHERE `movie_id` = ?
          AND `image_id` = ?
          AND `type` = ?
        LIMIT 1",
        "ddi",
        [ $movieId, $imageId, $type ]
      );

      if (empty($result[0])) {
        Logger::stack("Could not retrieve image (movie id: {$movieId}, image id: {$imageId})!", Logger::DEBUG);
      }
      else {
        $result = $result[0];

        if (empty($result["description"])) {
          $result["description"] = "";
        }

        foreach ($result as $k => $v) {
          $this->{$k} = $v;
        }

        if ($this->country) {
          $this->country = $i18n->getCountries()[$this->country];
        }

        switch ($type) {
          case self::IMAGETYPE_POSTER:
            $this->imageAlt = "{$movieTitle} {$i18n->t("movie poster{0}.", [ isset($this->country) ? $i18n->t(" for {0}", [ $this->country["name"] ]) : "" ])}";
            break;

          case self::IMAGETYPE_LOBBYCARD:
            $this->imageAlt = "{$movieTitle} {$i18n->t("movie lobby card{0}.", [ isset($this->country) ? $i18n->t(" for {0}", [ $this->country["name"] ]) : "" ])}";
            break;

          case self::IMAGETYPE_PHOTO:
            $this->imageAlt = "{$movieTitle} {$i18n->t("movie photo.")}";
            break;
        }

        $this->initImage($this->imageName, [
          new ResizeImageStyle(self::IMAGESTYLE_SPAN_1),
          new ResizeImageStyle(self::IMAGESTYLE_SPAN_3),
          new ResizeImageStyle(self::IMAGESTYLE_SPAN_9),
          new ResizeImageStyle(self::IMAGESTYLE_GALLERY),
          new ResizeImageStyle(self::IMAGESTYLE_DETAILS),
          new ResizeCropCenterImageStyle(self::IMAGESTYLE_DETAILS_STREAM),
        ]);
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function getImageDetails() {
    $details = parent::getImageDetails();
    $details["country"] = $this->country;
    return $details;
  }

  /**
   * Get the total count of images of the specific type.
   *
   * @return int
   *  The total count.
   */
  public function getTotalCount() {
    return $this->select(
      "SELECT COUNT(`image_id`) AS `count` FROM `movies_images` WHERE `movie_id` = ? AND `type` = ? ORDER BY `created` DESC",
      "di",
      [ $this->id, $this->type ]
    )[0]["count"];
  }

  protected function generateImageStyles($source) {

  }

  public function commit() {

  }

  public function getImageStyle($style = self::IMAGESTYLE_SPAN_2) {

  }

}
