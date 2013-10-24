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
 * Default countries implementation.
 *
 * <b>NOTE</b>
 * All countries are always translated to the current global language.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Countries extends \MovLib\Data\DatabaseArrayObject {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new countries object.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    if ($i18n->languageCode != $i18n->defaultLanguageCode) {
      $this->query = "COLUMN_GET(`dyn_translations`, '{$i18n->languageCode}' AS BINARY) AS ";
    }
    $this->query = "SELECT `country_id` AS `id`, {$this->query}`name`, `iso_alpha-2` AS `code` FROM `countries`";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Order selected countries by ISO Alpha-2 code.
   *
   * @param array $filter [optional]
   *   Array containing the country codes to fetch.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByCode(array $filter = null) {
    $this->objectsArray = [];
    if ($filter) {
      $c      = count($filter);
      $in     = rtrim(str_repeat("?,", $c), ",");
      $result = $this->query("{$this->query} WHERE `iso_alpha-2` IN({$in}) ORDER BY `iso_alpha-2` ASC", str_repeat("s", $c), $filter)->get_result();
    }
    else {
      $result = $this->query("{$this->query} ORDER BY `iso_alpha-2` ASC")->get_result();
    }
    /* @var $country \MovLib\Data\Country */
    while ($country = $result->fetch_object("\\MovLib\\Data\\Country")) {
      $this->objectsArray[$country->code] = $country;
    }
    return $this;
  }

  /**
   * Order selected countries by ID.
   *
   * @param array $filter [optional]
   *   Array containing all country IDs to fetch.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderById(array $filter = null) {
    $this->objectsArray = [];
    if ($filter) {
      $c      = count($filter);
      $in     = rtrim(str_repeat("?,", $c), ",");
      $result = $this->query("{$this->query} WHERE `country_id` IN({$in})", str_repeat("i", $c), $filter)->get_result();
    }
    else {
      $result = $this->query($this->query)->get_result();
    }
    /* @var $country \MovLib\Data\Country */
    while ($country = $result->fetch_object("\\MovLib\\Data\\Country")) {
      $this->objectsArray[$country->id] = $country;
    }
    return $this;
  }

  /**
   * Order selected countries by name.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByName() {
    global $i18n;
    $this->objectsArray = [];
    $result = $this->query($this->query)->get_result();
    /* @var $country \MovLib\Data\Country */
    while ($country = $result->fetch_object("\\MovLib\\Data\\Country")) {
      $this->objectsArray[$country->name] = $country;
    }
    $i18n->getCollator()->ksort($this->objectsArray);
    return $this;
  }

}
