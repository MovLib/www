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
namespace MovLib\Data;

/**
 * Defines the user set object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class UserSet extends \MovLib\Core\Database implements \MovLib\Data\SetInterface {

  /**
   * Get the total user count.
   *
   * @return integer
   *   The total user count.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getCount() {
    return $this->query("SELECT COUNT(*) FROM `users` WHERE `email` IS NOT NULL")->get_result()->fetch_row()[0];
  }

  /**
   * {@inheritdoc}
   * @return \mysqli_result
   *   The user's ordered <var>$by</var>.
   */
  public function getOrdered($by, $offset, $rowCount) {
    return $this->query(
      "SELECT `id`, `name`, UNIX_TIMESTAMP(`image_changed`) AS `imageChanged`, `image_extension` AS `imageExtension` FROM `users` WHERE `email` IS NOT NULL ORDER BY {$by} LIMIT ? OFFSET ?",
      "ii",
      [ $rowCount, $offset ]
    )->get_result();
  }

}
