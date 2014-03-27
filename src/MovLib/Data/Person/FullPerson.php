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
namespace MovLib\Data\Person;

use \MovLib\Data\Place;
use \MovLib\Data\Movie\FullMovie;
use \MovLib\Presentation\Error\NotFound;
use \MovLib\Partial\FormElement\InputSex;

/**
 * Contains all available information about a person.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FullPerson extends \MovLib\Data\Person\Person {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person's creation timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * The person's translated biography.
   *
   * @var string
   */
  public $biography;

  /**
   * The person's translated Wikipedia URL.
   *
   * @var string
   */
  public $wikipedia;

  /**
   * The person's aliases as numeric array.
   *
   * @var null|array
   */
  protected $aliases = false;

  /**
   * The person's place of birth.
   *
   * @var \MovLib\Data\Place
   */
  public $birthplace;

  /**
   * The person's translated cause of death.
   *
   * @var string
   */
  public $causeOfDeath;

  /**
   * The person's place of death.
   *
   * @var \MovLib\Data\Place
   */
  public $deathplace;

  /**
   * The person's external links as numeric array.
   *
   * @var array
   */
  protected $links = false;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Insert a new person into the database.
   *
   * @todo Index data with Elastic.
   * @return $this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function create() {
    $this->id = $this->query(
      "INSERT INTO `persons` SET
        `created` = CURRENT_TIMESTAMP,
        `dyn_biographies` = COLUMN_CREATE('{$this->intl->languageCode}', ?),
        `dyn_wikipedia`= COLUMN_CREATE('{$this->intl->languageCode}', ?),
        `dyn_image_descriptions` = '',
        `name` = ?,
        `sex` = ?,
        `birthdate` = ?,
        `born_name` = ?,
        `deathdate` = ?"
      ,
      "sssisss",
      [ $this->biography, $this->wikipedia, $this->name, $this->sex, $this->birthDate, $this->bornName, $this->deathDate ]
    )->insert_id;

    // Insert aliases.
    if ($this->aliases) {
      $query = null;
      $types = null;
      $params = [];
      $c = count($this->aliases);
      for ($i = 0; $i < $c; ++$i) {
        if ($query) {
          $query .= ", ";
        }
        $query .= "(?, ?)";
        $types .= "ds";
        $params[] = $this->id;
        $params[] = $this->aliases[$i];
      }
      $query = "INSERT INTO `persons_aliases` (`person_id`, `alias`) VALUES {$query}";
      $this->query($query, $types, $params);
    }

    // Insert external links.
    if ($this->links) {
      $query = null;
      $types = null;
      $params = [];
      $c = count($this->links);
      for ($i = 0; $i < $c; ++$i) {
        if ($query) {
          $query .= ", ";
        }
        $query .= "(?, ?, ?)";
        $types .= "dss";
        $params[] = $this->id;
        $params[] = $this->intl->languageCode;
        $params[] = $this->links[$i];
      }
      $query = "INSERT INTO `persons_links` (`person_id`, `language_code`, `url`) VALUES {$query}";
      $this->query($query, $types, $params);
    }

    return $this;
  }

  /**
   * Get the person's aliases as numeric array.
   *
   * @return null|array
   *   Numeric array containing the person's aliases or <code>NULL</code> if none were found.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getAliases() {
    if ($this->aliases === false) {
      $this->aliases = null;
      $result = $this->query(
        "SELECT
          `alias`
        FROM `persons_aliases`
        WHERE `person_id` = ?
        ORDER BY `alias` {$this->collations[$this->intl->languageCode]}",
        "d",
        [ $this->id ]
      )->get_result();
      while ($row = $result->fetch_row()) {
        $this->aliases[] = $row[0];
      }
    }
    return $this->aliases;
  }

  /**
   * Get the person's place of birth.
   *
   * @return null|\MovLib\Data\Place
   *   The person's place of birth or <code>NULL</code> if none was found.
   */
  public function getBirthPlace() {
    if (is_numeric($this->birthplace)) {
      $this->birthplace = new Place($this->birthplace);
    }
    return $this->birthplace;
  }

  /**
   * Get the person's place of death.
   *
   * @return null|\MovLib\Data\Place
   *   The person's place of death or <code>NULL</code> if none was found.
   */
  public function getDeathPlace() {
    if (is_numeric($this->deathplace)) {
      $this->deathplace = new Place($this->deathplace);
    }
    return $this->deathplace;
  }

  /**
   * Get the person's external links as numeric array.
   *
   * @return null|array
   *   Numeric array containing the person's external links or <code>NULL</code> if none were found.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getLinks() {
    if ($this->links === false) {
      $this->links = null;
      $result = $this->query(
        "SELECT `url` FROM `persons_links` WHERE `person_id` = ? AND `language_code` = ? ORDER BY `id` ASC",
        "ds",
        [ $this->id, $this->intl->languageCode ]
      )->get_result();
      while ($row = $result->fetch_row()) {
        $this->links[] = $row[0];
      }
    }
    return $this->links;
  }

  /**
   * Get the mysqli result for the person's movie cast.
   *
   * @return \mysqli_result
   *   The result containing the {@see \MovLib\Data\Movie\Cast} objects.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieCast() {
    return $this->query(
      "SELECT
        `id`,
        `movie_id` AS `movieId`,
        `person_id` AS `personId`,
        `job_id` AS `jobId`,
        IFNULL(COLUMN_GET(`dyn_role`, ? AS BINARY), COLUMN_GET(`dyn_role`, ? AS BINARY)) AS `roleName`,
        `alias_id` AS `alias`,
        `role_id` AS `role`
      FROM `movies_cast`
      WHERE `person_id` = ?",
      "ssd",
      [ $this->intl->languageCode, $this->intl->defaultLanguageCode, $this->id ]
    )->get_result();
  }

  /**
   * Get the mysqli result for the person's movie crew.
   *
   * @return \mysqli_result
   *   The result containing the {@see \MovLib\Data\Movie\Crew} objects.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieCrew() {
    return $this->query(
      "SELECT
        `mc`.`id`,
        `mc`.`movie_id` AS `movieId`,
        `mc`.`job_id` AS `jobId`,
        `mc`.`alias_id` AS `alias`,
        `mc`.`person_id` AS `personId`,
        IFNULL(COLUMN_GET(`j`.`dyn_names_sex0`, ? AS BINARY), COLUMN_GET(`j`.`dyn_names_sex0`, ? AS BINARY)) AS `jobNameSex0`,
        IFNULL(COLUMN_GET(`j`.`dyn_names_sex1`, ? AS BINARY), COLUMN_GET(`j`.`dyn_names_sex1`, ? AS BINARY)) AS `jobNameSex1`,
        IFNULL(COLUMN_GET(`j`.`dyn_names_sex2`, ? AS BINARY), COLUMN_GET(`j`.`dyn_names_sex2`, ? AS BINARY)) AS `jobNameSex2`
      FROM `movies_crew` AS `mc`
      INNER JOIN `jobs` AS `j`
        ON `j`.`id` = `mc`.`job_id`
      WHERE `mc`.`person_id` = ?",
      "ssssssd",
      [ $this->intl->languageCode, $this->intl->defaultLanguageCode, $this->intl->languageCode, $this->intl->defaultLanguageCode, $this->intl->languageCode, $this->intl->defaultLanguageCode, $this->id ]
    )->get_result();
  }

  /**
   * Get all movies this person has appeared in.
   *
   * @return null|array
   *   The movies this person has worked on or <code>NULL</code> if none were found.
   *
   *   Associative array containing the movie's identifier as key and {@see \MovLib\Stub\Data\Person\PersonMovie}
   *   objects as values.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovies() {
    $result = $this->query(
      "SELECT
        `movies`.`id` AS `movie_id`,
        `movies`.`deleted` AS `movie_deleted`,
        `movies`.`year` AS `movie_year`,
        `movies`.`mean_rating` AS `movie_rating_mean`,
        IFNULL(`dt`.`title`, `ot`.`title`) AS `movie_display_title`,
        IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `movie_display_title_language_code`,
        `ot`.`title` AS `movie_original_title`,
        `ot`.`language_code` AS `movie_original_title_language_code`,
        `p`.`poster_id` AS `movie_display_poster`,
        `movies_directors`.`job_id` AS `director_job_id`,
        `movies_cast`.`job_id` AS `cast_job_id`,
        `movies_cast`.`role_id`,
        IF (
          `movies_cast`.`job_id` IS NOT NULL,
          IFNULL(
            `role`.`name`,
            IFNULL(
              COLUMN_GET(`movies_cast`.`dyn_role`, ? AS BINARY),
              COLUMN_GET(`movies_cast`.`dyn_role`, '{$this->intl->defaultLanguageCode}' AS BINARY)
            )
          ),
          NULL
        ) AS `role_name`,
        `jobs`.`id` AS `crew_id`,
        IF (
          `movies_crew`.`job_id` IS NOT NULL,
          IFNULL(
            COLUMN_GET(`jobs`.`dyn_names_sex0`, ? AS BINARY),
            COLUMN_GET(`jobs`.`dyn_names_sex0`, '{$this->intl->defaultLanguageCode}' AS BINARY)
          ),
          NULL
        ) AS `job_title_crew0`,
        IF (
          `movies_crew`.`job_id` IS NOT NULL,
          IFNULL(
            COLUMN_GET(`jobs`.`dyn_names_sex1`, ? AS BINARY),
            COLUMN_GET(`jobs`.`dyn_names_sex1`, '{$this->intl->defaultLanguageCode}' AS BINARY)
          ),
          NULL
        ) AS `job_title_crew1`,
        IF (
          `movies_crew`.`job_id` IS NOT NULL,
          IFNULL(
            COLUMN_GET(`jobs`.`dyn_names_sex2`, ? AS BINARY),
            COLUMN_GET(`jobs`.`dyn_names_sex2`, '{$this->intl->defaultLanguageCode}' AS BINARY)
          ),
          NULL
        ) AS `job_title_crew2`
      FROM `movies`
        LEFT JOIN `movies_directors` AS `movies_directors`
          ON `movies_directors`.`movie_id` = `movies`.`id`
          AND `movies_directors`.`person_id` = ?
        LEFT JOIN `movies_cast`
          ON `movies_cast`.`movie_id` = `movies`.`id`
          AND `movies_cast`.`person_id` = ?
        LEFT JOIN `persons` AS `role`
          ON `role`.`id` = `movies_cast`.`role_id`
        LEFT JOIN `movies_crew`
          ON `movies_crew`.`movie_id` = `movies`.`id`
          AND `movies_crew`.`person_id` = ?
        LEFT JOIN `jobs`
          ON `jobs`.`id` = `movies_crew`.`job_id`
        LEFT JOIN `movies_display_titles` AS `mdt`
          ON `mdt`.`movie_id` = `movies`.`id`
          AND `mdt`.`language_code` = ?
        LEFT JOIN `movies_titles` AS `dt`
          ON `dt`.`movie_id` = `movies`.`id`
          AND `dt`.`id` = `mdt`.`title_id`
        LEFT JOIN `movies_original_titles` AS `mot`
          ON `mot`.`movie_id` = `movies`.`id`
        LEFT JOIN `movies_titles` AS `ot`
          ON `ot`.`movie_id` = `movies`.`id`
          AND `ot`.`id` = `mot`.`title_id`
        LEFT JOIN `display_posters` AS `p`
          ON `p`.`movie_id` = `movies`.`id`
          AND `p`.`language_code` = ?
      WHERE `movies`.`deleted` = false
        AND NOT (`movies_directors`.`person_id` IS NULL AND `movies_cast`.`person_id` IS NULL AND `movies_crew`.`person_id` IS NULL)
      ORDER BY `movies`.`year` DESC",
      "ssssdddss",
      [
        $this->intl->languageCode,
        $this->intl->languageCode,
        $this->intl->languageCode,
        $this->intl->languageCode,
        $this->id,
        $this->id,
        $this->id,
        $this->intl->languageCode,
        $this->intl->languageCode
      ]
    )->get_result();

    switch ($this->sex) {
      case InputSex::MALE:
        $roleSelf = $this->intl->t("Himself");
        break;

      case InputSex::FEMALE:
        $roleSelf = $this->intl->t("Herself");
        break;

      default:
        $roleSelf = $this->intl->t("Self");
        break;
    }

    $movies = null;
    while ($row = $result->fetch_assoc()) {
      // Initialize the {@see \MovLib\Stub\Data\Person\PersonMovie} stub and the
      // {@see \MovLib\Data\Movie\FullMovie} within.
      if (!isset($movies[$row["movie_id"]])) {
        $movies[$row["movie_id"]] = (object) [
          "movie"    => new FullMovie($this->diContainer),
          "director" => null,
          "cast"     => null,
          "roles"    => [],
          "jobs"     => [],
        ];

        $movies[$row["movie_id"]]->movie->id                        = $row["movie_id"];
        $movies[$row["movie_id"]]->movie->deleted                   = $row["movie_deleted"];
        $movies[$row["movie_id"]]->movie->year                      = $row["movie_year"];
        $movies[$row["movie_id"]]->movie->ratingMean                = $row["movie_rating_mean"];
        $movies[$row["movie_id"]]->movie->displayTitle              = $row["movie_display_title"];
        $movies[$row["movie_id"]]->movie->displayTitleLanguageCode  = $row["movie_display_title_language_code"];
        $movies[$row["movie_id"]]->movie->originalTitle             = $row["movie_original_title"];
        $movies[$row["movie_id"]]->movie->originalTitleLanguageCode = $row["movie_original_title_language_code"];
        $movies[$row["movie_id"]]->movie->displayPoster             = $row["movie_display_poster"];
        $movies[$row["movie_id"]]->movie->initFetchObject();
      }

      // Set the director job if present.
      if (!isset($movies[$row["movie_id"]]->director) && isset($row["director_job_id"])) {
        $movies[$row["movie_id"]]->director = (object) [
          "id"    => $row["director_job_id"],
          "title" => $this->getJobTitle($row["director_job_id"]),
        ];
      }

      // Set the cast job if present.
      if (!isset($movies[$row["movie_id"]]->cast) && isset($row["cast_job_id"])) {
        $movies[$row["movie_id"]]->cast = (object) [
          "id"    => $row["cast_job_id"],
          "title" => $this->getJobTitle($row["cast_job_id"]),
        ];
      }

      // Add a role if present.
      if (isset($row["role_name"])) {
        // Person is playing himself/herself.
        if ($row["role_id"] === $this->id) {
          $name = $roleSelf;
        }
        else {
          $name = $row["role_name"];
        }
        // Role is a real person.
        if ($row["role_id"]) {
          $key = $row["role_id"];
        }
        // Minor role without a person page.
        else {
          $key = $row["role_name"];
        }
        $movies[$row["movie_id"]]->roles[$key] = [ $row["role_id"], $name ];
      }

      // Add a crew job if present.
      if (isset($row["crew_id"]) && !isset($movies[$row["movie_id"]]->jobs[$row["crew_id"]])) {
        $movies[$row["movie_id"]]->jobs[$row["crew_id"]] = $row["job_title_crew{$this->sex}"];
      }
    }

    return $movies;
  }

  /**
   * Initialize new Person.
   *
   * @param integer $id
   *   The person's unique ID to load.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function init($id) {
    $this->id = $id;
    $stmt = $this->query(
      "SELECT
        `created`,
        `deleted`,
        IFNULL(COLUMN_GET(`dyn_biographies`, ? AS BINARY), COLUMN_GET(`dyn_biographies`, '{$this->intl->defaultLanguageCode}' AS BINARY)),
        IFNULL(COLUMN_GET(`dyn_wikipedia`, ? AS BINARY), COLUMN_GET(`dyn_wikipedia`, '{$this->intl->defaultLanguageCode}' AS BINARY)),
        `name`,
        `sex`,
        `birthdate`,
        `birthplace_id`,
        `born_name`,
        `cause_of_death_id`,
        `deathdate`,
        `deathplace_id`,
        `nickname`,
        `image_uploader_id`,
        `image_width`,
        `image_height`,
        `image_filesize`,
        `image_extension`,
        UNIX_TIMESTAMP(`image_changed`),
        IFNULL(COLUMN_GET(`dyn_image_descriptions`, ? AS BINARY), COLUMN_GET(`dyn_image_descriptions`, '{$this->intl->defaultLanguageCode}' AS BINARY)),
        `image_styles`
      FROM `persons`
      WHERE
        `id` = ?
      LIMIT 1",
      "sssd",
      [ $this->intl->languageCode, $this->intl->languageCode, $this->intl->languageCode, $id ]
    );
    $stmt->bind_result(
      $this->created,
      $this->deleted,
      $this->biography,
      $this->wikipedia,
      $this->name,
      $this->sex,
      $this->birthDate,
      $this->birthplace,
      $this->bornName,
      $this->causeOfDeath,
      $this->deathDate,
      $this->deathplace,
      $this->nickname,
      $this->uploaderId,
      $this->width,
      $this->height,
      $this->filesize,
      $this->extension,
      $this->changed,
      $this->description,
      $this->styles
    );
    if (!$stmt->fetch()) {
      throw new NotFound;
    }
    $stmt->close();
    $this->id = $id;

    // The person's photo name is always the person's identifier, so set it here.
    $this->filename = &$this->id;
    $this->initFetchObject();
  }

}
