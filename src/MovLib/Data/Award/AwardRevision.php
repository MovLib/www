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
namespace MovLib\Data\Award;

use \MovLib\Core\Database\Database;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision entity object for award entities.
 *
 * @property \MovLib\Data\Award\Award $entity
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class AwardRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AwardRevision";
  // @codingStandardsIgnoreEnd

  /**
   * The revision entity's unique identifier.
   *
   * @var integer
   */
  const REVISION_ENTITY_ID = 6;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award’s aliases.
   *
   * @var array
   */
  public $aliases = [];

  /**
   * Associative array containing all the award's localized descriptions, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $descriptions = [];

  /**
   * Associative array containing all the award's localized image descriptions, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $imageDescriptions = [];

  /**
   * The award’s weblinks.
   *
   * @var array
   */
  public $links = [];

  /**
   * The award's name.
   *
   * @var string
   */
  public $name;

  /**
   * {@inheritdoc}
   */
  public $revisionEntityId = 6;

  /**
   * {@inheritdoc}
   */
  protected $tableName = "awards";

  /**
   * Associative array containing all the award's localized wikipedia links, keyed by language code.
   *
   * @var array
   */
  public $wikipediaLinks = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award revision.
   *
   * @param integer $id
   *   The award's unique identifier to load the revision for. The default value (<code>NULL</code>) is only used for
   *   internal purposes when loaded via <code>fetch_object()</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no award was found for the given unique identifier.
   */
  public function __construct($id = null) {
    $connection = Database::getConnection();
    if ($id) {
      $stmt = $connection->prepare(<<<SQL
SELECT
  `awards`.`id`,
  `revisions`.`user_id`,
  `awards`.`changed` + 0,
  `awards`.`deleted`,
  `awards`.`aliases`,
  `awards`.`links`,
  `awards`.`name`,
  COLUMN_JSON(`awards`.`dyn_descriptions`),
  COLUMN_JSON(`awards`.`dyn_image_descriptions`),
  COLUMN_JSON(`awards`.`dyn_wikipedia`)
FROM `awards`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `awards`.`id`
    AND `revisions`.`id` = `awards`.`changed`
    AND `revisions`.`revision_entity_id` = 6
WHERE `awards`.`id` = ?
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
        $this->links,
        $this->name,
        $this->descriptions,
        $this->imageDescriptions,
        $this->wikipediaLinks
      );
      $found = $stmt->fetch();
      $stmt->close();
      if ($found === false) {
        throw new NotFoundException("Couldn't find award for {$id}.");
      }
    }
    if ($this->id) {
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
        "links",
        "name",
        "descriptions",
        "imageDescriptions",
        "wikipediaLinks",
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
      ->setDynamicConditional("image_descriptions", $languageCode, $this->imageDescriptions, $oldRevision->imageDescriptions)
      ->setDynamicConditional("wikipedia", $languageCode, $this->wikipediaLinks, $oldRevision->wikipediaLinks)
      ->set("aliases", serialize($this->aliases))
      ->set("links", serialize($this->links))
      ->set("name", $this->name)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert) {
    return $insert
      ->set("descriptions", $this->descriptions)
      ->set("image_descriptions", $this->imageDescriptions)
      ->set("wikipedia", $this->wikipediaLinks)
      ->set("aliases", serialize($this->aliases))
      ->set("links", serialize($this->links))
      ->set("name", $this->name)
    ;
  }

}
