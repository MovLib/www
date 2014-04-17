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
namespace MovLib\Data\Job;

use \MovLib\Exception\ClientException\NotFoundException;
use \MovLib\Partial\Sex;

/**
 * Defines the job entity object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Job extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties

  /**
   * The job's company count.
   *
   * @var integer
   */
  public $companyCount;

  /**
   * The job's description in the current display language.
   *
   * @var string
   */
  public $description;

  /**
   * The job's translated female name.
   *
   * @var string
   * @deprecated since version 0.0.1-dev
   */
  public $femaleName;

  /**
   * The job's translated male name.
   *
   * @var string
   * @deprecated since version 0.0.1-dev
   */
  public $maleName;

  /**
   * The job's translated unisex name.
   *
   * @var string
   * @deprecated since version 0.0.1-dev
   */
  public $name;

  /**
   * The job's translated and gender specific names.
   *
   * The Keys are {@see \MovLib\Partial\Sex} class constants.
   *
   * @var array
   */
  public $names = [
    Sex::UNKNOWN => null,
    Sex::MALE    => null,
    Sex::FEMALE  => null,
  ];

  /**
   * The job's person count.
   *
   * @var integer
   */
  public $personCount;

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "jobs";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "job";


  // ------------------------------------------------------------------------------------------------------------------- Initialize


  /**
   * Instantiate new job object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The job's unique identifier to instantiate, defaults to <code>NULL</code> (no job will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `jobs`.`changed` AS `changed`,
  `jobs`.`created` AS `created`,
  `jobs`.`deleted` AS `deleted`,
  COLUMN_GET(`jobs`.`dyn_descriptions`, ? AS CHAR) AS `description`,
  IFNULL(COLUMN_GET(`jobs`.`dyn_names_sex2`, ? AS CHAR), COLUMN_GET(`dyn_names_sex2`, '{$this->intl->defaultLanguageCode}' AS CHAR)) AS `femaleName`,
  `jobs`.`id` AS `id`,
  IFNULL(COLUMN_GET(`jobs`.`dyn_names_sex1`, ? AS CHAR), COLUMN_GET(`dyn_names_sex1`, '{$this->intl->defaultLanguageCode}' AS CHAR)) AS `maleName`,
  IFNULL(COLUMN_GET(`jobs`.`dyn_names_sex0`, ? AS CHAR), COLUMN_GET(`dyn_names_sex0`, '{$this->intl->defaultLanguageCode}' AS CHAR)) AS `name`,
  IFNULL(COLUMN_GET(`jobs`.`dyn_wikipedia`, ? AS CHAR), COLUMN_GET(`dyn_wikipedia`, '{$this->intl->defaultLanguageCode}' AS CHAR)) AS `wikipedia`,
  `jobs`.`count_companies` AS `companyCount`,
  `jobs`.`count_persons` AS `personCount`
FROM `jobs`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param(
        "sssssd",
        $this->intl->languageCode,
        $this->intl->languageCode,
        $this->intl->languageCode,
        $this->intl->languageCode,
        $this->intl->languageCode,
        $id
      );
      $stmt->execute();
      $stmt->bind_result(
        $this->changed,
        $this->created,
        $this->deleted,
        $this->description,
        $this->femaleName,
        $this->id,
        $this->maleName,
        $this->name,
        $this->wikipedia,
        $this->companyCount,
        $this->personCount
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
   * Update the job.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function commit() {
    $stmt = $this->getMySQLi()->prepare(<<<SQL
UPDATE `jobs` SET
  `dyn_descriptions` = COLUMN_ADD(`dyn_descriptions`, '{$this->intl->languageCode}', ?),
  `dyn_names_sex0`   = COLUMN_ADD(`dyn_names_sex0`, '{$this->intl->languageCode}', ?),
  `dyn_names_sex1`   = COLUMN_ADD(`dyn_names_sex1`, '{$this->intl->languageCode}', ?),
  `dyn_names_sex2`   = COLUMN_ADD(`dyn_names_sex2`, '{$this->intl->languageCode}', ?),
  `dyn_wikipedia`    = COLUMN_ADD(`dyn_wikipedia`, '{$this->intl->languageCode}', ?)
WHERE `id` = {$this->id}
SQL
    );
    $stmt->bind_param(
      "sssss",
      $this->description,
      $this->name,
      $this->maleName,
      $this->femaleName,
      $this->wikipedia
    );
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * Update the job.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function create() {
    $stmt = $this->getMySQLi()->prepare(<<<SQL
INSERT INTO `jobs` (
  `dyn_descriptions`,
  `dyn_names_sex0`,
  `dyn_names_sex1`,
  `dyn_names_sex2`,
  `dyn_wikipedia`
) VALUES (
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?),
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?),
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?),
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?),
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?)
);
SQL
    );
    $stmt->bind_param(
      "sssss",
      $this->description,
      $this->name,
      $this->maleName,
      $this->femaleName,
      $this->wikipedia
    );
    $stmt->execute();
    $this->id = $stmt->insert_id;
    $stmt->close();
    return $this->init();
  }

  /**
   * Get the mysqli result for all movies connected to this job.
   *
   * @return \mysqli_result
   *   The mysqli result for all movies connected to this job.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMoviesResult() {
    return $this->query(
      "SELECT
        `movies`.`id` AS `id`,
        `movies`.`year` AS `year`,
        IFNULL(`dt`.`title`, `ot`.`title`) AS `displayTitle`,
        IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `displayTitleLanguageCode`,
        `ot`.`title` AS `originalTitle`,
        `ot`.`language_code` AS `originalTitleLanguageCode`,
        `p`.`poster_id` AS `displayPoster`
      FROM `movies`
        LEFT JOIN `movies_crew`
          ON `movies`.`id` = `movies_crew`.`movie_id`
        LEFT JOIN `movies_display_titles` AS `mdt`
          ON `mdt`.`movie_id` = `movies`.`id`
          AND `mdt`.`language_code` = ?
        LEFT JOIN `movies_titles` AS `dt`
          ON `dt`.`id` = `mdt`.`title_id`
        LEFT JOIN `movies_original_titles` AS `mot`
          ON `mot`.`movie_id` = `movies`.`id`
        LEFT JOIN `movies_titles` AS `ot`
          ON `ot`.`id` = `mot`.`title_id`
        LEFT JOIN `display_posters` AS `p`
          ON `p`.`movie_id` = `movies`.`id`
          AND `p`.`language_code` = ?
      WHERE `movies_crew`.`job_id` = ?
      ORDER BY `displayTitle` DESC",
      "ssd",
      [ $this->intl->languageCode, $this->intl->languageCode, $this->id ]
    )->get_result();
  }

}
