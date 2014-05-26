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
 * Defines the entity revision trait.
 *
 * The trait provides default implementations for the methods required by the {@see EntityRevisionInterface}.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait EntityRevisionTrait {


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Continue revision creation.
   *
   * The trait will take care of instantiating the revision and setting the default properties that are the same for
   * any entity. After that the concrete class has to take over and export the rest.
   *
   * @param \MovLib\Data\Revision\RevisionEntityInterface $revision
   *   Prepared revision with default properties already set.
   * @return \MovLib\Data\Revision\RevisionEntityInterface
   *   The new revision with the complete state set.
   */
  abstract protected function doCreateRevision(\MovLib\Data\Revision\RevisionEntityInterface $revision);

  /**
   * Continue revision setting.
   *
   * The trait will take care of setting the default properties that are the same for any entity. After that the
   * concrete class has to take over and set the rest.
   *
   * @param \MovLib\Data\Revision\RevisionEntityInterface $revision
   *   The revision to set with default properties already exported.
   * @return this
   */
  abstract protected function doSetRevision(\MovLib\Data\Revision\RevisionEntityInterface $revision);


  // ------------------------------------------------------------------------------------------------------------------- Hooks


  /**
   * Hook called before the entity is going to be commited.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @param \MovLib\Data\Revision\RevisionEntityInterface $revision
   *   The revision entity that will be commited.
   * @param integer $oldRevisionId
   *   The old revision's identifier that was sent along the form when the user started editing the entity.
   * @return this
   */
  protected function preCommit(\MovLib\Core\Database\Connection $connection, \MovLib\Data\Revision\RevisionEntityInterface $revision, $oldRevisionId) {
    return $this;
  }

  /**
   * Hook called after the entity has been commited.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @param \MovLib\Data\Revision\RevisionEntityInterface $revision
   *   The revision entity that was commited.
   * @param integer $oldRevisionId
   *   The old revision's identifier that was sent along the form when the user started editing the entity.
   * @return this
   */
  protected function postCommit(\MovLib\Core\Database\Connection $connection, \MovLib\Data\Revision\RevisionEntityInterface $revision, $oldRevisionId) {
    return $this;
  }

  /**
   * Hook called before the entity is going to be created.
   *
   * <b>NOTE</b><br>
   * The entity has no unique identifier at this point because it wasn't commited to the database at this point.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @param \MovLib\Data\Revision\RevisionEntityInterface $revision
   *   The revision entity that will be created.
   * @return this
   */
  protected function preCreate(\MovLib\Core\Database\Connection $connection, \MovLib\Data\Revision\RevisionEntityInterface $revision) {
    return $this;
  }

  /**
   * Hook called after the entity has been created.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database transaction connection.
   * @param \MovLib\Data\Revision\RevisionEntityInterface $revision
   *   The revision entity that was created.
   * @return this
   */
  protected function postCreate(\MovLib\Core\Database\Connection $connection, \MovLib\Data\Revision\RevisionEntityInterface $revision) {
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @see \MovLib\Data\Revision\EntityRevisionInterface::commit()
   */
  final public function commit($userId, \MovLib\Component\DateTime $changed, $oldRevisionId) {
    $connection = Database::getConnection();
    try {
      $connection->autocommit(false);
      $connection->begin_transaction(MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT | MYSQLI_TRANS_START_READ_WRITE);
      $revision = $this->createRevision($userId, $changed);
      $this->preCommit($connection, $revision, $oldRevisionId);
      $revision->commit($connection, $oldRevisionId);
      $connection->commit();
      $this->postCommit($connection, $revision, $oldRevisionId);
    }
    catch (\Exception $e) {
      $connection->rollback();
      throw $e;
    }
    finally {
      $connection->autocommit(true);
    }
    return $this;
  }

  /**
   * @see \MovLib\Data\Revision\EntityRevisionInterface::create()
   */
  final public function create($userId, \MovLib\Component\DateTime $created) {
    $connection = Database::getConnection();
    try {
      $connection->autocommit(true);
      $connection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
      $revision = $this->createRevision($userId, $created);
      $this->preCreate($connection, $revision);
      $this->id = $revision->create($connection);
      $connection->commit();
      $this->postCreate($connection, $revision);
    }
    catch (\Exception $e) {
      $connection->rollback();
      throw $e;
    }
    finally {
      $connection->autocommit(true);
    }
    return $this;
  }

  /**
   * @see \MovLib\Data\Revision\EntityRevisionInterface::createRevision()
   */
  final public function createRevision($userId, \MovLib\Component\DateTime $created) {
    // We are always able to create a revision instance from the concrete class by simply appending Revision. Also note
    // that we are always able to instantiate the revision without checking for our own id property's value, because it
    // will be NULL if we're a new instance and not commited yet, thus, no query will be executed by the revision class.
    $class    = static::class . "Revision";
    $revision = new $class($this->id);

    // Update the just loaded revision with the new values that we have in absolutely every entity.
    $revision->id      = $created->formatInteger();
    $revision->created = $created;
    $revision->deleted = $this->deleted;
    $revision->userId  = $userId;

    // The following properties are language dependent, an entity instance always contains only one language in contrast
    // to the revision, which contains all languages.
    $revision->wikipediaLinks[$this->intl->languageCode] = $this->wikipedia;

    // Let the concrete class perform more export work and the revision.
    return $this->doCreateRevision($revision);
  }

  /**
   * @see \MovLib\Data\Revision\EntityRevisionInterface::setRevision()
   */
  final public function setRevision(\MovLib\Data\Revision\RevisionEntityInterface $revision) {
    // @devStart
    // @codeCoverageIgnoreStart
    $class = static::class . "Revision";
    assert($revision instanceof $class, "You can only set a revision that is of the correct type.");
    assert($revision->entityId === $this->id, "You can only set a revision of the same entity.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Export all values that we have in absolutely every entity.
    $this->changed = $revision->created;
    $this->deleted = $revision->deleted;

    // The following properties are language dependent, an entity instance always contains only one language in contrast
    // to the revision, which contains all languages.
    if (isset($revision->wikipediaLinks[$this->intl->languageCode])) {
      $this->wikipedia = $revision->wikipediaLinks[$this->intl->languageCode];
    }

    // Let the concrete class export more properties.
    return $this->doSetRevision($revision);
  }

}
