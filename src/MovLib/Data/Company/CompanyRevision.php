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
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision object for company entities.
 *
 * @property \MovLib\Data\Company\Company $entity
 *
 * @author Richard Fussenegger <richard@fussengger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CompanyRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "CompanyRevision";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * {@inheritdoc}
   */
  public static $originatorClassId = 5;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company's aliases.
   *
   * @var null|array
   */
  public $aliases;

  /**
   * The company's defunct date.
   *
   * @var null|string
   */
  public $defunctDate;

  /**
   * Associative array containing all the company's localized descriptions, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $descriptions;

  /**
   * The company's founding date.
   *
   * @var null|string
   */
  public $foundingDate;

  /**
   * Associative array containing all the company's localized image descriptions, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $imageDescriptions;

  /**
   * The company's weblinks.
   *
   * @var null|array
   */
  public $links;

  /**
   * The company's name.
   *
   * @var string
   */
  public $name;

  /**
   * The company's unique place identifier.
   *
   * @var null|integer
   */
  public $placeId;

  /**
   * Associative array containing all the company's localized wikipedia links, keyed by language code.
   *
   * @var array
   */
  public $wikipediaLinks;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new company revision.
   *
   * @param integer $id
   *   The company's unique identifier to load the revision for. The default value (<code>NULL</code>) is only used for
   *   internal purposes when loaded via <code>fetch_object()</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no company was found for the given unique identifier.
   */
  public function __construct($id = null) {
    $connection = Database::getConnection();
    if ($id) {
      $stmt = $connection->prepare(<<<SQL
SELECT
  `companies`.`id`,
  `revisions`.`user_id`,
  `companies`.`changed` + 0,
  `companies`.`deleted`,
  `companies`.`aliases`,
  `companies`.`name`,
  `companies`.`links`,
  `companies`.`founding_date`,
  `companies`.`defunct_date`,
  `companies`.`place_id`,
  COLUMN_JSON(`companies`.`dyn_descriptions`),
  COLUMN_JSON(`companies`.`dyn_image_descriptions`),
  COLUMN_JSON(`companies`.`dyn_wikipedia`)
FROM `companies`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `companies`.`id`
    AND `revisions`.`id` = `companies`.`changed`
    AND `revisions`.`revision_entity_id` = {$this::$originatorClassId}
WHERE `companies`.`id` = ?
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
        $this->name,
        $this->links,
        $this->foundingDate,
        $this->defunctDate,
        $this->placeId,
        $this->descriptions,
        $this->imageDescriptions,
        $this->wikipediaLinks
      );
      $found = $stmt->fetch();
      $stmt->close();
      if ($found === false) {
        throw new NotFoundException("Couldn't find genre for {$id}.");
      }
    }
    if ($this->id) {
      $this->aliases && ($this->aliases = unserialize($this->aliases));
      $this->links   && ($this->links   = unserialize($this->links));
      $connection->dynamicDecode($this->descriptions);
      $connection->dynamicDecode($this->imageDescriptions);
      $connection->dynamicDecode($this->wikipediaLinks);
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
        "name",
        "links",
        "foundingDate",
        "defunctDate",
        "placeId",
        "descriptions",
        "imageDescriptions",
        "wikipediaLinks"
      ]);
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function __wakeup() {
    parent::__wakeup();
    $this->aliases && ($this->aliases = unserialize($this->aliases));
    $this->links   && ($this->links   = unserialize($this->links));
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function addCommitFields(\MovLib\Core\Database\Query\Update $update, \MovLib\Core\Revision\RevisionInterface $oldRevision, $languageCode) {
    return $update
      ->setDynamicConditional("descriptions", $languageCode, $this->descriptions, $oldRevision->descriptions)
      ->setDynamicConditional("image_descriptions", $languageCode, $this->imageDescriptions, $oldRevision->imageDescriptions)
      ->setDynamicConditional("wikipedia", $languageCode, $this->wikipediaLinks, $oldRevision->wikipediaLinks)
      ->setConditional("aliases", $this->aliases, $oldRevision->aliases)
      ->setConditional("defunct_date", $this->defunctDate, $oldRevision->defunctDate)
      ->setConditional("links", $this->links, $oldRevision->links)
      ->setConditional("name", $this->name, $oldRevision->name)
      ->setConditional("place_id", $this->placeId, $oldRevision->placeId)
      ->setConditional("founding_date", $this->foundingDate, $oldRevision->foundingDate)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert) {
    return $insert
      ->setDynamic("descriptions", $this->descriptions)
      ->setDynamic("image_descriptions", $this->imageDescriptions)
      ->setDynamic("wikipedia", $this->wikipediaLinks)
      ->set("aliases", $this->aliases)
      ->set("defunct_date", $this->defunctDate)
      ->set("links", $this->links)
      ->set("name", $this->name)
      ->set("place_id", $this->placeId)
      ->set("founding_date", $this->foundingDate)
    ;
  }

}
