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
 * Represents a single backdrop of a specific movie.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieBackdrop extends \MovLib\Data\Image\AbstractImage {


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


  // ------------------------------------------------------------------------------------------------------------------- Properties


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
   * @inheritdoc
   */
  protected $placeholder = "backdrop";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie backdrop.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param null|integer $movieId [optional]
   *   The movie's unique identifier this backdrop belongs to, defaults to no movie identifier which is reserved for
   *   instantiation via fetch object.
   * @param null|string $movieTitle [optional]
   *   The movie's display title (and year) this backdrop belongs to, defaults to no movie title which is reserved for
   *   instantiation via fetch object.
   * @param integer $id [optional]
   *   The backdrop's unique identifier, if not passed (default) an empty backdrop is created ready for creation of a
   *   new movie backdrop.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Preentation\Error\NotFound
   */
  public function __construct($movieId = null, $movieTitle = null, $id = null) {
    global $db, $i18n;

    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this->movieId && $movieId) || !($this->movieTitle && $movieTitle)) {
      throw new \LogicException("You either have to pass the movie parameters to the constructor or instantiate the backdrop via fetch object and load everything yourself.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Ensure we aren't exporting the optional parameters if we were instantiated via fetch object.
    if (!$this->movieId) {
      $this->movieId    = $movieId;
      $this->movieTitle = $movieTitle;
    }

    // Try to load the backdrop from the database if we have an identifier to load.
    if ($id) {
      $stmt = $db->query(
        "SELECT
          `uploader_id`,
          UNIX_TIMESTAMP(`changed`),
          UNIX_TIMESTAMP(`created`),
          `deleted`,
          `deletion_request_id`,
          IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_descriptions`, '{$i18n->defaultLanguageCode}' AS CHAR)),
          `extension`,
          `filesize`,
          `height`,
          `styles`,
          `width`
        FROM `backdrops`
        WHERE `id` = ? AND `movie_id` = ?
        LIMIT 1",
        "sdd",
        [ $i18n->languageCode, $id, $this->movieId ]
      );
      $stmt->bind_result(
        $this->uploaderId,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->deletionId,
        $this->description,
        $this->extension,
        $this->filesize,
        $this->height,
        $this->styles,
        $this->width
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $this->id = $id;
    }

    // Initialize the movie backdrop if we have an identifier (from the query above or via fetch object).
    if ($this->id) {
      $this->alternativeText = $i18n->t("Backdrop for {movie_title}", [ "movie_title" => $this->movieTitle]);
      $this->deleted         = (boolean) $this->deleted;
      $this->imageExists     = (boolean) $this->changed && !$this->deleted;
      $this->filename        = $this->id;
    }

    // Always initialize the following, it doesn't matter if the image exists or not.
    $this->movieId   = $movieId;
    $this->directory = "movie/{$this->movieId}/backdrops";

    // Initialize the movie backdrop's route.
    if ($this->imageExists === true) {
      $this->route = $i18n->r("/movie/{0}/backdrop/{1}", [ $this->movieId, $this->id ]);
    }
    else {
      $this->route = $i18n->r("/movie/{0}/backdrops/upload", [ $this->movieId ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Mark the movie backdrop as deleted.
   *
   * @todo User with enough reputation should be able to remove the image without leaving traces as well.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\User\Session $session
   * @param boolean $shred [optional]
   *   Whether the image should be removed traceless or not, defaults to keep traces.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   * @throws \RuntimeException
   */
  public function delete($shred = false) {
    global $db, $session;

    // Only attempt to delete this image if we have a regular deletion request.
    if (!isset($this->deletionId)) {
      throw new \RuntimeException;
    }

    // Flag this image as deleted in the database.
    $db->query(
      "UPDATE `backdrops` SET `deleted` = true, `styles` = NULL WHERE `id` = ? AND `movie_id` = ?",
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
   * Generate all supported image styles.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param string $source
   *   Absolute path to the uploaded image.
   * @param boolean $regenerate [optional]
   *   Whether to regenerate existing styles.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function generateStyles($source, $regenerate = false) {
    global $db, $i18n;

    try {
      // Start transaction and prepare database record if this is a new upload.
      $db->transactionStart();
      if ($this->imageExists === false) {
        $this->insert()->createDirectories();
        $this->route = $i18n->t("/movie/{0}/backdrop/{1}", [ $this->movieId, $this->id ]);
      }

      // Generate the various image's styles and always go from best quality down to worst quality.
      $this
        ->convert($source, self::STYLE_SPAN_07, self::STYLE_SPAN_07, self::STYLE_SPAN_07)
        ->convert($source, self::STYLE_SPAN_03)
        ->convert($source, self::STYLE_SPAN_02)
        ->convert($source, self::STYLE_SPAN_01)
        ->convert($source, self::STYLE_SPAN_01_SQUARE, self::STYLE_SPAN_01, self::STYLE_SPAN_01, true)
      ;

      // Only update the styles if this is a new upload or the only purpose of calling this method was to regenerate
      // the styles.
      if ($this->imageExists === false || $regenerate === true) {
        $query  = "UPDATE `backdrops` SET `styles` = ? WHERE `id` = ?";
        $types  = "sd";
        $params = [ serialize($this->styles), $this->id ];
      }
      // Update just about everything if this is an upload that replaces the existing image.
      else {
        $query =
          "UPDATE `backdrops` SET
            `uploader_id`      = ?
            `changed`          = FROM_UNIXTIME(?),
            `dyn_descriptions` = COLUMN_ADD(`dyn_descriptions`, ?, ?),
            `extension`        = ?,
            `filesize`         = ?,
            `height`           = ?,
            `styles`           = ?,
            `width`            = ?
          WHERE `id` = ?"
        ;
        $types  = "disssiisi";
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
   * Insert new backdrop.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function insert() {
    global $db, $i18n, $session;
    $this->id = $db->query(
      "INSERT INTO `backdrops` SET
        `movie_id`         = ?,
        `uploader_id`      = ?,
        `changed`          = FROM_UNIXTIME(?),
        `created`          = FROM_UNIXTIME(?),
        `dyn_descriptions` = COLUMN_CREATE(`dyn_descriptions`, ?, ?),
        `extension`        = ?,
        `filesize`         = ?,
        `height`           = ?,
        `width`            = ?",
      "ddiisssiii",
      [
        $this->movieId,
        $session->id,
        $_SERVER["REQUEST_TIME"],
        $_SERVER["REQUEST_TIME"],
        $i18n->languageCode,
        $this->description,
        $this->extension,
        $this->filesize,
        $this->height,
        $this->width,
      ]
    )->insert_id;
    $this->filename = $this->id;
    return $this;
  }

  protected function update() {
    global $db, $i18n;
    $db->query(
      "UPDATE `backdrops` SET"
    )->close();
    return $this;
  }

  /**
   * Update the image's description.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param string $description
   *   The updated description text.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function updateDescription($description) {
    global $db, $i18n;
    $db->query("UPDATE `backdrops` SET `dyn_descriptions` = COLUMN_ADD(?, ?) WHERE `id` = ?", "ssd", [ $i18n->languageCode, $description, $this->id ])->close();
    $this->description = $description;
    // @todo Commit
    return $this;
  }

  /**
   * Set deletion request identifier.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $id
   *   The deletion request's unique identifier to set.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function setDeletionRequest($id) {
    global $db;
    $db->query("UPDATE `backdrops` SET `deletion_request_id` = ? WHERE `id` = ? AND `movie_id` = ?", "didi", [ $id, $this->id, $this->movieId ])->close();
    return $this;
  }

}
