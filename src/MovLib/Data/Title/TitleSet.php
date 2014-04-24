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
namespace MovLib\Data\Title;

/**
 * Defines the title set object.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class TitleSet extends \MovLib\Data\AbstractSet {

  /**
   * The entity's identifier the titles belong to.
   *
   * @var integer
   */
  protected $entityId;

  /**
   * The entity's singular key the titles belong to.
   *
   * @var string
   */
  protected $entitySingularKey;

  /**
   * The entity's plural key the titles belong to.
   *
   * @var string
   */
  protected $entityPluralKey;


  /**
   * Instantiate new title set.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $entityId
   *   The entity's identifier the titles belong to.
   */
  final public function __construct(\MovLib\Core\DIContainer $diContainer, $entityId) {
    parent::__construct($diContainer);
    $this->entityId = $entityId;
  }

  /**
   * Load the titles for an entity ordered by the titles
   *
   * @param string $entitySingularKey
   *   The entity specific singular key.
   * @param string $entityPluralKey
   *   The entity specific plural key.
   * @param null|string $where [optional]
   *   The WHERE clause.
   * @return string
   *   {@inheritdoc}
   */
  public function loadEntityTitles($where = null) {
    $where && $where = " AND {$where}";
    $result = $this->getMySQLi()->query(<<<SQL
SELECT
  `{$this->tableName}`.`id`,
  `{$this->tableName}`.`{$this->entitySingularKey}_id` AS `{$this->entitySingularKey}Id`,
  COLUMN_GET(`{$this->tableName}`.`dyn_comments`, '{$this->intl->languageCode}' AS BINARY),
  `{$this->tableName}`.`language_code` AS `languageCode`,
  `{$this->tableName}`.`title`
FROM `{$this->tableName}`
WHERE `{$this->tableName}`.`{$this->entitySingularKey}_id` = {$this->entityId}{$where}
ORDER BY `{$this->tableName}`.`title`{$this->collations[$this->intl->languageCode]} ASC
SQL
    );

    while ($title = $result->fetch_object($this->entityClassName, [ $this->diContainer ])) {
      $this->entities[$title->id] = $title;
    }
    $result->free();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractSet $set, $in) {

  }

  protected function getEntitiesQuery($where = null, $orderBy = null) {

  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($this->entitySingularKey), "You must set \$entitySingularKey in your class " . static::class);
    assert(!empty($this->entityPluralKey), "You must set \$entityPluralKey in your class " . static::class);
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->tableName = "{$this->entityPluralKey}_titles";
    return parent::init();
  }

}
