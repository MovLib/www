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

use \MovLib\Core\Database\Database;

/**
 * Defines the revision object.
 *
 * Our revisioning system is pretty similar to the memento design pattern. Revision (the care taker) is responsible for
 * storing and recreating revision entities. Different revisions are stored via diff patches, this helps us to keep
 * track of all revisions of any entity while keeping the storage consumption as low as possible. Our system is working
 * backwards, unlike most diff patching systems (e.g. Wikipedia). This means that the database tables of an entity
 * always contain the latest and current state of an entity and the revisions table (controlled by this class, the care
 * taker, and used to keep the revision entities [mementos]) contains diff patches that allow backward patching of
 * that data to a previous revision. The first revision is therefore always empty, because there's no data that we could
 * create a diff from.
 *
 * We are using an adopted version of Raymond Hill's {@link https://github.com/gorhill/PHP-FineDiff PHP FineDiff} which
 * is based on an optimized version of {@link https://github.com/cogpowered/FineDiff/ Cog Powered}. We are always
 * creating diff patches between serialized PHP strings. Those strings never contain line breaks, not actual words and
 * our PHP FineDiff implementation doesn't have to be extensible nor configurable and always acts on character level.
 * Have a look at {@see Revision::diff()} and {@see Revision::patch()} to see the actual implementations.
 *
 * @todo Explain the generated diff patches opcodes.
 *
 * @link http://www.oodesign.com/memento-pattern.html
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Revision {


  //-------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Revision";
  // @codingStandardsIgnoreEnd


  /**
   * Exception code identifying that new revision wasn't found.
   *
   * @var integer
   */
  const RANGE_EXCEPTION_NEW = 2;

  /**
   * Exception code identifying that old revision wasn't found.
   *
   * @var integer
   */
  const RANGE_EXCEPTION_OLD = 1;


  //-------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The current revision entity.
   *
   * @var \MovLib\Data\Revision\AbstractRevisionEntity
   */
  protected $cur;

  /**
   * The new revision entity.
   *
   * @var \MovLib\Data\Revision\AbstractRevisionEntity
   */
  public $new;

  /**
   * The old revision entity.
   *
   * @var \MovLib\Data\Revision\AbstractRevisionEntity
   */
  public $old;


  //-------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new revision object.
   *
   * @param \MovLib\Data\Revision\AbstractRevisionEntity $currentRevisionEntity
   *   The current revision entity, you can get the current revision of the entity that you control via its
   *   {@see \MovLib\Data\Revision\EntityIterface::createRevision()} method.
   */
  public function __construct(\MovLib\Data\Revision\AbstractRevisionEntity $currentRevisionEntity) {
    $this->cur = $currentRevisionEntity;
  }

  // @devStart
  // @codeCoverageIgnoreStart

  /**
   * Implements magic method <code>__clone()</code>.
   *
   * @throws \BadFunctionCallException
   *   Always thrown to prevent wrong usage of the revision class.
   */
  public function __clone() {
    throw new \BadFunctionCallException("You cannot clone a revision object.");
  }

  /**
   * Implements <code>serialize()</code> callback.
   *
   * @throws \BadFunctionCallException
   *   Always thrown to prevent wrong usage of the revision class.
   */
  public function __sleep() {
    throw new \BadFunctionCallException("You cannot serialize() revision objects.");
  }

  /**
   * Implements <code>unserialize()</code> callback.
   *
   * @throws \BadFunctionCallException
   *   Always thrown to prevent wrong usage of the revision class.
   */
  public function __wakeup() {
    throw new \BadFunctionCallException("You cannot unserialize() revision objects.");
  }

  // @codeCoverageIgnoreEnd
  // @devEnd


  //-------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Commit a new revision of an existing entity.
   *
   * @param integer $inputRevisionId
   *   The user submitted revision identifier. You had to create a revision form that contains a hidden form field with
   *   the last revision identifier of the entity. This identifier is needed again to validate once more that nobody
   *   else changed the entity before we attempt to update it.
   *
   *   <b>IMPORTANT</b><br>
   *   This has to be the same value that was previously validated with the form.
   * @param string $languageCode
   *   The current ISO 639-1 language code.
   * @return this
   * @throws \mysqli_sql_exception
   * @throws \UnexpectedValueException
   *   If the passed <var>$inputRevisionId</var> doesn't match the revision identifier of the revision entity that would
   *   be overwritten by this commit. You should catch this exception and present an appropriate error message to the
   *   user.
   */
  public function commit($inputRevisionId, $languageCode) {
    $connection = Database::getConnection();
    try {
      // Disable autocommit and begin transaction process.
      $connection->autocommit(false);
      $connection->begin_transaction(MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT | MYSQLI_TRANS_START_READ_WRITE);

      // Load the currently stored revision from the database, this will be the new old revision for this entity.
      /* @var $old \MovLib\Data\Revision\AbstractRevisionEntity */
      $class = get_class($this->cur);
      $old   = $class::createFromId($this->cur->entityId);

      // We just loaded the old current revision, make sure that this old current revision is the same revision the
      // user just edited.
      if ($old->id !== $inputRevisionId) {
        throw new \UnexpectedValueException();
      }

      // Create a diff between both revisions, we have to pass the result per reference to bind_param() below and thus
      // store the diff in a local variable. This is going to take some time...
      $diff = $this->diff(serialize($this->cur), serialize($old));

      // Let the current revision update its database record to the newly created revision.
      $this->cur->commit($old, $languageCode);

      // Store the diff patch that we just created in the revisions table for later reconstruction.
      $connection->real_query(<<<SQL
UPDATE `revisions`
  SET `data` = '{$connection->real_escape_string($diff)}'
WHERE `id` = CAST({$old->id} AS DATETIME)
  AND `entity_id` = {$old->entityId}
  AND `revision_entity_id` = {$old->revisionEntityId}
SQL
      );

      $this->insert($connection, $this->cur);
      $connection->commit();
    }
    // Catch any kind of exception at this point and be sure to close the prepared statements and rollback all staged
    // changes that weren't already commited.
    catch (\Exception $e) {
      isset($update) && $update->close();
      $connection->rollback();
      throw $e;
    }
    // Always reactivate autocommits.
    finally {
      $connection->autocommit(true);
    }

    return $this;
  }

  /**
   * Get all diff patches for this entity up to the given revision's identifier.
   *
   * @internal
   *   The diff patches aren't useful for anything on their own, because they only contain cryptic characters for a
   *   person that doesn't understand the FineDiff system. Still, might be useful for debugging or...
   * @param integer $revisionId
   *   The revision identifier that acts as limit.
   *
   *   <b>NOTE</b><br>
   *   If you'd like to get absolutely all diff patches pass the creation date and time of your entity.
   * @return array
   *   Array containing all diff patches up to the specified revision identifier.
   *
   *   <b>NOTE</b><br>
   *   You can use the <code>\MovLib\Stub\Data\Revision\DiffPatch</code> stub class for IDE auto-completion for the
   *   values in the returned array.
   */
  public function getDiffPatches($revisionId) {
    $result = Database::getConnection()->query($this->getDiffPatchQuery((integer) $revisionId));
    $diffPatches = null;
    /* @var $diffPatch \MovLib\Stub\Data\Revision\DiffPatch */
    while ($diffPatch = $result->fetch_object()) {
      $diffPatches[] = $diffPatch;
    }
    $result->free();
    return $diffPatches;
  }

  /**
   * Commit a new entity.
   *
   * @return integer
   *   The unique identifier of the newly created entity.
   */
  public function initialCommit() {
    $connection = Database::getConnection();
    try {
      $connection->autocommit(false);
      $connection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
      $this->cur->create();
      $this->insert($connection, $this->cur);
      $connection->commit();
    }
    catch (\Exception $e) {
      $connection->rollback();
      throw $e;
    }
    finally {
      $connection->autocommit(true);
    }
    return $this->cur->entityId;
  }

  /**
   * Create patched entity revisions.
   *
   * <b>NOTE</b><br>
   * The <var>Revision::$newRevision</var> property will contain the real current revision if only the first parameter
   * (<var>$oldRevisionId</var>) is passed to this method.
   *
   * <b>In-depth explanation of the patching process</b>
   *
   * The current revision was loaded during the construction of this revision instance. Calling this method with one or
   * two revision identifiers will patch the current revision backwards and recreate those old revisions. The old
   * revisions are exported to the class scope of this instance and accessible via <var>Revision::$newRevision</var> and
   * <var>Revision::$oldRevision</var>.
   *
   * Our patching process is performed backwards, in contrast to many other patching processes (e.g. Wikipedia). This
   * keeps the storage consumption for the diff patches as low as possible and ensures that we aren't storing the same
   * data multiple times. Remember, redundancy isn't the same as a backup. Storing the same data multiple times in the
   * same database wouldn't help us with anything.
   *
   * The database tables (e.g. movies, genres, series) contain the current version, the version that is displayed on a
   * page request and the revision that is used to create previous versions. This means that our revisions table won't
   * contain any data, unless a client performed at least a single edit. We create a diff patch between the new current
   * version and the old current version, which will then be stored in the revisions table, any additional meta data
   * that is from use will be moved from the entity's table to the revisions table. Currently the following meta data
   * is directly moved:
   * <ul>
   *   <li>The <b>changed</b> value of the entity becomes the <b>id</b> value of the revision. This allows us to easily
   *   sort and efficiently access the revisions.</li>
   *   <li>The <b>user_id</b> value of the entity becomes the <b>user_id</b> value of the revision. This allows us to
   *   directly load the user that performed the edit.</li>
   * </ul>
   *
   * @param integer|string $oldRevisionId
   *   The older revision to retrieve, will be exported to the <var>Revision::$oldRevision</var> property.
   * @param integer|string $newRevisionId [optional]
   *   The newer revision to retrieve, will be exported to the <var>Revision::$newRevision</var> property. The property
   *   isn't overwritten if no value is passed (default) and thus contains the real current revision that is stored in
   *   the database.
   * @return this
   * @throws \mysqli_sql_exception
   *   If retrieving of the older revisions from the database fails.
   * @throws \ErrorException
   *   If unserializing of a patched revision fails.
   */
  public function restore($oldRevisionId, $newRevisionId = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    // The format of the revision identifiers is validated via nginx if a request is made via the WWW. We still validate
    // them at this point again to avoid poor API usage through developers.
    assert(
      preg_match("/[1-9][0-9]{13}/", $oldRevisionId) === 1,
      "The old revision identifier must match the date-time-integer format YYYYMMDDhhmmss."
    );
    if (isset($newRevisionId)) {
      assert(
        preg_match("/[1-9][0-9]{13}/", $newRevisionId) === 1,
        "The new revision identifer must either be NULL or match the date-time-integer format YYYYMMDDhhmmss"
      );
      assert(
        $oldRevisionId < $newRevisionId,
        "The old revision identifier must be less than the new revision identifier: {$oldRevisionId} < {$newRevisionId}"
      );
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Serialize the current revision entity, patching starts from here.
    $revision = serialize($this->cur);

    // Retrieve the revision range back to the requested old revision, which will automatically include the newer
    // revision as well (if passed). Note that we cast the passed old revision identifier to a DATETIME and we do that
    // in the WHERE clause of the query. This means that the database only has to perform the cast once, before starting
    // the actual search and it uses the correct datatype for comparison, which in turn increases performance further.
    // We also add zero to each returned revision identifier, this will transform the DATETIME to the desired integer,
    // something that we can't do as efficient in PHP as we can do it with the database.
    $result = Database::getConnection()->query($this->getDiffPatchQuery($oldRevisionId));

    try {
      /* @var $diffPatch \MovLib\Stub\Data\Revision\DiffPatch */
      while ($diffPatch = $result->fetch_object()) {
        // Copy the row's content into another local variable, this ensures that we'll have the last patch from the
        // result and not NULL (which is the abort condition for this loop). We can skip the comparison for the old
        // revision by doing so.
        $patch = $diffPatch;

        // Apply the patches from the result.
        $revision = $this->patch($revision, $patch->data);

        // Check if this patch matches the newer requested revision and recreate the instance if it does.
        if ($patch->revisionId == $newRevisionId) {
          $this->new = unserialize($revision);
        }
      }
    }
    // Always free the result handle, even if an exception occurred during the patching process. This is important to
    // ensure that subsequent database calls won't fail with "commands out of sync". We don't catch the exception, the
    // kernel shall catch it and display or log it.
    finally {
      $result->free();
    }

    // The last patch returned by the database query is always the oldest revision.
    if ($patch->revisionId == $oldRevisionId) {
      $this->old = unserialize($revision);
    }
    else {
      throw new \RangeException("Couldn't find old revision {$oldRevisionId}.", static::RANGE_EXCEPTION_OLD);
    }

    // We should have a new revision if a new revision identifier was passed, if not something is wrong.
    if ($newRevisionId && !$this->new) {
      throw new \RangeException("Couldn't find new revision {$newRevisionId}.", static::RANGE_EXCEPTION_NEW);
    }
    // Use the current revision if no new revision was requested.
    else {
      $this->new = clone $this->cur;
    }

    return $this;
  }


  //-------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Insert revision entity into revisions table without settings the diff patch data.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database connection.
   * @param \MovLib\Data\Revision\AbstractRevisionEntity $revisionEntity
   *   The revision entity to insert.
   * @return this
   * @throws \mysqli_sql_exception
   */
  protected function insert(\MovLib\Core\Database\Connection $connection, \MovLib\Data\Revision\AbstractRevisionEntity $revisionEntity) {
    try {
      $insert = $connection->prepare(
        "INSERT INTO `revisions` (`id`, `entity_id`, `revision_entity_id`, `user_id`) VALUES (CAST(? AS DATETIME), ?, ?, ?)"
      );
      $insert->bind_param("sdid", $revisionEntity->id, $revisionEntity->entityId, $revisionEntity->revisionEntityId, $revisionEntity->userId);
      $insert->execute();
    }
    finally {
      $insert && $insert->close();
    }
    return $this;
  }

  /**
   * Get the SQL query to fetch diff patches from the revisions table.
   *
   * @param integer $oldRevisionId
   *   The identifier of the older revision to fetch.
   *
   *   If you need a new and an old revision, this query will give you both solely based on the identifier of the older
   *   revision!
   * @return string
   *   The SQL query to fetch diff patches from the revisions table.
   */
  protected function getDiffPatchQuery($oldRevisionId) {
    // We want to skip the first result, because it will always contain an empty diff patch and is actually already
    // loaded (it's required in the constructor of the class). The problem is, we can't have an OFFSET without a LIMIT
    // and the solution to this problem looks like a hack, but seems to be only way:
    // https://stackoverflow.com/questions/255517
    return <<<SQL
SELECT
  `id` + 0 AS `revisionId`,
  `user_id` AS `userId`,
  `data`
FROM `revisions`
WHERE `id` >= CAST({$oldRevisionId} AS DATETIME)
  AND `entity_id` = {$this->cur->entityId}
  AND `revision_entity_id` = {$this->cur->revisionEntityId}
ORDER BY `id` DESC
LIMIT 18446744073709551615 OFFSET 1
SQL;
  }

}
