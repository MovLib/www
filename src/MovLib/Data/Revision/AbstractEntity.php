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
namespace MovLib\Data\Revision;

use \MovLib\Data\DateTime;

/**
 * Defines the base object for revisioned database entities.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractEntity extends \MovLib\Core\AbstractDatabase {


  //-------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The revision entity's creation date and time.
   *
   * @var \MovLib\Data\DateTime
   */
  public $created;

  /**
   * The revision entity's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The revision entity's unique entity identifier.
   *
   * @var integer
   */
  public $entityId;

  /**
   * The revision entity's type identifier.
   *
   * <b>NOTE</b><br>
   * In order to efficiently select rows from the revisions table and to ensure data integrity each entity has a unique
   * identifier. Those identifiers are managed through the database table <code>"revision_entities"</code> which allows
   * us to easily add new entity's to the revision system without ever checking the current implementation for the
   * identifier. A concrete revision entity class has to define the own unique identifier as class constant, again to
   * increase performance.
   *
   * @var integer
   */
  public $entityTypeId;

  /**
   * The revision entity's identifier.
   *
   * <b>NOTE</b><br>
   * Only unique together with <var>AbstractEntity::$entityId</var> and <var>AbstractEntity::ENTITY_ID</var> when
   * selecting an entity's revision from the revisions table in the database.
   *
   * @var integer
   */
  public $id;

  /**
   * The revision entity's user who created this revision.
   *
   * <b>NOTE</b><br>
   * This property is only set if this entity was instantiated via a {@see RevisionSet}.
   *
   * @var \MovLib\Data\User\User|null
   */
  public $user;

  /**
   * The revision entity's unique identifier of the user who created this revision.
   *
   * @var integer
   */
  public $userId;

  /**
   * Associative array containing all the genre's localized wikipedia links, keyed by language code.
   *
   * @var array
   */
  public $wikipediaLinks;


  //-------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new revision entity object.
   */
  public function __construct() {
    if ($this->id) {
      $this->created = new DateTime($this->id);
    }
  }

  /**
   * Implements <code>serialize()</code> callback.
   *
   * @return array
   *   Array containing the names of all properties that should be serialized.
   */
  public function __sleep() {
    return [ "deleted", "entityId", "id", "userId" ];
  }

  /**
   * Implements <code>unserialize()</code> callback.
   */
  public function __wakeup() {
    $this->entityTypeId = static::ENTITY_ID;
    $this->created      = new DateTime($this->id);
  }


  //-------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Update the search index with the revision.
   *
   * @return this
   */
  abstract public function indexSearch();


  //-------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Update the state of the revision with edit changes.
   *
   * @param \MovLib\Data\AbstractEntity $entity
   *   The entity with the changes.
   * @param string $languageCode
   *   The language code of the current request, this is used to determin which dynamic columns have to be updated for
   *   this revision. Remember that revisions are language independent, while the entity's aren't.
   * @param mixed $properties
   *   The properties that contain the data and the properties to which the data should be exported.
   * @return this
   */
  public function setEntity(\MovLib\Data\AbstractEntity $entity, $languageCode, &...$dynamicProperties) {
    $this->created  = $entity->changed;
    $this->deleted  = $entity->deleted;
    $this->entityId = $entity->id;
    $this->id       = $entity->changed->formatInteger();
    $this->userId   = $entity->userId;

    $c = count($dynamicProperties);
    if ($c > 0) {
      // @devStart
      // @codeCoverageIgnoreStart
      assert($c % 2 === 0, "Wrong dynamic properties count.");
      // @codeCoverageIgnoreEnd
      // @devEnd
      for ($i = 0, $j = 1; $i < $c; $i += 2, $j += 2) {
        if (empty($dynamicProperties[$i])) {
          if (isset($dynamicProperties[$j][$languageCode])) {
            unset($dynamicProperties[$j][$languageCode]);
          }
        }
        else {
          $dynamicProperties[$j][$languageCode] = $dynamicProperties[$i];
        }
      }
    }

    return $this;
  }

}
