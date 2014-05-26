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

use \MovLib\Core\Database\Database;
use \MovLib\Component\Date;
use \MovLib\Data\Search\Search;
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
      $result = $connection->query(<<<SQL
SELECT
  `persons`.`id` AS `personId`,
  `revisions`.`user_id` AS `userId`,
  `persons`.`changed` + 0 AS `revisionId`,
  `persons`.`deleted`,
  COLUMN_JSON(`persons`.`dyn_biographies`) AS `biographies`,
  COLUMN_JSON(`persons`.`dyn_wikipedia`) AS `wikipediaLinks`,
  COLUMN_JSON(`persons`.`dyn_image_descriptions`) AS `imageDescriptions`,
  `persons`.`name`,
  `persons`.`sex`,
  `persons`.`birthdate` AS `birthDate`,
  `persons`.`birthplace_id` AS `birthPlaceId`,
  `persons`.`born_name` AS `bornName`,
  `persons`.`cause_of_death_id` AS `causeOfDeathId`,
  `persons`.`deathdate` AS `deathDate`,
  `persons`.`deathplace_id` AS `deathPlaceId`,
  `persons`.`links`,
  `persons_awards`.`id` AS `personsAwardsId`,
  `persons_awards`.`award_id` AS `personsAwardsAwardId`,
  `persons_awards`.`award_category_id` AS `personsAwardsAwardCategoryId`,
  `persons_awards`.`event_id` AS `personsAwardsEventId`,
  `persons_awards`.`won` AS `personsAwardsWon`,
  `persons_aliases`.`id` AS `personsAliasesId`,
  `persons_aliases`.`alias` AS `personsAliasesAlias`
FROM `persons`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `persons`.`id`
    AND `revisions`.`id` = `persons`.`changed`
    AND `revisions`.`revision_entity_id` = 4
  LEFT JOIN `persons_awards`
    ON `persons_awards`.`person_id` = `persons`.`id`
  LEFT JOIN `persons_aliases`
    ON `persons_aliases`.`person_id` = `persons`.`id`
WHERE `persons`.`id` = {$id}
SQL
      );

      while ($row = $result->fetch_object()) {
        if (empty($this->id)) {
          $this->entityId          = $row->personId;
          $this->userId            = $row->userId;
          $this->id                = $row->revisionId;
          $this->deleted           = $row->deleted;
          $this->biographies       = $row->biographies;
          $this->wikipediaLinks    = $row->wikipediaLinks;
          $this->imageDescriptions = $row->imageDescriptions;
          $this->name              = $row->name;
          $this->sex               = (integer) $row->sex;
          $this->birthDate         = isset($row->birthDate) ? new Date($row->birthDate) : null;
          $this->birthPlaceId      = (integer) $row->birthPlaceId;
          $this->bornName          = $row->bornName;
          $this->causeOfDeathId    = (integer) $row->causeOfDeathId;
          $this->deathDate         = isset($row->deathDate) ? new Date($row->deathDate) : null;
          $this->deathPlaceId      = (integer) $row->deathPlaceId;
          $this->links             = $row->links;
        }

        if (isset($row->personsAwardsId)) {
          $this->awards[(integer) $row->personsAwardsId] = [
            "award_id"          => (integer) $row->personsAwardsAwardId,
            "award_category_id" => (integer) $row->personsAwardsAwardCategoryId,
            "event_id"          => (integer) $row->personsAwardsEventId,
            "won"               => (boolean) $row->personsAwardsWon,
          ];
        }

        if (isset($row->personsAliasesId)) {
          $this->aliases[(integer) $row->personsAliasesId] = $row->personsAliasesAlias;
        }
      }

      if (empty($this->id)) {
        throw new NotFoundException("Couldn't find person for {$id}.");
      }
    }
    if ($this->id) {
      $this->biographies === (array) $this->biographies || $this->biographies = json_decode($this->biographies, true);
      $this->imageDescriptions === (array) $this->imageDescriptions || $this->imageDescriptions = json_decode($this->imageDescriptions, true);
      $this->links === (array) $this->links || ($this->links = unserialize($this->links));
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
  protected function addCommitFields(\MovLib\Core\Database\Update $update) {
    return $update;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Insert $insert) {
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
    ;
  }

}
