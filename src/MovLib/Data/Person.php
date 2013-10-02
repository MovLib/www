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
 * Handling of Persons.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Person extends \MovLib\Data\Database {

 /**
   * Get numeric array with person names.
   *
   * @param array $personIds
   *   Numeric array containing the desired person IDs.
   * @return array
   *   Array containing the person names with the person's unique ID as key.
   */
  public function getPersonNames(array $personIds) {
   $personIds = array_unique($personIds);
    $c = count($personIds);
    $in = rtrim(str_repeat("?,", $c), ",");
    $result = $this->select(
      "SELECT `person_id`, `name` FROM `persons` WHERE `person_id` IN ({$in})",
      str_repeat("d", $c),
      $personIds
    );
    $persons = [];
    $c = count($result);
    for ($i = 0; $i < $c; ++$i) {
      $persons[$result[$i]["person_id"]] = $result[$i]["name"];
    }

    return $persons;
  }

}
