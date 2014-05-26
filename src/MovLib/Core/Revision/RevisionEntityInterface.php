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
 * Defines the interface for revision entities that represent a (previous or current) state of an entity.
 *
 * Our revisioning system is pretty similar to the memento design pattern. A revision entity is the memento class in our
 * system and used to set and get a previous or the current state of an entity via <code>serialize()</code> and
 * <code>unserialize()</code> calls. The care taker ({@see \MovLib\Data\Revision\Revision}) is responsible for storing
 * any revision entity (memento) to the persistent storage (namely in the revisions table).
 *
 * @link http://www.oodesign.com/memento-pattern.html
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface RevisionEntityInterface {

  /**
   * Implements <code>serialize()</code> callback.
   *
   * @staticvar array $properties
   *   Should be used internally to cache the property names.
   * @return array
   *   Array containing the names of the properties that should be serialized.
   */
  public function __sleep();

  /**
   * Implements <code>unserialize()</code> callback.
   */
  public function __wakeup();

  /**
   * Commit a new revision of the entity.
   *
   * An entity (originator) isn't able to commit itself, because of the various language dependent dynamic column fields
   * that are needed for our international system. An entity usualy contains only values for the current display locale.
   * <i>Usualy</i> because some entity's include the value of the default locale during their creation, to produce
   * fallback values for the interface. This is, if the value is mandatory for presenting the entity to a user in all
   * system locales. A good example for this is a genre. Suppose you create a genre on the German MovLib website, how
   * could you present that new genre to a user of the Japanese MovLib website? You would have to fall back to the only
   * available name, the German one, but how do you know that you have the German one? You don't! Therefore a user is
   * required to enter the name in the default system locale as well, to ensure that we can rely on that value. Never-
   * theless, a revision entity has to know all values of all fields at any given time because of the international
   * approach of MovLib. We can't implement this in the revision (care taker) class because it can't know everything
   * about every entity, in fact it doesn't know anything about any entity (just like in the original memento pattern,
   * the revision entity [memento] is opaque to the revision [care taker]).
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   The transaction connection.
   * @param integer $oldRevisionId
   *   The identifier of the old revision for validation, this will be compared to the identifier of the revision that
   *   is loaded from the persistent storage.
   * @return this
   * @throws \mysqli_sql_exception
   * @throws \BadMethodCallException
   *   If nothing is to be commited a BadMethodCallException is thrown because it should be checked long before calling
   *   this method that there is actually something to commit.
   */
  public function commit(\MovLib\Core\Database\Connection $connection, $oldRevisionId);

  /**
   * Initial commit of a new entity.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   The transaction connection.
   * @return integer
   *   The unique identifier that was given by the database to the newly inserted entity.
   */
  public function initialCommit(\MovLib\Core\Database\Connection $connection);

}
