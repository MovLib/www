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
namespace MovLib\Data\Event;

use \MovLib\Core\Database\Database;
use \MovLib\Core\Revision\OriginatorTrait;
use \MovLib\Core\Search\RevisionTrait;
use \MovLib\Component\Date;
use \MovLib\Data\Place\Place;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the event entity object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Event extends \MovLib\Data\AbstractEntity implements \MovLib\Core\Revision\OriginatorInterface {
  use OriginatorTrait, RevisionTrait {
    RevisionTrait::postCommit insteadof OriginatorTrait;
    RevisionTrait::postCreate insteadof OriginatorTrait;
  }


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Event";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award’s aliases.
   *
   * @var array
   */
  public $aliases = [];

  /**
   * The award this event belongs to.
   *
   * @var mixed
   */
  public $award;

  /**
   * The event's award categories.
   *
   * @var \MovLib\Data\Award\CategorySet
   */
  public $categories;

  /**
   * The count of companies connected to this event.
   *
   * @var integer
   */
  public $companyCount;

  /**
   * The event's description in the current display language.
   *
   * @var string
   */
  public $description;

  /**
   * The event’s end date.
   *
   * @var string
   */
  public $endDate;

  /**
   * The event’s weblinks.
   *
   * @var array
   */
  public $links = [];

  /**
   * The event's name.
   *
   * @var string
   */
  public $name;

  /**
   * The count of movies connected to this event.
   *
   * @var integer
   */
  public $movieCount;

  /**
   * The count of persons connected to this event.
   *
   * @var integer
   */
  public $personCount;

  /**
   * The event’s place.
   *
   * @var integer|object
   */
  public $place;

  /**
   * The count of series connected to this event.
   *
   * @var integer
   */
  public $seriesCount;

  /**
   * The event’s start date.
   *
   * @var string
   */
  public $startDate;

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "events";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "event";


  // ------------------------------------------------------------------------------------------------------------------- Initialize


  /**
   * Instantiate new event object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The event's unique identifier to instantiate, defaults to <code>NULL</code> (no event will be loaded).
   * @param array $values [optional]
   *   An array of values to set, keyed by property name, defaults to <code>NULL</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null, array $values = null) {
    $this->lemma =& $this->name;
    parent::__construct($container, $values);
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
SELECT
  `aliases`,
  `award_id`,
  `changed`,
  `created`,
  `deleted`,
   COLUMN_GET(`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR),
  `end_date`,
  `id`,
  `links`,
  `name`,
  `place_id`,
  `start_date`,
  COLUMN_GET(`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR),
  `count_movies`,
  `count_series`,
  `count_persons`,
  `count_companies`
FROM `events`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->aliases,
        $this->award,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->description,
        $this->endDate,
        $this->id,
        $this->links,
        $this->name,
        $this->place,
        $this->startDate,
        $this->wikipedia,
        $this->movieCount,
        $this->seriesCount,
        $this->personCount,
        $this->companyCount
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Event {$id}");
      }
    }
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
   * @param \MovLib\Data\Event\EventRevision $revision {@inheritdoc}
   * @return \MovLib\Data\Event\EventRevision {@inheritdoc}
   */
  protected function doCreateRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->setRevisionArrayValue($revision->descriptions, $this->description);
    $revision->aliases   = $this->aliases;
    $revision->award     = $this->award;
    $revision->endDate   = $this->endDate;
    $revision->links     = $this->links;
    $revision->name      = $this->name;
    $revision->place     = $this->place;
    $revision->startDate = $this->startDate;

    return $revision;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Event\EventRevision $revision {@inheritdoc}
   * @return this {@inheritdoc}
   */
  protected function doSetRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->description = $this->getRevisionArrayValue($revision->descriptions);
    $revision->aliases   && $this->aliases   = $revision->aliases;
    $revision->award     && $this->award     = $revision->award;
    $revision->endDate   && $this->endDate   = $revision->endDate;
    $revision->links     && $this->links     = $revision->links;
    $revision->name      && $this->name      = $revision->name;
    $revision->place     && $this->place     = $revision->place;
    $revision->startDate && $this->startDate = $revision->startDate;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function init(array $values = null) {
    parent::init($values);
    $this->place     && $this->place = new Place($this->container, $this->place);
    if (isset($this->aliases) && !is_array($this->aliases)) {
      $this->aliases = unserialize($this->aliases);
    }
    if (isset($this->links) && !is_array($this->links)) {
      $this->links = unserialize($this->links);
    }
    $this->startDate && ($this->startDate = new Date($this->startDate));
    $this->endDate   && ($this->endDate = new Date($this->endDate));
    return $this;
  }

  /**
   * Update the award's first and last event year.
   *
   * @return this
   */
  private function updateAwardYears() {
    $stmt = Database::getConnection()->prepare(<<<SQL
UPDATE `awards` SET
  `first_event_year` = (SELECT YEAR(MIN(`start_date`)) FROM `events` WHERE `award_id` = ?),
  `last_event_year`  = IFNULL(
    (SELECT YEAR(MAX(`end_date`)) FROM `events` WHERE `award_id` = ?),
    (SELECT YEAR(MAX(`start_date`)) FROM `events` WHERE `award_id` = ?)
   )
WHERE `awards`.`id` = ?
SQL
    );
    $stmt->bind_param(
      "dddd",
      $this->award->id,
      $this->award->id,
      $this->award->id,
      $this->award->id
    );
    $stmt->execute();
    $stmt->close();

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function lemma($locale) {
    return $this->name;
  }

}
