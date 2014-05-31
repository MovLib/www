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
 * Defines the interface for revisions that represent a (previous or current) state of an originator.
 *
 * Our revisioning system is pretty similar to the memento design pattern. A revision entity is the memento class in our
 * system and used to set and get a previous or the current state of an entity via <code>serialize()</code> and
 * <code>unserialize()</code> calls. The care takers are the presentation classes and responsible for triggering the
 * created and commit actions to the persistent storage. The actual storing is performed in combination of both, the
 * revision (memento) and the originator. A presentation class cannot access the database, that's why we break the
 * pattern and let the memento handle the actual storing.
 *
 * @link http://www.oodesign.com/memento-pattern.html
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface RevisionInterface {

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
   * Commit a new revision of the originator.
   *
   * An originator isn't able to commit itself, because of the various language dependent dynamic column fields that are
   * needed for our international system. An entity usualy contains only values for the current display locale.
   * <i>Usualy</i> because some originator's include the value of the default locale during their creation, to produce
   * fallback values for the interface. This is, if the value is mandatory for presenting the originator to a user in
   * all system locales. A good example for this is a genre. Suppose you create a genre on the German MovLib website,
   * how could you present that new genre to a user of the Japanese MovLib website? You would have to fall back to the
   * only available name, the German one, but how do you know that you have the German one? You don't! Therefore a user
   * is required to enter the name in the default system locale as well, to ensure that we can rely on that value.
   * Nevertheless, a revision has to know all values of all fields at any given time because of the international
   * approach of MovLib.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   The transaction connection.
   * @param integer $oldRevisionId
   *   The identifier of the old revision for validation, this will be compared to the identifier of the revision that
   *   is loaded from the persistent storage.
   * @param string $languageCode
   *   The ISO 639-1 language code with which the user edited the originator. This is important for comparison of new
   *   and old values.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function commit(\MovLib\Core\Database\Connection $connection, $oldRevisionId, $languageCode);

  /**
   * Initial commit of a new originator.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   The transaction connection.
   * @param \MovLib\Component\DateTime $created
   *   The date and time the originator was created, this is usually the request date and time.
   * @return integer
   *   The unique identifier that was given by the database to the newly created originator.
   */
  public function create(\MovLib\Core\Database\Connection $connection, \MovLib\Component\DateTime $created);

  /**
   * Get the revision originator's unique class identifier.
   *
   * <b>NOTE</b><br>
   * You can also access the value directly via the static <var>$originatorClassId</var> property.
   *
   * @return integer
   *   The revision originator's unique class identifier.
   */
  public function getOriginatorClassId();

}
