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
   * @throws \MovLib\Exception\DatabaseException
   * @throws \OutOfBoundsException
   */
  public function __construct($personId, $personName) {
    global $db, $i18n;

    $stmt = $db->query(
      "SELECT
        `id`,
        `image_uploader_id`,
        `image_width`,
        `image_height`,
        `image_filesize`,
        `image_extension`,
        UNIX_TIMESTAMP(`image_changed`),
        COLUMN_GET(`dyn_image_descriptions`, ? AS BINARY),
        `image_styles`
      FROM `persons`
      WHERE `id` = ?
      LIMIT 1",
      "sd",
      [ $i18n->languageCode, $personId ]
    );
    $stmt->bind_result(
      $this->personId,
      $this->uploaderId,
      $this->width,
      $this->height,
      $this->filesize,
      $this->extension,
      $this->changed,
      $this->description,
      $this->styles
    );
    if (!$stmt->fetch()) {
      throw new \OutOfBoundsException("Couldn't find person photo for identifier '{$id}' (person identifier '{$personId}')");
    }
    $stmt->close();
    if ($this->uploaderId) {
      $this->imageExists = true;
    }

    $this->alternativeText = $i18n->t("Photo of {person_name}.", [ "person_name" => $personName ]);
    $this->personId        = $personId;
    $this->filename        = $this->personId;

    if ($this->imageExists === true) {
      $this->route = $i18n->r("/person/{0}/photo/", [ $personId ]);
    }
    else {
      $this->route = $i18n->r("/person/{0}/photo/edit", [ $personId ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Delete the image.
   *
   * @return this
   */
  public function delete() {
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

    // If this is a new upload create the directory.
    if ($this->imageExists === false) {
      $this->route    = $i18n->r("/person/{0}/photo", [ $this->personId ]);
      $this->createDirectories();
    }

    // Generate the various image's styles and always go from best quality down to worst quality.
    $this->convert($source, self::STYLE_SPAN_02, self::STYLE_SPAN_02, self::STYLE_SPAN_02, true);
    $this->convert($this->getPath(self::STYLE_SPAN_02), self::STYLE_SPAN_01);

    if ($regenerate === true) {
      $query  = "UPDATE `persons` SET `image_styles` = ? WHERE `id` = ?";
      $types  = "sd";
      $params = [ serialize($this->styles), $this->personId ];
    }
    else {
      $query =
        "UPDATE `persons_images` SET
          `image_changed`          = FROM_UNIXTIME(?),
          `dyn_image_descriptions` = COLUMN_ADD(`dyn_image_descriptions`, ?, ?),
          `image_extension`        = ?,
          `image_filesize`         = ?,
          `image_height`           = ?,
          `image_styles`           = ?,
          `image_uploader_id`      = ?,
          `image_width`            = ?
        WHERE `id` = ?"
      ;
      $types  = "ssssiisdid";
      $params = [
        $this->changed,
        $i18n->languageCode,
        $this->description,
        $this->extension,
        $this->filesize,
        $this->height,
        serialize($this->styles),
        $this->uploaderId,
        $this->width,
        $this->personId,
      ];
    }
    $db->query($query, $types, $params)->close();

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
    return $this;
  }

}
