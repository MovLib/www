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
namespace MovLib\Data\User;

/**
 * Handling of large amounts of user data.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Users {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The query to fetch the basic user data without <code>WHERE</code> clause.
   *
   * @var string
   */
  protected $query =
    "SELECT
      `id`,
      `name`,
      UNIX_TIMESTAMP(`image_changed`) AS `changed`,
      `image_extension` AS `extension`
    FROM `users`"
  ;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the total count of undeleted users.
   *
   * @return integer
   *   The total count of undeleted users.
   */
  public function getTotalCount() {
    return $db->query("SELECT COUNT(*) FROM `users` WHERE `email` IS NOT NULL")->get_result()->fetch_row()[0];
  }

  /**
   * Get all undeleted users ordered by their creation timestamp.
   *
   * @param integer $offset
   *   The offset, usually provided by the pagination trait.
   * @param integer $rowCount
   *   The row count, usually provided by the pagination trait.
   * @return \mysqli_result
   *   The mysqli result of the query.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getOrderedByCreatedResult($offset, $rowCount) {
    return $db->query("{$this->query} WHERE `email` IS NOT NULL ORDER BY `created` DESC LIMIT ? OFFSET ?", "ii", [ $rowCount, $offset ])->get_result();
  }

}
