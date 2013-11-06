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

use \MovLib\Data\Pagination;

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
class Users extends \MovLib\Data\DatabaseArrayObject {


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
      UNIX_TIMESTAMP(`imageChanged`) AS `imageChanged`,
      `imageExtension`,
      `imageChanged` IS NOT NULL AS `imageExists`
    FROM `users`"
  ;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Order selected users by ID.
   *
   * @param array $filter
   *   Array containing the user IDs to fetch.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderById(array $filter) {
    $this->objectsArray = [];
    if (!empty($filter)) {
      $c      = count($filter);
      $in     = rtrim(str_repeat("?,", $c), ",");
      $result = $this->query("{$this->query} WHERE `id` IN ({$in}) ORDER BY `id` ASC", str_repeat("d", $c), $filter)->get_result();
      /* @var $user \MovLib\Data\User\User */
      while ($user = $result->fetch_object("\\MovLib\\Data\\User\\User")) {
        $this->objectsArray[$user->id] = $user;
      }
    }
    return $this;
  }

  /**
   * Order by creation time, newest first.
   *
   * @param int $offset [optional]
   *   The offset within all users, defaults to <code>0</code>.
   * @param int $rowCount [optional]
   *   Defines how many users are fetched from <var>$offset</var>, defaults to <code>Pagination::SPAN8</code>.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByNewest($offset = 0, $rowCount = Pagination::SPAN_08) {
    $this->objectsArray = [];
    $result = $this->query("{$this->query} WHERE `deactivated` = false ORDER BY `created` DESC LIMIT ?, ?", "ii", [ $offset, $rowCount ])->get_result();
    /* @var $user \MovLib\Data\User\User */
    while ($user = $result->fetch_object("\\MovLib\\Data\\User\\User")) {
      $this->objectsArray[] = $user;
    }
    return $this;
  }

  /**
   * Order by username.
   *
   * @param int $offset [optional]
   *   The offset within all users, defaults to <code>0</code>.
   * @param int $rowCount [optional]
   *   Defines how many users are fetched from <var>$offset</var>, defaults to <code>Pagination::SPAN8</code>.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByName($offset = 0, $rowCount = Pagination::SPAN_08) {
    $this->objectsArray = [];
    $result = $this->query("{$this->query} WHERE `deactivated` = false ORDER BY `name` ASC LIMIT ?, ?", "ii", [ $offset, $rowCount ])->get_result();
    /* @var $user \MovLib\Data\User\User */
    while ($user = $result->fetch_object("\\MovLib\\Data\\User\\User")) {
      $this->objectsArray[$user->name] = $user;
    }
    return $this;
  }

}
