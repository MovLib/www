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
namespace MovLib\Data\History;

use \MovLib\Core\Database\Database;
use \MovLib\Core\Diff\Diff;

/**
 * Defines the history object.
 *
 * The history unifies handling of loading and patching of specific revisions of any originator.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class History {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "History";
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
   * The current revision.
   *
   * @var \MovLib\Core\Revision\RevisionInterface
   */
  protected $cur;

  /**
   * The new revision.
   *
   * @var \MovLib\Core\Revision\RevisionInterface
   */
  public $new;

  /**
   * The old revision.
   *
   * @var \MovLib\Core\Revision\RevisionInterface
   */
  public $old;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   *
   * @param string $classNamespace [optional]
   *   The namespace if it differs from <code>"\\MovLib\\Data\\{$className}"</code>.
   */
  public function __construct($className, $id, $oldRevisionId, $newRevisionId = null, $classNamespace = null) {
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

    // Create current revision, this is always our reference point.
    if (!isset($classNamespace)) {
      $classNamespace = "\\MovLib\\Data\\{$className}";
    }
    $class             = "{$classNamespace}\\{$className}Revision";
    $this->cur         = new $class($id);
    $originatorClassId = $class::$originatorClassId;

    // Serialize the current revision, patching starts from here.
    $revision = serialize($this->cur);

    // Retrieve the revision range back to the requested old revision, which will automatically include the newer
    // revision as well (if passed). Note that we cast the passed old revision identifier to a DATETIME and we do that
    // in the WHERE clause of the query. This means that the database only has to perform the cast once, before starting
    // the actual search and it uses the correct datatype for comparison, which in turn increases performance further.
    // We also add zero to each returned revision identifier, this will transform the DATETIME to the desired integer,
    // something that we can't do as efficient in PHP as we can do it with the database.
    $stmt = Database::getConnection()->prepare(<<<SQL
SELECT
  `id` + 0 AS `revisionId`,
  `user_id` AS `userId`,
  `data`
FROM `revisions`
WHERE `id` >= CAST(? AS DATETIME)
  AND `entity_id` = ?
  AND `revision_entity_id` = ?
ORDER BY `id` DESC
LIMIT 18446744073709551615 OFFSET 1
SQL
    );
    $stmt->bind_param("ddd", $oldRevisionId, $this->cur->originatorId, $originatorClassId);
    $stmt->execute();
    $stmt->bind_result($patchRevisionId, $patchUserId, $patch);

    // We need a diff instance to apply the patches.
    $diff = new Diff();

    try {
      while ($stmt->fetch()) {
        // Apply the patch from the fetched result.
        $revision = $diff->applyPatch($revision, $patch);

        // Check if this patch matches the newer requested revision and recreate the instance if it does.
        if ($patchRevisionId === $newRevisionId) {
          $this->new          = unserialize($revision);
          $this->new->id      = $patchRevisionId;
          $this->new->userId  = $patchUserId;
          $this->new->created = new \MovLib\Component\DateTime($this->new->id);
        }
      }
    }
    // Always free the result handle, even if an exception occurred during the patching process. This is important to
    // ensure that subsequent database calls won't fail with "commands out of sync". We don't catch the exception, the
    // kernel shall catch it and display or log it.
    finally {
      $stmt->close();
    }

    // The last patch returned by the database query is always the oldest revision.
    if ($patchRevisionId == $oldRevisionId) {
      $this->old          = unserialize($revision);
      $this->old->id      = $patchRevisionId;
      $this->old->userId  = $patchUserId;
      $this->old->created = new \MovLib\Component\DateTime($this->old->id);
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
      $this->new = $this->cur;
    }
  }

}
