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
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Users extends \MovLib\Data\Database {

  /**
   * Get numeric array with basic user information.
   *
   * @param array $userIds
   *   Numeric array containing the desired user IDs.
   * @return array
   *   Array containing the users with the user's unique ID as key.
   */
  public function getUsers(array $userIds) {
    $userIds = array_unique($userIds);
    $c = count($userIds);
    $in = rtrim(str_repeat("?,", $c), ",");
    $result = $this->select(
      "SELECT `user_id` AS `id`, `name` FROM `users` WHERE `user_id` IN ({$in})",
      str_repeat("d", $c),
      $userIds
    );
    $users = null;
    for ($i = 0; $i < $c; ++$i) {
      $users[$userIds[$i]] = $result[$i];
    }
    return $users;
  }

}
