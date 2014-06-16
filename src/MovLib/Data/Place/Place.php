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
namespace MovLib\Data\Place;

use \MovLib\Core\Database\Database;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the place entity.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Place extends \MovLib\Data\AbstractEntity {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Place";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The place's country code.
   *
   * @var string
   */
  public $countryCode;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new place object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The place's unique identifier to load.
   * @param array $values [optional]
   *   An array of values to set, keyed by property name, defaults to <code>NULL</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null, array $values = null) {
    $this->lemma =& $this->name;
    if ($id) {
      $stmt = Database::getConnection()->prepare(<<<SQL
SELECT
  `id`,
  `changed`,
  `created`,
  IFNULL(COLUMN_GET(`dyn_names`, '{$container->intl->code}' AS BINARY), `name`) AS `name`,
  `country_code` AS `countryCode`,
  `latitude`,
  `longitude`
FROM `places`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->changed,
        $this->created,
        $this->name,
        $this->countryCode,
        $this->latitude,
        $this->longitude
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Place {$id}");
      }
    }
    parent::__construct($container, $values);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init(array $values = null) {
    parent::init($values);
    $this->pluralKey   = "places";
    $this->singularKey = "place";
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function lemma($locale) {
    return $this->name;
  }

}
