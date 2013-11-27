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


  /**
   * 220x>
   *
   * Image style used on the show page to display the movie poster.
   *
   * @var integer
   */
  const STYLE_SPAN_03 = \MovLib\Data\Image\SPAN_03;

  /**
   * 620x>
   *
   * Image style used on the image details page for the big preview.
   *
   * @var integer
   */
  const STYLE_SPAN_08 = \MovLib\Data\Image\SPAN_08;

  /**
   * The movie image's type identifier.
   *
   * @var integer
   */
  const TYPE_ID = 1;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image's country code.
   *
   * @var null|string
   */
  public $countryCode;

  /**
   * The image's path within the upload directory.
   *
   * @see MoviePoster::__construct()
   * @var string
   */
  protected $directory = "movie";

  /**
   * The image's unique movie ID.
   *
   * @var integer
   */
  protected $movieId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie poster image.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $movieId
   *   The unique movie ID this poster belongs to.
   * @param string $displayTitleWithYear
   *   The display title (with year) of the movie this image belongs to.
   * @param null|integer $imageId [optional]
   *   The image ID to load, if none is given (default) no image is loaded.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \OutOfBoundsException
   */
  public function __construct($movieId, $displayTitleWithYear, $imageId = null) {
    global $db, $i18n;

    if ($imageId) {
      $stmt = $db->query(
        "SELECT
          `id`,
          `user_id`,
          `license_id`,
          `country_code`,
          `width`,
          `height`,
          `size`,
          `extension`,
          UNIX_TIMESTAMP(`changed`),
          UNIX_TIMESTAMP(`created`),
          `upvotes`,
          COLUMN_GET(`dyn_descriptions`, ? AS BINARY),
          `source`,
          `styles`
        FROM `movies_images`
        WHERE `id` = ? AND `movie_id` = ? AND `type_id` = ?
        LIMIT 1",
        "sidi",
        [ $i18n->languageCode, $imageId, $movieId, static::TYPE_ID ]
      );
      $stmt->bind_result(
        $this->id,
        $this->userId,
        $this->licenseId,
        $this->countryCode,
        $this->width,
        $this->height,
        $this->filesize,
        $this->extension,
        $this->changed,
        $this->created,
        $this->upvotes,
        $this->description,
        $this->source,
        $this->styles
      );
      if (!$stmt->fetch()) {
        throw new \OutOfBoundsException("Couldn't find image with ID '{$imageId}' of type '" . static::TYPE_ID . "' for movie with ID '{$movieId}'.");
      }
      $stmt->close();
      $this->exists = true;
    }
    // If we already have an identifier we were instantiated via PHP's built-in fetch_object() method.
    elseif ($this->id) {
      $this->exists = (boolean) $this->changed;
    }

    $this->alternativeText = $i18n->t("Poster for {movie_title_with_year}.", [ "movie_title_with_year" => $displayTitleWithYear ]);
    $this->directory      .= "/{$movieId}/poster";
    $this->movieId         = $movieId;

    if ($this->exists === true) {
      $this->route    = $i18n->r("/movie/{0}/poster/{1}", [ $this->movieId, $this->id ]);
      $this->filename = $this->id;
    }
    else {
      $this->route = $i18n->t("/movie/{0}/posters/upload", [ $this->movieId ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  protected function generateStyles($source) {
    global $db, $i18n, $session;

    $db->transactionStart();

    if ($this->exists === false) {
      $stmt           = $db->query("SELECT IFNULL(MAX(`id`), 1) FROM `movies_images` WHERE `movie_id` = ? AND `type_id` = ? LIMIT 1", "di", [ $this->movieId, static::TYPE_ID ]);
      $this->filename = $this->id = $stmt->get_result()->fetch_row()[0];
      $stmt->close();
      $this->createDirectories();
    }

    // Generate the various image's styles and always go from best quality down to worst quality.
    $span08 = $this->convert($source, self::STYLE_SPAN_08);
    $span03 = $this->convert($span08, self::STYLE_SPAN_03);
    $span02 = $this->convert($span03, self::STYLE_SPAN_02);
    $this->convert($span02, self::STYLE_SPAN_01);

    // Update the record with the new data if this is an update.
    if ($this->exists === true) {
      throw new \LogicException("Not implemented yet!");
    }
    // If this is a new upload insert the record and create the new details route for this upload.
    else {
      $db->query(
        "INSERT INTO `movies_images` SET
          `id`               = `next_id`,
          `movie_id`         = ?,
          `type_id`          = ?,
          `user_id`          = ?,
          `license_id`       = ?,
          `country_code`     = ?,
          `width`            = ?,
          `height`           = ?,
          `size`             = ?,
          `extension`        = ?,
          `changed`          = FROM_UNIXTIME(?),
          `created`          = FROM_UNIXTIME(?),
          `dyn_descriptions` = COLUMN_CREATE(?, ?),
          `source`           = ?,
          `styles`           = ?
        SELECT IFNULL(MAX(`id`), 1) AS `next_id` FROM `movies_images`",
        "didisiiisssssss",
        [
          $this->movieId,
          static::TYPE_ID,
          $session->userId,
          $this->licenseId,
          $this->countryCode,
          $this->width,
          $this->height,
          $this->filesize,
          $this->extension,
          $this->changed,
          $this->created,
          $i18n->languageCode,
          $this->description,
          $this->source,
          serialize($this->styles),
        ]
      )->close();

      $this->route = $i18n->r("/movie/{0}/poster/{1}", [ $this->movieId, $this->id ]);
    }

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getStyle($style = self::STYLE_SPAN_02) {
    if (!isset($this->styles[$style])) {
      $this->styles = unserialize($this->styles);
    }
    if (!isset($this->stylesCache[$style])) {
      $this->stylesCache[$style] = new Style(
        $this->alternativeText,
        $this->getURL($style),
        $this->styles[$style]["width"],
        $this->styles[$style]["height"],
        $this->route
      );
    }
    return $this->stylesCache[$style];
  }

}
