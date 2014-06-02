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
 * Defines the revision entity object for award category entities.
 *
 * @property \MovLib\Data\Award\Category $entity
 *
 * @author Richard Fussenegger <richard@fussengger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CategoryRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "CategoryRevision";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * {@inheritdoc}
   */
  public static $originatorClassId = 7;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award category’s award id.
   *
   * @var integer
   */
  public $awardId;

  /**
   * Associative array containing all the award category's localized descriptions, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $descriptions;

  /**
   * The award category's first year.
   *
   * @var integer
   */
  public $firstYear;

  /**
   * The award category's last year.
   *
   * @var null|integer
   */
  public $lastYear;

  /**
   * Associative array containing all the award category's localized names, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $names;

  /**
   * Associative array containing all the award category's localized wikipedia links, keyed by language code.
   *
   * @var array
   */
  public $wikipediaLinks;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award category revision.
   *
   * @param integer $id
   *   The award category's unique identifier to load the revision for. The default value (<code>NULL</code>) is only used for
   *   internal purposes when loaded via <code>fetch_object()</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no award category was found for the given unique identifier.
   */
  public function __construct($id = null) {
    $connection = Database::getConnection();
    if ($id) {
      $stmt = $connection->prepare(<<<SQL
SELECT
  `awards_categories`.`id`,
  `revisions`.`user_id`,
  `awards_categories`.`changed` + 0,
  `awards_categories`.`deleted`,
  `awards_categories`.`award_id`,
  `awards_categories`.`first_year`,
  `awards_categories`.`last_year`,
  COLUMN_JSON(`awards_categories`.`dyn_descriptions`),
  COLUMN_JSON(`awards_categories`.`dyn_names`),
  COLUMN_JSON(`awards_categories`.`dyn_wikipedia`)
FROM `awards_categories`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `awards_categories`.`id`
    AND `revisions`.`id` = `awards_categories`.`changed`
    AND `revisions`.`revision_entity_id` = {$this::$originatorClassId}
WHERE `awards_categories`.`id` = ?
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
        $this->awardId,
        $this->firstYear,
        $this->lastYear,
        $this->descriptions,
        $this->names,
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
      $connection->dynamicDecode($this->names);
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
        "awardId",
        "firstYear",
        "lastYear",
        "descriptions",
        "names",
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
      ->setDynamicConditional("names", $languageCode, $this->names, $oldRevision->names)
      ->setDynamicConditional("wikipedia", $languageCode, $this->wikipediaLinks, $oldRevision->wikipediaLinks)
      ->setConditional("award_id", $this->awardId, $oldRevision->awardId)
      ->setConditional("first_year", $this->firstYear, $oldRevision->firstYear)
      ->setConditional("last_year", $this->lastYear, $oldRevision->lastYear)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert) {
    return $insert
      ->setDynamic("descriptions", $this->descriptions)
      ->setDynamic("names", $this->names)
      ->setDynamic("wikipedia", $this->wikipediaLinks)
      ->set("award_id", $this->awardId)
      ->set("first_year", $this->firstYear)
      ->set("last_year", $this->lastYear)
    ;
  }

}
