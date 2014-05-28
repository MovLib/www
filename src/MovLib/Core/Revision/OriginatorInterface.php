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

/**
 * Defines the interface for entities that support revisioning.
 *
 * Our revisioning system is pretty similar to the memento design pattern. An entity that supports revisioning knows
 * it's current state and is the originator. Each revisionable entity has to have a memento class, the class has to have
 * the exact same name with the suffix <i>Revision</i>. The memento class is used together with <code>serialize()</code>
 * and <code>unserialize()</code> in the care taker {@see \MovLib\Data\Revision\Revision} to (re-)create previous
 * revisions of an entity.
 *
 * @link http://www.oodesign.com/memento-pattern.html
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface OriginatorInterface {

  /**
   * Commit the edited revisioned entity.
   *
   * @param integer $userId
   *   The user's unique identifier who edited the entity.
   * @param \MovLib\Component\DateTime $changed
   *   The date and time the user edited the entity.
   * @param integer $oldRevisionId
   *   The old revision's identifier that was sent along the form when the user started editing the entity.
   * @return this
   */
  public function commit($userId, \MovLib\Component\DateTime $changed, $oldRevisionId);

  /**
   * Create new revisioned entity.
   *
   * @param integer $userId
   *   The user's unique identifier who created the entity.
   * @param \MovLib\Component\DateTime $created
   *   The date and time the revision entity should use as its identifier within the history of the entity. This is
   *   usually the request date and time and should match the creation date and time if you create a new entity or the
   *   changed date and time if you edit an entity.
   * @return this
   */
  public function create($userId, \MovLib\Component\DateTime $created);

  /**
   * Create a revision based on the current state of the entity.
   *
   * @param integer $userId
   *   The user's unique identifier who created/edited the entity.
   * @param \MovLib\Component\DateTime $changed
   *   The date and time the user created/edited the entity, should be the request time.
   * @return \MovLib\Data\Revision\RevisionEntityInterface
   *   A revision based on the current state of the entity.
   */
  public function createRevision($userId, \MovLib\Component\DateTime $changed);

  /**
   * Set the state of the instance based on the given revision.
   *
   * @param \MovLib\Data\Revision\RevisionEntityInterface $revisionEntity
   *   The revision containing the state that should be recreated.
   * @return this
   */
  public function setRevision(RevisionInterface $revisionEntity);

}
