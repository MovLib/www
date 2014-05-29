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
namespace MovLib\Data\Person;

use \MovLib\Component\Date;
use \MovLib\Core\Database\Database;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision entity object for person entities.
 *
 * @property \MovLib\Data\Person\Person $entity
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PersonRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "PersonRevision";
  // @codingStandardsIgnoreEnd

  /**
   * The revision entity's unique identifier.
   *
   * @var integer
   */
  const REVISION_ENTITY_ID = 4;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person's aliases.
   *
   * @var array
   */
  public $aliases;

  /**
   * The awards associated with this person.
   *
   * @var array
   */
  public $awards;

  /**
   * Associative array containing all the person's localized biographies, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $biographies = [];

  /**
   * The person's date of birth in <code>"Y-m-d"</code> format.
   *
   * @var \MovLib\Component\Date
   */
  public $birthDate;

  /**
   * The person's place of birth's identifier.
   *
   * @var null|integer
   */
  public $birthPlaceId;

  /**
   * The person's birth name.
   *
   * @var string
   */
  public $bornName;

  /**
   * The person's cause of death's identifier.
   *
   * @var integer
   */
  public $causeOfDeathId;

  /**
   * The person's date of death in <code>"Y-m-d"</code> format.
   *
   * @var \MovLib\Component\Date
   */
  public $deathDate;

  /**
   * The person's place of death's identifier.
   *
   * @var null|integer
   */
  public $deathPlaceId;

  /**
   * Associative array containing all the person's localized image descriptions, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $imageDescriptions = [];

  /**
   * The person’s weblinks.
   *
   * @var array
   */
  public $links;

  /**
   * The person's name.
   *
   * @var string
   */
  public $name;

  /**
   * {@inheritdoc}
   */
  public $revisionEntityId = 4;

  /**
   * The person's sex.
   *
   * @var integer
   */
  public $sex;

  /**
   * {@inheritdoc}
   */
  protected $tableName = "persons";

  /**
   * Associative array containing all the genre's localized wikipedia links, keyed by language code.
   *
   * @var array
   */
  public $wikipediaLinks = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person revision.
   *
   * @param integer $id
   *   The person's unique identifier to load the revision for. The default value (<code>NULL</code>) is only used for
   *   internal purposes when loaded via <code>fetch_object()</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no person was found for the given unique identifier.
   */
  public function __construct($id = null) {
    if ($id) {
      $connection = Database::getConnection();
      // Fetch basic person data.
      $stmt = $connection->prepare(<<<SQL
SELECT
  `persons`.`id`,
  `revisions`.`user_id`,
  `persons`.`changed` + 0,
  `persons`.`deleted`,
  COLUMN_JSON(`persons`.`dyn_biographies`),
  COLUMN_JSON(`persons`.`dyn_wikipedia`),
  COLUMN_JSON(`persons`.`dyn_image_descriptions`),
  `persons`.`name`,
  `persons`.`sex`,
  `persons`.`birthdate`,
  `persons`.`birthplace_id`,
  `persons`.`born_name`,
  `persons`.`cause_of_death_id`,
  `persons`.`deathdate`,
  `persons`.`deathplace_id`,
  `persons`.`links`
FROM `persons`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `persons`.`id`
    AND `revisions`.`id` = `persons`.`changed`
    AND `revisions`.`revision_entity_id` = ?
WHERE `persons`.`id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("id", $this->revisionEntityId, $id);
      $stmt->bind_result(
        $this->entityId,
        $this->userId,
        $this->id,
        $this->deleted,
        $this->biographies,
        $this->wikipediaLinks,
        $this->imageDescriptions,
        $this->name,
        $this->sex,
        $this->birthDate,
        $this->birthPlaceId,
        $this->bornName,
        $this->causeOfDeathId,
        $this->deathDate,
        $this->deathPlaceId,
        $this->links
      );
      $stmt->execute();
      $found = $stmt->fetch();
      $stmt->close();

      if ($found === false) {
        throw new NotFoundException("Couldn't find person for {$id}.");
      }

      // Convert types, since bind_result() can't do that for us.
      $this->sex = (boolean) $this->sex;
      $this->birthDate && ($this->birthDate = new Date($this->birthDate));
      $this->deathDate && ($this->deathDate = new Date($this->deathDate));

      // Fetch the person's personal award wins and nominations.
      // Awards for movies and series will be revisioned in those revision entities.
      $result = $connection->query(<<<SQL
SELECT
  `id`,
  `award_id` AS `awardId`,
  `award_category_id` AS `awardCategoryId`,
  `event_id` AS `eventId`,
  `won`
FROM `persons_awards`
WHERE `person_id` = {$id}
SQL
      );

      while ($row = $result->fetch_object()) {
        $this->awards[$row->id] = [
          "award_id"          => $row->awardId,
          "award_category_id" => $row->awardCategorId,
          "event_id"          => $row->eventId,
          "won"               => (boolean) $row->won,
        ];
      }
      $result->free();

      // Fetch the person's aliases.
      $result = $connection->query(<<<SQL
SELECT
  `id`,
  `alias`
FROM `persons_aliases`
WHERE `person_id` = {$id}
SQL
      );

      while ($row = $result->fetch_object()) {
        $this->aliases[$row->id] = $row->alias;
      }
      $result->free();
    }
    if ($this->id) {
      $this->biographies       === (array) $this->biographies       || $this->biographies = json_decode($this->biographies, true);
      $this->imageDescriptions === (array) $this->imageDescriptions || $this->imageDescriptions = json_decode($this->imageDescriptions, true);
      $this->links             === (array) $this->links             || ($this->links = unserialize($this->links));
      $this->wikipediaLinks    === (array) $this->wikipediaLinks    || ($this->wikipediaLinks = json_decode($this->wikipediaLinks, true));

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
        "awards",
        "biographies",
        "birthDate",
        "birthPlaceId",
        "bornName",
        "deathDate",
        "deathPlaceId",
        "imageDescriptions",
        "links",
        "name",
        "sex"
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
      ->setDynamicConditional("biographies", $languageCode, $this->biographies, $oldRevision->biographies)
      ->setConditional("birthdate", $this->birthDate, $oldRevision->birthDate)
      ->setConditional("birthplace_id", $this->birthPlaceId, $oldRevision->birthPlaceId)
      ->setConditional("born_name", $this->bornName, $oldRevision->bornName)
      ->setConditional("cause_of_death_id", $this->causeOfDeathId, $oldRevision->causeOfDeathId)
      ->setConditional("deathdate", $this->deathDate, $oldRevision->deathDate)
      ->setConditional("deathplace_id", $this->deathPlaceId, $oldRevision->deathPlaceId)
      ->setDynamicConditional("image_descriptions", $languageCode, $this->imageDescriptions, $oldRevision->imageDescriptions)
      ->setConditional("links", serialize($this->links), serialize($oldRevision->links))
      ->setConditional("name", $this->name, $oldRevision->name)
      ->setConditional("sex", $this->sex, $oldRevision->sex)
      ->setDynamicConditional("wikipedia", $languageCode, $this->wikipediaLinks, $oldRevision->wikipediaLinks)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert) {
    return $insert
      ->set("biographies", $this->biographies)
      ->set("birthdate", $this->birthDate)
      ->set("birthplace_id", $this->birthPlaceId)
      ->set("born_name", $this->bornName)
      ->set("cause_of_death_id", $this->causeOfDeathId)
      ->set("deathdate", $this->deathDate)
      ->set("deathplace_id", $this->deathPlaceId)
      ->set("image_descriptions", $this->imageDescriptions)
      ->set("links", serialize($this->links))
      ->set("name", $this->name)
      ->set("sex", $this->sex)
      ->set("wikipedia", $this->wikipediaLinks)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function preCommit(\MovLib\Core\Database\Connection $connection, \MovLib\Core\Revision\RevisionInterface $oldRevision, $languageCode) {
    $oldAliases = (array) $oldRevision->aliases;
    $inserts    = null;

    foreach ((array) $this->aliases as $id => $alias) {
      $old = array_key_exists($id, $oldAliases);
      if ((!$old && ($id = "NULL")) || $oldAliases[$id] != $alias) {
        $inserts && ($inserts .= ", ");
        $inserts .= "({$id}, {$this->entityId}, '{$connection->real_escape_string($alias)}')";
      }
      if ($old) {
        unset($oldAliases[$id]);
      }
    }

    if (!empty($oldAliases)) {
      $deleteIds = implode(", ", array_keys($oldAliases));
      $connection->real_query("DELETE FROM `persons_aliases` WHERE `id` IN ({$deleteIds})");
    }

    if ($inserts) {
      $connection->real_query("INSERT INTO `persons_aliases` (`id`, `person_id`, `alias`) VALUES {$inserts} ON DUPLICATE KEY UPDATE `alias` = VALUES(`alias`)");
    }

    return parent::preCommit($connection, $oldRevision, $languageCode);
  }

}