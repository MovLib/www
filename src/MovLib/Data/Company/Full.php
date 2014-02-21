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
namespace MovLib\Data\Company;

use \MovLib\Data\Place;
use \MovLib\Presentation\Error\NotFound;

/**
 * Contains all available information about a person.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Full extends \MovLib\Data\Company\Company {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company’s creation timestamp.
   *
   * @var string
   */
  public $created;

  /**
   * The company’s translated descriptions.
   *
   * @var string
   */
  public $description;

  /**
   * The company’s translated Wikipedia link.
   *
   * @var string
   */
  public $wikipedia;

  /**
   * The company’s aliases.
   *
   * @var array
   */
  public $aliases = [];

  /**
   * The company’s weblinks.
   *
   * @var array
   */
  public $links = [];

  /**
   * The company’s place.
   *
   * @var integer|object
   */
  public $place;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Intantiate new Company.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id
   *   The company's unique ID to load.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    global $db, $i18n;
    // Try to load the company for the given identifier.
    if ($id) {
      $this->id = $id;
      $stmt = $db->query("
          SELECT
            `aliases`,
            `created`,
            `defunct_date` AS `defunctDate`,
            `deleted`,
            COLUMN_GET(`dyn_descriptions`, '{$i18n->languageCode}' AS BINARY),
            COLUMN_GET(`dyn_wikipedia`, '{$i18n->languageCode}' AS BINARY),
            `founding_date` AS `foundingDate`,
            `links`,
            `name`,
            `place_id` AS `place`
          FROM `companies`
          WHERE
            `id` = ?
          LIMIT 1",
        "d",
        [ $this->id ]
      );
      $stmt->bind_result(
        $this->aliases,
        $this->created,
        $this->defunctDate,
        $this->deleted,
        $this->description,
        $this->wikipedia,
        $this->foundingDate,
        $this->links,
        $this->name,
        $this->place
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
    }

    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Insert a new company into the database.
   *
   * @todo Index data with Elastic.
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   */
  public function create() {
    global $db, $i18n;
    $this->id = $db->query(
      "INSERT INTO `companies` SET
        `aliases` = ?,
        `created` = CURRENT_TIMESTAMP,
        `defunct_date` = ?,
        `dyn_descriptions` = COLUMN_CREATE('{$i18n->languageCode}', ?),
        `dyn_wikipedia`= COLUMN_CREATE('{$i18n->languageCode}', ?),
        `dyn_image_descriptions` = '',
        `founding_date` = ?,
        `links` = ?,
        `name` = ?,
        `place_id` = ?
        ",
      "sssssssi",
      [
        serialize($this->aliases),
        $this->defunctDate,
        $this->description,
        $this->wikipedia,
        $this->foundingDate,
        serialize($this->links),
        $this->name,
        $this->place,
      ]
    )->insert_id;

    // Create a display photo.
    parent::init();

    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function init() {
    parent::init();
    if ($this->place) {
      $this->place = new Place($this->place);
    }
    if ($this->aliases) {
      $this->aliases = unserialize($this->aliases);
    }
    else {
      $this->aliases = [];
    }
    if ($this->links) {
      $this->links = unserialize($this->links);
    }
    else {
      $this->links = [];
    }
  }
}
