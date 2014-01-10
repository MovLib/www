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
 * Deletion related methods.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Deletion {

  /**
   * Request deletion of content.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @param string $title
   *   The translated deletion request title.
   * @param string $reason
   *   The user supplied explanation why this content should be deleted.
   * @param string $url
   *   The absolute URL of the content that should be deleted.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function request($title, $reason, $url) {
    global $db, $i18n, $session;
    $db->query(
      "INSERT INTO `deletions` SET `language_code` = ?, `reason` = ?, `title` = ?, `url` = ?, `user_id` = ?",
      "ssssd",
      [ $i18n->languageCode, $reason, $title, $url, $session->userId ]
    );
  }

  /**
   * Get total deletion request count for the current language.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return integer
   *   The total deletion request count for the current language.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getCount() {
    global $db, $i18n;
    return $db->query("SELECT COUNT(*) FROM `deletions` WHERE `language_code` = ?", "s", [ $i18n->languageCode ])->get_result()->fetch_row()[0];
  }

  /**
   * Get deletion requests for current language ordered by creation date.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $offset
   *   The offset, usually provided by the pagination trait.
   * @param integer $rowCount
   *   The row count, usually provided by the pagination trait.
   * @return \mysqli_result
   *   The mysqli result of the query.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getResult($offset, $rowCount) {
    global $db, $i18n;
    return $db->query(
      "SELECT `id`, UNIX_TIMESTAMP(`created`) AS `created`, `reason`, `title`, `url`, `user_id` FROM `deletions` WHERE `language_code` = ? ORDER BY `created` DESC LIMIT ? OFFSET ?",
      "sii",
      [ $i18n->languageCode, $rowCount, $offset ]
    )->get_result();
  }

}
