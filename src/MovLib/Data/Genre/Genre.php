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
namespace MovLib\Data\Genre;

use \MovLib\Core\Database\Database;
use \MovLib\Data\Genre\GenreRevision;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the genre entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Genre extends \MovLib\Data\AbstractEntity implements \MovLib\Data\Revision\EntityInterface {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The genre's name in default language.
   *
   * @var string
   */
  public $defaultName;

  /**
   * The genre's description in the current locale.
   *
   * @var null|string
   */
  public $description;

  /**
   * The genre's movie count.
   *
   * @var null|integer
   */
  public $movieCount;

  /**
   * The genre's name in the current locale (default locale as fallback).
   *
   * @var string
   */
  public $name;

  /**
   * The genre's series count.
   *
   * @var null|integer
   */
  public $seriesCount;

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "genres";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "genre";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new genre object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The genre's unique identifier to instantiate, defaults to <code>NULL</code> (no genre will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct($diContainer, $id = null) {
    if ($diContainer instanceof \MovLib\Core\DIContainer) {
      parent::__construct($diContainer);
    }
    else {
      $this->intl = $diContainer;
    }
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `id`,
  `changed`,
  `created`,
  `deleted`,
  IFNULL(
    COLUMN_GET(`dyn_names`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ),
  COLUMN_GET(`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR),
  COLUMN_GET(`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR),
  `count_movies`,
  `count_series`
FROM `genres`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->name,
        $this->description,
        $this->wikipedia,
        $this->movieCount,
        $this->seriesCount
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Genre {$id}");
      }
    }
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Static Methods


  public static function createFromId(\MovLib\Core\Intl $intl, $id) {
    // Guard against wrong usage and return empty instance.
    if (!$id) {
      return new static($intl);
    }
    $query = <<<SQL
SELECT
  `id`,
  `changed`,
  `created`,
  `deleted`,
  IFNULL(
    COLUMN_GET(`dyn_names`, '{$intl->languageCode}' AS CHAR),
    COLUMN_GET(`dyn_names`, '{$intl->defaultLanguageCode}' AS CHAR)
  ) AS `name`,
  COLUMN_GET(`dyn_descriptions`, '{$intl->languageCode}' AS CHAR) AS `description`,
  COLUMN_GET(`dyn_wikipedia`, '{$intl->languageCode}' AS CHAR) AS `wikipedia`,
  `count_movies` AS `movieCount`,
  `count_series` AS `seriesCount`
FROM `genres`
WHERE `id` = {$id}
LIMIT 1
SQL;
    return Database::getConnection()->fetchObject($query, static::class, [ $intl ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function createRevision($userId, $dateTime) {
    // Now we can create the new revision of ourself. Note that our id property is NULL if we're a new genre, so we don't
    // have to take care of any not found exceptions that might occur while creating the revision.
    $revision = GenreRevision::createFromId($this->id);

    // Update the just loaded revision with the new values.
    $revision->id                                        = $dateTime->formatInteger();
    $revision->created                                   = $dateTime;
    $revision->deleted                                   = $this->deleted;
    $revision->descriptions[$this->intl->languageCode]   = $this->description;
    $revision->names[$this->intl->languageCode]          = $this->name;
    $revision->userId                                    = $userId;
    $revision->wikipediaLinks[$this->intl->languageCode] = $this->wikipedia;

    // Don't forget that we might be a new genre and that we might have been created via a different system locale than
    // the default one, in which case the user was required to enter a default name. Of course we have to export that
    // as well to our revision.
    if (isset($this->defaultName)) {
      $revision->names[$this->intl->defaultLanguageCode] = $this->defaultName;
    }

    return $revision;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevision() {
    return GenreRevision::createFromId($this->id);
  }

  /**
   * {@inheritdoc}
   */
  public function setRevision(\MovLib\Data\Revision\RevisionEntityInterface $revisionEntity, $languageCode, $defaultLanguageCode) {
    $this->changed = $revisionEntity->created;
    $this->deleted = $revisionEntity->deleted;
    isset($revisionEntity->descriptions[$languageCode])   && ($this->description = $revisionEntity->descriptions[$languageCode]);
    isset($revisionEntity->wikipediaLinks[$languageCode]) && ($this->wikipedia = $revisionEntity->wikipediaLinks[$languageCode]);
    if (isset($revisionEntity->names[$languageCode])) {
      $this->name = $revisionEntity->names[$languageCode];
    }
    else {
      $this->name = $revisionEntity->names[$defaultLanguageCode];
    }
    return $this;
  }

}
