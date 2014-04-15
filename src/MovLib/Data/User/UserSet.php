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
  public function getCount() {
    $result = $this->getMySQLi()->query("SELECT COUNT(*) FROM `users` WHERE `email` IS NOT NULL LIMIT 1");
    $count  = $result->fetch_row()[0];
    $result->free();
    return $count;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `id`,
  `name`,
  `access`,
  `changed`,
  `created`,
  HEX(`image_cache_buster`) AS `imageCacheBuster`,
  `image_extension` AS `imageExtension`,
  `image_styles` AS `imageStyles`
FROM `users`
{$where}
{$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOrdered($by, $offset, $limit, $where = null) {
    if ($where) {
     $where = " AND {$where}";
    }
    return $this->loadEntities("WHERE `email` IS NOT NULL{$where}", "ORDER BY {$by} LIMIT {$limit} OFFSET {$offset}");
  }

  /**
   * {@inheritdoc}
   * @return null|string
   *   A random, unique, and existing user's name ready for use as route. <code>NULL</code> if no user could be found.
   */
  public function getRandom() {
    $name   = null;
    $result = $this->getMySQLi()->query("SELECT `name` FROM `users` WHERE `email` IS NOT NULL ORDER BY RAND() LIMIT 1");
    if ($result) {
      $name = mb_strtolower($result->fetch_row()[0]);
    }
    $result->free();
    return $name;
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->pluralKey   = $this->tableName = "users";
    $this->route       = $this->intl->rp("/users");
    $this->singularKey = "user";
    return parent::init();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractSet $set, $in) {
    return <<<SQL

SQL;
  }

}
