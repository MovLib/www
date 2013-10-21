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
class Languages extends \MovLib\Data\DatabaseArrayObject {


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


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Order selected languages by ISO Alpha-2 code.
   *
   * @param array $filter [optional]
   *   Array containing the language codes to fetch.
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
    /* @var $language \MovLib\Data\Language */
    while ($language = $result->fetch_object("\\MovLib\\Data\\Language")) {
      $this->objectsArray[$language->code] = $language;
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
    $this->objectsArray = [];
    if ($filter) {
      $c      = count($filter);
      $in     = rtrim(str_repeat("?,", $c), ",");
      $result = $this->query("{$this->query} WHERE `language_id` IN({$in}) ORDER BY `id` ASC", str_repeat("i", $c), $filter)->get_result();
    }
    else {
      $result = $this->query("{$this->query} ORDER BY `id` ASC")->get_result();
    }
    /* @var $language \MovLib\Data\Language */
    while ($language = $result->fetch_object("\\MovLib\\Data\\Language")) {
      $this->objectsArray[$language->id] = $language;
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
    $this->objectsArray = [];
    $result = $this->query($this->query)->get_result();
    /* @var $language \MovLib\Data\Language */
    while ($language = $result->fetch_object("\\MovLib\\Data\\Language")) {
      $this->objectsArray[$language->name] = $language;
    }
    $i18n->getCollator()->ksort($this->objectsArray);
    return $this;
  }

}
