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
 * Handling of large amounts of user data.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Users extends \MovLib\Data\Database implements \ArrayAccess, \Countable, \Iterator, \MovLib\Data\Pagination {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The query to fetch the basic user data without <code>WHERE</code> clause.
   *
   * @var string
   */
  protected $query =
    "SELECT
      `user_id` AS `id`,
      `name`,
      `avatar_name` AS `imageName`,
      UNIX_TIMESTAMP(`avatar_changed`) AS `imageChanged`,
      `avatar_extension` AS `imageExtension`,
      `avatar_changed` IS NOT NULL as `imageExists`
    FROM `users`"
  ;

  /**
   * Array containing all users from the last query.
   *
   * @var array
   */
  protected $users;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get numeric array with basic user information.
   *
   * @param array $userIds
   *   Numeric array containing the desired user IDs.
   * @return array
   *   Array containing the users with the user's unique ID as key.
   * @throws \MovLib\Exception\DatabaseException
   * @deprecated since version 0.0.1-dev
   */
  public function getUsersById(array $userIds) {
    if (empty($userIds)) {
      return [];
    }
    $userIds     = array_unique($userIds);
    $c           = count($userIds);
    $in          = rtrim(str_repeat("?,", $c), ",");
    $result      = $this->select("{$this->query} WHERE `user_id` IN ({$in})", str_repeat("d", $c), $userIds);
    $users       = [];
    $c           = count($result);
    for ($i = 0; $i < $c; ++$i) {
      $users[$result[$i]["id"]] = $result[$i];
    }
    return $users;
  }

  /**
   * Order selected users by ID.
   *
   * @param array $filter
   *   Array containing the user IDs to fetch.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderById(array $filter) {
    if (!empty($filter)) {
      $c     = count($filter);
      $in    = rtrim(str_repeat("?,", $c), ",");
      $users = $this->getResult("{$this->query} WHERE `user_id` IN ({$in}) ORDER BY `id` ASC", str_repeat("d", $c), $filter);
      while ($user = $users->fetch_object("\\MovLib\\Data\\User")) {
        $this->users[] = $user;
      }
    }
    return $this;
  }

  /**
   * Order by creation time.
   *
   * @param int $offset [optional]
   *   The offset within all users, defaults to <code>0</code>.
   * @param int $rowCount [optional]
   *   Defines how many users are fetched from <var>$offset</var>, defaults to <code>Pagination::SPAN8</code>.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByCreated($offset = 0, $rowCount = Pagination::SPAN8) {
    $users = $this->getResult("{$this->query} WHERE `deactivated` = false ORDER BY `created` DESC LIMIT ?, ?", "ii", [ $offset, $rowCount ]);
    while ($user = $users->fetch_object("\\MovLib\\Data\\User")) {
      $this->users[] = $user;
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
  public function orderByName($offset = 0, $rowCount = Pagination::SPAN8) {
    $users = $this->getResult("{$this->query} WHERE `deactivated` = false ORDER BY `name` DESC COLLATE `utf8mb4_unicode_cs` LIMIT ?, ?", "ii", [ $offset, $rowCount ]);
    while ($user = $users->fetch_object("\\MovLib\\Data\\User")) {
      $this->users[$user->name] = $user;
    }
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Interface Methods


  /**
   * @inheritdoc
   */
  public function count() {
    return $this->affectedRows;
  }

  /**
   * @inheritdoc
   */
  public function current() {
    return current($this->users);
  }

  /**
   * @inheritdoc
   */
  public function key() {
    return key($this->users);
  }

  /**
   * @inheritdoc
   */
  public function next() {
    return next($this->users);
  }

  /**
   * @inheritdoc
   */
  public function rewind() {
    return reset($this->users);
  }

  /**
   * @inheritdoc
   */
  public function valid() {
    return isset($this->users[key($this->users)]);
  }

  /**
   * @inheritdoc
   */
  public function offsetExists($offset) {
    return isset($this->countries[$offset]);
  }

  /**
   * @inheritdoc
   */
  public function &offsetGet($offset) {
    return $this->countries[$offset];
  }

  /**
   * @inheritdoc
   */
  public function offsetSet($offset, $value) {
    $this->countries[$offset] = $value;
  }

  /**
   * @inheritdoc
   */
  public function offsetUnset($offset) {
    unset($this->countries[$offset]);
  }

}
