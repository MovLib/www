<?php

/* !
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

use \MovLib\Presentation\Error\NotFound;

/**
 * Contains information about a place.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Place {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The place's unique ID.
   *
   * @var integer
   */
  public $id;

  /**
   * The place's countrycode.
   *
   * @var string
   */
  public $countryCode;

  /**
   * The place's localized name.
   *
   * @var string
   */
  public $name;

  /**
   * The place's latitude.
   *
   * @var float
   */
  public $latitude;

  /**
   * The place's longitude.
   *
   * @var float
   */
  public $longitude;


  // ------------------------------------------------------------------------------------------------------------------- Magic methods.


  /**
   * Intantiate new Place.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id
   *   The place's unique ID to load.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    global $db, $i18n;
    // Try to load the place for the given identifier.
    if ($id) {
      $this->id = $id;
      $stmt = $db->query(
        "SELECT
            `country_code`,
            IFNULL(COLUMN_GET(`dyn_names`, '{$i18n->languageCode}' AS BINARY), COLUMN_GET(`dyn_names`, '{$i18n->defaultLanguageCode}' AS BINARY)),
            `latitude`,
            `longitude`
          FROM `places`
          WHERE
            `place_id` = ?
          LIMIT 1",
        "d",
        [ $this->id ]
      );
      $stmt->bind_result(
        $this->countryCode,
        $this->name,
        $this->latitude,
        $this->longitude
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
    }
  }

}
