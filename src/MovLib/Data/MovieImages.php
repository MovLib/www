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

use \MovLib\Data\Image\Movie as MovieImage;

/**
 * Represents multiple images for a movie.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieImages extends \MovLib\Data\AbstractImages {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The count of the stream images without the current one.
   *
   * @var int
   */
  const STREAM_IMAGE_COUNT = 8;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie ID the images belong to.
   *
   * @var int
   */
  public $movieId;

  /**
   * The movie title for the alt attributes.
   *
   * @var string
   */
  private $movieTitle;

  /**
   * The generic query.
   *
   * @var string
   */
  private $query;

  /**
   * The type of the images (one of the <code>\MovLib\Data\MovieImage::IMAGETYPE_*</code> constants).
   *
   * @var int
   */
  public $type;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie images.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param int $movieId
   *   The movie id the images belong to.
   * @param int $type
   *   The type of the images (one of the <code>\MovLib\Data\MovieImage::IMAGETYPE_*</code> constants).
   * @param \MovLib\View\ImageStyle\AbstractImageStyle $style
   *   The style to display the images with.
   * @param string $route
   *   The route to the movie without the image ID.
   * @param type $movieTitle
   *   The movie title for the alt attributes.
   */
  public function __construct($movieId, $type, $style, $route, $movieTitle) {
    global $i18n;
    $imgDir                = MovieImage::$imageDirectories[$type];
    $this->imagesDirectory = "movie/{$imgDir}/{$movieId}";
    $this->movieId         = $movieId;
    $this->movieTitle      = $movieTitle;
    $this->style           = $style;
    $this->type            = $type;
    $this->route           = $route;
    $this->query           =
      "SELECT
        `movie_id`,
        `image_id`,
        `user_id`,
        `license_id`,
        `country_id`,
        `filename`,
        `width`,
        `height`,
        `size` AS `image_size`,
        `ext`,
        UNIX_TIMESTAMP(`created`) AS `created`,
        UNIX_TIMESTAMP(`changed`) AS `changed`,
        `upvotes`,
        COLUMN_GET(`dyn_descriptions`, '{$i18n->languageCode}' AS BINARY) AS `description`,
        `hash`,
        `source`
      FROM `movies_images`
      WHERE `movie_id` = ?
        AND `type` = ?"
    ;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get images ordered by created timestamp in ascending order.
   *
   * @param int $imageId
   *   The image ID to start from.
   * @param boolean $include [optional]
   *   Include the supplied id in result. Defaults to <code>FALSE</code>.
   * @param int $paginationSize [optional]
   *   The count of images to fetch. Defaults to <var>$GLOBALS["movlib"]["pagination_size"]</var>.
   * @return array
   *   The stream images.
   */
  public function getOrderedByCreatedAsc($imageId, $include = false, $paginationSize = null) {
    $paginationSize = $paginationSize ?: $GLOBALS["movlib"]["pagination_size"];
    $operator = $include === true ? ">=" : ">";
    return $this->initImageProperties($this->select(
      "{$this->query} AND `image_id` {$operator} ? ORDER BY `image_id` ASC LIMIT ?",
      "didi",
      [ $this->movieId, $this->type, $imageId, $paginationSize ]
    ));
  }

  /**
   * Get images ordered by created timestamp in descending order.
   *
   * @param int $imageId
   *   The image ID to start from.
   * @param boolean $include [optional]
   *   Include the supplied id in result. Defaults to <code>FALSE</code>.
   * @param int $paginationSize [optional]
   *   The count of images to fetch. Defaults to <var>$GLOBALS["movlib"]["pagination_size"]</var>.
   * @return array
   *   The stream images.
   */
  public function getOrderedByCreatedDesc($imageId, $include = false, $paginationSize = null) {
    $paginationSize = $paginationSize ?: $GLOBALS["movlib"]["pagination_size"];
    if ($include === true) {
      $operator = "<=";
    }
    else {
      $operator = "<";
    }
    return $this->initImageProperties($this->select(
      "{$this->query} AND `image_id` {$operator} ? ORDER BY `image_id` DESC LIMIT ?",
      "didi",
      [ $this->movieId, $this->type, $imageId, $paginationSize ]
    ));
  }

  /**
   * Get images sorted by the creation timestamp.
   *
   * @param int $lowerBound
   *   The lower bound for the pagination.
   * @param string $sortOrder [optional]
   *   The sort order (<code>"DESC"</code> or <code>"ASC"</code>). Defaults to <code>"DESC"</code>.
   * @return array
   *   The images ordered by upvotes.
   */
  public function getOrderedByUpvotes($lowerBound, $sortOrder = "DESC") {
    return $this->initImageProperties($this->select(
      "{$this->query} ORDER BY `upvotes` {$sortOrder} LIMIT ?, ?",
      "diii",
      [ $this->movieId, $this->type, $lowerBound, $GLOBALS["movlib"]["pagination_size"] ]
    ));
  }

  /**
   * Get the stream images without the current image ID.
   *
   * @param int $imageId
   *   The current image ID.
   * @return array
   *   The stream images
   */
  public function getStreamImages($imageId) {
    $lowerBound = $imageId - (self::STREAM_IMAGE_COUNT / 2);
    $limit = self::STREAM_IMAGE_COUNT;
    if ($lowerBound <= 0) {
      $limit += $lowerBound - 1;
    }
    return $this->initImageProperties($this->select(
      "{$this->query} AND `image_id` >= ? AND `image_id` != ? ORDER BY `image_id` ASC LIMIT ?",
      "diidi",
      [ $this->movieId, $this->type, $lowerBound, $imageId, $limit ]
    ));
  }

  /**
   * Initialize the additional image properties.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param array $result
   *   Numeric array containing the images from the database that should be initialized.
   * @return array
   *   The numeric array containing the initialized images.
   */
  private function initImageProperties(array $result) {
    global $i18n;
    $result = $this->initImagePaths($result, $this->style);

    switch ($this->type) {
      case MovieImage::IMAGETYPE_POSTER:
        $alt = "{$this->movieTitle} {$i18n->t("movie poster.")}";
        break;

      case MovieImage::IMAGETYPE_LOBBYCARD:
        $alt = "{$this->movieTitle} {$i18n->t("movie lobby card.")}";
        break;

      case MovieImage::IMAGETYPE_PHOTO:
        $alt = "{$this->movieTitle} {$i18n->t("movie photo.")}";
        break;
    }

    $i18nCountries = $i18n->getCountries();
    $c = count($result);
    for ($i = 0; $i < $c; ++$i) {
      if (empty($result[$i]["description"])) {
        $result[$i]["description"] = "";
      }

      if ($result[$i]["country_id"]) {
        $result[$i]["country"] = $i18nCountries[ $result[$i]["country_id"] ];
      }
      $result[$i]["alt"] = $alt;
      $result[$i]["uri"] = "{$this->route}/{$result[$i]["image_id"]}";
    }

    return $result;
  }

}
