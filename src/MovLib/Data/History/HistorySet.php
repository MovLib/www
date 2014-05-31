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
namespace MovLib\Data\History;

use \MovLib\Core\Database\Database;
use \MovLib\Data\User\UserSet;

/**
 * Defines the revision set object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class HistorySet extends \ArrayObject {
  use \MovLib\Component\ArrayObjectTrait;


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "HistorySet";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The originator's unique identifier.
   *
   * This usually is the primary key for the database record the originator is representing, e.g. the unique movie id.
   *
   * @var integer
   */
  protected $originatorId;

  /**
   * The originator's class (incl. namespace).
   *
   * @var string
   */
  protected $originatorClass;

  /**
   * The originator's unique class identifier.
   *
   * @var integer
   */
  protected $originatorClassId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new history set.
   *
   * @param string $originatorName
   *   The originator's name.
   * @param integer $originatorId
   *   The originator's unique identifier.
   * @param string $originatorNamespace [optional]
   *   The originator's namespace if it differs from <code>"\\MovLib\\Data\\{$originatorName}"</code>.
   */
  public function __construct($originatorName, $originatorId, $originatorNamespace = null) {
    if (!isset($originatorNamespace)) {
      $originatorNamespace = "\\MovLib\\Data\\{$originatorName}";
    }
    $this->originatorId      = $originatorId;
    $this->originatorClass   = $class = "{$originatorNamespace}\\{$originatorName}Revision";
    $this->originatorClassId = $class::$originatorClassId;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getTotalCount() {
    return (integer) Database::getConnection()->query(
      "SELECT COUNT(*) FROM `revisions` WHERE `revision_entity_id` = {$this->originatorClassId} AND `entity_id` = {$this->originatorId} LIMIT 1"
    )->fetch_all()[0][0];
  }

  /**
   * @todo Unify method name to work for pagination, recreate pagination interface?
   */
  public function load(\MovLib\Core\Container $container, $offset, $limit) {
    $result = Database::getConnection()->query(<<<SQL
SELECT
  `id` + 0 AS `id`,
  `user_id` AS `userId`
FROM `revisions`
WHERE `revision_entity_id` = {$this->originatorClassId}
  AND `entity_id` = {$this->originatorId}
ORDER BY `id` DESC
LIMIT {$limit} OFFSET {$offset}
SQL
    );

    $userIds = null;
    /* @var $revision \MovLib\Data\Revision\AbstractRevision */
    while ($revision = $result->fetch_object($this->originatorClass)) {
      $this[$revision->id] = $revision;
      $userIds[] = $revision->userId;
    }
    $result->free();

    if ($userIds) {
      $userSet = (new UserSet($container))->loadIdentifiers($userIds);
      foreach ($this as $key => $value) {
        $this[$key]->user = $userSet[$value->userId];
      }
    }

    return $this;
  }

}
