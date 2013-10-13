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
class Users extends \MovLib\Data\Images\AbstractImages {
  use \MovLib\Data\Image\TraitUser;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The query to fetch the basic user data without <code>WHERE</code> clause.
   *
   * @var string
   */
  protected $query;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new users database object.
   */
  public function __construct() {
    $dim = self::IMAGE_STYLE_SPAN2;
    $this->query =
      "SELECT
        `user_id` AS `id`,
        `name`,
        `avatar_name` AS `imageName`,
        UNIX_TIMESTAMP(`avatar_changed`) AS `imageChanged`,
        `avatar_extension` AS `imageExtension`,
        `avatar_changed` IS NOT NULL AS `imageExists`,
        {$dim} AS `imageHeight`,
        {$dim} AS `imageWidth`
      FROM `users`"
    ;
  }

  /**
   * Get numeric array with basic user information.
   *
   * @param array $userIds
   *   Numeric array containing the desired user IDs.
   * @return array
   *   Array containing the users with the user's unique ID as key.
   * @throws \MovLib\Exception\DatabaseException
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
   * @override
   */
  public function getImageStyleAttributes($offset, $style) {
    return [
      "alt"    => "",
      "height" => $style,
      "src"    => "{$GLOBALS["movlib"]["static_domain"]}{$this->imageDirectory}/{$this->entities[$offset]["imageName"]}.{$style}.{$this->entities[$offset]["imageExtension"]}?c={$this->entities[$offset]["imageChanged"]}",
      "width"  => $style,
    ];
  }

  /**
   * Get numeric array with basic user information sorted by creation time.
   *
   * @param int $lowerBound [optional]
   *   Lower pagination limit, defaults to <code>0</code>.
   * @param int $upperBound [optional]
   *   Upper pagination limit (how many items), defaults to <code>Users::DEFAULT_PAGINATION_SIZE</code>.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByCreated($lowerBound = 0, $upperBound = self::DEFAULT_PAGINATION_SIZE) {
    $this->entities = $this->select("{$this->query} WHERE `deactivated` = false ORDER BY `created` DESC LIMIT ?, ?", "ii", [ $lowerBound, $upperBound ]);
    return $this;
  }

}
