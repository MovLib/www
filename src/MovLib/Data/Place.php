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

use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Contains information about a place.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Place extends \MovLib\Core\AbstractDatabase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The place's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The place's country code.
   *
   * @var string
   */
  public $countryCode;

  /**
   * The place's country.
   *
   * @var \MovLib\Stub\Data\Country
   */
  public $country;

  /**
   * The place's name in the current locale if available, otherwise the name returned by the map provider.
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
   * Initialize place from unique identifier.
   *
   * @param integer $id
   *   The place's unique identifier.
   * @return this
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function init($id) {
    $result = $this->getMySQLi()->query(<<<SQL
SELECT
  IFNULL(COLUMN_GET(`dyn_names`, '{$this->intl->languageCode}' AS BINARY), `name`),
  `country_code`,
  `latitude`,
  `longitude`
FROM `places` WHERE `id` = {$id} LIMIT 1
SQL
    );
    $row = $result->fetch_row();
    if (empty($row)) {
      throw new NotFoundException("Couldn't find place for '{$id}'!");
    }
    foreach ([ $this->name, $this->countryCode, $this->latitude, $this->longitude ] as $delta => &$property) {
      $property = $row[$delta];
    }
    $result->free();
    return $this->initFetchObject();
  }

  /**
   * Initialize place further after fetching the basic data.
   *
   * @return this
   */
  public function initFetchObject() {
    $this->countryCode && ($this->country = $this->intl->getTranslations("countries")[$this->countryCode]);
    return $this;
  }

}
