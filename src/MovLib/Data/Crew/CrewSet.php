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
namespace MovLib\Data\Crew;

use \MovLib\Core\Database\Database;
use \MovLib\Data\Cast\Cast;
use \MovLib\Data\Crew\Crew;
use \MovLib\Data\Company\Company;
use \MovLib\Data\Job\Job;
use \MovLib\Data\Person\Person;
use \MovLib\Partial\Sex;

/**
 * Defines the crew set object.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CrewSet extends \MovLib\Data\AbstractEntitySet {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "CrewSet";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "crew";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "crew";

  /**
   * {@inheritdoc}
   */
  public static $tableName = "movies_crew";

  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container, null, null, null);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {

  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {

  }


  public function loadMovieCrew(\MovLib\Data\Movie\Movie $movie) {
    $castJobId = Cast::JOB_ID;
    $connection = Database::getConnection();
    $result = $connection->query(<<<SQL
SELECT
  `movies_crew`.`person_id` AS `personId`,
  `movies_crew`.`company_id` AS `companyId`,
  IFNULL(
    `persons`.`name`,
    `companies`.`name`
  ) AS `entityName`,
  `movies_crew`.`id`,
  `movies_crew`.`job_id` AS `jobId`,
  IFNULL(
    COLUMN_GET(`jobs`.`dyn_titles_sex0`, '{$this->intl->code}' AS CHAR(255)),
    COLUMN_GET(`jobs`.`dyn_titles_sex0`, '{$this->intl->defaultCode}' AS CHAR(255))
  ) AS `jobTitle`,
  `crew_alias`.`alias` AS `alias`,
  `jobs`.`changed` AS `jobChanged`,
  `jobs`.`created` AS `jobCreated`,
  `persons`.`changed` AS `personChanged`,
  `persons`.`created` AS `personCreated`,
  `companies`.`changed` AS `companyChanged`,
  `companies`.`created` AS `companyCreated`
FROM `movies_crew`
  INNER JOIN `jobs`
    ON `jobs`.`id` = `movies_crew`.`job_id`
  LEFT JOIN `persons`
    ON `persons`.`id` = `movies_crew`.`person_id`
  LEFT JOIN `persons_aliases` AS `crew_alias`
    ON `crew_alias`.`id` = `movies_crew`.`alias_id`
  LEFT JOIN `companies`
    ON `companies`.`id` = `movies_crew`.`company_id`
WHERE `movies_crew`.`movie_id` = {$movie->id}
  AND `movies_crew`.`job_id` > {$castJobId}
  AND `jobs`.`deleted` = false
  AND (`persons`.`deleted` = false OR `companies`.`deleted` = false)
ORDER BY `jobTitle`{$connection->collate($this->intl->code)} ASC,
  `entityName`{$connection->collate($this->intl->code)} ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      $row->id        = (integer) $row->id;
      $row->personId  = (integer) $row->personId;
      $row->companyId = (integer) $row->companyId;
      $row->jobId     = (integer) $row->jobId;

      if (empty($this->entities[$row->jobId])) {
        $this->entities[$row->jobId] = (object) [
          "job"     => new Job($this->container),
          "crewSet" => new CrewSet($this->container),
        ];
        $this->entities[$row->jobId]->job->id = $row->jobId;
        $this->entities[$row->jobId]->job->changed = $row->jobChanged;
        $this->entities[$row->jobId]->job->created = $row->jobCreated;
        $this->entities[$row->jobId]->job->names[Sex::UNKNOWN] = $row->jobTitle;
        $reflector = new \ReflectionMethod($this->entities[$row->jobId]->job, "init");
        $reflector->setAccessible(true);
        $reflector->invoke($this->entities[$row->jobId]->job);
      }

      if (empty($this->entities[$row->jobId]->crewSet->entities[$row->id])) {
        //$this->log->debug(print_r($row, true));
        $this->entities[$row->jobId]->crewSet->entities[$row->id]            = new Crew($this->container);
        $this->entities[$row->jobId]->crewSet->entities[$row->id]->id        = $row->id;
        $this->entities[$row->jobId]->crewSet->entities[$row->id]->movieId   = $movie->id;
        $this->entities[$row->jobId]->crewSet->entities[$row->id]->personId  = $row->personId;
        $this->entities[$row->jobId]->crewSet->entities[$row->id]->companyId = $row->companyId;
        $this->entities[$row->jobId]->crewSet->entities[$row->id]->jobId     = $row->jobId;
        $this->entities[$row->jobId]->crewSet->entities[$row->id]->alias     = $row->alias;

        if (isset($row->personId) && $row->personId !== 0) {
          $this->entities[$row->jobId]->crewSet->entities[$row->id]->person          = new Person($this->container);
          $this->entities[$row->jobId]->crewSet->entities[$row->id]->person->id      = $row->personId;
          $this->entities[$row->jobId]->crewSet->entities[$row->id]->person->name    = $row->entityName;
          $this->entities[$row->jobId]->crewSet->entities[$row->id]->person->changed = $row->personChanged;
          $this->entities[$row->jobId]->crewSet->entities[$row->id]->person->created = $row->personCreated;
          $reflector = new \ReflectionMethod($this->entities[$row->jobId]->crewSet->entities[$row->id]->person, "init");
          $reflector->setAccessible(true);
          $reflector->invoke($this->entities[$row->jobId]->crewSet->entities[$row->id]->person);
        }
        elseif (isset($row->companyId) && $row->companyId !== 0) {
          $this->entities[$row->jobId]->crewSet->entities[$row->id]->company          = new Company($this->container);
          $this->entities[$row->jobId]->crewSet->entities[$row->id]->company->id      = $row->companyId;
          $this->entities[$row->jobId]->crewSet->entities[$row->id]->company->name    = $row->entityName;
          $this->entities[$row->jobId]->crewSet->entities[$row->id]->company->changed = $row->companyChanged;
          $this->entities[$row->jobId]->crewSet->entities[$row->id]->company->created = $row->companyCreated;
          $reflector = new \ReflectionMethod($this->entities[$row->jobId]->crewSet->entities[$row->id]->company, "init");
          $reflector->setAccessible(true);
          $reflector->invoke($this->entities[$row->jobId]->crewSet->entities[$row->id]->company);
        }

        $reflector = new \ReflectionMethod($this->entities[$row->jobId]->crewSet->entities[$row->id], "init");
        $reflector->setAccessible(true);
        $reflector->invoke($this->entities[$row->jobId]->crewSet->entities[$row->id]);
      }
    }

    return $this;
  }

}
