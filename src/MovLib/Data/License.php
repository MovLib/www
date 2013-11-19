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
class License extends \MovLib\Data\Image\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  public $abbreviation;

  public $description;

  public $id;

  public $name;

  protected static $query;

  public $url;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new license.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id [optional]
   *   The unique license identifier. If no identifier is passed an empty license is created.
   */
  public function __construct($id = null) {
    global $i18n;

    // The default query for all selects.
    if (!self::$query) {
      self::$query =
        "SELECT
          `id`,
          IFNULL(COLUMN_GET(`dyn_names`, ? AS CHAR), COLUMN_GET(`dyn_names`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `name`,
          COLUMN_GET(`dyn_descriptions`, ? AS CHAR) AS `description`,
          `abbreviation`,
          `url`,
          UNIX_TIMESTAMP(`icon_changed`) AS `imageChanged`,
          `icon_extension` AS `imageExtension`
        FROM `licenses`"
      ;
    }

    // If we have an identifier try to fetch the license from the database.
    if ($id) {
      $stmt = $this->query(self::$query . " WHERE `id` = ? LIMIT 1", "ssi", [ $i18n->languageCode, $i18n->languageCode, $id ]);
      $stmt->bind_result($this->name, $this->description, $this->abbreviation, $this->url, $this->imageChanged, $this->imageExtension);
      if (!$stmt->fetch()) {
        throw new \DomainException;
      }
      $stmt->close();
      $this->imageExists = (boolean) $this->imageChanged;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  protected function generateImageStyles($source) {

  }

  public function getImageStyle($style = self::IMAGE_STYLE_SPAN_02) {

  }

  public function getLicenses() {
    global $i18n;
    static $licenses = [];
    if (!isset($licenses[$i18n->locale])) {
      $result = $this->query(self::$query . " ORDER BY `name` ASC", "ss", [ $i18n->languageCode, $i18n->languageCode ])->get_result();
      while ($license = $result->fetch_object(__CLASS__)) {
        $licenses[$i18n->locale][$license->id] = $license;
      }
    }
    return $licenses[$i18n->locale];
  }

}
