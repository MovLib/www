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
namespace MovLib\Data\Award;

use \MovLib\Data\Date;
use \MovLib\Data\Award\Award;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the award category entity object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Category extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The category's award.
   *
   * @var mixed
   */
  public $award;

  /**
   * The category's company count.
   *
   * @var null|integer
   */
  public $companyCount;

  /**
   * The category's description in the current locale.
   *
   * @var null|string
   */
  public $description;

  /**
   * The category's first year.
   *
   * @var null|\MovLib\Data\Date
   */
  public $firstYear;

  /**
   * The category's last year.
   *
   * @var null|\MovLib\Data\Date
   */
  public $lastYear;

  /**
   * The category's person count.
   *
   * @var null|integer
   */
  public $personCount;


  /**
   * The category's movie count.
   *
   * @var null|integer
   */
  public $movieCount;

  /**
   * The category's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The category's series count.
   *
   * @var null|integer
   */
  public $seriesCount;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award category object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The category's unique identifier to instantiate, defaults to <code>NULL</code> (no category will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `awards_categories`.`id` AS `id`,
  `awards_categories`.`award_id` AS `awardId`,
  `awards_categories`.`changed` AS `changed`,
  `awards_categories`.`created` AS `created`,
  `awards_categories`.`deleted` AS `deleted`,
  IFNULL(
    COLUMN_GET(`awards_categories`.`dyn_names`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`awards_categories`.`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `name`,
  `awards_categories`.`first_year` AS `firstYear`,
  `awards_categories`.`last_year` AS `lastYear`,
  COLUMN_GET(`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR) AS `description`,
  COLUMN_GET(`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR) AS `wikipedia`,
  `awards_categories`.`count_movies` AS `movieCount`,
  `awards_categories`.`count_series` AS `seriesCount`,
  `awards_categories`.`count_persons` AS `personCount`,
  `awards_categories`.`count_companies` AS `companyCount`
FROM `awards_categories`
WHERE `awards_categories`.`id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->award,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->name,
        $this->firstYear,
        $this->lastYear,
        $this->description,
        $this->wikipedia,
        $this->movieCount,
        $this->seriesCount,
        $this->personCount,
        $this->companyCount
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Award {$id}");
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
    $this->award          = new Award($this->diContainer, $this->award);
    $this->firstYear      && ($this->firstYear = new Date($this->firstYear));
    $this->lastYear       && ($this->lastYear  = new Date($this->lastYear));
    $this->pluralKey      = "categories";
    $this->singularKey    = "category";
    $this->tableName      = "awards_categories";
    $this->routeKey       = "/award/{0}/category/{1}";
    $this->routeArgs      = [ $this->award->id, $this->id ];
    $this->routeIndex     = $this->intl->r("/award/{0}/categories", $this->award->id);
    return parent::init();
  }

}
