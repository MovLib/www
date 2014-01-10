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

use \MovLib\Presentation\Error\NotFound;

/**
 * Represents a single image of a specific movie.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieImage extends \MovLib\Data\Image\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * 60x60>
   *
   * Image style used on the image details page within the stream.
   *
   * @var string
   */
  const STYLE_SPAN_01_SQUARE = "60x60";

  /**
   * 220x>
   *
   * Image style used on the show page to display the movie poster.
   *
   * @var integer
   */
  const STYLE_SPAN_03 = \MovLib\Data\Image\SPAN_03;

  /**
   * 620x620>
   *
   * Image style used on the image details page for the big preview.
   *
   * @var integer
   */
  const STYLE_SPAN_07 = \MovLib\Data\Image\SPAN_07;

  /**
   * The movie image's type identifier.
   *
   * @var integer
   */
  const TYPE_ID = 1;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image's path within the upload directory.
   *
   * @see MovieImage::init()
   * @var string
   */
  protected $directory = "movie";


  /**
   * The image's country code.
   *
   * @var null|string
   */
  public $countryCode;

  /**
   * The image's language code.
   *
   * @var null|string
   */
  public $languageCode;

  /**
   * The image's unique movie ID.
   *
   * @var integer
   */
  protected $movieId;

  /**
   * @inheritdoc
   */
  protected $placeholder = "image";

  /**
   * The image's route key.
   *
   * @var string
   */
  protected $routeKey;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie image.
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
    $this->init($movieId, $id, "image", $i18n->t("Image for {title}", [ "title" => $movieTitle ]));
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   */
  protected function generateStyles($source) {
    global $db, $i18n;

    // Reserve identifier if this is a new upload.
    if ($this->imageExists === false) {
      // Do not add LIMIT 1 to subquery, this logs an "unsafe statement" warning in the binary logs.
      $db->query(
        "INSERT INTO `movies_images` SET
          `id`       = (SELECT IFNULL(MAX(`s`.`id`), 0) + 1 FROM `movies_images` AS `s` WHERE `s`.`movie_id` = ? AND `s`.`type_id` = ?),
          `movie_id` = ?,
          `type_id`  = ?,
          `created`  = FROM_UNIXTIME(?)",
        "didis",
        [ $this->movieId, static::TYPE_ID, $this->movieId, static::TYPE_ID, $_SERVER["REQUEST_TIME"] ]
      )->close();

      // Fetch the just generated identifier from the database.
      $stmt = $db->query(
        "SELECT MAX(`id`) FROM `movies_images` WHERE `movie_id` = ? AND `type_id` = ? LIMIT 1",
        "di",
        [ $this->movieId, static::TYPE_ID ]
      );
      $this->filename    = $this->id = $stmt->get_result()->fetch_row()[0];
      $stmt->close();
      $this->imageExists = true;
      $this->route       = $i18n->r("/movie/{0}/{$this->routeKey}/{1}", [ $this->movieId, $this->id]);

      // We always have to call this method even if our identifier is greater than one. It could be that all other
      // images have been deleted and if that's the case the directory was deleted as well.
      $this->createDirectories();
    }

    // Generate the various image's styles and always go from best quality down to worst quality.
    $span07 = $this->convert($source, self::STYLE_SPAN_07, self::STYLE_SPAN_07, self::STYLE_SPAN_07);
    $span03 = $this->convert($span07, self::STYLE_SPAN_03);
    $span02 = $this->convert($span03, self::STYLE_SPAN_02);
    $span01 = $this->convert($span02, self::STYLE_SPAN_01);

    // Generate a square span 1 version for the image stream on the details page.
    $this->convert($span01, self::STYLE_SPAN_01_SQUARE, self::STYLE_SPAN_01, self::STYLE_SPAN_01, true);

    // This is a new upload, therefor we have to update everything. We already reserved an ID for us at the beginning
    // and we have to update that ID now.
    $db->query(
      "UPDATE `movies_images` SET
        `changed`          = FROM_UNIXTIME(?),
        `country_code`     = ?,
        `deleted`          = false,
        `dyn_descriptions` = COLUMN_CREATE(?, ?),
        `extension`        = ?,
        `filesize`         = ?,
        `height`           = ?,
        `language_code`    = ?,
        `license_id`       = ?,
        `styles`           = ?,
        `user_id`          = ?,
        `width`            = ?
      WHERE `id` = ? AND `movie_id` = ? AND `type_id` = ?",
      "sssssiisisdiidi",
      [
        $_SERVER["REQUEST_TIME"],
        $this->countryCode,
        $i18n->languageCode, $this->description,
        $this->extension,
        $this->filesize,
        $this->height,
        $this->languageCode,
        $this->licenseId,
        serialize($this->styles),
        $this->uploaderId,
        $this->width,
        $this->id,
        $this->movieId,
        static::TYPE_ID,
      ]
    )->close();

    return $this->update();
  }

  /**
   * Initialize movie image.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $movieId
   *   The unique movie's identifier this image belongs to.
   * @param null|integer $id
   *   The identifier of the movie image that should be loaded from the database. If none is passed (default) an empty
   *   movie image is created, ready for creating a new movie image.
   * @param string $routeKey
   *   The movie image route key.
   * @param string $alternativeText
   *   The alternate text for the image.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Preentation\Error\NotFound
   */
  protected function init($movieId, $id, $routeKey, $alternativeText) {
    global $db, $i18n;
    // Ensure that we aren't going to override our $id property if we were instantiated via fetch_object().
    if (!$this->id) {
      $this->id = $id;
    }

    // Export everything that we know without asking the database right away.
    $this->alternativeText = $alternativeText;
    $this->movieId         = $movieId;
    $this->directory      .= "/{$this->movieId}/{$routeKey}";
    $this->filename        = $this->id;
    $this->routeKey        = $routeKey;

    // Only attempt to load the image from the database if we weren't called via fetch_object(). The local variable
    // $id is passed by the constructor down to us and contains the default value if this class was constructed by
    // PHP itself.
    if ($id) {
      $stmt = $db->query(
        "SELECT
          UNIX_TIMESTAMP(`changed`),
          `country_code`,
          UNIX_TIMESTAMP(`created`),
          COLUMN_GET(`dyn_descriptions`, ? AS BINARY),
          `extension`,
          `filesize`,
          `height`,
          `language_code`,
          `license_id`,
          `styles`,
          `upvotes`,
          `user_id`,
          `width`
        FROM `movies_images`
        WHERE `id` = ? AND `movie_id` = ? AND `type_id` = ?
        LIMIT 1",
        "sidi",
        [ $i18n->languageCode, $this->id, $this->movieId, static::TYPE_ID ]
      );
      $stmt->bind_result(
        $this->changed,
        $this->countryCode,
        $this->created,
        $this->description,
        $this->extension,
        $this->filesize,
        $this->height,
        $this->languageCode,
        $this->licenseId,
        $this->styles,
        $this->upvotes,
        $this->uploaderId,
        $this->width
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
    }

    // Export everything to class scope for which we have to ask the database.
    $this->imageExists = (boolean) $this->changed;
    $this->route  = $this->imageExists === true
      ? $i18n->r("/movie/{0}/{$routeKey}/{1}", [ $this->movieId, $this->id ])
      : $i18n->r("/movie/{0}/{$routeKey}/upload", [ $this->movieId ])
    ;

    return $this;
  }

  /**
   * {@inheritdoc}
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function update() {
    global $db, $i18n;
    $db->query(
      "UPDATE `movies_images` SET
        `changed`          = FROM_UNIXTIME(?),
        `country_code`     = ?,
        `deleted`          = false,
        `dyn_descriptions` = COLUMN_CREATE(?, ?),
        `extension`        = ?,
        `filesize`         = ?,
        `height`           = ?,
        `language_code`    = ?,
        `license_id`       = ?,
        `styles`           = ?,
        `user_id`          = ?,
        `width`            = ?
      WHERE `id` = ? AND `movie_id` = ? AND `type_id` = ?",
      "sssssiisisdiidi",
      [
        $_SERVER["REQUEST_TIME"],
        $this->countryCode,
        $i18n->languageCode, $this->description,
        $this->extension,
        $this->filesize,
        $this->height,
        $this->languageCode,
        $this->licenseId,
        (is_array($this->styles) ? serialize($this->styles) : $this->styles),
        $this->uploaderId,
        $this->width,
        $this->id,
        $this->movieId,
        static::TYPE_ID,
      ]
    )->close();
    return $this;
  }

}
