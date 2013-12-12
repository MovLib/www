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
 * Handling of one protected page.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ProtectedPage extends \MovLib\Data\Database {

  /**
   * The page's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The page's localized title.
   *
   * @var string
   */
  public $title;

  /**
   * The page's localized text.
   *
   * @var string
   */
  public $text;

  /**
   * Instantiate new protected page.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id [optional]
   *   The page's unique ID.
   */
  public function __construct($id = null) {
    global $db, $i18n;

    if ($id) {
      $query = self::getQuery();
      $stmt = $db->query("{$query} WHERE `id` = ? LIMIT 1", "ssi", [ $i18n->languageCode, $i18n->languageCode, $id ]);
      $stmt->bind_result($this->id, $this->title, $this->text);
      $stmt->close();
    }
  }

  /**
   * Get the default query.
   *
   * @global \MovLib\Data\I18n $i18n
   * @staticvar string $query
   *   Used to cache the default query.
   * @return string
   *   The default query.
   * @throws \MovLib\Exception\DatabaseException
   */
  protected static function getQuery() {
    global $i18n;
    return
      "SELECT
        `id`,
        IFNULL(COLUMN_GET(`dyn_titles`, ? AS CHAR), COLUMN_GET(`dyn_titles`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `title`,
        COLUMN_GET(`dyn_text`, ? AS CHAR) AS `text`
      FROM `protected_pages`"
    ;
  }

}
