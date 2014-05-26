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
namespace MovLib\Core\Revision;

use \MovLib\Core\Database\Insert;
use \MovLib\Component\DateTime;

/**
 * Defines the base object for revisioned database entities.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractRevisionEntity implements RevisionEntityInterface {


  //-------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The revision entity's creation date and time.
   *
   * @var \MovLib\Component\DateTime
   */
  public $created;

  /**
   * The revision entity's deletion state.
   *
   * @var boolean
   */
  public $deleted = false;

  /**
   * The revision entity's unique entity identifier.
   *
   * @var integer
   */
  public $entityId;

  /**
   * The revision entity's identifier.
   *
   * <b>NOTE</b><br>
   * Only unique together with <var>AbstractRevisionEntity::$entityId</var> and <var>AbstractRevisionEntity::ENTITY_ID</var>
   * when selecting an entity's revisions from the revisions table in the database.
   *
   * @var integer
   */
  public $id;

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
  public $revisionEntityId;

  /**
   * The revision entity's user who created this revision.
   *
   * <b>NOTE</b><br>
   * This property is only set if this entity was instantiated via a {@see RevisionSet} for presentation purposes.
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
    // @devStart
    // @codeCoverageIgnoreStart
    // The fact that a class has to include both, the constant and the property, might be annoying while implementing
    // but is very good for performance. Any class that has to work with the revision entity can either access the
    // constant without having an instance or the property for easy embedding within strings.
    //
    // NOTE for the future: We can dump this as soon as accessors are available.
    assert(defined("static::REVISION_ENTITY_ID"), "You have to set the REVISION_ENTITY_ID in your class.");
    assert(isset($this->revisionEntityId), "You have to set the \$revisionEntityId property in your class.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    if ($this->id) {
      $this->entityId       = (integer) $this->entityId;
      $this->id             = (integer) $this->id;
      $this->created        = new DateTime($this->id);
      $this->deleted        = (boolean) $this->deleted;
      $this->userId         = (integer) $this->userId;
      $this->wikipediaLinks = json_decode($this->wikipediaLinks, true);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    static $properties = [ "deleted", "entityId", "id", "userId" ];
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function __wakeup() {
    $this->created = new DateTime($this->id);
  }


  //-------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   *
   */
  abstract protected function getCommitQuery(\MovLib\Core\Database\Connection $connection);

  /**
   * Set the table and add additional columns for the inital commit.
   *
   * @param \MovLib\Core\Database\Insert $insert
   *   The insert statement to set the table and add additional columns.
   * @return \MovLib\Core\Database\Insert
   *   The final insert statement ready for execution.
   */
  abstract protected function addInitialCommitColumns(\MovLib\Core\Database\Insert $insert);


  //-------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  final public function commit(\MovLib\Core\Database\Connection $connection, $oldRevisionId) {
    return $this->addCommitColumns((new Update($connection))
      ->dateTimeField("changed", $this->changed)
      ->dynamicField("wikipedia", $this->wikipediaLinks)
    );
  }

  /**
   * {@inheritdoc}
   */
  final public function initialCommit(\MovLib\Core\Database\Connection $connection) {
    // Insert the entity itself first.
    $this->entityId = $this->addInitialCommitColumns((new Insert($connection))
      ->dateTimeField("created", $this->created)
      ->dateTimeField("changed", $this->changed)
      ->dynamicField("wikipedia", $this->wikipediaLinks)
    )->execute();

    // Create entry in the revisions table, otherwise we aren't able to list initial commits in the history nor on the
    // user's contribution page.
    (new Insert($connection))
      ->table("revisions")
      ->columnDateTime("id", $this->created)
      ->numberField("entity_id", $this->entityId)
      ->numberField("revision_entity_id", static::REVISION_ENTITY_ID)
      ->numberField("user_id", $this->userId)
      ->execute()
    ;

    // We have to update the user's edit count.
    (new Update($connection))
      ->table("users")
      ->increment("edits")
      ->where("id", $this->userId)
    ;

    return $this->entityId;
  }

}