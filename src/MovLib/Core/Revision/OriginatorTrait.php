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
 * Defines the originator trait.
 *
 * The trait provides default implementations for the methods required by the {@see OriginatorInterface}.
 *
 * <b>WARNING</b><br>
 * Do not overwrite the methods that are marked with final in this trait. Although the final keyword makes sure that the
 * childs of your concrete class cannot overwrite them, it doesn't make sure that you cannot overwrite them. Remember
 * that this is a trait and the methods are simply copied into your class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait OriginatorTrait {


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Continue revision creation.
   *
   * The trait will take care of instantiating the revision and setting the default properties that are the same for
   * any originator. After that the concrete class has to take over and export the rest.
   *
   * @param \MovLib\Core\Revision\RevisionInterface $revision
   *   Prepared revision with default properties already set.
   * @return \MovLib\Core\Revision\RevisionInterface
   *   The new revision with the complete state exported.
   */
  abstract protected function doCreateRevision(\MovLib\Core\Revision\RevisionInterface $revision);

  /**
   * Continue revision setting.
   *
   * The trait will take care of setting the default properties that are the same for any originator. After that the
   * concrete class has to take over and set the rest.
   *
   * @param \MovLib\Data\Revision\RevisionEntityInterface $revision
   *   The revision to set with default properties already exported.
   * @return this
   */
  abstract protected function doSetRevision(RevisionInterface $revision);


  // ------------------------------------------------------------------------------------------------------------------- Hooks


  /**
   * Hook called before the originator is going to be commited.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @param \MovLib\Core\Revision\RevisionInterface $revision
   *   The revision that will be commited.
   * @param integer $oldRevisionId
   *   The old revision's identifier that was sent along the form when the user started editing the originator.
   * @return this
   */
  protected function preCommit(\MovLib\Core\Database\Connection $connection, \MovLib\Core\Revision\RevisionInterface $revision, $oldRevisionId) {
    return $this;
  }

  /**
   * Hook called after the originator has been commited.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @param \MovLib\Core\Revision\RevisionInterface $revision
   *   The revision that was commited.
   * @param integer $oldRevisionId
   *   The old revision's identifier that was sent along the form when the user started editing the entity.
   * @return this
   */
  protected function postCommit(\MovLib\Core\Database\Connection $connection, \MovLib\Core\Revision\RevisionInterface $revision, $oldRevisionId) {
    return $this;
  }

  /**
   * Hook called before the originator is going to be created.
   *
   * <b>NOTE</b><br>
   * The originator has no unique identifier at this point because it wasn't commited to the database. Remember that the
   * unique identifier is assigned by the corresponding table's auto increment field.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @param \MovLib\Core\Revision\RevisionInterface $revision
   *   The revision that will be created.
   * @return this
   */
  protected function preCreate(\MovLib\Core\Database\Connection $connection, \MovLib\Core\Revision\RevisionInterface $revision) {
    return $this;
  }

  /**
   * Hook called after the originator has been created.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @param \MovLib\Core\Revision\RevisionInterface $revision
   *   The revision that was created.
   * @return this
   */
  protected function postCreate(\MovLib\Core\Database\Connection $connection, \MovLib\Core\Revision\RevisionInterface $revision) {
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @see \MovLib\Data\Revision\EntityInterface::commit()
   */
  final public function commit($userId, \MovLib\Component\DateTime $changed, $oldRevisionId) {
    $connection = Database::getConnection();
    try {
      $connection->autocommit(false);
      $connection->real_query("SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE");
      $connection->real_query("START TRANSACTION WITH CONSISTENT SNAPSHOT, READ WRITE");
      $revision = $this->createRevision($userId, $changed);
      $this->preCommit($connection, $revision, $oldRevisionId);
      $revision->commit($connection, $oldRevisionId, $this->intl->code);
      $this->postCommit($connection, $revision, $oldRevisionId);
      $connection->commit();
    }
    catch (\Exception $e) {
      $connection->rollback();
      throw $e;
    }
    finally {
      $connection->real_query("SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ");
      $connection->autocommit(true);
    }
    return $this;
  }

  /**
   * @see \MovLib\Data\Revision\EntityInterface::create()
   */
  final public function create($userId, \MovLib\Component\DateTime $created) {
    $connection = Database::getConnection();
    try {
      $connection->autocommit(true);
      $connection->real_query("SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE");
      $connection->real_query("START TRANSACTION WITH CONSISTENT SNAPSHOT, READ WRITE");
      $revision = $this->createRevision($userId, $created);
      $this->preCreate($connection, $revision);
      $this->id      = $revision->create($connection, $created);
      $this->created = $created;
      $this->postCreate($connection, $revision);
      $connection->commit();
    }
    catch (\Exception $e) {
      $connection->rollback();
      throw $e;
    }
    finally {
      $connection->real_query("SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ");
      $connection->autocommit(true);
    }

    // Make sure that we're working with a fully initialized originator.
    if (method_exists($this, "init")) {
      $this->init();
    }

    return $this;
  }

  /**
   * @see \MovLib\Data\Revision\OriginatorInterface::createRevision()
   */
  final public function createRevision($userId, \MovLib\Component\DateTime $changed) {
    // We are always able to create a revision instance from the concrete class by simply appending Revision. Also note
    // that we are always able to instantiate the revision without checking for our own id property's value, because it
    // will be NULL if we're a new instance and not commited yet, thus, no query will be executed by the revision class.
    $class    = static::class . "Revision";
    $revision = new $class($this->id);

    // Update the just loaded revision with the new values that we have in absolutely every originator.
    $revision->id      = $changed->formatInteger();
    $revision->deleted = $this->deleted;
    $revision->userId  = $userId;

    // Note that we're also updating our own changed date. It doesn't matter if this revision is going to be commited to
    // the persistent storage. The current object has to assume that the global state of it will change if it creates a
    // new revision of itself.
    $this->changed = $revision->created = $changed;

    // Let the concrete class perform more export work on the revision.
    return $this->doCreateRevision($revision);
  }

  /**
   * Get atomic value from revision array property.
   *
   * @param array|null $revisionProperty
   *   The array property of the revision, can be <code>NULL</code>.
   * @param mixed $defaultValue [optional]
   *   An atomic default value that should be used in case the revision array property doesn't contain <var>$key</var>.
   *   Defaults to <code>NULL</code>.
   * @param mixed $key [optional]
   *   The key that is used to decide if the value has to be set or not, defaults to <code>NULL</code> and the current
   *   ISO 639-1 language code is used.
   * @return mixed
   *   The the atomic value from the revision array property.
   */
  final protected function getRevisionArrayValue($revisionProperty, $defaultValue = null, $key = null) {
    // Use the current language code of the current concrete instance if no key was passed.
    $key || ($key = $this->intl->code);

    // We use array key exists at this point because it returns true in case the value is NULL, in contrast to isset().
    if (array_key_exists($key, $revisionProperty)) {
      return $revisionProperty[$key];
    }

    // If the key isn't present in the array, use default.
    return $defaultValue;
  }

  /**
   * @see \MovLib\Data\Revision\EntityInterface::setRevision()
   */
  final public function setRevision(RevisionInterface $revision) {
    // @devStart
    // @codeCoverageIgnoreStart
    $class = static::class . "Revision";
    assert($revision instanceof $class, "You can only set a revision that is of the correct type.");
    assert($revision->originatorId === $this->id, "You can only set a revision of the same originator.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Export all values that we have in absolutely every originator.
    $this->changed = $revision->created;
    $this->deleted = $revision->deleted;

    // Let the concrete class export more properties.
    return $this->doSetRevision($revision);
  }

  /**
   * Set atomic value to revision array property.
   *
   * @param array|null $revisionProperty
   *   The array property of the revision, can be <code>NULL</code>.
   * @param mixed $value
   *   The originator's atomic value that should be set. <var>$key</var> will be removed from the revision's array
   *   property if <var>$key</var> exists within the revision's array property and this value evaluates to
   *   <code>FALSE</code> (note that <code>"0"</code> is treated as <code>TRUE</code>).
   * @param mixed $key [optional]
   *   The key that is used to decide if the value has to be set or not, defaults to <code>NULL</code> and the current
   *   ISO 639-1 language code is used.
   * @return this
   */
  final protected function setRevisionArrayValue(&$revisionProperty, $value, $key = null) {
    // Use the current language code of the current concrete instance if no key was passed.
    $key || ($key = $this->intl->code);

    // If the value is false (note the two equal signs) and not simply a string containing 0 don't export.
    if ($value == false && $value != "0") {
      // Remove the key from the revision property in this case, this is important for serialization.
      if (array_key_exists($key, (array) $revisionProperty)) {
        unset($revisionProperty[$key]);
      }
    }
    // Otherwise export the value to the revision.
    else {
      $revisionProperty[$key] = $value;
    }

    return $this;
  }

}
