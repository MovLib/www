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
 * Defines the base object for revision objects.
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


  //-------------------------------------------------------------------------------------------------------------------- Static Properties



  /**
   * The originator's class identifier.
   *
   * <b>NOTE</b><br>
   * In order to efficiently select rows from the revisions table and to ensure data integrity each originator has a
   * unique identifier. Those identifiers are managed through the database table <code>"revision_entities"</code> which
   * allows us to easily add new originator's to the revision system without ever checking the current implementation
   * for the identifier. A concrete revision has set its originator's unique identifier in this static property.
   *
   * @var integer
   */
  public static $originatorClassId;

  /**
   * The revision originators's primary table name.
   *
   * @var string
   */
  public static $tableName;


  //-------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The revision's creation date and time.
   *
   * @var \MovLib\Component\DateTime
   */
  public $created;

  /**
   * The revision's deletion state.
   *
   * @var boolean
   */
  public $deleted = false;

  /**
   * The originator's unique database identifier.
   *
   * This is usually the value of the primary database key of the originator, e.g. unique movie id.
   *
   * @var integer
   */
  public $originatorId;

  /**
   * The revision's identifier.
   *
   * <b>NOTE</b><br>
   * Only unique together with <var>AbstractRevision::$originatorId</var> and <var>AbstractRevision::$originatorClassId</var>
   * when selecting revisions from the table in the database.
   *
   * @var integer
   */
  public $id;

  /**
   * The revision's user who created this revision.
   *
   * <b>NOTE</b><br>
   * This property is only set if this entity was instantiated via a {@see \MovLib\Data\History\HistorySet} for
   * presentation purposes.
   *
   * @var \MovLib\Data\User\User|null
   */
  public $user;

  /**
   * The revision's unique identifier of the user who created this revision.
   *
   * @var integer
   */
  public $userId;


  //-------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new revision object.
   */
  public function __construct() {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty(static::$originatorClassId), "You have to set the static \$originatorClassId property in your class.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // We can safely assume that there is a set that contains the table name if no table name was defined by the
    // concrete revision class, most entity's have one.
    if (!static::$tableName) {
      $set = substr(static::class, 0, -8) . "Set";
      static::$tableName = $set::$tableName;
    }

    // Make sure all known property's are of correct type.
    if ($this->id) {
      $this->originatorId = (integer) $this->originatorId;
      $this->id           = (integer) $this->id;
      $this->created      = new DateTime($this->id);
      $this->deleted      = (boolean) $this->deleted;
      $this->userId       = (integer) $this->userId;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    static $properties = [ "deleted", "originatorId", "id", "userId" ];
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
   * Hook called before the revision is going to be commited.
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
   * Hook called after the revision has been commited.
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
   * Hook called before the revision is going to be created.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @return this
   */
  protected function preCreate(\MovLib\Core\Database\Connection $connection) {
    return $this;
  }

  /**
   * Hook called after the revision has been created.
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
    $oldRevision = new static($this->originatorId);

    // We have to make sure that the revision currently stored in the database is the same revision the user edited. We
    // have an exclusive lock on all rows that we read during our transaction. If someone would have changed this row
    // that belongs to our originator before the instantiation above, we'd know it after this comparison. But nobody is
    // able to change the row of our originator after the above instantiation, because of that exclusive row lock.
    if ($oldRevision->id !== $oldRevisionId) {
      throw new CommitConflictException();
    }

    // Serialize the old revision before passing it to the concrete class, you'll never know if a developer might change
    // the revision (which would be a mistake).
    $oldSerialized = serialize($oldRevision);

    // We also create a backup of the serialized old revision to ensure that we are able to easily recreate patches and
    // stuff in case something should ever go wrong. Note that the table name's are already unique (you can't have two
    // tables within a single database that have the same name) and combined with the originator's unique identifier
    // nothing bad can happen. We don't want to create any subdirectories within the backup directories. A direct
    // listing of all available backups with `ls -l` is what we want.
    $dir = "dr://var/backups/revisions/{$this::$tableName}/{$this->originatorId}";
    mkdir($dir, FileSystem::MODE_DIR, true);
    file_put_contents("{$dir}/{$oldRevision->id}.ser", $oldSerialized);

    // Allow the concrete revision to perform work before we create the diff patch and start the commit.
    $this->preCommit($connection, $oldRevision, $languageCode);

    header("content-type: text/plain");
    echo
      "Error in getPatch() which results in an infinite loop because new jobs get stacked and stacked!" , PHP_EOL , PHP_EOL ,
      str_repeat("-", 4) , " FROM " , str_repeat("-", 4) , PHP_EOL , PHP_EOL ,
      serialize($this) , PHP_EOL , PHP_EOL ,
      str_repeat("-", 4) , " TO " , str_repeat("-", 4) , PHP_EOL , PHP_EOL ,
      $oldSerialized , PHP_EOL , PHP_EOL
    ;
    exit();

    // Now we can create the actual diff patch that we'll store in the revisions row of the old revision.
    $diffPatch = (new Diff())->getPatch(serialize($this), $oldSerialized);

    // Prepare the update query and set the default properties.
    $update = (new Update($connection, static::$tableName))->set("changed", $this->created)->where("id", $this->originatorId);

    // Let the concrete revision add its custom fields.
    $this->addCommitFields($update, $oldRevision, $languageCode);

    // We don't trust the concrete revision to return the update statement.
    $update->execute();

    // Now we can insert the previously generated diff patch into the data field of the old revision.
    (new Update($connection, "revisions"))
      ->set("data", $diffPatch)
      ->where("id", $oldRevision->id)
      ->where("entity_id", $this->originatorId)
      ->where("revision_entity_id", static::$originatorClassId)
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
  final public function create(\MovLib\Core\Database\Connection $connection, \MovLib\Component\DateTime $created) {
    // Allow the concrete revision to perform work before the actual revision is created.
    $this->preCreate($connection);

    // Prepare insert statement and set default values.
    $insert = (new Insert($connection, static::$tableName))->set("created", $this->created)->set("changed", $this->created);

    // Let the concrete revision add its custom fields.
    $this->addCreateFields($insert);

    // Now insert the revision and be sure to store the unique identifier that was assigned to our originator.
    $this->originatorId = $insert->execute();

    // Insert revision, update the user and allow the concrete revision entity to perform work after the actual revision
    // was created.
    $this->insertRevisionUpdateUser($connection)->postCreate($connection);

    // We have to return the originator's new unique identifier because it doesn't know it's identifier yet, remember
    // that the identifier is assigned by the database table's auto increment field.
    return $this->originatorId;
  }

  /**
   * {@inheritdoc}
   */
  final public function getOriginatorClassId() {
    return static::$originatorClassId;
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
      ->set("entity_id", $this->originatorId)
      ->set("revision_entity_id", static::$originatorClassId)
      ->set("user_id", $this->userId)
      ->execute()
    ;

    // We have to update the user's edit count.
    //
    // @todo This should be unified, maybe in the session? An event system would be nice for this.
    (new Update($connection, "users"))
      ->increment("edits")
      ->where("id", $this->userId)
      ->execute()
    ;

    return $this;
  }

}
