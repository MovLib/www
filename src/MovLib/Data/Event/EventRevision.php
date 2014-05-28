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
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision entity object for event entities.
 *
 * @property \MovLib\Data\Event\Event $entity
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class EventRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "EventRevision";
  // @codingStandardsIgnoreEnd

  /**
   * The revision entity's unique identifier.
   *
   * @var integer
   */
  const REVISION_ENTITY_ID = 8;


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
   * Associative array containing all the event's localized descriptions, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $descriptions = [];

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
   * The event’s place.
   *
   * @var integer|object
   */
  public $place;

  /**
   * The event’s start date.
   *
   * @var string
   */
  public $startDate;

  /**
   * {@inheritdoc}
   */
  public $revisionEntityId = 8;

  /**
   * {@inheritdoc}
   */
  protected $tableName = "events";

  /**
   * Associative array containing all the event's localized wikipedia links, keyed by language code.
   *
   * @var array
   */
  public $wikipediaLinks = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new event revision.
   *
   * @param integer $id
   *   The event's unique identifier to load the revision for. The default value (<code>NULL</code>) is only used for
   *   internal purposes when loaded via <code>fetch_object()</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no event was found for the given unique identifier.
   */
  public function __construct($id = null) {
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
SELECT
  `events`.`id`,
  `revisions`.`user_id`,
  `events`.`changed` + 0,
  `events`.`deleted`,
  `events`.`aliases`,
  `events`.`award_id`,
  `events`.`end_date`,
  `events`.`links`,
  `events`.`name`,
  `events`.`place_id`,
  `events`.`start_date`,
  COLUMN_JSON(`events`.`dyn_descriptions`),
  COLUMN_JSON(`events`.`dyn_wikipedia`)
FROM `events`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `events`.`id`
    AND `revisions`.`id` = `events`.`changed`
    AND `revisions`.`revision_entity_id` = 8
WHERE `events`.`id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->entityId,
        $this->userId,
        $this->id,
        $this->deleted,
        $this->aliases,
        $this->award,
        $this->endDate,
        $this->links,
        $this->name,
        $this->place,
        $this->startDate,
        $this->descriptions,
        $this->wikipediaLinks
      );
      $found = $stmt->fetch();
      $stmt->close();
      if ($found === false) {
        throw new NotFoundException("Couldn't find event for {$id}.");
      }
    }
    if ($this->id) {
      $this->descriptions   === (array) $this->descriptions   || ($this->descriptions   = json_decode($this->descriptions, true));
      $this->wikipediaLinks === (array) $this->wikipediaLinks || ($this->wikipediaLinks = json_decode($this->wikipediaLinks, true));
      parent::__construct();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    static $properties = null;
    if (!$properties) {
      $properties = array_merge(parent::__sleep(), [
        "aliases",
        "award",
        "endDate",
        "links",
        "name",
        "place",
        "startDate",
        "descriptions",
        "wikipediaLinks"
      ]);
    }
    return $properties;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function addCommitFields(\MovLib\Core\Database\Query\Update $update, \MovLib\Core\Revision\RevisionInterface $oldRevision, $languageCode) {
    return $update
      ->setDynamicConditional("descriptions", $languageCode, $this->descriptions, $oldRevision->descriptions)
      ->setDynamicConditional("wikipedia", $languageCode, $this->wikipediaLinks, $oldRevision->wikipediaLinks)
      ->set("aliases", empty($this->aliases)? serialize([]) : serialize(explode("\n", $this->aliases)))
      ->set("award_id", $this->award->id)
      ->set("end_date", $this->endDate)
      ->set("links", empty($this->links)? serialize([]) : serialize(explode("\n", $this->links)))
      ->set("name", $this->name)
      //->set("place_id", $this->place->id)
      ->set("start_date", $this->startDate)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert) {
    return $insert
      ->set("descriptions", $this->descriptions)
      ->set("wikipedia", $this->wikipediaLinks)
      ->set("aliases", serialize($this->aliases))
      ->set("award_id", $this->award->id)
      ->set("end_date", $this->endDate)
      ->set("links", serialize($this->links))
      ->set("name", $this->name)
      //->set("place_id", $this->place->id)
      ->set("start_date", $this->startDate)
    ;
  }

}
