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

use \MovLib\Component\Date;
use \MovLib\Core\Database\Database;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision entity object for event entities.
 *
 * @property \MovLib\Data\Event\Event $entity
 *
 * @author Richard Fussenegger <richard@fussengger.info>
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


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * {@inheritdoc}
   */
  public static $originatorClassId = 8;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award’s aliases.
   *
   * @var array
   */
  public $aliases;

  /**
   * The award this event belongs to.
   *
   * @var integer
   */
  public $awardId;

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
  public $descriptions;

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
  public $links;

  /**
   * The event's name.
   *
   * @var string
   */
  public $name;

  /**
   * The event’s place identifier.
   *
   * @var integer
   */
  public $placeId;

  /**
   * The event’s start date.
   *
   * @var string
   */
  public $startDate;

  /**
   * Associative array containing all the event's localized wikipedia links, keyed by language code.
   *
   * @var array
   */
  public $wikipediaLinks;


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
    $connection = Database::getConnection();
    if ($id) {
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
    AND `revisions`.`revision_entity_id` = {$this::$originatorClassId}
WHERE `events`.`id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->originatorId,
        $this->userId,
        $this->id,
        $this->deleted,
        $this->aliases,
        $this->awardId,
        $this->endDate,
        $this->links,
        $this->name,
        $this->placeId,
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
      $this->aliases && ($this->aliases = unserialize($this->aliases));
      $this->links   && ($this->links   = unserialize($this->links));
      $connection->dynamicDecode($this->descriptions);
      $connection->dynamicDecode($this->wikipediaLinks);

      // Convert types, since bind_result() can't do that for us.
      $this->startDate && ($this->startDate = new Date($this->startDate));
      $this->endDate   && ($this->endDate   = new Date($this->endDate));
    }
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    static $properties = null;
    if (!$properties) {
      $properties = array_merge(parent::__sleep(), [
        "aliases",
        "awardId",
        "endDate",
        "links",
        "name",
        "placeId",
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
      ->setConditional("aliases", $this->aliases, $oldRevision->aliases)
      ->setConditional("award_id", $this->awardId, $this->awardId)
      ->setConditional("end_date", $this->endDate, $oldRevision->endDate)
      ->setConditional("links", $this->links, $oldRevision->links)
      ->setConditional("name", $this->name, $oldRevision->name)
      ->setConditional("place_id", $this->placeId, $oldRevision->placeId)
      ->setConditional("start_date", $this->startDate, $oldRevision->startDate)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert) {
    return $insert
      ->setDynamic("descriptions", $this->descriptions)
      ->setDynamic("wikipedia", $this->wikipediaLinks)
      ->set("aliases", $this->aliases)
      ->set("award_id", $this->awardId)
      ->set("end_date", $this->endDate)
      ->set("links", $this->links)
      ->set("name", $this->name)
      ->set("place_id", $this->placeId)
      ->set("start_date", $this->startDate)
    ;
  }

}
