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

use \MovLib\Data\FileSystem;
use \MovLib\Presentation\Error\NotFound;

/**
 * Handling of one system page.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SystemPage extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The page's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The page's localized route.
   *
   * @var string
   */
  public $route;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new protected page.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id [optional]
   *   The page's unique identifier, defaults to no identifier which creates an empty object.
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    global $db, $i18n;

    if ($id) {
      $stmt = $db->query(
        "SELECT
          `id`,
          COLUMN_GET(`dyn_titles`, ? AS CHAR(255)) AS `title`,
          COLUMN_GET(`dyn_texts`, ? AS BINARY) AS `text`,
          COLUMN_GET(`dyn_titles`, '{$i18n->defaultLanguageCode}' AS CHAR(255)) AS `route`
        FROM `system_pages`
        WHERE `id` = ?
        LIMIT 1",
        "ssi",
        [ $i18n->languageCode, $i18n->languageCode, $id ]
      );
      $stmt->bind_result($this->id, $this->title, $this->text, $this->route);
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
    }

    if ($this->route) {
      $this->route = FileSystem::sanitizeFilename($this->route);
      $this->route = "/{$this->route}";
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Write changes to the system page to the database.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function commit() {
    global $db, $i18n;

    $db->query(
      "UPDATE `system_pages` SET
        `dyn_titles` = COLUMN_ADD(`dyn_titles`, ?, ?),
        `dyn_texts` = COLUMN_ADD(`dyn_texts`, ?, ?)
      WHERE `id` = ?
      LIMIT 1",
      "ssssi",
      [
        $i18n->languageCode,
        $this->title,
        $i18n->languageCode,
        $this->text,
        $this->id,
      ]
    )->close();

    return $this;
  }

  /**
   * Get all available system pages.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   All available system pages.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getSystemPages() {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `id`,
        COLUMN_GET(`dyn_titles`, ? AS CHAR(255)) AS `title`,
        COLUMN_GET(`dyn_titles`, '{$i18n->defaultLanguageCode}' AS CHAR(255)) AS `route`
      FROM `system_pages`",
      "s",
      [ $i18n->languageCode ]
    )->get_result();
  }

}
