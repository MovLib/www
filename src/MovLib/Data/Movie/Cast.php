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
namespace MovLib\Data\Movie;

use \MovLib\Data\Person\Person;
use \MovLib\Presentation\Error\NotFound;

/**
 * Represents a single cast appearance of a person.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Cast {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The cast's unique ID.
   *
   * @var integer
   */
  public $id;

  /**
   * The movie's ID the cast belongs to.
   *
   * @var integer
   */
  public $movieId;

  /**
   * The cast's person ID.
   *
   * @var integer
   */
  public $personId;

  /**
   * The cast's job (always the ID of the "Actor" job).
   *
   * @var integer
   */
  public $jobId;

  /**
   * The cast's translated role name (roles without their own person entry).
   *
   * @var string
   */
  public $roleName;

  /**
   * The cast specific alias for the person.
   *
   * @var string
   */
  public $alias;

  /**
   * The cast's role (with their own person entry).
   *
   * Contains either <code>NULL</code>, an object of type {@see \MovLib\Data\Person\Person} or <code>TRUE</code>
   * if the person is the same as the role (plays himself/herself).
   *
   * @var boolean|\MovLib\Data\Person\Person
   */
  public $role;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new cast.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $castId [optional]
   *   The unique cast's identifier to load, omit to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($castId = null) {
    global $db, $i18n;

    // Try to load the cast for the given identifier.
    // @todo Fetch real alias when it's implemented.
    if ($castId) {
      $stmt = $db->query(
        "SELECT
          `id`,
          `movie_id`,
          `person_id`,
          `job_id`,
          IFNULL(COLUMN_GET(`dyn_role`, ? AS BINARY), COLUMN_GET(`dyn_role`, ? AS BINARY)),
          `alias_id`,
          `role_id`
        FROM `movies_cast`
        WHERE `cast_id` = ?",
        "ssd",
        [ $i18n->languageCode, $i18n->defaultLanguageCode, $castId ]
      );
      $stmt->bind_result(
        $this->id,
        $this->movieId,
        $this->personId,
        $this->jobId,
        $this->roleName,
        $this->alias,
        $this->role
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
    }

    // If we have an identifier, either from the above query or directly set via PHP's fetch_object() method, try to
    // load the person of the role.
    if ($this->id && $this->role) {
      // If the person plays himself/herself, don't fetch a person object.
      if ($this->role === $this->personId) {
        $this->role = true;
      }
      else {
        $this->role = new Person($this->role);
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the person playing the role.
   *
   * @return \MovLib\Data\Person\Person
   *   The person playing this role.
   */
  public function getPerson() {
    if ($this->personId) {
      return new Person($this->personId);
    }
  }

}
