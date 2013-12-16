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
   *   The page's unique ID.
   */
  public function __construct($id) {
    global $db, $i18n;
    $stmt = $db->query(
      "SELECT
        `id`,
        IFNULL(COLUMN_GET(`dyn_titles`, ? AS CHAR(255)), COLUMN_GET(`dyn_titles`, '{$i18n->defaultLanguageCode}' AS CHAR(255))) AS `title`,
        COLUMN_GET(`dyn_titles`, '{$i18n->defaultLanguageCode}' AS CHAR(255)) AS `route`,
        COLUMN_GET(`dyn_texts`, ? AS BINARY) AS `text`
      FROM `system_pages`
      WHERE `id` = ?
      LIMIT 1",
      "ssi",
      [ $i18n->languageCode, $i18n->languageCode, $id ]
    );
    $stmt->bind_result($this->id, $this->title, $this->route, $this->text);
    if (!$stmt->fetch()) {
      throw new \OutOfBoundsException("Couldn't fetch system page with '{$id}'");
    }
    $this->route = FileSystem::sanitizeFilename($this->route);
    $this->route = "/{$this->route}";
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
        `dyn_texts` = COLUMN_ADD(`dyn_titles`, ?, ?)
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
    );

    return $this;
  }

}
