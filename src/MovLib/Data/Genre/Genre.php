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

/**
 * Defines the genre entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Genre extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The entity type used to store revisions.
   *
   * @var int
   */
  const REVISION_ENTITY_TYPE = 9;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The timestamp on which this genre was changed.
   *
   * @var integer
   */
  public $changed;

  /**
   * The timestamp on which this genre was created.
   *
   * @var integer
   */
  public $created;

  /**
   * The genre's name in default language.
   *
   * @var string
   */
  public $defaultName;

  /**
   * The genre's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The genre's description in the current locale.
   *
   * @var null|string
   */
  public $description;

  /**
   * The genre's unique identifier.
   *
   * @var integer
   */
  public $id;

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
   * The translated route of this event.
   *
   * @var string
   */
  public $route;

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
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `genres`.`id` AS `id`,
  `genres`.`changed` AS `changed`,
  `genres`.`created` AS `created`,
  `genres`.`deleted` AS `deleted`,
  IFNULL(
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `name`,
  COLUMN_GET(`genres`.`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR) AS `description`,
  COLUMN_GET(`genres`.`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR) AS `wikipedia`,
  `genres`.`count_movies` AS `movieCount`,
  `genres`.`count_series` AS `seriesCount`
FROM `genres`
WHERE `genres`.`id` = ?
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


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Update the genre.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function commit() {
    $stmt = $this->getMySQLi()->prepare(<<<SQL
UPDATE `genres` SET
  `dyn_descriptions` = COLUMN_ADD(`dyn_descriptions`, '{$this->intl->languageCode}', ?),
  `dyn_names`        = COLUMN_ADD(`dyn_names`, '{$this->intl->languageCode}', ?),
  `dyn_wikipedia`    = COLUMN_ADD(`dyn_wikipedia`, '{$this->intl->languageCode}', ?)
WHERE `id` = {$this->id}
SQL
    );
    $stmt->bind_param(
      "sss",
      $this->description,
      $this->name,
      $this->wikipedia
    );
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * Create new genre.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function create() {
    $mysqli = $this->getMySQLi();

    if ($this->intl->languageCode === $this->intl->defaultLanguageCode) {
      $stmt = $mysqli->prepare(<<<SQL
INSERT INTO `genres` (
  `dyn_descriptions`,
  `dyn_names`,
  `dyn_wikipedia`
) VALUES (
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?),
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?),
  COLUMN_CREATE('{$this->intl->defaultLanguageCode}', ?)
);
SQL
      );
      $stmt->bind_param(
        "sss",
        $this->description,
        $this->name,
        $this->wikipedia
      );
    }
    else {
      $stmt = $mysqli->prepare(<<<SQL
INSERT INTO `genres` (
  `dyn_descriptions`,
  `dyn_names`,
  `dyn_wikipedia`
) VALUES (
  COLUMN_CREATE('{$this->intl->languageCode}', ?),
  COLUMN_CREATE(
    '{$this->intl->defaultLanguageCode}', ?,
    '{$this->intl->languageCode}', ?
  ),
  COLUMN_CREATE('{$this->intl->languageCode}', ?)
);
SQL
      );
      $stmt->bind_param(
        "ssss",
        $this->description,
        $this->defaultName,
        $this->name,
        $this->wikipedia
      );
    }

    $stmt->execute();
    $this->id = $stmt->insert_id;

    return $this->init();
  }

}
