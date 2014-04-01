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
namespace MovLib\Data\Person;

use \MovLib\Presentation\Error\NotFound;

/**
 * Represents a single person including their photo.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Person extends \MovLib\Data\AbstractDatabaseEntity {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * 220x220>
   *
   * Image style used on the show page to display the person photo.
   *
   * @var integer
   */
  const STYLE_SPAN_03 = null;//\MovLib\Data\Image\SPAN_03;


  // ------------------------------------------------------------------------------------------------------------------- Properties



  /**
   * The person's creation timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * The person's translated biography.
   *
   * @var string
   */
  public $biography;

  /**
   * The person's date of birth in <code>"Y-m-d"</code> format.
   *
   * @var string
   */
  public $birthDate;

  /**
   * The person's place of birth's identifier.
   *
   * @var null|integer
   */
  protected $birthPlaceId;

  /**
   * The person's birth name.
   *
   * @var string
   */
  public $bornName;

  /**
   * The person's date of death in <code>"Y-m-d"</code> format.
   *
   * @var string
   */
  public $deathDate;

  /**
   * The person's place of death's identifier.
   *
   * @var null|integer
   */
  protected $deathPlaceId;

  /**
   * The person's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The photo's path within the upload directory.
   *
   * @var string
   */
  protected $directory = "person";

  /**
   * The person's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * Associative array containing gendered and translated job names with the job identifiers as keys.
   *
   * @var array
   */
  protected $jobTranslations;

  /**
   * The person's name.
   *
   * @var string
   */
  public $name;

  /**
   * The person's total movie count.
   *
   * @var integer
   */
  public $movieCount = 0;

  /**
   * The person's nickname.
   *
   * @var string
   */
  public $nickname;

  /**
   * The person's total release count.
   *
   * @var integer
   */
  public $releaseCount = 0;

  /**
   * The person's translated route.
   *
   * @var string
   */
  public $route;

  /**
   * The person's total series count.
   *
   * @var integer
   */
  public $seriesCount = 0;

  /**
   * The person's sex.
   *
   * @var integer
   */
  public $sex;

  /**
   * The person's translated Wikipedia URL.
   *
   * @var string
   */
  public $wikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Initialization Methods


  /**
   * Initialize new person.
   *
   * @param integer $id
   *   The unique person's identifier to load.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function init($id) {
    $stmt = $this->getMySQLi()->prepare(
      "SELECT
        `deleted`,
        `name`,
        `sex`,
        `birthdate`,
        `birthplace_id`,
        `born_name`,
        `deathdate`,
        `deathplace_id`,
        `nickname`,
        `count_movies`,
        `count_series`,
        `count_releases`,
        `image_uploader_id`,
        `image_width`,
        `image_height`,
        `image_filesize`,
        `image_extension`,
        `image_styles`
      FROM `persons`
      WHERE
        `id` = ?
      LIMIT 1"
    );
    $stmt->bind_param("d", $id);
    $stmt->execute();
    $stmt->bind_result(
      $this->deleted,
      $this->name,
      $this->sex,
      $this->birthDate,
      $this->birthPlaceId,
      $this->bornName,
      $this->deathDate,
      $this->deathPlaceId,
      $this->nickname,
      $this->movieCount,
      $this->seriesCount,
      $this->releaseCount,
      $this->uploaderId,
      $this->width,
      $this->height,
      $this->filesize,
      $this->extension,
      $this->styles
    );
    if (!$stmt->fetch()) {
      throw new NotFound;
    }
    $stmt->close();
    $this->id = $id;

    // The person's photo name is always the person's identifier, so set it here.
    $this->filename = &$this->id;
    $this->initFetchObject();
  }

  /**
   * Initialize the person with their image, deleted flag and translate their route.
   *
   * return this
   */
  public function initFetchObject() {
    $this->deleted = (boolean) $this->deleted;
    $this->route   = $this->intl->r("/person/{0}", [ $this->id]);
    $this->toDates([ &$this->birthDate, &$this->deathDate ]);
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the person's place of birth.
   *
   * @return null|\MovLib\Data\Place
   *   The person's place of birth or <code>NULL</code> if none was found.
   */
  public function getBirthPlace() {
    if ($this->birthPlaceId) {
      return (new \MovLib\Data\Place($this->diContainer))->init($this->birthPlaceId);
    }
  }

  /**
   * Get the person's place of death.
   *
   * @return null|\MovLib\Data\Place
   *   The person's place of death or <code>NULL</code> if none was found.
   */
  public function getDeathPlace() {
    if ($this->deathPlaceId) {
      return (new \MovLib\Data\Place($this->diContainer))->init($this->deathPlaceId);
    }
  }

  /**
   * Get the translated and gendered job title.
   *
   * @param integer $id
   *   The job's identifier.
   * @return string
   *   The translated and gendered job title.
   */
  public function getJobTitle($id) {
    if (!isset($this->jobTranslations[$id])) {
      $this->jobTranslations[$id] = $this->query(
        "SELECT
          IFNULL(
            COLUMN_GET(`jobs`.`dyn_names_sex0`, ? AS BINARY),
            COLUMN_GET(`jobs`.`dyn_names_sex0`, '{$this->intl->defaultLanguageCode}' AS BINARY)
          ),
          IFNULL(
            COLUMN_GET(`jobs`.`dyn_names_sex1`, ? AS BINARY),
            COLUMN_GET(`jobs`.`dyn_names_sex1`, '{$this->intl->defaultLanguageCode}' AS BINARY)
          ),
          IFNULL(
            COLUMN_GET(`jobs`.`dyn_names_sex2`, ? AS BINARY),
            COLUMN_GET(`jobs`.`dyn_names_sex2`, '{$this->intl->defaultLanguageCode}' AS BINARY)
          )
        FROM `jobs`
        WHERE `id` = ?
        LIMIT 1",
        "sssd",
        [ $this->intl->languageCode, $this->intl->languageCode, $this->intl->languageCode, $id ]
      )->get_result()->fetch_row()[$this->sex];
    }
    return $this->jobTranslations[$id];
  }

  /**
   * {@inheritdoc}
   */
  public function getPluralName() {
    return "persons";
  }

  /**
   * {@inheritdoc}
   */
  public function getSingularName() {
    return "person";
  }


  // ------------------------------------------------------------------------------------------------------------------- Image Methods


  /**
   * Delete the image.
   *
   * @todo Implement delete person image
   * @return this
   */
  public function delete() {
    return $this;
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
    // Generate the various image's styles and always go from best quality down to worst quality.
    $this->convert($source, self::STYLE_SPAN_03, self::STYLE_SPAN_03, self::STYLE_SPAN_03, true);
    $this->convert($source, self::STYLE_SPAN_02, self::STYLE_SPAN_02, self::STYLE_SPAN_02, true);
    $this->convert($source, self::STYLE_SPAN_01, self::STYLE_SPAN_01, self::STYLE_SPAN_01, true);

    if ($regenerate === true) {
      $query  = "UPDATE `persons` SET `image_styles` = ? WHERE `id` = ?";
      $types  = "sd";
      $params = [ serialize($this->styles), $this->id ];
    }
    else {
      $this->changed = time();
      $query =
        "UPDATE `persons` SET
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
      $types  = "isssiisdid";
      $params = [
        $this->changed,
        $this->intl->languageCode,
        $this->description,
        $this->extension,
        $this->filesize,
        $this->height,
        serialize($this->styles),
        $this->uploaderId,
        $this->width,
        $this->id,
      ];
    }
    $this->query($query, $types, $params)->close();

    return $this;
  }

  /**
   * Set deletion request identifier.
   *
   * @todo Implement deletion request
   * @param integer $id
   *   The deletion request's unique identifier to set.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function setDeletionRequest($id) {
    return $this;
  }

}
