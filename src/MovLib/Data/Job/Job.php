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

use \MovLib\Data\Company\CompanySet;
use \MovLib\Data\Job\JobRevision;
use \MovLib\Data\Person\PersonSet;
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
class Job extends \MovLib\Data\AbstractEntity implements \MovLib\Data\Revision\EntityRevisionInterface {


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
   * The job's gender specific titles in default language.
   *
   * The Keys are {@see \MovLib\Partial\Sex} class constants.
   *
   * @var array
   */
  public $defaultTitles = [
    Sex::UNKNOWN => null,
    Sex::MALE    => null,
    Sex::FEMALE  => null,
  ];

  /**
   * The job's translated unisex title.
   *
   * @var string
   */
  public $title;

  /**
   * The job's translated and gender specific titles.
   *
   * The Keys are {@see \MovLib\Partial\Sex} class constants.
   *
   * @var array
   */
  public $titles = [
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
  `id`,
  `changed`,
  `created`,
  `deleted`,
  COLUMN_GET(`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR),
  IFNULL(
    COLUMN_GET(`dyn_titles_sex0`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`dyn_titles_sex0`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ),
  IFNULL(
    COLUMN_GET(`dyn_titles_sex1`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`dyn_titles_sex1`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ),
  IFNULL(
    COLUMN_GET(`dyn_titles_sex2`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`dyn_titles_sex2`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ),
  COLUMN_GET(`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR),
  `count_companies` AS `companyCount`,
  `count_persons` AS `personCount`
FROM `jobs`
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
        $this->deleted,
        $this->description,
        $this->titles[Sex::UNKNOWN],
        $this->titles[Sex::MALE],
        $this->titles[Sex::FEMALE],
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
  public function init() {
    $this->titles[Sex::UNKNOWN] && $this->title = $this->titles[Sex::UNKNOWN];
    return parent::init();
  }

  /**
   * Get all companies related to this job.
   *
   * @param integer $offset [optional]
   *   The offset, usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @param integer $limit [optional]
   *   The limit (row count), usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   *
   * @return \MovLib\Data\Company\CompanySet
   */
  public function getCompanies($offset = null, $limit = null) {
    $companySet = new CompanySet($this->diContainer);
    $result     = $this->getMySQLi()->query(<<<SQL
(
SELECT `companies`.`id` FROM `companies`
  INNER JOIN `movies_crew` ON `movies_crew`.`company_id` = `companies`.`id` AND `movies_crew`.`job_id` = {$this->id}
  WHERE `companies`.`deleted` = false
) UNION ALL (
SELECT `companies`.`id` FROM `companies`
  INNER JOIN `episodes_crew` ON `episodes_crew`.`company_id` = `companies`.`id` AND `episodes_crew`.`job_id` = {$this->id}
  WHERE `companies`.`deleted` = false
 )
LIMIT {$limit}
OFFSET {$offset}
SQL
    );
    $companyIds = [];
    while ($entity = $result->fetch_assoc()) {
      $companyIds[] = $entity["id"];
    }
    $result->free();
    if(!empty($companyIds)) {
      $companySet->loadIdentifiers($companyIds);
    }

    return $companySet;
  }

  /**
   * Get the total amount of companies related to a job.
   */
  public function getCompanyTotalCount() {
    return (integer) $this->getMySQLi()->query(<<<SQL
SELECT count(*) FROM `companies`
  INNER JOIN `movies_crew` ON `movies_crew`.`company_id` = `companies`.`id` AND `movies_crew`.`job_id` = {$this->id}
  WHERE `companies`.`deleted` = false
UNION
SELECT count(*) FROM `companies`
  INNER JOIN `episodes_crew` ON `episodes_crew`.`company_id` = `companies`.`id` AND `episodes_crew`.`job_id` = {$this->id}
  WHERE `companies`.`deleted` = false
LIMIT 1
SQL
    )->fetch_all()[0][0];
  }

  /**
   * Get the job's persons.
   *
   * @param integer $offset [optional]
   *   The offset, usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @param integer $limit [optional]
   *   The limit (row count), usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   *
   * @return \MovLib\Data\Person\PersonSet
   */
  public function getPersons($offset = null, $limit = null) {
    $personSet = new PersonSet($this->diContainer);
    $result = $this->getMySQLi()->query(<<<SQL
(
SELECT `persons`.`id` FROM `persons`
  INNER JOIN `movies_crew` ON `movies_crew`.`person_id` = `persons`.`id` AND `movies_crew`.`job_id` = {$this->id}
  WHERE `persons`.`deleted` = false
) UNION ALL (
SELECT `persons`.`id` FROM `persons`
  INNER JOIN `episodes_crew` ON `episodes_crew`.`person_id` = `persons`.`id` AND `episodes_crew`.`job_id` = {$this->id}
  WHERE `persons`.`deleted` = false
 )
LIMIT {$limit}
OFFSET {$offset}
SQL
    );
    $personIds = [];
    while ($entity = $result->fetch_assoc()) {
      $personIds[] = $entity["id"];
    }
    $result->free();
    if(!empty($personIds)) {
      $personSet->loadIdentifiers($personIds);
    }

    return $personSet;
  }

  /**
   * Get the total amount of persons related to a job.
   */
  public function getPersonTotalCount() {
    return (integer) $this->getMySQLi()->query(<<<SQL
SELECT count(*) FROM `persons`
  INNER JOIN `movies_crew` ON `movies_crew`.`person_id` = `persons`.`id` AND `movies_crew`.`job_id` = {$this->id}
  WHERE `persons`.`deleted` = false
UNION
SELECT count(*) FROM `persons`
  INNER JOIN `episodes_crew` ON `episodes_crew`.`person_id` = `persons`.`id` AND `episodes_crew`.`job_id` = {$this->id}
  WHERE `persons`.`deleted` = false
LIMIT 1
SQL
    )->fetch_all()[0][0];
  }

  /**
   * {@inheritdoc}
   */
  public function createRevision($userId, $dateTime) {
    // Now we can create the new revision of ourself. Note that our id property is NULL if we're a new job, so we don't
    // have to take care of any not found exceptions that might occur while creating the revision.
    $revision                                            = new JobRevision($this->id);
    $revision->id                                        = $dateTime->formatInteger();
    $revision->created                                   = $dateTime;
    $revision->deleted                                   = $this->deleted;
    $revision->descriptions[$this->intl->languageCode]   = $this->description;
    $revision->titlesSex0[$this->intl->languageCode]     = $this->titles[Sex::UNKNOWN];
    $revision->titlesSex1[$this->intl->languageCode]     = $this->titles[Sex::MALE];
    $revision->titlesSex2[$this->intl->languageCode]     = $this->titles[Sex::FEMALE];
    $revision->userId                                    = $userId;
    $revision->wikipediaLinks[$this->intl->languageCode] = $this->wikipedia;

    // Don't forget that we might be a new job and that we might have been created via a different system locale than
    // the default one, in which case the user was required to enter a default title. Of course we have to export that
    // as well to our revision.
    if (isset($this->defaultTitles[Sex::UNKNOWN])) {
      $revision->titlesSex0[$this->intl->defaultLanguageCode] = $this->defaultTitles[Sex::UNKNOWN];
    }
    if (isset($this->defaultTitles[Sex::MALE])) {
      $revision->titlesSex1[$this->intl->defaultLanguageCode] = $this->defaultTitles[Sex::MALE];
    }
    if (isset($this->defaultTitles[Sex::FEMALE])) {
      $revision->titlesSex2[$this->intl->defaultLanguageCode] = $this->defaultTitles[Sex::FEMALE];
    }

    return $revision;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevision(\MovLib\Data\Revision\RevisionEntityInterface $revisionEntity, $languageCode, $defaultLanguageCode) {
    $this->changed = $revisionEntity->created;
    $this->deleted = $revisionEntity->deleted;
    isset($revisionEntity->descriptions[$languageCode])   && ($this->description = $revisionEntity->descriptions[$languageCode]);
    isset($revisionEntity->wikipediaLinks[$languageCode]) && ($this->wikipedia = $revisionEntity->wikipediaLinks[$languageCode]);
    if (isset($revisionEntity->titlesSex0[$languageCode])) {
      $this->title = $revisionEntity->titlesSex0[$languageCode];
    }
    else {
      $this->title = $revisionEntity->titlesSex0[$defaultLanguageCode];
    }
    return $this;
  }

  public function getRevision() {
    
  }

}
