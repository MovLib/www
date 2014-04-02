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
 * Default styles implementation.
 *
 * <b>NOTE</b>
 * All styles are always translated to the current global language.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Styles extends \MovLib\Data\DatabaseArrayObject {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new styles object.
   *
   */
  public function __construct() {
    if ($i18n->languageCode != $i18n->defaultLanguageCode) {
      $this->query = "COLUMN_GET(`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `dynName`,";
    }
    $this->query = "SELECT `style_id` AS `id`, {$this->query} `name` FROM `styles`";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Order selected styles by ID.
   *
   * @param array $filter [optional]
   *   Array containing all style IDs to fetch.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderById(array $filter = null) {
    $this->objectsArray = [];
    if ($filter) {
      $c      = count($filter);
      $in     = rtrim(str_repeat("?,", $c), ",");
      $result = $this->query("{$this->query} WHERE `style_id` IN({$in}) ORDER BY `style_id` ASC", str_repeat("i", $c), $filter)->get_result();
    }
    else {
      $result = $this->query($this->query)->get_result();
    }
    /* @var $style \MovLib\Data\Style */
    while ($style = $result->fetch_object("\\MovLib\\Data\\Style")) {
      $style->name = (empty($style->dynName)) ? $style->name : $style->dynName;
      $this->objectsArray[$style->id] = $style;
    }
    return $this;
  }

  /**
   * Order selected styles by name.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByName() {
    $this->objectsArray = [];
    $result = $this->query("{$this->query} ORDER BY `style_id` ASC")->get_result();
    /* @var $style \MovLib\Data\Style */
    while ($style = $result->fetch_object("\\MovLib\\Data\\Style")) {
      $style->name = (empty($style->dynName)) ? $style->name : $style->dynName;
      $this->objectsArray[$style->name] = $style;
    }
    $i18n->getCollator()->ksort($this->objectsArray);
    return $this;
  }

}
