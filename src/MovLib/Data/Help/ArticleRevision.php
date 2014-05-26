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
namespace MovLib\Data\Help;

use \MovLib\Core\Database\Database;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision entity object for help article entities.
 *
 * @property \MovLib\Data\Help\Article $entity
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ArticleRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "ArticleRevision";
  // @codingStandardsIgnoreEnd

  /**
   * The revision entity's unique identifier.
   *
   * @var integer
   */
  const REVISION_ENTITY_ID = 11;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing all the articles's localized titles, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $titles;

  /**
   * Associative array containing all the articles's localized texts, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $texts;

  /**
   * {@inheritdoc}
   */
  public $revisionEntityId = 11;

  /**
   * {@inheritdoc}
   */
  protected $tableName = "help_articles";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new help article revision.
   *
   * @param integer $id
   *   The help article's unique identifier to load the revision for. The default value (<code>NULL</code>) is only used
   *   for internal purposes when loaded via <code>fetch_object()</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no help article was found for the given unique identifier.
   */
  public function __construct($id = null) {
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
SELECT
  `help_articles`.`id`,
  `revisions`.`user_id`,
  `help_articles`.`changed` + 0,
  `help_articles`.`deleted`,
  COLUMN_JSON(`help_articles`.`dyn_texts`),
  COLUMN_JSON(`help_articles`.`dyn_titles`)
FROM `help_articles`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `help_articles`.`id`
    AND `revisions`.`id` = `help_articles`.`changed`
    AND `revisions`.`revision_entity_id` = 11
WHERE `help_articles`.`id` = ?
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
        $this->texts,
        $this->titles
      );
      $found = $stmt->fetch();
      $stmt->close();
      if ($found === false) {
        throw new NotFoundException("Couldn't find help article for {$id}.");
      }
    }
    if ($this->id) {
      $this->texts  = json_decode($this->texts, true);
      $this->titles = json_decode($this->titles, true);
      parent::__construct();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    static $properties = null;
    if (!$properties) {
      $properties = array_merge(parent::__sleep(), [ "texts", "titles" ]);
    }
    return $properties;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function getCommitQuery(\MovLib\Core\Database\Connection $connection) {
    return "";
  }

  /**
   * {@inheritdoc}
   */
  protected function addCommitFields(\MovLib\Core\Database\Update $update) {
    return $update
      ->table("help_articles")
      ->dynamicColumn("texts", $this->texts)
      ->dynamicField("titles", $this->titles)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Insert $insert) {
    return $insert
      ->table("help_articles")
      ->dynamicColumn("texts", $this->texts)
      ->dynamicField("titles", $this->titles)
    ;
  }

}
