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
 * Handling of Countries.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Country extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Static Methods

  /**
   * Get array with localiced country names.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param array $countryIds
   *   Numeric array containing the desired country IDs.
   * @return array
   *   Array containing the localiced country names with the country's unique ID as key.
   */
  public function getCountryNames(array $countryIds) {
    global $i18n;
    if (empty($countryIds)) {
      return [];
    }
    
    $countryIds = array_unique($countryIds);
    $c = count($countryIds);
    $in = rtrim(str_repeat("?,", $c), ",");

    if ($i18n->languageCode == "en") {
      $result = $this->select(
        "SELECT `country_id`, `name` FROM `countries` WHERE `country_id` IN ({$in})",
        str_repeat("d", $c),
        $countryIds
      );
    }
    else {
      $result = $this->select(
        "SELECT `country_id`, COLUMN_GET(`dyn_translations`, '{$i18n->languageCode}' AS BINARY) AS `name`" .
        "FROM `countries` WHERE `country_id` IN ({$in})",
        str_repeat("d", $c),
        $countryIds
      );
    }

    $countryNames = [];
    $c = count($result);
    for ($i = 0; $i < $c; ++$i) {
      $countryNames[$result[$i]["country_id"]] = $result[$i]["name"];
    }
    return $countryNames;
  }

}
