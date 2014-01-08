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

use \MovLib\Data\Image\PersonPhoto;

/**
 * Represents a single person.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Person {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person's date of birth.
   *
   * @var \DateTime
   */
  public $birthDate;

  /**
   * The person's birth name.
   *
   * @var string
   */
  public $bornName;

  /**
   * The person's date of death.
   *
   * @var \DateTime
   */
  public $deathDate;

  /**
   * The person's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The person's display photo.
   *
   * @var \MovLib\Data\Image\PersonPhoto
   */
  public $displayPhoto;

  /**
   * The person's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The person's name.
   *
   * @var string
   */
  public $name;

  /**
   * The person's nickname.
   *
   * @var string
   */
  public $nickname;

  /**
   * The person's sex.
   *
   * @var integer
   */
  public $sex;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $id [optional]
   *   The unique person's identifier to load, leave empty to create empty instance.
   * @throws \OutOfBoundsException
   */
  public function __construct($id = null) {
    global $db;

    // Try to load the person for the given identifier.
    if ($id) {
      $stmt = $db->query("SELECT `name`, `deleted` FROM `persons` WHERE `person_id` = ? LIMIT 1", "d", [ $id ]);
      $stmt->bind_result($this->name, $this->deleted);
      if (!$stmt->fetch()) {
        throw new \OutOfBoundsException("Couldn't find person for identifier '{$id}'");
      }
      $stmt->close();
      $this->id = $id;
    }

    // If we have an identifier, either from the above query or directly set via PHP's fetch_object() method, try to
    // load the photo for this person.
    if ($this->id) {
      $this->deleted = (boolean) $this->deleted;
      $this->displayPhoto = $db->query(
        "SELECT `id`, `extension`, UNIX_TIMESTAMP(`changed`) AS `changed`, `styles` FROM `persons_images` WHERE `person_id` = ? ORDER BY `upvotes` DESC LIMIT 1",
        "d",
        [ $this->id ]
      )->get_result()->fetch_object("\\MovLib\\Data\\Image\\PersonPhoto", [ $this->id, $this->name ]);

      if (!$this->displayPhoto) {
        $this->displayPhoto = new PersonPhoto($this->id, $this->name);
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the count of all persons which haven't been deleted.
   *
   * @global \MovLib\Data\Database $db
   * @staticvar null|integer $count
   * @return integer
   */
  public static function getTotalCount() {
    global $db;
    static $count = null;
    if (!$count) {
      $count = $db->query("SELECT COUNT(`id`) FROM `persons` WHERE `deleted` = false LIMIT 1")->get_result()->fetch_row()[0];
    }
    return $count;
  }

  /**
   * Get all movies matching the offset and row count.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $offset
   *   The offset in the result.
   * @param integer $rowCount
   *   The number of rows to retrieve.
   * @return \mysqli_result
   *   The query result.
   */
  public static function getPersons($offset, $rowCount) {
    global $db;
    return $db->query("
        SELECT
          `id`,
          `deleted`,
          `name`,
          `sex`,
          `birthdate` AS `birthDate`,
          `born_name` AS `bornName`,
          `deathdate` AS `deathDate`,
          `nickname`
        FROM `persons`
        WHERE
          `deleted` = false
        ORDER BY `id` DESC
        LIMIT ? OFFSET ?",
      "di",
      [ $rowCount, $offset ]
    )->get_result();
  }

}
