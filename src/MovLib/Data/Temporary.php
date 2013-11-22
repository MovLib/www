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

use \MovLib\Exception\DatabaseException;

/**
 * Interface for the temporary database.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Temporary extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * TTL value for records in the temporary table that are deleted on a daily basis.
   *
   * @var int
   */
  const TMP_TTL_DAILY = "@daily";


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Delete a temporary record.
   *
   * @param string $key
   *   The key of the entry that should be deleted.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function delete($key) {
    return $this->query("DELETE FROM `tmp` WHERE `key` = ?", "s", [ $key ]);
  }

  /**
   * Get record from temporary table.
   *
   * @param string $key
   *   The key of the record to get.
   * @return mixed
   *   The unserialized data of the record that was previously stored or <code>FALSE</code> if nothing was found.
   * @throws DatabaseException
   */
  public function get($key) {
    if (!($result = $this->query("SELECT `data` FROM `tmp` WHERE `key` = ? LIMIT 1", "s", [ $key ])->get_result()->fetch_row())) {
      return false;
    }
    return unserialize($result[0]);
  }

  /**
   * Create new record in the tmp table.
   *
   * @param mixed $data
   *   The data that should be stored in the table.
   * @param string $key [optional]
   *   The <var>$key</var> can be used to override the generation of a hash as key.
   * @param string $ttl [optional]
   *   The cron interval in which this entry should be deleted.
   * @return string
   *   The key of the newly created entry.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function set($data, $key = null, $ttl = self::TMP_TTL_DAILY) {
    if (!$key) {
      $key = hash("sha256", openssl_random_pseudo_bytes(1024));
    }
    $this->query("INSERT INTO `tmp` (`data`, `key`, `ttl`) VALUES (?, ?, ?)", "sss", [ serialize($data), $key, $ttl ]);
    return $key;
  }

  /**
   * Update existing record in the tmp table.
   *
   * @param mixed $data
   *   The record's data.
   * @param string $key [optional]
   *   The <var>$key</var> can be used to override the generation of a hash as key.
   * @param string $ttl [optional]
   *   The cron interval in which this entry should be deleted.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function update($data, $key, $ttl = self::TMP_TTL_DAILY) {
    $this->query("UPDATE `tmp` SET `created` = CURRENT_TIMESTAMP, `data` = ?, `ttl` = ?  WHERE `key` = ?", "sss", [ serialize($data), $ttl, $key ]);
    return $this;
  }

}
