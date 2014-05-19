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
namespace MovLib\Data;

/**
 * Defines the revision object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Revision extends \MovLib\Core\AbstractDatabase {


  //-------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The new revision entity, depending on the construction parameters.
   *
   * @var \MovLib\Data\AbstractRevisionEntity
   */
  public $newRevision;

  /**
   * The old revision entity, depending on the construction parameters.
   *
   * @var \MovLib\Data\AbstractRevisionEntity
   */
  public $oldRevision;

  /**
   * The presenting presenter.
   *
   * @var \MovLib\Presentation\AbstractPresenter
   */
  public $presenter;

  /**
   * The class name of the revision class.
   *
   * @var string
   */
  public $revisionClass;

  /**
   * The revision entity's identifier.
   *
   * @var integer
   */
  public $revisionEntityId;

  /**
   * Active request instance.
   *
   * @var \MovLib\Core\HTTP\Request
   */
  public $request;

  /**
   * Active response instance.
   *
   * @var \MovLib\Core\HTTP\Response
   */
  public $response;

  /**
   * Active session instance.
   *
   * @var \MovLib\Core\HTTP\Session
   */
  public $session;


  //-------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new Revision.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainer
   *   The dependency injection container for the HTTP context.
   * @param string $entityClassName
   *   The entity's class name without namespace.
   * @param integer $entityId
   *   The entity's identifier.
   * @param integer $oldChanged [optional]
   *   The version to use for the <code>$oldRevision</code> property, will be patched automatically.
   *   Leave empty to create an empty revision object with the newest revision in the <code>$newRevision</code> property.
   * @param integer $newChanged [optional]
   *   The version to use for the <code>$newRevision</code> property, will be patched automatically.
   *   Leave empty to have the newest revision in the <code>$newRevision</code> property.
   * @param integer $entityTypeId
   *
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainer, $entityClassName, $entityId, $oldChanged = null, $newChanged = null) {
    parent::__construct($diContainer);
    $this->presenter = $diContainer->presenter;
    $this->request   = $diContainer->request;
    $this->response  = $diContainer->response;
    $this->session   = $diContainer->session;

    $this->revisionClass = "\\MovLib\\Data\\{$entityClassName}\\{$entityClassName}Revision";
    $this->revisionEntityId = constant("{$this->revisionClass}::ENTITY_ID");
    $this->newRevision = new $this->revisionClass($diContainer, $entityId);

    // Path back revision according to the parameters.
    if ($oldChanged) {
      $this->patch($oldChanged, $newChanged);
    }
  }

  /**
   * Apply transformations to a string as computed by @see \MovLib\Data\Revision::diff().
   *
   * @link https://github.com/cogpowered/FineDiff.git FineDiff
   *
   * @param string $from
   *   The string to apply the tranformations to.
   * @param string $patch
   *   The transformations to apply.
   * @return string
   *   The patched string.
   */
  public function applyPatch($from, $patch) {
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
   * @link https://github.com/cogpowered/FineDiff.git FineDiff
   *
   * @param string $fromText
   *   The old string.
   * @param string $toText
   *   The new string.
   * @return string
   *   The transformations needed to modify the from string to the to string.
   */
  public function diff($fromText, $toText) {
    // Initialize all variables needed beforehand and add the first parse job.
    $result     = [];
    $jobs       = [[ 0, mb_strlen($fromText), 0, mb_strlen($toText) ]];
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
          $insertText   = mb_substr($toText, $toSegmentStart, $toSegmentLength);
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
              mb_substr($fromText, $fromSegmentStart, $fromSegmentLength),
              mb_substr($toText, $toCopyStart, $copyLength)
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
              mb_substr($toText, $toSegmentStart, $toSegmentLength),
              mb_substr($fromText, $fromCopyStart, $copyLength)
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
        $insertText   = mb_substr($toText, $toSegmentStart, $toSegmentLength);
        $result[$fromSegmentStart * 4] = "d{$deleteLength}i{$insertLength}:{$insertText}";
      }
    }

    // Sort and return the string representation of the transformations.
    ksort($result, SORT_NUMERIC);
    return implode(array_values($result));
  }

  /**
   * Patch revisions back starting from the newest one and export them
   * into the <code>$oldRevision</code> and <code>$newRevision</code> properties.
   *
   * @param integer $oldChanged
   *   The older revision to retrieve, will be exported to the <code>$oldRevision</code> property.
   * @param integer $newChanged [optional]
   *   The newer revision to retrieve, will be exported to the <code>$newRevision</code> property.
   *   Leave empty to use the newest revision.
   */
  protected function patch($oldChanged, $newChanged = null) {
    $oldChanged = (integer) $oldChanged;
    $newChanged = (integer) $newChanged;
    // Retrieve the revision range back to the older revision.
    $id     = null;
    $userId = null;
    $patch  = null;
    $stmt   = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `id` + 0,
  `user_id`,
  `data`
FROM `revisions`
WHERE `id` >= CAST(? AS DATETIME)
ORDER BY `id` DESC
SQL
    );
    $stmt->bind_param("s", $oldChanged);
    $stmt->execute();
    $stmt->bind_result($id, $userId, $patch);

    // Do the patching.
    $entityId = $this->newRevision->entityId;
    $revision = serialize($this->newRevision);
    while ($stmt->fetch()) {
      $revision = $this->applyPatch($revision, $patch);
      if ($id === $oldChanged) {
        $this->oldRevision               = unserialize($revision);
        $this->oldRevision->id           = new DateTime($id);
        $this->oldRevision->entityTypeId = $this->revisionEntityId;
        $this->oldRevision->entityId     = $entityId;
        $this->oldRevision->userId       = $userId;
      }
      elseif ($id === $newChanged) {
        $this->newRevision               = unserialize($revision);
        $this->newRevision->id           = new DateTime($id);
        $this->newRevision->entityTypeId = $this->revisionEntityId;
        $this->newRevision->entityId     = $entityId;
        $this->newRevision->userId       = $userId;
      }
    }
    $stmt->close();

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
  public function saveRevision(\MovLib\Data\AbstractEntity $entity) {
    // The current new revision will be the new old revision.
    $old = serialize($this->newRevision);

    // The new current edition is the entity we just got.
    $this->newRevision->setEntity($entity);

    // Create a diff between both revisions, we have to pass the result per reference to bind_param() below.
    $diff = $this->diff(serialize($this->newRevision), $old);

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
      $mysqli->commit();
    }
    catch (\Exception $e) {
      // Drop the just create new revision, we couldn't update the database, therefore we don't need it anymore.
      unset($this->newRevision);
      $mysqli->rollback();
      throw $e;
    }
    finally {
      $mysqli->autocommit(true);
    }

    return $this;
  }

}
