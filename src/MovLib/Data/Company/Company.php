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

use \MovLib\Component\Date;
use \MovLib\Data\Movie\MovieSet;
use \MovLib\Data\Series\SeriesSet;
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
final class Company extends \MovLib\Data\Image\AbstractImageEntity {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Company";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The entity type used to store revisions.
   *
   * @var int
   */
  const REVISION_ENTITY_TYPE = 5;


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
   * @var null|\MovLib\Component\Date
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
   * @var null|\MovLib\Component\Date
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
   * {@inheritdoc}
   */
  public $pluralKey = "companies";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "company";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new company object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The company's unique identifier to instantiate, defaults to <code>NULL</code> (no company will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null) {
    parent::__construct($container);
    if ($id) {
      $stmt = Database::getConnection()->prepare(<<<SQL
SELECT
  `companies`.`id` AS `id`,
  `companies`.`changed` AS `changed`,
  `companies`.`created` AS `created`,
  `companies`.`deleted` AS `deleted`,
  `companies`.`aliases` AS `aliases`,
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
        $this->aliases,
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
   * Update the genre.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function commit() {
    $this->aliases = empty($this->aliases)? serialize([]) : serialize(explode("\n", $this->aliases));
    $this->links   = empty($this->links)? serialize([]) : serialize(explode("\n", $this->links));

    $stmt = Database::getConnection()->prepare(<<<SQL
UPDATE `companies` SET
  `aliases`          = ?,
  `defunct_date`     = ?,
  `dyn_descriptions` = COLUMN_ADD(`dyn_descriptions`, '{$this->intl->languageCode}', ?),
  `dyn_wikipedia`    = COLUMN_ADD(`dyn_wikipedia`, '{$this->intl->languageCode}', ?),
  `founding_date`    = ?,
  `name`             = ?,
  `links`            = ?
WHERE `id` = {$this->id}
SQL
    );
    $stmt->bind_param(
      "sssssss",
      $this->aliases,
      $this->defunctDate,
      $this->description,
      $this->wikipedia,
      $this->foundingDate,
      $this->name,
      $this->links
    );
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * Create a new company.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function create() {
    $this->aliases = empty($this->aliases)? serialize([]) : serialize(explode("\n", $this->aliases));
    $this->links   = empty($this->links)? serialize([]) : serialize(explode("\n", $this->links));

    $mysqli = Database::getConnection();
    $stmt = $mysqli->prepare(<<<SQL
INSERT INTO `companies` (
  `aliases`,
  `defunct_date`,
  `dyn_descriptions`,
  `dyn_image_descriptions`,
  `dyn_wikipedia`,
  `founding_date`,
  `name`,
  `links`
) VALUES (
  ?,
  ?,
  COLUMN_CREATE('{$this->intl->languageCode}', ?),
  '',
  COLUMN_CREATE('{$this->intl->languageCode}', ?),
  ?,
  ?,
  ?
);
SQL
    );
    $stmt->bind_param(
      "sssssss",
      $this->aliases,
      $this->defunctDate,
      $this->description,
      $this->wikipedia,
      $this->foundingDate,
      $this->name,
      $this->links
    );

    $stmt->execute();
    $this->id = $stmt->insert_id;

    return $this->init();
  }

  /**
   * Get all movies related to this company.
   *
   * @param integer $offset [optional]
   *   The offset, usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @param integer $limit [optional]
   *   The limit (row count), usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   *
   * @return \MovLib\Data\Movie\MovieSet
   */
  public function getMovies($offset = null, $limit = null) {
    $movieSet = new MovieSet($this->container);
    $result   = Database::getConnection()->query(<<<SQL
SELECT `movies`.`id` FROM `movies`
  INNER JOIN `movies_crew` ON `movies_crew`.`movie_id` = `movies`.`id` AND `movies_crew`.`company_id` = {$this->id}
WHERE `movies`.`deleted` = false
LIMIT {$limit}
OFFSET {$offset}
SQL
    );
    $movieIds = [];
    while ($entity = $result->fetch_assoc()) {
      $movieIds[] = $entity["id"];
    }
    $result->free();
    if(!empty($movieIds)) {
      $movieSet->loadIdentifiers($movieIds);
    }

    return $movieSet;
  }

  /**
   * Get the total amount of movies related to a company.
   */
  public function getMovieTotalCount() {
    return (integer) Database::getConnection()->query(<<<SQL
SELECT count(*) FROM `movies`
  INNER JOIN `movies_crew` ON `movies_crew`.`movie_id` = `movies`.`id` AND `movies_crew`.`company_id` = {$this->id}
WHERE `movies`.`deleted` = false
LIMIT 1
SQL
    )->fetch_all()[0][0];
  }

  /**
   * Get all series related to this company.
   *
   * @param integer $offset [optional]
   *   The offset, usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @param integer $limit [optional]
   *   The limit (row count), usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   *
   * @return \MovLib\Data\Series\SeriesSet
   */
  public function getSeries($offset = null, $limit = null) {
    $seriesSet = new SeriesSet($this->container);
    $result   = Database::getConnection()->query(<<<SQL
SELECT DISTINCT `series`.`id` FROM `series`
  INNER JOIN `episodes_crew` ON `episodes_crew`.`series_id` = `series`.`id` AND `episodes_crew`.`company_id` = {$this->id}
WHERE `series`.`deleted` = false
LIMIT {$limit}
OFFSET {$offset}
SQL
    );
    $movieIds = [];
    while ($entity = $result->fetch_assoc()) {
      $movieIds[] = $entity["id"];
    }
    $result->free();
    if(!empty($movieIds)) {
      $seriesSet->loadIdentifiers($movieIds);
    }

    return $seriesSet;
  }

  /**
   * Get the total amount of series related to a company.
   */
  public function getSeriesTotalCount() {
    return (integer) Database::getConnection()->query(<<<SQL
SELECT count(DISTINCT `episodes_crew`.`series_id`) FROM `series`
  INNER JOIN `episodes_crew` ON `episodes_crew`.`series_id` = `series`.`id` AND `episodes_crew`.`company_id` = {$this->id}
WHERE `series`.`deleted` = false
LIMIT 1
SQL
    )->fetch_all()[0][0];
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->aliases      && ($this->aliases      = unserialize($this->aliases));
    $this->links        && ($this->links        = unserialize($this->links));
    $this->foundingDate && ($this->foundingDate = new Date($this->foundingDate));
    $this->defunctDate  && ($this->defunctDate  = new Date($this->defunctDate));
    $this->placeId      && ($this->place        = new Place($this->container, $this->placeId));
    $this->imageAlternativeText = $this->intl->t("{company_name} logo.", $this->name);
    $this->imageDirectory       = "upload://company";
    return parent::init();
  }

  /**
   * {@inheritdoc}
   */
  protected function imageSaveStyles() {

  }

}
