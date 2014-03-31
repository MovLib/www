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
 * Defines the user set object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class UserSet extends \MovLib\Data\AbstractSet {

  /**
   * {@inheritdoc}
   */
  public function getEntityClassName() {
    return "\\MovLib\\Data\\User\\User";
  }

  /**
   * {@inheritdoc}
   */
  public function getCount() {
    return $this->getMySQLi()->query("SELECT COUNT(*) FROM `users` WHERE `email` IS NOT NULL LIMIT 1")->fetch_row()[0];
  }

  /**
   * {@inheritdoc}
   */
  public function getOrdered($by, $offset, $limit) {
    return $this->getMySQLi()->query(<<<SQL
SELECT
  `id`,
  `name`,
  UNIX_TIMESTAMP(`image_changed`) AS `imageChanged`,
  `image_extension` AS `imageExtension`
FROM `users`
WHERE `email` IS NOT NULL
ORDER BY {$by} LIMIT {$limit} OFFSET {$offset}
SQL
    );
  }

  /**
   * {@inheritdoc}
   * @return string|null
   *   A random, unique, and existing user's name ready for use as route. <code>NULL</code> if no user could be found.
   */
  public function getRandom() {
    if (($result = $this->getMySQLi()->query("SELECT `name` FROM `users` WHERE `email` IS NOT NULL ORDER BY RAND() LIMIT 1"))) {
      return mb_strtolower($result->fetch_row()[0]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTableName() {
    return "users";
  }

}