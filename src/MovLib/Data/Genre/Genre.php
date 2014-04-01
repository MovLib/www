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
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Genre extends \MovLib\Data\AbstractEntity {


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
   * The genre's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The genre's description in the current display language.
   *
   * @var string
   */
  public $description;

  /**
   * The genre's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The genre's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The genre's movie count.
   *
   * @var integer
   */
  public $movieCount = 0;

  /**
   * The translated route of this genre.
   *
   * @var string
   */
  public $route;

  /**
   * The genre's series count.
   *
   * @var integer
   */
  public $seriesCount = 0;

  /**
   * The genre’s translated Wikipedia link.
   *
   * @var string
   */
  public $wikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Initialize


  /**
   * Instantiate new genre.
   *
   * @param integer $id
   *   The genre's unique identifier, omit to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function init($id) {
    $stmt = $this->getMySQLi()->prepare("
      {$this->getDefaultQuery()}
      WHERE
        `id` = ?
      LIMIT 1"
    );
    $stmt->bind_param("ssd", $this->intl->languageCode, $this->intl->languageCode, $id);
    $stmt->execute();
    $stmt->bind_result(
      $this->changed,
      $this->created,
      $this->deleted,
      $this->description,
      $this->id,
      $this->name,
      $this->wikipedia
    );
    $found = $stmt->fetch();
    $stmt->close();
    if ($found === null) {
      throw new NotFoundException("Couldn't find genre for '{$id}'!");
    }

    // @todo Store counts as columns in table.
    $this->movieCount  = $this->getCount("movies_genres", "DISTINCT `movie_id`");
    $this->seriesCount = $this->getCount("series_genres", "DISTINCT `series_id`");

    return $this->initFetchObject();
  }

  /**
   * Initialize after instantiation via PHP's built in <code>\mysqli_result::fetch_object()}
   */
  public function initFetchObject() {
    $this->deleted = (boolean) $this->deleted;
    $this->route   = $this->intl->r("/genre/{0}", $this->id);
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * The default query.
   *
   * @return string
   */
  public function getDefaultQuery() {
    return
      "SELECT
        `changed`,
        `created`,
        `deleted`,
        COLUMN_GET(`dyn_descriptions`, ? AS CHAR) AS `description`,
        `id`,
        COLUMN_GET(`dyn_names`, ? AS CHAR) AS `name`,
        IFNULL(COLUMN_GET(`dyn_wikipedia`, ? AS CHAR), COLUMN_GET(`dyn_wikipedia`, '{$this->intl->defaultLanguageCode}' AS CHAR)) AS `wikipedia`
      FROM `genres`"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluralName() {
    return "genres";
  }

  /**
   * {@inheritdoc}
   */
  public function getSingularName() {
    return "genre";
  }

 /**
   * Get the mysqli result for all movies connected to this genre.
   *
   * @return \mysqli_result
   *   The mysqli result for all movies connected to this genre.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMoviesResult() {
    return $this->query(
      "SELECT
        `movies`.`id` AS `id`,
        `movies`.`year` AS `year`,
        IFNULL(`dt`.`title`, `ot`.`title`) AS `displayTitle`,
        IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `displayTitleLanguageCode`,
        `ot`.`title` AS `originalTitle`,
        `ot`.`language_code` AS `originalTitleLanguageCode`,
        `p`.`poster_id` AS `displayPoster`
      FROM `movies`
        LEFT JOIN `movies_genres`
          ON `movies`.`id` = `movies_genres`.`movie_id`
        LEFT JOIN `movies_display_titles` AS `mdt`
          ON `mdt`.`movie_id` = `movies`.`id`
          AND `mdt`.`language_code` = ?
        LEFT JOIN `movies_titles` AS `dt`
          ON `dt`.`id` = `mdt`.`title_id`
        LEFT JOIN `movies_original_titles` AS `mot`
          ON `mot`.`movie_id` = `movies`.`id`
        LEFT JOIN `movies_titles` AS `ot`
          ON `ot`.`id` = `mot`.`title_id`
        LEFT JOIN `display_posters` AS `p`
          ON `p`.`movie_id` = `movies`.`id`
          AND `p`.`language_code` = ?
      WHERE `movies_genres`.`genre_id` = ?
      ORDER BY `displayTitle` DESC",
      "ssd",
      [ $this->intl->languageCode, $this->intl->languageCode, $this->id ]
    )->get_result();
  }

}
