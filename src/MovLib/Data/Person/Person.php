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

use \MovLib\Data\Date;
use \MovLib\Exception\ClientException\NotFoundException;

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
class Person extends \MovLib\Data\Image\AbstractImageEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person's total award count.
   *
   * @var integer
   */
  public $awardCount;

  /**
   * The person's translated biography.
   *
   * @var null|string
   */
  public $biography;

  /**
   * The person's date of birth in <code>"Y-m-d"</code> format.
   *
   * @var \MovLib\Data\Date
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
   * @var \MovLib\Data\Date
   */
  public $deathDate;

  /**
   * The person's place of death's identifier.
   *
   * @var null|integer
   */
  protected $deathPlaceId;

  /**
   * The photo's path within the upload directory.
   *
   * @var string
   */
  protected $directory = "person";

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
  public $movieCount;

  /**
   * The person's total release count.
   *
   * @var integer
   */
  public $releaseCount;

  /**
   * The person's total series count.
   *
   * @var integer
   */
  public $seriesCount;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The person's unique identifier to instantiate, defaults to <code>NULL</code> (no person will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `id`,
  `deleted`,
  `name`,
  COLUMN_GET(`dyn_biographies`, '{$this->intl->languageCode}' AS CHAR),
  `sex`,
  `birthdate`,
  `birthplace_id`,
  `born_name`,
  `deathdate`,
  `deathplace_id`,
  COLUMN_GET(`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR),
  `award_count`,
  `movie_count`,
  `series_count`,
  `release_count`,
  HEX(`image_cache_buster`),
  `image_extension`,
  `image_filesize`,
  `image_height`,
  `image_styles`,
  `image_width`
FROM `persons` WHERE `id` = ? LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->deleted,
        $this->name,
        $this->biography,
        $this->sex,
        $this->birthDate,
        $this->birthPlaceId,
        $this->bornName,
        $this->deathDate,
        $this->deathPlaceId,
        $this->wikipedia,
        $this->awardCount,
        $this->movieCount,
        $this->seriesCount,
        $this->releaseCount,
        $this->imageCacheBuster,
        $this->imageExtension,
        $this->imageFilesize,
        $this->imageHeight,
        $this->imageStyles,
        $this->imageWidth
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find person {$id}");
      }
    }
    if ($this->id) {
      $this->init();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->birthDate && ($this->birthDate = new Date($this->birthDate));
    $this->deathDate && ($this->deathDate = new Date($this->deathDate));
    $this->imageAlternativeText = $this->intl->t("Photo of {name}", [ "name" => $this->name]);
    $this->imageDirectory       = "upload://person";
    $this->imageFilename        = $this->id;
    $this->pluralKey            = $this->tableName = "persons";
    $this->route                = $this->intl->r("/person/{0}", [ $this->id]);
    $this->singularKey          = "person";
    return parent::init();
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
      return new \MovLib\Data\Place($this->diContainer, $this->birthPlaceId);
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
      return new \MovLib\Data\Place($this->diContainer, $this->deathPlaceId);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function imageSaveStyles() {
    $styles = serialize($this->imageStyles);
    $stmt   = $this->getMySQLi()->prepare("UPDATE `persons` SET `image_styles` = ? WHERE `id` = ?");
    $stmt->bind_param("sd", $styles, $this->id);
    $stmt->execute();
    $stmt->close();
    return $this;
  }

}
