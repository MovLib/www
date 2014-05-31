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
 * Defines the interface for classes that support revisioning.
 *
 * Our revisioning system is pretty similar to the memento design pattern. A class that supports revisioning knows its
 * current state and is the originator. Each revisionable entity has to have a memento class, the class has to have the
 * exact same name with the suffix <i>Revision</i>. The memento class is used together with <code>serialize()</code>
 * and <code>unserialize()</code> to (re-)create previous revisions of an entity.
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
   * Commit the edited originator.
   *
   * <b>NOTE</b><br>
   * The word <i>commit</i> has nothing to do with the database transaction, it's about the commit of the edits the user
   * made to the originator to our system.
   *
   * @param integer $userId
   *   The user's unique identifier who edited the originator.
   * @param \MovLib\Component\DateTime $changed
   *   The date and time the user edited the originator. Usually the request date and time.
   * @param integer $oldRevisionId
   *   The old revision's identifier that was sent along the form when the user started editing the originator.
   * @return this
   */
  public function commit($userId, \MovLib\Component\DateTime $changed, $oldRevisionId);

  /**
   * Create new originator.
   *
   * @param integer $userId
   *   The user's unique identifier who created the originator.
   * @param \MovLib\Component\DateTime $created
   *   The date and time the user created the originator. Usually the request date and time.
   * @return this
   */
  public function create($userId, \MovLib\Component\DateTime $created);

  /**
   * Create a revision based on the current state of the originator.
   *
   * @param integer $userId
   *   The user's unique identifier who created/edited the originator.
   * @param \MovLib\Component\DateTime $changed
   *   The date and time the user created/edited the originator. Usually the request date and time.
   * @return \MovLib\Core\Revision\RevisionInterface
   *   A revision based on the current state of the originator.
   */
  public function createRevision($userId, \MovLib\Component\DateTime $changed);

  /**
   * Set the state of the originator on the given revision.
   *
   * @param \MovLib\Core\Revision\RevisionInterface $revision
   *   The revision containing the state that should be recreated.
   * @return this
   */
  public function setRevision(\MovLib\Core\Revision\RevisionInterface $revision);

}
