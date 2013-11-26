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
 * Represents a single person photo.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PersonPhoto extends \MovLib\Data\Image\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The photo's alternative text.
   *
   * @var string
   */
  protected $alternativeText;

  /**
   * The photo's translated description.
   *
   * @var string
   */
  protected $description;

  /**
   * The photo's path within the upload directory.
   *
   * @see PersonPhoto::__construct()
   * @var string
   */
  protected $imageDirectory = "person";

  /**
   * The photo's identifier (unique together with the person identifier).
   *
   * @var integer
   */
  public $id;

  /**
   * The photo's license identifier.
   *
   * @var integer
   */
  public $licenseId;

  /**
   * The photo's unique person identifier.
   *
   * @var integer
   */
  protected $personId;

  /**
   * The photo's route to its own details page or to the upload page if this photo doesn't exist yet.
   *
   * @var string
   */
  public $route;

  /**
   * The photo's source URL.
   *
   * @var string
   */
  public $source;

  /**
   * The photo's upvotes.
   *
   * @var integer
   */
  public $upvotes;

  /**
   * The photo's unique uploader's identifier.
   *
   * @var integer
   */
  public $userId;


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
    $this->personId = $personId;
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
        FROM `persons_photos`
        WHERE `id` = ? AND `person_id` = ?
        LIMIT 1",
        "id",
        [ $id, $this->personId ]
      );
      $stmt->bind_result(
        $this->id,
        $this->userId,
        $this->licenseId,
        $this->imageWidth,
        $this->imageHeight,
        $this->imageSize,
        $this->imageExtension,
        $this->imageChanged,
        $this->imageCreated,
        $this->upvotes,
        $this->description,
        $this->source,
        $this->imageStyles
      );
      if (!$stmt->fetch()) {
        throw new \OutOfBoundsException("Couldn't find person photo for identifier '{$id}' (person identifier '{$this->personId}')");
      }
      $stmt->close();
      $this->imageExists = true;
    }
    else {
      $this->id          = $this->getNextId();
      $this->imageExists = (boolean) $this->imageExists;
    }
    $this->alternativeText = $i18n->t("Photo of {person_name}.", [ "person_name" => $personName ]);
    $this->imageDirectory .= "/{$this->personId}";
    $this->imageName       = $this->id;
    if ($this->imageExists === true) {
      $this->route = $i18n->r("/person/{0}/photo/{1}", [ $this->personId, $this->id ]);
    }
    else {
      $this->route = $i18n->r("/person/{0}/photos/upload", [ $this->personId ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  protected function generateImageStyles($source) {
    global $db, $i18n, $session;

    // Generate the various image styles and always go from best quality down to worst quality.

  }

  public function getImageStyle($style = self::IMAGE_STYLE_SPAN_02) {

  }

  /**
   * Get the next available photo identifier.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The next available photo identifier.
   */
  protected function getNextId() {
    global $db;
    return $db->query(
      "SELECT IFNULL(MAX(`id`), 1) FROM `persons_photos` WHERE `person_id` = ? LIMIT 1", "d", [ $this->personId ]
    )->get_result()->fetch_row()[0];
  }

}
