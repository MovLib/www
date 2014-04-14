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
final class Job extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The timestamp on which this job was changed.
   *
   * @var integer
   */
  public $changed;

  /**
   * The timestamp on which this job was created.
   *
   * @var integer
   */
  public $created;

  /**
   * The job's company count.
   *
   * @var integer
   */
  public $companyCount;

  /**
   * The job's deletion state.
   *
   * @var boolean
   */
  public $deleted;

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
   */
  public $femaleName;

  /**
   * The job's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The job's translated male name.
   *
   * @var string
   */
  public $maleName;

  /**
   * The job's translated unisex name.
   *
   * @var string
   */
  public $name;

  /**
   * The job's person count.
   *
   * @var integer
   */
  public $personCount;

  /**
   * The translated route of this job.
   *
   * @var string
   */
  public $route;


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
   * {@inheritdoc}
   */
  protected function init() {
    $this->pluralKey   = "jobs";
    $this->singularKey = "job";
    return parent::init();
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
