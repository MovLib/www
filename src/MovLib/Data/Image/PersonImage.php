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

/**
 * Represents a single person photo.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PersonImage extends \MovLib\Data\Image\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Properties

  /**
   * The photo's path within the upload directory.
   *
   * @see PersonImage::__construct()
   * @var string
   */
  protected $directory = "person";

  /**
   * The photo's unique person identifier.
   *
   * @var integer
   */
  protected $personId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person photo.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $personId
   *   The unique person identifier this photo belongs to.
   * @param string $personName
   *   The display name of the person.
   * @param integer $id [optional]
   *   The photo's identifier to load, leave empty to instantiate empty person photo (default).
   * @throws \MovLib\Exception\DatabaseException
   * @throws \OutOfBoundsException
   */
  public function __construct($personId, $personName, $id = null) {
    global $db, $i18n;

    if ($id) {
      $stmt = $db->query(
        "SELECT
          `id`,
          `user_id`,
          `license_id`,
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
        FROM `persons_images`
        WHERE `id` = ? AND `person_id` = ?
        LIMIT 1",
        "id",
        [ $id, $personId ]
      );
      $stmt->bind_result(
        $this->id,
        $this->userId,
        $this->licenseId,
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
        throw new \OutOfBoundsException("Couldn't find person photo for identifier '{$id}' (person identifier '{$personId}')");
      }
      $stmt->close();
      $this->imageExists = true;
    }
    elseif ($this->id) {
      $this->imageExists = true;
    }

    $this->alternativeText = $i18n->t("Photo of {person_name}.", [ "person_name" => $personName ]);
    $this->directory      .= "/{$personId}";
    $this->personId        = $personId;
    $this->filename        = $this->id;

    if ($this->imageExists === true) {
      $this->route = $i18n->r("/person/{0}/photo/{1}", [ $personId, $this->id ]);
    }
    else {
      $this->route = $i18n->rp("/person/{0}/photos/upload", [ $personId ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


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

    // If this is a new upload insert the just uploaded image.
    if ($this->imageExists === false) {
      // Reserve the identifier in the table and directly insert the person's identifier as well, as it can't change in
      // the future plus the creation timestamp. We still keep the image in the deleted state because we don't want
      // that any request that might come in right now while we generate the new image styles is considered to exists.
      $db->query(
        "INSERT INTO `persons_images` SET
          `id`        = (SELECT IFNULL(MAX(`s`.`id`), 0) + 1 FROM `persons_images` AS `s` WHERE `s`.`person_id` = ? LIMIT 1),
          `person_id` = ?,
          `created`   = FROM_UNIXTIME(?)",
        "dds",
        [ $this->personId, $this->personId, $this->created ]
      )->close();

      // Snatch the just inserted identifier from the database and prepare filename, identifier and route which allows
      // us to generate the directory for the images and the various image styles.
      $stmt           = $db->query("SELECT MAX(`id`) FROM `persons_images` WHERE `person_id` = ? LIMIT 1", "d", [ $this->personId ]);
      $this->filename = $this->id = $stmt->get_result()->fetch_row()[0];
      $this->route    = $i18n->r("/person/{0}/photo/{1}", [ $this->personId, $this->id ]);
      $stmt->close();
      $this->createDirectories();
    }

    // Generate the various image's styles and always go from best quality down to worst quality.
    $this->convert($source, self::STYLE_SPAN_02, self::STYLE_SPAN_02, self::STYLE_SPAN_02, true);
    $this->convert($this->getPath(self::STYLE_SPAN_02), self::STYLE_SPAN_01);

    if ($regenerate === false) {
      $this->update();
    }
    else {
      $db->query("UPDATE `persons_images` SET `styles` = ? WHERE `id` = ? AND `person_id` = ?", "sid", [
        serialize($this->styles),
        $this->id,
        $this->personId,
      ])->close();
    }

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
    global $db, $i18n, $session;
    $db->query(
      "UPDATE `persons_images` SET
        `changed`          = FROM_UNIXTIME(?),
        `deleted`          = false,
        `dyn_descriptions` = COLUMN_CREATE(?, ?),
        `extension`        = ?,
        `filesize`         = ?,
        `height`           = ?,
        `license_id`       = ?,
        `styles`           = ?,
        `user_id`          = ?,
        `width`            = ?
      WHERE `id` = ? AND `person_id` = ?",
      "ssssiiisdiid",
      [
        $this->changed,
        $i18n->languageCode,
        $this->description,
        $this->extension,
        $this->filesize,
        $this->height,
        $this->licenseId,
        serialize($this->styles),
        $session->userId,
        $this->width,
        $this->id,
        $this->personId,
      ]
    )->close();
    return $this;
  }

}
