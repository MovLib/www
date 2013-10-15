<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Countries extends \MovLib\Data\Database implements \ArrayAccess, \Countable, \Iterator {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The basic query for all methods.
   *
   * @var string
   */
  protected $query;

  /**
   * The internal array to store the resulting country instances.
   *
   * @var array
   */
  protected $countries;


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


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Order selected countries by ISO Alpha-2 code.
   *
   * @param array $filter [optional]
   *   Array containing the country codes to fetch.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByCode(array $filter = null) {
    if ($filter) {
      $c         = count($filter);
      $in        = rtrim(str_repeat("?,", $c), ",");
      $countries = $this->getResult("{$this->query} WHERE `iso_alpha-2` IN({$in}) ORDER BY `iso_alpha-2` ASC", str_repeat("s", $c), $filter);
    }
    else {
      $countries = $this->getResult("{$this->query} ORDER BY `iso_alpha-2` ASC");
    }
    while ($country = $countries->fetch_object("\\MovLib\\Data\\Country")) {
      $this->countries[$country->code] = $country;
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
    if ($filter) {
      $c         = count($filter);
      $in        = rtrim(str_repeat("?,", $c), ",");
      $countries = $this->getResult("{$this->query} WHERE `country_id` IN({$in}) ORDER BY `id` ASC", str_repeat("i", $c), $filter);
    }
    else {
      $countries = $this->getResult("{$this->query} ORDER BY `id` ASC");
    }
    while ($country = $countries->fetch_object("\\MovLib\\Data\\Country")) {
      $this->countries[$country->id] = $country;
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
    $countries = $this->getResult($this->query);
    while ($country = $countries->fetch_object("\\MovLib\\Data\\Country")) {
      $this->countries[$country->name] = $country;
    }
    $this->countries = $i18n->getCollator()->ksort($this->countries);
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Interface Methods


  /**
   * @inheritdoc
   */
  public function count() {
    return $this->affectedRows;
  }

  /**
   * @inheritdoc
   */
  public function current() {
    return current($this->countries);
  }

  /**
   * @inheritdoc
   */
  public function key() {
    return key($this->countries);
  }

  /**
   * @inheritdoc
   */
  public function next() {
    return next($this->countries);
  }

  /**
   * @inheritdoc
   */
  public function rewind() {
    return reset($this->countries);
  }

  /**
   * @inheritdoc
   */
  public function valid() {
    return isset($this->countries[key($this->countries)]);
  }

  /**
   * @inheritdoc
   */
  public function offsetExists($offset) {
    return isset($this->countries[$offset]);
  }

  /**
   * @inheritdoc
   */
  public function &offsetGet($offset) {
    return $this->countries[$offset];
  }

  /**
   * @inheritdoc
   */
  public function offsetSet($offset, $value) {
    $this->countries[$offset] = $value;
  }

  /**
   * @inheritdoc
   */
  public function offsetUnset($offset) {
    unset($this->countries[$offset]);
  }

}
