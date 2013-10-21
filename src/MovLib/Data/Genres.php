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
 * Default genres implementation.
 *
 * <b>NOTE</b>
 * All genres are always translated to the current global language.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Genres extends \MovLib\Data\DatabaseArrayObject {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new genres object.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    if ($i18n->languageCode != $i18n->defaultLanguageCode) {
      $this->query = "COLUMN_GET(`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `dynName`,";
    }
    $this->query = "SELECT `genre_id` AS `id`, {$this->query} `name` FROM `genres`";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Order selected genres by ID.
   *
   * @param array $filter [optional]
   *   Array containing all genre IDs to fetch.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderById(array $filter = null) {
    $this->objectsArray = [];
    if ($filter) {
      $c      = count($filter);
      $in     = rtrim(str_repeat("?,", $c), ",");
      $result = $this->query("{$this->query} WHERE `genre_id` IN({$in})", str_repeat("i", $c), $filter)->get_result();
    }
    else {
      $result = $this->query($this->query)->get_result();
    }
    /* @var $genre \MovLib\Data\Genre */
    while ($genre = $result->fetch_object("\\MovLib\\Data\\Genre")) {
      $genre->name = (empty($genre->dynName)) ? $genre->name : $genre->dynName;
      $this->objectsArray[$genre->id] = $genre;
    }
    return $this;
  }

  /**
   * Order selected genres by name.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByName() {
    global $i18n;
    $this->objectsArray = [];
    $result = $this->query($this->query)->get_result();
    /* @var $genre \MovLib\Data\Genre */
    while ($genre = $result->fetch_object("\\MovLib\\Data\\Genre")) {
      $genre->name = (empty($genre->dynName)) ? $genre->name : $genre->dynName;
      $this->objectsArray[$genre->name] = $genre;
    }
    $i18n->getCollator()->ksort($this->objectsArray);
    return $this;
  }

}
