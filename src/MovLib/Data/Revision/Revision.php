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
namespace MovLib\Data\Revision;

/**
 * Defines the revision object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Revision extends \MovLib\Core\AbstractDatabase {


  //-------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The current revision entity.
   *
   * @var \MovLib\Data\Revision\AbstractEntity
   */
  protected $cur;

  /**
   * The new revision entity.
   *
   * @var \MovLib\Data\Revision\AbstractEntity
   */
  public $new;

  /**
   * The old revision entity.
   *
   * @var \MovLib\Data\Revision\AbstractEntity
   */
  public $old;

  /**
   * The revision entity's class name.
   *
   * @var string
   */
  protected $revisionEntityClassName;

  /**
   * The revision entity's identifier.
   *
   * @var integer
   */
  protected $revisionEntityId;


  //-------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new revision object.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainer
   *   {@inheritdoc}
   * @param string $entityName
   *   The entity's name, e.g. <code>"Genre"</code>.
   * @param integer $entityId
   *   The entity's unique identifier.
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainer, $entityName, $entityId) {
    parent::__construct($diContainer);

    // Build the class name, get the entity's identifier and instantiate current revision.
    $this->revisionEntityClassName = "\\MovLib\\Data\\{$entityName}\\{$entityName}Revision";

    // @devStart
    // @codeCoverageIgnoreStart
    assert(class_exists($this->revisionEntityClassName), "Couldn't find concrete entity revision class.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    $this->revisionEntityId = constant("{$this->revisionEntityClassName}::ENTITY_ID");
    $this->cur = new $this->revisionClass($diContainer, $entityId);
  }


  //-------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Apply transformations to a string as computed by {@see \MovLib\Data\Revision::diff()}.
   *
   * @link https://github.com/cogpowered/FineDiff/
   * @param string $from
   *   The string to apply the tranformations to.
   * @param string $patch
   *   The transformations to apply.
   * @return string
   *   The patched string.
   */
  protected function applyPatch($from, $patch) {
    $output      = null;
    $patchLength = mb_strlen($patch);
    $fromOffset  = $patchOffset = 0;

    while ($patchOffset < $patchLength) {
      $transformation = mb_substr($patch, $patchOffset, 1);
      ++$patchOffset;
      $n = (integer) mb_substr($patch, $patchOffset);

      if ($n) {
        $patchOffset += strlen((string) $n);
      }
      else {
        $n = 1;
      }

      // Since we only use text, we can ignore all operations except copy and insert.
      if ($transformation == "c") {
        // Copy $n characters from the original string.
        $output .= mb_substr($from, $fromOffset, $n);
        $fromOffset += $n;
      }
      // Insert $n characters from the patch.
      elseif ($transformation == "i") {
        $output .= mb_substr($patch, ++$patchOffset, $n);
        $patchOffset += $n;
      }
      // Ignore $n characters for other operations.
      else {
        $fromOffset += $n;
      }
    }

    return $output;
  }

  /**
   * Compute the difference between two strings as transformations.
   *
   * Simplifies cogpowered's implementation, since we only need to compute differences at character level and don't
   * need to be extensible.
   *
   * @link https://github.com/cogpowered/FineDiff/
   * @param string $from
   *   The old string.
   * @param string $to
   *   The new string.
   * @return string
   *   The transformations needed to modify the <var>$from</var> string to the <var>$to</var> string.
   */
  public function diff($from, $to) {
    // Initialize all variables needed beforehand and add the first parse job.
    $result     = [];
    $jobs       = [[ 0, mb_strlen($from), 0, mb_strlen($to) ]];
    $copyLength = $fromCopyStart = $toCopyStart = 0;

    while ($job = array_pop($jobs)) {
      list($fromSegmentStart, $fromSegmentEnd, $toSegmentStart, $toSegmentEnd) = $job;
      $fromSegmentLength = $fromSegmentEnd - $fromSegmentStart;
      $toSegmentLength   = $toSegmentEnd - $toSegmentStart;

      // Detect simple insert/delete operations and continue with next job.
      if ($fromSegmentLength === 0 || $toSegmentLength === 0) {
        if ($fromSegmentLength > 0) {
          $deleteLength = $fromSegmentLength === 1 ? null : $fromSegmentLength;
          $result[$fromSegmentStart * 4 + 0] = "d{$deleteLength}";
        }
        elseif ($toSegmentLength > 0) {
          $insertText   = mb_substr($to, $toSegmentStart, $toSegmentLength);
          $insertLength = mb_strlen($insertText);
          $insertLength = $insertLength === 1 ? null : $insertLength;
          $result[$fromSegmentStart * 4 + 1] = "i{$insertLength}:{$insertText}";
        }
        continue;
      }

      // Determine start and length for a copy transformation.
      if ($fromSegmentLength >= $toSegmentLength) {
        $copyLength = $toSegmentLength;

        while ($copyLength) {
          $toCopyStartMax = $toSegmentEnd - $copyLength;

          for ($toCopyStart = $toSegmentStart; $toCopyStart <= $toCopyStartMax; ++$toCopyStart) {
            $fromCopyStart = mb_strpos(
              mb_substr($from, $fromSegmentStart, $fromSegmentLength),
              mb_substr($to, $toCopyStart, $copyLength)
            );

            if ($fromCopyStart !== false) {
              $fromCopyStart += $fromSegmentStart;
              break 2;
            }
          }

          --$copyLength;
        }
      }
      else {
        $copyLength = $fromSegmentLength;

        while ($copyLength) {
          $fromCopyStartMax = $fromSegmentEnd - $copyLength;

          for ($fromCopyStart = $fromSegmentStart; $fromCopyStart <= $fromCopyStartMax; ++$fromCopyStart) {
            $toCopyStart = mb_strpos(
              mb_substr($to, $toSegmentStart, $toSegmentLength),
              mb_substr($from, $fromCopyStart, $copyLength)
            );

            if ($toCopyStart !== false) {
              $toCopyStart += $toSegmentStart;
              break 2;
            }
          }

          --$copyLength;
        }
      }

      // A copy operation is possible.
      if ($copyLength) {
        $copyTranformationLength = $copyLength === 1 ? null : $copyLength;
        $result[$fromCopyStart * 4 + 2] = "c{$copyTranformationLength}";
        // Add new jobs for the parts of the segment before and after the copy part.
        $jobs[] = [ $fromSegmentStart, $fromCopyStart, $toSegmentStart, $toCopyStart ];
        $jobs[] = [ $fromCopyStart + $copyLength, $fromSegmentEnd, $toCopyStart + $copyLength, $toSegmentEnd ];
      }
      // No copy possible, replace everything.
      else {
        $deleteLength = $fromSegmentLength === 1 ? null : $fromSegmentLength;
        $insertLength = $toSegmentLength === 1 ? null : $toSegmentLength;
        $insertText   = mb_substr($to, $toSegmentStart, $toSegmentLength);
        $result[$fromSegmentStart * 4] = "d{$deleteLength}i{$insertLength}:{$insertText}";
      }
    }

    // Sort and return the string representation of the transformations.
    ksort($result, SORT_NUMERIC);
    return implode(array_values($result));
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
   * @param integer $newRevisionId [optional]
   *   The newer revision to retrieve, will be exported to the <var>Revision::$newRevision</var> property. The property
   *   isn't overwritten if no value is passed (default) and thus contains the real current revision that is stored in
   *   the database.
   * @return this
   * @throws \mysqli_sql_exception
   *   If retrieving of the older revisions from the database fails.
   * @throws \ErrorException
   *   If unserializing of a patched revision fails.
   */
  protected function patch($oldRevisionId, $newRevisionId = null) {
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

    // Ensure both requested revision identifiers are actual integer values for faster comparison during the patch
    // processing. It doesn't matter at this point that we cast the NULL value to an integer, because it can't match any
    // revision identifier, as they're always full date-time-integers (YYYYMMDDHHMMSS).
    $oldRevisionId = (integer) $oldRevisionId;
    $newRevisionId = (integer) $newRevisionId;

    // Serialize the current revision entity, patching starts from here.
    $revision = serialize($this->cur);

    // Retrieve the revision range back to the requested old revision, which will automatically include the newer
    // revision as well (if passed). Note that we cast the passed old revision identifier to a DATETIME and we do that
    // in the WHERE clause of the query. This means that the database only has to perform the cast once, before starting
    // the actual search and it uses the correct datatype for comparison, which in turn increases performance further.
    // We also add zero to each returned revision identifier, this will transform the DATETIME to the desired integer,
    // something that we can't do as efficient in PHP as we can do it with the database.
    $result = $this->getMySQLi()->query(<<<SQL
SELECT
  `id` + 0 AS `revisionId`,
  `user_id` AS `userId`,
  `data`
FROM `revisions`
WHERE `id` >= CAST({$oldRevisionId} AS DATETIME)
  AND `entity_id` = {$this->cur->entityId}
  AND `revision_entity_id` = {$this->revisionEntityId}
ORDER BY `id` DESC
SQL
    );

    try {
      while ($row = $result->fetch_object()) {
        // Copy the row's content into another local variable, this ensures that we'll have the last patch from the
        // result and not NULL (which is the abort condition for this loop). We can skip the comparison for the old
        // revision by doing so.
        $patch = $row;

        // Apply the patches from the result.
        $revision = $this->applyPatch($revision, $patch->data);

        // Check if this patch matches the newer requested revision and recreate the instance if it does.
        if ($patch->id === $newRevisionId) {
          $this->new = unserialize($revision);
        }
      }

      // The last patch returned by the database query always matches the requested old revision.
      $this->old = unserialize($revision);
    }
    // Always free the result handle, even if an exception occurred during the patching process. This is important to
    // ensure that subsequent database calls won't fail with "commands out of sync". We don't catch the exception, the
    // kernel shall catch it and display or log it.
    finally {
      $result->free();
    }

    return $this;
  }

  /**
   * Save a new entity revision.
   *
   * @param \MovLib\Data\AbstractEntity $entity
   *   The entity with the new values.
   * @return $this
   * @throws \MovLib\Data\Exception
   */
  public function save(\MovLib\Data\AbstractEntity $entity) {
    // The current new revision will be the new old revision.
    $old = serialize($this->new);

    // Set the new changed date and time.
    $entity->changed = new DateTime("@{$this->request->time}");

    // The new current edition is the entity we just got.
    $this->new->setEntity($entity);

    // Create a diff between both revisions, we have to pass the result per reference to bind_param() below.
    $diff = $this->diff(serialize($this->new), $old);

    $mysqli = $this->getMySQLi();
    try {
      $mysqli->autocommit(false);
      $entity->commit();
      $stmt = $mysqli->prepare("INSERT INTO `revisions` (`revision_entity_id`, `entity_id`, `id`, `user_id`, `data`) VALUES (?, ?, CAST(? AS DATETIME), ?, ?)");
      $stmt->bind_param(
        "ddsds",
        $this->revisionEntityId,
        $entity->id,
        $entity->changed,
        $this->session->userId,
        $diff
      );
      $stmt->execute();
      $stmt->close();
      $mysqli->commit();
      $this->new->indexSearch();
    }
    catch (\Exception $e) {
      // Drop the just create new revision, we couldn't update the database, therefore we don't need it anymore.
      unset($this->new);
      $mysqli->rollback();
      throw $e;
    }
    finally {
      $mysqli->autocommit(true);
    }

    return $this;
  }

}
