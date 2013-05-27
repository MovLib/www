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
namespace MovLib\Model;

use \MovLib\Exception\DatabaseException;
use \MovLib\Model\AbstractModel;

/**
 * Retrieve user specific data from the database.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserModel extends AbstractModel {

  /**
   * Contains the <tt>SELECT</tt> string of columns that should be loaded on any user data load.
   *
   * Have a look at the constructor to see which columns will be loaded.
   *
   * @var string
   */
  private $userDataColumns;

  public function __construct() {
    parent::__construct();
    $this->userDataColumns = "`" . implode("`, `", [
      "user_id` AS `id",
      "name",
      "email",
      "created",
      "access",
      "login",
      "status",
      "timezone",
      "language",
      "avatar_file_id",
    ]) . "`";
  }

  /**
   * Get all data for a user identified by email address.
   *
   * @param string $email
   *   Email address to identify the user.
   * @return array
   *   Associative array containing all related user data.
   * @throws \MovLib\Exception\DatabaseException
   *   If no user exists with the given email address.
   */
  public function getUserFrom($from, $type, $where) {
    $result = $this->query("SELECT {$this->userDataColumns} FROM `users` WHERE `{$from}` = ? LIMIT 1", $type, [ $where ]);
    if (empty($result)) {
      throw new DatabaseException("Could not find user with {$from} '{$where}'.");
    }
    return $result[0];
  }

}
