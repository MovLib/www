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
namespace MovLib\Data\Company;

use \MovLib\Data\Date;
use \MovLib\Data\Route\EntityRoute;
use \MovLib\Exception\ClientException\NotFoundException;
use \MovLib\Data\Place\Place;

/**
 * Defines the company object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Company extends \MovLib\Data\AbstractEntity {
  use \MovLib\Data\Company\CompanyTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company's aliases.
   *
   * @var null|array
   */
  public $aliases;

  /**
   * The company's defunct date.
   *
   * @var null|\MovLib\Data\Date
   */
  public $defunctDate;

  /**
   * The company's translated descriptions.
   *
   * @var null|string
   */
  public $description;

  /**
   * The company's founding date.
   *
   * @var null|\MovLib\Data\Date
   */
  public $foundingDate;

  /**
   * The company's weblinks.
   *
   * @var null|array
   */
  public $links;

  /**
   * The company's total movie count.
   *
   * @var null|integer
   */
  public $movieCount;

  /**
   * The company's name.
   *
   * @var string
   */
  public $name;

  /**
   * The company's place.
   *
   * @var null|\MovLib\Data\Place
   */
  public $place;

  /**
   * The company's unique place identifier.
   *
   * @var null|integer
   */
  protected $placeId;

  /**
   * The company's total release count.
   *
   * @var null|integer
   */
  public $releaseCount;

  /**
   * The company's total series count.
   *
   * @var null|integer
   */
  public $seriesCount;

  /**
   * The company's translated Wikipedia link.
   *
   * @var null|string
   */
  public $wikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new company object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The company's unique identifier to instantiate, defaults to <code>NULL</code> (no company will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `companies`.`id` AS `id`,
  `companies`.`changed` AS `changed`,
  `companies`.`created` AS `created`,
  `companies`.`deleted` AS `deleted`,
  `companies`.`name` AS `name`,
  COLUMN_GET(`companies`.`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR) AS `description`,
  `companies`.`links` AS `links`,
  `companies`.`founding_date` AS `foundingDate`,
  `companies`.`defunct_date` AS `defunctDate`,
  `companies`.`place_id` AS `placeId`,
  COLUMN_GET(`companies`.`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR) AS `wikipedia`,
  COUNT(DISTINCT `movies_crew`.`movie_id`) AS `movieCount`,
  COUNT(DISTINCT `episodes_crew`.`series_id`) AS `seriesCount`,
  COUNT(DISTINCT `releases_labels`.`release_id`) AS `releaseCount`
FROM `companies`
  LEFT JOIN `movies_crew`     ON `movies_crew`.`company_id`     = `companies`.`id`
  LEFT JOIN `episodes_crew`   ON `episodes_crew`.`company_id`   = `companies`.`id`
  LEFT JOIN `releases_labels` ON `releases_labels`.`company_id` = `companies`.`id`
WHERE `companies`.`id` = ?
GROUP BY `id`,`name`,`aliases`,`foundingDate`,`defunctDate`,`description`,`wikipedia`,`links`,`deleted`,`changed`,`created`,`placeId`
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->name,
        $this->description,
        $this->links,
        $this->foundingDate,
        $this->defunctDate,
        $this->placeId,
        $this->wikipedia,
        $this->movieCount,
        $this->seriesCount,
        $this->releaseCount
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Company {$id}");
      }
    }
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->aliases      && ($this->aliases      = unserialize($this->aliases));
    $this->links        && ($this->links        = unserialize($this->links));
    $this->foundingDate && ($this->foundingDate = new Date($this->foundingDate));
    $this->defunctDate  && ($this->defunctDate  = new Date($this->defunctDate));
    $this->placeId      && ($this->place        = new Place($this->diContainer, $this->placeId));
    $this->route        = new EntityRoute($this->intl, "/company/{0}", $this->id, "/companies");
    return parent::init();
  }

}
