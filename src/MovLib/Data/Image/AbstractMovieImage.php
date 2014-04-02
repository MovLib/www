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
 * Base class for all movie images.
 *
 * @internal This class is only abstract to ensure that nobody is instantiating it directly.
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractMovieImage extends \MovLib\Data\Image\AbstractImage {


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
   * The movie image's table name.
   *
   * @var string
   */
  const TABLE_NAME = "";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The poster's ISO alpha-2 country code.
   *
   * @var string
   */
  public $countryCode;

  /**
   * The movie image's translated name (e.g. <code>$i18n->t("Poster")</code>).
   *
   * @var string
   */
  public $name;

  /**
   * The movie image's translated plural name (e.g. <code>$i18n->t("Posters")</code>).
   *
   * @var string
   */
  public $namePlural;

  /**
   * The poster's ISO alpha-2 language code.
   *
   * @var string
   */
  public $languageCode = "xx";

  /**
   * The movie's unique identifier this backdrop belongs to.
   *
   * @var integer
   */
  protected $movieId;

  /**
   * The movie's display title (and year) this backdrop belongs to.
   *
   * @var string
   */
  protected $movieTitle;

  /**
   * The poster's publishing date.
   *
   * @var string
   */
  public $publishingDate;

  /**
   * The movie image's route key.
   *
   * @var string
   */
  public $routeKey;

  /**
   * The movie image's plural route key.
   *
   * @var string
   */
  public $routeKeyPlural;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie image.
   *
   * @param integer|null $id
   *   The movie image's unique identifier.
   * @param integer|null $movieId
   *   The movie image's unique movie identifier, pass <code>NULL</code> if instantiating via fetch object.
   * @param integer|null $movieTitle
   *   The movie image's translated title, pass <code>NULL</code> if instantiating via fetch object.
   * @param string $name
   *   The movie image's translated name (e.g. <code>$i18n->t("Poster")</code>).
   * @param string $namePlural
   *   The movie image's translated plural name (e.g. <code>$i18n->t("Posters")</code>).
   * @param string $routeKey
   *   The movie image's route key (e.g. <code>"poster"</code>).
   * @param string $routePluralKey
   *   The movie image's plural route key (e.g. <code>"posters"</code>).
   * @throws \LogicException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id, $movieId, $movieTitle, $name, $namePlural, $routeKey, $routePluralKey) {
    // Export route information to class scope.
    $this->routeKey       = $this->placeholder = $routeKey;
    $this->routeKeyPlural = $routePluralKey;

    // @devStart
    // @codeCoverageIgnoreStart
    if (static::TABLE_NAME === "") {
      throw new \LogicException("You have to set the table name constant in your implementing movie image class.");
    }
    if ((!$this->movieId && !$movieId) || (!$this->movieTitle && !$movieTitle)) {
      throw new \LogicException("You either have to pass the movie parameters to the constructor or instantiate " . __CLASS__ . " via fetch object and load everything yourself.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Ensure we aren't exporting the optional parameters if we were instantiated via fetch object.
    if (!$this->movieId) {
      $this->movieId    = $movieId;
      $this->movieTitle = $movieTitle;
    }

    // Try to load the movie image if an identifier was passed.
    if ($id) {
      $stmt = $db->query(
        "SELECT
          `uploader_id`,
          UNIX_TIMESTAMP(`changed`),
          UNIX_TIMESTAMP(`created`),
          `deleted`,
          `deletion_request_id`,
          COLUMN_EXISTS(`dyn_descriptions`, ?),
          IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_descriptions`, '{$i18n->defaultLanguageCode}' AS CHAR)),
          `extension`,
          `filesize`,
          `height`,
          `language_code`,
          `width`,
          `country_code`,
          `publishing_date`,
          `styles`
        FROM " . static::TABLE_NAME . "
        WHERE `id` = ?",
        "ssd",
        [ $i18n->languageCode, $i18n->languageCode, $id ]
      );
      $stmt->bind_result(
        $this->uploaderId,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->deletionId,
        $translatedDescriptionExists,
        $this->description,
        $this->extension,
        $this->filesize,
        $this->height,
        $this->languageCode,
        $this->width,
        $this->countryCode,
        $this->publishingDate,
        $this->styles
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
      $this->id                      = $id;
      $this->descriptionLanguageCode = $translatedDescriptionExists === 1 ? $i18n->languageCode : $i18n->defaultLanguageCode;
    }

    // Initialize the movie poster if we have an identifier (from the query above or via fetch object).
    if ($this->id) {
      $this->alternativeText = $i18n->t("{image_name} for {title}", [ "image_name" => $name, "title" => $this->movieTitle ]);
      $this->deleted         = (boolean) $this->deleted;
      $this->filename        = $this->id;
      $this->imageExists     = (boolean) $this->changed && !$this->deleted;
    }

    // Always initialize the following, it doesn't matter if the image exists or not.
    $this->name       = $name;
    $this->namePlural = $namePlural;
    $this->movieId    = $movieId;
    $this->directory  = "movie/{$this->movieId}/{$routePluralKey}";

    // Initialize the movie poster's route.
    if ($this->imageExists === true) {
      $this->route = $i18n->r("/movie/{0}/{$routeKey}/{1}", [ $this->movieId, $this->id ]);
    }
    else {
      $this->route = $i18n->r("/movie/{0}/{$routeKey}/upload", [ $this->movieId ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Update the image's properties.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function commit() {
    $db->query(
      "UPDATE `" . static::TABLE_NAME . "` SET
        `dyn_descriptions` = COLUMN_ADD(`dyn_descriptions`, ?, ?),
        `language_code`    = ?,
        `country_code`     = ?,
        `publishing_date`  = ?
      WHERE `id` = ?",
      "sssssd",
      [ $i18n->languageCode, $this->description, $this->languageCode, $this->countryCode, $this->publishingDate, $this->id ]
    )->close();
    // @todo Commit update to Git repository
    return $this;
  }

  /**
   * Mark the movie image as deleted.
   *
   * @todo User with enough reputation should be able to remove the image without leaving traces as well.
   *
   * @param boolean $shred [optional]
   *   Whether the image should be removed traceless or not, defaults to keep traces.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   * @throws \RuntimeException
   */
  public function delete($shred = false) {
    // Only attempt to delete this image if we have a regular deletion request.
    if (!isset($this->deletionId)) {
      throw new \RuntimeException;
    }

    // Flag this image as deleted in the database.
    $db->query(
      "UPDATE `" . static::TABLE_NAME . "` SET `deleted` = true, `styles` = NULL WHERE `id` = ? AND `movie_id` = ?",
      "dd",
      [ $this->id, $this->movieId ]
    )->close();

    // Delete generated images from persistent storage.
    $this->deleteImageStyles();

    // Remove all traces as well if the user has the right to do so.
    if ($shred === true && $session->isAdmin() === true) {
      // @todo Shred the image.
    }

    return $this;
  }

  /**
   * Get all movie images.
   *
   * @param integer $movieId [optional]
   *   Filter images by movie identifier, defaults to no filtering.
   * @param integer $offset [optional]
   *   The offset, usually provided by the pagination trait. Defaults to no offset.
   * @param integer $limit [optional]
   *   The row count, usually provided by the pagination trait. Defaults to no row count (all).
   * @param string $orderBy [optional]
   *   Order the result by given table row, defaults to row <code>"created"</code>.
   * @param string $sortOrder [optional]
   *
   * @param boolean|null $deleted [optional]
   *   Pass <code>TRUE</code> to count only deleted movie images, <code>NULL</code> to count absolutely all movie images,
   *   or <code>FALSE</code> (default) to count only undeleted movie images.
   * @return \mysqli_result
   *   All movie images.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getImages($movieId = null, $offset = null, $limit = null, $orderBy = "created", $sortOrder = "ASC", $deleted = false) {
    $query  = $types = null;
    $params = [];

    // Filter by movie identifier if present.
    if ($movieId) {
      $query   .= " WHERE `movie_id` = ?";
      $types   .= "d";
      $params[] = $movieId;
    }

    // Filter by deleted state if requested.
    if ($deleted === true || $deleted === false) {
      $query   .= (isset($query) ? " AND" : " WHERE") . " `deleted` = ?";
      $types   .= "i";
      $params[] = $deleted;
    }

    // Always order by something, default is created ascending.
    $query .= " ORDER BY `{$orderBy}` {$sortOrder}";

    // Paginate if requested.
    if (isset($offset) && isset($limit)) {
      $query   .= " LIMIT ? OFFSET ?";
      $types   .= "ii";
      $params[] = $limit;
      $params[] = $offset;
    }

    // Put it all together and we're done.
    return $db->query(
      "SELECT
        `id`,
        UNIX_TIMESTAMP(`changed`) AS `changed`,
        `extension`,
        `height`,
        `language_code` AS `languageCode`,
        `width`,
        `country_code` AS `countryCode`,
        `styles`
      FROM `" . static::TABLE_NAME . "`{$query}",
      $types,
      $params
    )->get_result();
  }

  /**
   * Get total movie images count.
   *
   * @param integer $movieId [optional]
   *   Filter images by movie identifier, defaults to no filtering.
   * @param boolean|null $deleted [optional]
   *   Pass <code>TRUE</code> to count only deleted movie images, <code>NULL</code> to count absolutely all movie images,
   *   or <code>FALSE</code> (default) to count only undeleted movie images.
   * @return integer
   *   Total movie images count.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getCount($movieId = null, $deleted = false) {
    $where  = $types = null;
    $params = [];

    // Filter by movie identifier if present.
    if ($movieId) {
      $where   .= " WHERE `movie_id` = ?";
      $types   .= "d";
      $params[] = $movieId;
    }

    // Filter by deleted state if requested.
    if ($deleted === true || $deleted === false) {
      $where   .= (isset($where) ? " AND" : " WHERE") . " `deleted` = ?";
      $types   .= "i";
      $params[] = $deleted;
    }

    // Put it all together and we're done.
    return $db->query("SELECT COUNT(*) FROM `" . static::TABLE_NAME . "`{$where}", $types, $params)->get_result()->fetch_row()[0];
  }

  /**
   * Generate all supported image styles.
   *
   * @param string $source
   *   Absolute path to the uploaded image.
   * @param boolean $regenerate [optional]
   *   Whether to regenerate existing styles.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function generateStyles($source, $regenerate = false) {
    try {
      // Start transaction and prepare database record if this is a new upload.
      $db->transactionStart();
      if ($this->imageExists === false) {
        $stmt = $db->query(
          "INSERT INTO `" . static::TABLE_NAME . "` SET
            `movie_id`         = ?,
            `uploader_id`      = ?,
            `changed`          = FROM_UNIXTIME(?),
            `created`          = FROM_UNIXTIME(?),
            `dyn_descriptions` = COLUMN_CREATE(?, ?),
            `extension`        = ?,
            `filesize`         = ?,
            `height`           = ?,
            `width`            = ?",
          "ddiisssiii",
          [
            $this->movieId,
            $this->uploaderId,
            $_SERVER["REQUEST_TIME"],
            $_SERVER["REQUEST_TIME"],
            $i18n->languageCode,
            $this->description,
            $this->extension,
            $this->filesize,
            $this->height,
            $this->width,
          ]
        );
        $this->filename = $this->id = $stmt->insert_id;
        $stmt->close();
        $this->createDirectories();
        $this->route = $i18n->r("/movie/{0}/{$this->routeKey}/{1}", [ $this->movieId, $this->id ]);
      }

      // Generate the various image's styles and always go from best quality down to worst quality.
      $this
        ->convert($source, self::STYLE_SPAN_07, self::STYLE_SPAN_07, self::STYLE_SPAN_07)
        ->convert($source, self::STYLE_SPAN_03)
        ->convert($source, self::STYLE_SPAN_02)
        ->convert($source, self::STYLE_SPAN_01)
        ->convert($source, self::STYLE_SPAN_01_SQUARE, self::STYLE_SPAN_01, self::STYLE_SPAN_01, true);

      // Only update the styles if we are regenerating.
      if ($regenerate === true) {
        $query  = "UPDATE `" . static::TABLE_NAME . "` SET `styles` = ? WHERE `id` = ?";
        $types  = "sd";
        $params = [ serialize($this->styles), $this->id ];
      }
      // Update just about everything if this is an upload that replaces the existing image.
      else {
        $query =
          "UPDATE `" . static::TABLE_NAME . "` SET
            `uploader_id`      = ?,
            `changed`          = FROM_UNIXTIME(?),
            `dyn_descriptions` = COLUMN_ADD(`dyn_descriptions`, ?, ?),
            `extension`        = ?,
            `filesize`         = ?,
            `height`           = ?,
            `styles`           = ?,
            `width`            = ?,
            `country_code`     = ?,
            `language_code`    = ?,
            `publishing_date`  = ?
          WHERE `id` = ?"
        ;
        $types  = "disssiisisssd";
        $params = [
          $this->uploaderId,
          $_SERVER["REQUEST_TIME"],
          $i18n->languageCode,
          $this->description,
          $this->extension,
          $this->filesize,
          $this->height,
          serialize($this->styles),
          $this->width,
          $this->countryCode,
          $this->languageCode,
          $this->publishingDate,
          $this->id,
        ];
      }
      $db->query($query, $types, $params)->close();
      $db->transactionCommit();
    }
    catch (\Exception $e) {
      $db->transactionRollback();
      throw $e;
    }

    return $this;
  }

  /**
   * Set deletion request identifier.
   *
   * @param integer $id
   *   The deletion request's unique identifier to set.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function setDeletionRequest($id) {
    $db->query(
      "UPDATE `" . static::TABLE_NAME . "` SET `deletion_request_id` = ? WHERE `id` = ? AND `movie_id` = ?",
      "didi",
      [ $id, $this->id, $this->movieId ]
    )->close();
    return $this;
  }

}
