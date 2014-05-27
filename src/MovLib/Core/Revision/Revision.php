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
