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
 * Default languages implementation.
 *
 * <b>NOTE</b>
 * All languages are always translated to the current global language.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Languages extends \MovLib\Data\Database implements \ArrayAccess, \Countable, \Iterator {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The basic query for all methods.
   *
   * @var string
   */
  protected $query;

  /**
   * The internal array to store the resulting language instances.
   *
   * @var array
   */
  protected $languages;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new languages object.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    if ($i18n->languageCode != $i18n->defaultLanguageCode) {
      $this->query = "COLUMN_GET(`dyn_translations`, '{$i18n->languageCode}' AS BINARY) AS ";
    }
    $this->query = "SELECT `language_id` AS `id`, {$this->query}`name`, `iso_alpha-2` AS `code` FROM `languages`";
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Order selected languages by ISO Alpha-2 code.
   *
   * @param array $filter [optional]
   *   Array containing the language codes to fetch.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByCode(array $filter = null) {
    if ($filter) {
      $c         = count($filter);
      $in        = rtrim(str_repeat("?,", $c), ",");
      $languages = $this->getResult("{$this->query} WHERE `iso_alpha-2` IN({$in}) ORDER BY `iso_alpha-2` ASC", str_repeat("s", $c), $filter);
    }
    else {
      $languages = $this->getResult("{$this->query} ORDER BY `iso_alpha-2` ASC");
    }
    while ($language = $languages->fetch_object("\\MovLib\\Data\\Language")) {
      $this->languages[$language->code] = $language;
    }
    return $this;
  }

  /**
   * Order selected languages by ID.
   *
   * @param array $filter [optional]
   *   Array containing all language IDs to fetch.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderById(array $filter = null) {
    if ($filter) {
      $c         = count($filter);
      $in        = rtrim(str_repeat("?,", $c), ",");
      $languages = $this->getResult("{$this->query} WHERE `language_id` IN({$in}) ORDER BY `id` ASC", str_repeat("i", $c), $filter);
    }
    else {
      $languages = $this->getResult("{$this->query} ORDER BY `id` ASC");
    }
    while ($language = $languages->fetch_object("\\MovLib\\Data\\Language")) {
      $this->languages[$language->id] = $language;
    }
    return $this;
  }

  /**
   * Order selected languages by name.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByName() {
    global $i18n;
    $languages = $this->getResult($this->query);
    while ($language = $languages->fetch_object("\\MovLib\\Data\\Language")) {
      $this->languages[$language->name] = $language;
    }
    $this->languages = $i18n->getCollator()->ksort($this->languages);
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
    return current($this->languages);
  }

  /**
   * @inheritdoc
   */
  public function key() {
    return key($this->languages);
  }

  /**
   * @inheritdoc
   */
  public function next() {
    return next($this->languages);
  }

  /**
   * @inheritdoc
   */
  public function rewind() {
    return reset($this->languages);
  }

  /**
   * @inheritdoc
   */
  public function valid() {
    return isset($this->languages[key($this->languages)]);
  }

  /**
   * @inheritdoc
   */
  public function offsetExists($offset) {
    return isset($this->languages[$offset]);
  }

  /**
   * @inheritdoc
   */
  public function &offsetGet($offset) {
    return $this->languages[$offset];
  }

  /**
   * @inheritdoc
   */
  public function offsetSet($offset, $value) {
    $this->languages[$offset] = $value;
  }

  /**
   * @inheritdoc
   */
  public function offsetUnset($offset) {
    unset($this->languages[$offset]);
  }

}
