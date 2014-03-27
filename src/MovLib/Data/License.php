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
 * Represents a single license.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class License {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The license's official abbreviation.
   *
   * @var string
   */
  public $abbreviation;

  /**
   * The license's translated description text.
   *
   * @var string
   */
  public $description;

  /**
   * The license's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The license's translated name.
   *
   * @var string
   */
  public $name;

  /**
   * The license's absolute URL.
   *
   * @var string
   */
  public $url;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new license.
   *
   * @param integer $id [optional]
   *   The unique license identifier. If no identifier is passed an empty license is created.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \OutOfBoundsException
   */
  public function __construct($id = null) {
    // If we have an identifier try to fetch the license from the database.
    if ($id) {
      $stmt = $db->query(
        "SELECT
          `id`,
          IFNULL(COLUMN_GET(`dyn_names`, ? AS CHAR(255)), COLUMN_GET(`dyn_names`, '{$i18n->defaultLanguageCode}' AS CHAR(255))) AS `name`,
          COLUMN_GET(`dyn_descriptions`, ? AS CHAR) AS `description`,
          `abbreviation`,
          COLUMN_GET(`dyn_url`, ? AS CHAR(255)) AS `url`
        FROM `licenses`
        WHERE `id` = ? LIMIT 1",
        "sssi",
        [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $id ]
      );
      $stmt->bind_result($this->id, $this->name, $this->description, $this->abbreviation, $this->url);
      if (!$stmt->fetch()) {
        throw new \OutOfBoundsException("Couldn't find license for identifier '{$id}'");
      }
      $stmt->close();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get all available licenses.
   *
   * The projection of the returned mysqli result contains the following offsets:
   * <ul>
   *   <li><code>"id"</code>: the unique license's identifier</li>
   *   <li><code>"name"</code>: the license's translated name</li>
   *   <li><code>"abbreviation"</code>: the license's unique abbreviation</li>
   * </ul>
   *
   * @return array
   *   Associative array containing all available licenses.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getLicensesResult() {
    return $db->query(
      "SELECT
        `id`,
        IFNULL(COLUMN_GET(`dyn_names`, ? AS CHAR(255)), COLUMN_GET(`dyn_names`, '{$i18n->defaultLanguageCode}' AS CHAR(255))) AS `name`,
        `abbreviation`
      FROM `licenses`
      ORDER BY `abbreviation` ASC",
      "s",
      [ $i18n->languageCode ]
    )->get_result();
  }

}
