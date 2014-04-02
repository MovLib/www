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
final class Genre extends \MovLib\Data\AbstractDatabaseEntity {
  use \MovLib\Data\Genre\GenreTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


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
   * The genre's wikipedia link for the current locale.
   *
   * @var null|string
   */
  public $wikipedia;


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
    COLUMN_GET(`genres`,`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `name`,
  COLUMN_GET(`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR) AS `description`,
  COLUMN_GET(`genres`.`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR) AS `wikipedia`,
  COUNT(DISTINCT `movies_genres`.`movies_id`) AS `movieCount`,
  COUNT(DISTINCT `series_genres`.`series_id`) AS `seriesCount`
FROM `genres`
  LEFT JOIN `movies_genres` ON `movies_genres`.`genre_id` = `genres`.`id`
  LEFT JOIN `series_genres` ON `series_genres`.`genre_id` = `genres`.`id`
WHERE `genres`.`id` = ?
GROUP BY `id`,`created`,`changed`,`deleted`,`name`,`description`
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



}
