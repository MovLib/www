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

use \MovLib\Core\Database\Database;
use \MovLib\Component\Date;
use \MovLib\Core\Revision\OriginatorTrait;
use \MovLib\Core\Search\RevisionTrait;
use \MovLib\Exception\ClientException\NotFoundException;
use \MovLib\Data\Movie\MovieSet;
use \MovLib\Data\Series\SeriesSet;
use \MovLib\Data\Place\Place;

/**
 * Defines the company entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Company extends \MovLib\Data\AbstractEntity implements \MovLib\Core\Revision\OriginatorInterface {
  use OriginatorTrait, RevisionTrait {
    RevisionTrait::postCommit insteadof OriginatorTrait;
    RevisionTrait::postCreate insteadof OriginatorTrait;
  }


  //-------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Company";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company's aliases.
   *
   * @var null|array
   */
  public $aliases;

  /**
   * The company's total release count.
   *
   * @var null|integer
   */
  public $awardCount;

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
   * The company's translated image descriptions.
   *
   * @var null|string
   */
  public $imageDescription;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new company object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The company's unique identifier to instantiate, defaults to <code>NULL</code> (no company will be loaded).
   * @param array $values [optional]
   *   An array of values to set, keyed by property name, defaults to <code>NULL</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null, array $values = null) {
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
SELECT
  `id`,
  `changed`,
  `created`,
  `deleted`,
  `aliases`,
  `name`,
  COLUMN_GET(`dyn_descriptions`, '{$container->intl->languageCode}' AS CHAR),
  COLUMN_GET(`dyn_image_descriptions`, '{$container->intl->languageCode}' AS CHAR),
  `links`,
  `founding_date`,
  `defunct_date`,
  `place_id`,
  COLUMN_GET(`dyn_wikipedia`, '{$container->intl->languageCode}' AS CHAR),
  `count_awards`,
  `count_movies`,
  `count_releases`,
  `count_series`
FROM `companies`
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
        $this->aliases,
        $this->name,
        $this->description,
        $this->imageDescription,
        $this->links,
        $this->foundingDate,
        $this->defunctDate,
        $this->placeId,
        $this->wikipedia,
        $this->awardCount,
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
    parent::__construct($container, $values);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function defineSearchIndex(\MovLib\Core\Search\SearchIndexer $search, \MovLib\Core\Revision\RevisionInterface $revision) {
    return $search->indexSimpleSuggestion($revision->name);
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Company\CompanyRevision $revision {@inheritdoc}
   * @return \MovLib\Data\Company\CompanyRevision {@inheritdoc}
   */
  protected function doCreateRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->setRevisionArrayValue($revision->descriptions, $this->description);
    $this->setRevisionArrayValue($revision->imageDescription, $this->imageDescription);
    $this->setRevisionArrayValue($revision->wkipediaLinks, $this->wikipedia);
    $revision->aliases      = $this->aliases;
    $revision->links        = $this->links;
    $revision->defunctDate  = $this->defunctDate;
    $revision->foundingDate = $this->foundingDate;
    $revision->name         = $this->name;
    $revision->placeId      = $this->placeId;

    return $revision;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Company\CompanyRevision $revision {@inheritdoc}
   * @return this {@inheritdoc}
   */
  protected function doSetRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->description = $this->getRevisionArrayValue($revision->descriptions);
    $this->description = $this->getRevisionArrayValue($revision->imageDescription);
    $this->wikipedia   = $this->getRevisionArrayValue($revision->wikipediaLinks);
    $revision->aliases      && $this->aliases       = $revision->aliases;
    $revision->links        && $this->links         = $revision->links;
    $revision->defunctDate  && $this->defunctDate   = $revision->defunctDate;
    $revision->foundingDate && $this->foundingDate  = $revision->foundingDate;
    $revision->name         && $this->name          = $revision->name;
    $revision->placeId      && $this->placeId       = $revision->placeId;
    return $this;
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
    if (empty($this->movieCount)) {
      $this->movieCount = (integer) Database::getConnection()->query(<<<SQL
SELECT count(*) FROM `movies`
  INNER JOIN `movies_crew` ON `movies_crew`.`movie_id` = `movies`.`id` AND `movies_crew`.`company_id` = {$this->id}
WHERE `movies`.`deleted` = false
LIMIT 1
SQL
      )->fetch_all()[0][0];
    }
    return $this->movieCount;
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
    if (empty($this->seriesCount)) {
      $this->seriesCount = (integer) Database::getConnection()->query(<<<SQL
SELECT count(DISTINCT `episodes_crew`.`series_id`) FROM `series`
  INNER JOIN `episodes_crew` ON `episodes_crew`.`series_id` = `series`.`id` AND `episodes_crew`.`company_id` = {$this->id}
WHERE `series`.`deleted` = false
LIMIT 1
SQL
      )->fetch_all()[0][0];
    }
    return $this->seriesCount;
  }

  /**
   * {@inheritdoc}
   */
  public function init(array $values = null) {
    parent::init($values);
    if (isset($this->aliases) && !is_array($this->aliases)) {
      $this->aliases = unserialize($this->aliases);
    }
    if (isset($this->links) && !is_array($this->links)) {
      $this->links = unserialize($this->links);
    }
    $this->foundingDate && ($this->foundingDate = new Date($this->foundingDate));
    $this->defunctDate  && ($this->defunctDate  = new Date($this->defunctDate));
    $this->placeId      && ($this->place        = new Place($this->container, $this->placeId));
    $this->imageAlternativeText = $this->intl->t("{company_name} logo.", $this->name);
    $this->imageDirectory       = "upload://company";
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function imageSaveStyles() {

  }

  /**
   * {@inheritdoc}
   */
  public function lemma($locale) {
    return $this->name;
  }

}
