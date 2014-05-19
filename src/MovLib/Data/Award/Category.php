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

use \MovLib\Data\Award\Award;
use \MovLib\Data\Date;
use \MovLib\Data\Revision;
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
final class Category extends \MovLib\Data\AbstractEntity implements \MovLib\Data\RevisionInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The entity type used to store revisions.
   *
   * @var int
   */
  const REVISION_ENTITY_TYPE = 7;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The category's award.
   *
   * @var mixed
   */
  public $award;

  /**
   * The category's award identifier.
   *
   * @var integer
   */
  public $awardId;

  /**
   * The category's company count.
   *
   * @var null|integer
   */
  public $companyCount;

  /**
   * The category's name in our default language.
   *
   * @var null|string
   */
  public $defaultName;

  /**
   * The category's description in the current locale.
   *
   * @var null|string
   */
  public $description;

  /**
   * The category's event.
   *
   * @var mixed
   */
  public $event;

  /**
   * The category's first year.
   *
   * @var null|\MovLib\Data\Date
   */
  public $firstYear;

  /**
   * Flag determining if an award should be instanitated
   * (needed for preventing unnecessary instantiations in fetch_object()).
   *
   * @var boolean
   */
  protected $initAward;

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

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "categories";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "category";

  /**
   * {@inheritdoc}
   */
  public $tableName = "awards_categories";

  /**
   * {@inheritdoc}
   */
  public $routeKey = "/award/{0}/category/{1}";

  /**
   * {@inheritdoc}
   */
  public $routeIndexKey = "/award/{0}/category";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award category object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The category's unique identifier to instantiate, defaults to <code>NULL</code> (no category will be loaded).
   * @param mixed $initAward [optional]
   *   Whether to instantiate an award object or not, defaults to <code>TRUE</code>
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null, $initAward = true) {
    parent::__construct($diContainer);
    $this->initAward = $initAward;
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `awards_categories`.`id` AS `id`,
  `awards_categories`.`award_id` AS `awardId`,
  `awards_categories`.`changed` AS `changed`,
  `awards_categories`.`created` AS `created`,
  COLUMN_GET(`awards_categories`.`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR),
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
        $this->awardId,
        $this->changed,
        $this->created,
        $this->defaultName,
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
   * Update the award category.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function commit() {

    $stmt = $this->getMySQLi()->prepare(<<<SQL
UPDATE `awards_categories` SET
  `award_id`         = ?,
  `dyn_descriptions` = COLUMN_ADD(`dyn_descriptions`, '{$this->intl->languageCode}', ?),
  `dyn_names`        = COLUMN_ADD(`dyn_names`, '{$this->intl->languageCode}', ?),
  `dyn_wikipedia`    = COLUMN_ADD(`dyn_wikipedia`, '{$this->intl->languageCode}', ?),
  `first_year`       = ?,
  `last_year`        = ?
WHERE `id` = {$this->id}
SQL
    );
    $stmt->bind_param(
      "dsssii",
      $this->award->id,
      $this->description,
      $this->name,
      $this->wikipedia,
      $this->firstYear->year,
      $this->lastYear->year
    );
    $stmt->execute();

    return $this;
  }

  /**
   * Create new award category.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function create() {
    $mysqli = $this->getMySQLi();
    if ($this->intl->languageCode === $this->intl->defaultLanguageCode) {
      $stmt = $mysqli->prepare(<<<SQL
INSERT INTO `awards_categories` (
  `award_id`,
  `dyn_descriptions`,
  `dyn_names`,
  `dyn_wikipedia`,
  `first_year`,
  `last_year`
) VALUES (
  ?,
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?),
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?),
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?),
  ?,
  ?
);
SQL
      );
      $stmt->bind_param(
        "dsssii",
        $this->award->id,
        $this->description,
        $this->name,
        $this->wikipedia,
        $this->firstYear->year,
        $this->lastYear->year
      );
    }
    else {
      $stmt = $mysqli->prepare(<<<SQL
INSERT INTO `awards_categories` (
  `award_id`,
  `dyn_descriptions`,
  `dyn_names`,
  `dyn_wikipedia`,
  `first_year`,
  `last_year`
) VALUES (
  ?,
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?),
  COLUMN_CREATE(
    '{$this->intl->defaultLanguageCode}', ?,
    '{$this->intl->languageCode}', ?
  ),
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?),
  ?,
  ?
);
SQL
      );
      $stmt->bind_param(
        "dssssii",
        $this->award->id,
        $this->description,
        $this->defaultName,
        $this->name,
        $this->wikipedia,
        $this->firstYear->year,
        $this->lastYear->year
      );
    }

    $stmt->execute();
    $this->id = $stmt->insert_id;

    return $this->init();
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionInfo() {
    return new Revision(
      $this->name,
      $this->route,
      $this->intl->t("Award Category")
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    if ($this->initAward === true && isset($this->awardId)) {
      $this->award = new Award($this->diContainer, $this->awardId);
    }
    if (isset($this->firstYear) && !$this->firstYear instanceof \stdClass) {
      $this->firstYear = new Date($this->firstYear);
    }
    if (isset($this->lastYear) && !$this->lastYear instanceof \stdClass) {
      $this->lastYear  = new Date($this->lastYear);
    }
    $this->routeArgs     = [ $this->awardId, $this->id ];
    $this->routeIndex    = $this->intl->r($this->routeIndexKey, $this->awardId);
    $this->routeKey      = $this->intl->r($this->routeKey, $this->routeArgs);
    return parent::init();
  }

}
