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

use \MovLib\Exception\ClientException\NotFoundException;
use \MovLib\Data\Genre\Genre;

/**
 * @todo Description of GenreRevision
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class GenreRevision extends \MovLib\Data\AbstractRevisionEntity {

  /**
   * The genre entity type identifier.
   *
   * @var integer
   */
  const ENTITY_ID = 9;

  /**
   * Associative array containing all the genre's localized names, keyed by language code.
   *
   * @var array
   */
  public $names;

  /**
   * Associative array containing all the genre's localized descriptions, keyed by language code.
   *
   * @var array
   */
  public $descriptions;

  /**
   * Associative array containing all the genre's localized wikipedia links, keyed by language code.
   *
   * @var array
   */
  public $wikipedia;


  public function __construct(\MovLib\Core\DIContainer $diContainer, $genreId) {
    parent::__construct($diContainer);
    if ($genreId) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  IF (COLUMN_JSON(`dyn_names`) = '{}', NULL, COLUMN_JSON(`dyn_names`)),
  IF (COLUMN_JSON(`dyn_descriptions`) = '{}', NULL, COLUMN_JSON(`dyn_descriptions`)),
  IF (COLUMN_JSON(`dyn_wikipedia`) = '{}', NULL, COLUMN_JSON(`dyn_wikipedia`)),
  `changed`,
  `deleted`
FROM `genres`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $genreId);
      $stmt->execute();
      $stmt->bind_result($this->names, $this->descriptions, $this->wikipedia, $this->id, $this->deleted);
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Genre {$genreId}");
      }
      $this->entityId     = $genreId;
      $this->deleted      = (boolean) $this->deleted;
      foreach ([ "names", "descriptions", "wikipedia" ] as $property) {
        if (isset($this->$property)) {
          $this->$property = json_decode($this->$property, true);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    return array_merge(parent::__sleep(), [ "names", "descriptions", "wikipedia" ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    if (empty($this->entity)) {
      $this->entity = new Genre($this->diContainer);
      $this->entity->id          = $this->entityId;
      $this->entity->changed     = $this->id;
      $this->entity->deleted     = $this->deleted;
      $this->entity->name        = $this->names[$this->intl->languageCode];
      $this->entity->description = $this->descriptions[$this->intl->languageCode];
      $this->entity->wikipedia   = $this->wikipedia[$this->intl->languageCode];
      $reflector = new \ReflectionMethod($this->entity, "init");
      $reflector->setAccessible(true);
      $reflector->invoke($this->entity);
    }
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   *
   * @param \MovLib\Data\Genre\Genre $entity
   *   {@inheritdoc}
   * @return this
   */
  public function setEntity(\MovLib\Data\AbstractEntity $entity) {
    parent::setEntity($entity);
    if (!empty($entity->name)) {
      $this->names[$this->intl->languageCode]        = $entity->name;
    }
    if (!empty($entity->description)) {
      $this->descriptions[$this->intl->languageCode] = $entity->description;
    }
    if (!empty($entity->wikipedia)) {
      $this->wikipedia[$this->intl->languageCode]    = $entity->wikipedia;
    }
    return $this;
  }

}
