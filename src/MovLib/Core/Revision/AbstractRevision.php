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

use \MovLib\Component\DateTime;
use \MovLib\Core\Database\Query\Insert;
use \MovLib\Core\Database\Query\Update;
use \MovLib\Core\Diff\Diff;
use \MovLib\Core\FileSystem;

/**
 * Defines the base object for revisioned database entities.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractRevision implements RevisionInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractRevision";
  // @codingStandardsIgnoreEnd


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
   * The revision entity's table name.
   *
   * @var string
   */
  protected $tableName;

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
    assert(!empty($this->revisionEntityId), "You have to set the \$revisionEntityId property in your class.");
    assert(!empty($this->tableName), "You have to set the \$tableName property in your class.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    if ($this->id) {
      $this->entityId = (integer) $this->entityId;
      $this->id       = (integer) $this->id;
      $this->created  = new DateTime($this->id);
      $this->deleted  = (boolean) $this->deleted;
      $this->userId   = (integer) $this->userId;
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
   * Add fields to the commit's update statement.
   *
   * @param \MovLib\Core\Database\Query\Update $update
   *   The update statement to add fields.
   * @param \MovLib\Core\Revision\RevisionInterface $oldRevision
   *   The old revision that is currently stored in the database for comparison.
   * @param string $languageCode
   *   The ISO 639-1 language code with which the user edited the originator. This is important for comparison of new
   *   and old values.
   * @return \MovLib\Core\Database\Query\Update
   *   The final update statement ready for execution.
   */
  abstract protected function addCommitFields(\MovLib\Core\Database\Query\Update $update, \MovLib\Core\Revision\RevisionInterface $oldRevision, $languageCode);

  /**
   * Add fields to the create's insert statement.
   *
   * @param \MovLib\Core\Database\Query\Insert $insert
   *   The insert statement to add fields.
   * @return \MovLib\Core\Database\Query\Insert
   *   The final insert statement ready for execution.
   */
  abstract protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert);


  //-------------------------------------------------------------------------------------------------------------------- Hooks


  /**
   * Hook called before the revision entity is going to be commited.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @param \MovLib\Core\Revision\RevisionInterface $oldRevision
   *   The old revision that is currently stored in the database for comparison.
   * @param string $languageCode
   *   The ISO 639-1 language code with which the user edited the originator. This is important for comparison of new
   *   and old values.
   * @return this
   */
  protected function preCommit(\MovLib\Core\Database\Connection $connection, RevisionInterface $oldRevision, $languageCode) {
    return $this;
  }

  /**
   * Hook called after the revision entity has been commited.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @param \MovLib\Core\Revision\RevisionInterface $oldRevision
   *   The old revision that was stored in the database for comparison.
   * @param string $languageCode
   *   The ISO 639-1 language code with which the user edited the originator. This is important for comparison of new
   *   and old values.
   * @return this
   */
  protected function postCommit(\MovLib\Core\Database\Connection $connection, RevisionInterface $oldRevision, $languageCode) {
    return $this;
  }

  /**
   * Hook called before the revision entity is going to be created.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @return this
   */
  protected function preCreate(\MovLib\Core\Database\Connection $connection) {
    return $this;
  }

  /**
   * Hook called after the revision entity has been created.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @return this
   */
  protected function postCreate(\MovLib\Core\Database\Connection $connection) {
    return $this;
  }


  //-------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  final public function commit(\MovLib\Core\Database\Connection $connection, $oldRevisionId, $languageCode) {
    // Load the currently stored revision from the database, this will be the new old revision for the commit.
    $oldRevision = new static($this->entityId);

    // We have to make sure that the revision currently stored in the database is the same revision the user edited. We
    // have an exclusive lock on all rows that we read during our transaction. If someone would have changed this row
    // that belongs to our originator before the instantiation above, we'd know it after this comparison. But nobody is
    // able to change the row of our originator after the above instantiation, because of that exclusive row lock.
    if ($oldRevision->id !== $oldRevisionId) {
      throw new CommitConflictException();
    }

    // Serialize the old revision before passing it to the concrete class, you'll never know...
    $oldSerialized = serialize($oldRevision);

    // We also create a backup of the serialized old revision to ensure that we are able to easily recreate patches and
    // stuff in case something should ever go wrong. Note that the table name's are already unique (you can't have two
    // tables within a single database that have the same name) and combined with the entity's uniqu identifier nothing
    // bad can happen. We don't want to create any subdirectories within the backup directories. A direct listing of
    // all available backups with `ls -l` is what we want.
    $dir = "dr://var/backups/revisions/{$this->tableName}/{$this->entityId}";
    mkdir($dir, FileSystem::MODE_DIR, true);
    file_put_contents("{$dir}/{$oldRevision->id}.ser", $oldSerialized);

    // @todo FIXME -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    // Still problems with array properties during serialization. The offset "de" is created with a value of NULL but
    // while it's needed for the update statement, it bloats or maybe even breaks (edge cases) unserialize() calls.
    $this->wikipediaLinks = null;

    // Allow the concrete revision to perform work before we create the diff patch and start the commit.
    $this->preCommit($connection, $oldRevision, $languageCode);

    // Now we can create the actual diff patch that we'll store in the revisions row of the old revision.
    $diffPatch = (new Diff())->getPatch(serialize($this), $oldSerialized);

    // Prepare the update query and set the default properties.
    $update = (new Update($connection, $this->tableName))->set("changed", $this->created);

    // Let the concrete revision add its custom fields.
    $this->addCommitFields($update, $oldRevision, $languageCode);

    // We don't trust the concrete revision to return the update statement.
    $update->execute();

    // Now we can insert the previously generated diff patch into the data field of the old revision.
    (new Update($connection, "revisions"))
      ->set("data", $diffPatch)
      ->where("id", $oldRevision->id)
      ->where("entity_id", $this->entityId)
      ->where("revision_entity_id", $this->revisionEntityId)
      ->execute()
    ;

    // Insert revision, update the user and allow the concrete revision to perform work after the actual revision was
    // commited.
    $this->insertRevisionUpdateUser($connection)->postCommit($connection, $oldRevision, $languageCode);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  final public function create(\MovLib\Core\Database\Connection $connection) {
    // Allow the concrete revision to perform work before the actual revision is created.
    $this->preCreate($connection);

    // Prepare insert statement and set default values.
    $insert = (new Insert($connection, $this->tableName))->set("created", $this->created)->set("changed", $this->created);

    // Let the concrete revision add its custom fields.
    $this->addCreateFields($insert);

    // Now insert the revision and be sure to store the unique identifier that was assigned to our originator.
    $this->entityId = $insert->execute();

    // Insert revision, update the user and allow the concrete revision entity to perform work after the actual revision
    // was created.
    $this->insertRevisionUpdateUser($connection)->postCreate($connection);

    // We have to return the originator's new unique identifier because it doesn't know it's identifier yet, remember
    // that the identifier is assigned by the database table's auto increment field.
    return $this->entityId;
  }

  /**
   * Create entry in the revisions table.
   *
   * We have to create an empty record in the revisions table, otherwise we wouldn't be able to list initial commits in
   * the history tab nor on the user's contribution page.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @return this
   */
  private function insertRevisionUpdateUser(\MovLib\Core\Database\Connection $connection) {
    (new Insert($connection, "revisions"))
      ->set("id", $this->created)
      ->set("entity_id", $this->entityId)
      ->set("revision_entity_id", static::REVISION_ENTITY_ID)
      ->set("user_id", $this->userId)
      ->execute()
    ;

    // We have to update the user's edit count.
    //
    // @todo This should be unified, maybe in the session? An event system would be nice for this.
    (new Update($connection, "users"))
      ->increment("edits")
      ->where("id", $this->userId)
    ;

    return $this;
  }

}
