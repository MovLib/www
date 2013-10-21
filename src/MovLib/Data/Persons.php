<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Data;

/**
 * Handling of large amounts of person data.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Persons extends \MovLib\Data\DatabaseArrayObject {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The query to fetch the basic person data without <code>WHERE</code> clause.
   *
   * @var string
   */
  protected $query =
    "SELECT
      `person_id` AS `id`,
      `name`,
      `created`
    FROM `persons`"
  ;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Order selected persons by ID.
   *
   * @param array $filter
   *   Array containing the person IDs to fetch.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderById(array $filter) {
    $this->objectsArray = [];
    if (!empty($filter)) {
      $c      = count($filter);
      $in     = rtrim(str_repeat("?,", $c), ",");
      $result = $this->query("{$this->query} WHERE `person_id` IN ({$in}) ORDER BY `id` ASC", str_repeat("d", $c), $filter)->get_result();
      /* @var $person \MovLib\Data\Person */
      while ($person = $result->fetch_object("\\MovLib\\Data\\Person")) {
        $this->objectsArray[$person->id] = $person;
      }
    }
    return $this;
  }

  /**
   * Order by creation time.
   *
   * @param int $offset [optional]
   *   The offset within all persons, defaults to <code>0</code>.
   * @param int $rowCount [optional]
   *   Defines how many persons are fetched from <var>$offset</var>, defaults to <code>Pagination::SPAN8</code>.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByCreated($offset = 0, $rowCount = Pagination::SPAN8) {
    $this->objectsArray = [];
    $result = $this->query("{$this->query} WHERE `deleted` = false ORDER BY `created` ASC LIMIT ?, ?", "ii", [ $offset, $rowCount ])->get_result();
    /* @var $person \MovLib\Data\Person */
    while ($person = $result->fetch_object("\\MovLib\\Data\\Person")) {
      $this->objectsArray[] = $person;
    }
    return $this;
  }

  /**
   * Order by personname.
   *
   * @param int $offset [optional]
   *   The offset within all persons, defaults to <code>0</code>.
   * @param int $rowCount [optional]
   *   Defines how many persons are fetched from <var>$offset</var>, defaults to <code>Pagination::SPAN8</code>.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByName($offset = 0, $rowCount = Pagination::SPAN8) {
    $this->objectsArray = [];
    $result = $this->query("{$this->query} WHERE `deleted` = false ORDER BY `name` COLLATE `utf8mb4_unicode_ci` ASC LIMIT ?, ?", "ii", [ $offset, $rowCount ])->get_result();
    /* @var $person \MovLib\Data\Person */
    while ($person = $result->fetch_object("\\MovLib\\Data\\Person")) {
      $this->objectsArray[$person->name] = $person;
    }
    return $this;
  }

}
