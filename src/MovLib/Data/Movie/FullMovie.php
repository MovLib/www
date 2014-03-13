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
namespace MovLib\Data\Movie;

use \MovLib\Data\Person\Person;
use \MovLib\Presentation\Error\NotFound;

/**
 * Represents a single movie containing all available properties.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FullMovie extends \MovLib\Data\Movie\Movie {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  public $commit;
  public $countries;
  public $created;
  public $displayTagline;
  public $displayTaglineLanguageCode;
  public $genres;
  public $rank;
  public $rating;
  public $runtime;
  public $synopsis;
  public $taglines;
  public $title;
  public $votes;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate full movie by given identifier.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id
   *   The movie's unique identifier to load.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    global $db, $i18n;
    if ($id) {
      $this->id = $id;
      $stmt = $db->query(
        "SELECT
          `movies`.`created`,
          `movies`.`deleted`,
          COLUMN_GET(`movies`.`dyn_synopses`, ? AS CHAR),
          `movies`.`mean_rating`,
          `movies`.`rating`,
          `movies`.`votes`,
          `movies`.`commit`,
          `movies`.`rank`,
          `movies`.`runtime`,
          `movies`.`year`,
          IFNULL(`dt`.`title`, `ot`.`title`),
          IFNULL(`dt`.`language_code`, `ot`.`language_code`),
          `ot`.`title`,
          `ot`.`language_code`,
          `p`.`poster_id`
        FROM `movies`
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
        WHERE `movies`.`id` = ?
        LIMIT 1",
        "sssd",
        [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $this->id ]
      );
      $stmt->bind_result(
        $this->created,
        $this->deleted,
        $this->synopsis,
        $this->ratingMean,
        $this->rating,
        $this->votes,
        $this->commit,
        $this->rank,
        $this->runtime,
        $this->year,
        $this->displayTitle,
        $this->displayTitleLanguageCode,
        $this->originalTitle,
        $this->originalTitleLanguageCode,
        $this->displayPoster
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
    }
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the movie's cast.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return null|array
   *   Associative array containing the cast or <code>NULL</code> if no cast was found.
   *
   *   The array contains person's id as key and the {@see \MovLib\Data\Person\Person} as value.
   */
  public function getCast() {
    global $db, $i18n;

    $persons = null;
    $result = $db->query(
      "SELECT
        `movies_cast`.`person_id`,
        `persons`.`name` AS `person_name`,
        `persons`.`sex` AS `person_sex`,
        `persons`.`image_uploader_id` AS `person_uploader_id`,
        `persons`.`image_width` AS `person_width`,
        `persons`.`image_height` AS `person_height`,
        `persons`.`image_filesize` AS `person_filesize`,
        `persons`.`image_extension` AS `person_extension`,
        `persons`.`image_styles` AS `person_styles`,
        `movies_cast`.`role_id`,
        IFNULL(
          `role`.`name`,
          IFNULL(
            COLUMN_GET(`movies_cast`.`dyn_role`, ? AS BINARY),
            COLUMN_GET(`movies_cast`.`dyn_role`, '{$i18n->defaultLanguageCode}' AS BINARY)
          )
        ) AS `role_name`
      FROM `movies_cast`
      INNER JOIN `persons`
        ON `movies_cast`.`person_id` = `persons`.`id`
      LEFT JOIN `persons` AS `role`
        ON `movies_cast`.`role_id` = `role`.`id`
      WHERE `movies_cast`.`movie_id` = ? AND `persons`.`deleted` = FALSE
      ORDER BY `persons`.`name`{$db->collations[$i18n->languageCode]} ASC",
      "sd",
      [ $i18n->languageCode, $this->id ]
    )->get_result();
    while ($row = $result->fetch_assoc()) {
      // Instantiate and initialize a Person if it is not present yet.
      if (!isset($persons[$row["person_id"]])) {
        $persons[$row["person_id"]]             = new Person();
        $persons[$row["person_id"]]->id         = $row["person_id"];
        $persons[$row["person_id"]]->name       = $row["person_name"];
        $persons[$row["person_id"]]->sex        = $row["person_sex"];
        $persons[$row["person_id"]]->uploaderId = $row["person_uploader_id"];
        $persons[$row["person_id"]]->width      = $row["person_width"];
        $persons[$row["person_id"]]->height     = $row["person_height"];
        $persons[$row["person_id"]]->filesize   = $row["person_filesize"];
        $persons[$row["person_id"]]->extension  = $row["person_extension"];
        $persons[$row["person_id"]]->styles     = $row["person_styles"];
        $persons[$row["person_id"]]->init();
      }
      // Always append the roles.
      $persons[$row["person_id"]]->roles[] = [ "id" => $row["role_id"], "name" => $row["role_name"] ];
    }
    return $persons;
  }

  /**
   * Get the movie's countries.
   *
   * @global \MovLib\Data\Database $db
   * @return \mysqli_result
   *   The movie's countries.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getCountries() {
    global $db;
    return $db->query("SELECT `country_code` FROM `movies_countries` WHERE `movie_id` = ?", "d", [ $this->id ])->get_result();
  }

  /**
   * Get only the most basic information about the movie's cast.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $limit [optional]
   *   The number of cast members to retrieve.
   * @return null|array
   *   Numeric array containing the cast or <code>NULL</code> if no cast was found.
   *
   *   Every entry contains the offset "id", "name" and "nickname" for the corresponding properties of a person.
   */
  public function getCastLimited($limit = 5) {
    global $db, $i18n;
    $result = $db->query(
      "SELECT DISTINCT
        `persons`.`id`,
        `persons`.`name`,
        `persons`.`nickname`
      FROM `movies_cast`
      INNER JOIN `persons`
        ON `movies_cast`.`person_id` = `persons`.`id`
      WHERE `movies_cast`.`movie_id` = ? AND `persons`.`deleted` = FALSE
      ORDER BY `persons`.`name`{$db->collations[$i18n->languageCode]} ASC
      LIMIT ?",
      "dd",
      [ $this->id, $limit ]
    )->get_result();
    $cast = null;
    $cast = $result->fetch_all(MYSQLI_ASSOC);
    return $cast;
  }

  /**
   * Get the mysqli result for the movie's crew.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param null|integer $limit
   *   Limit of unique crew members to return, defaults to <code>8</code>. <code>NULL</code> means no limit.
   * @return \mysqli_result
   *   The result containing the {@see \MovLib\Data\Movie\Crew} objects.
   */
  public function getCrew($limit = 8) {
    global $db, $i18n;
    $query =
      "SELECT
        `mc`.`id`,
        `mc`.`movie_id` AS `movieId`,
        `mc`.`job_id` AS `jobId`,
        `mc`.`alias_id` AS `aliasId`,
        `mc`.`person_id` AS `personId`,
        IFNULL(COLUMN_GET(`j`.`dyn_titles`, ? AS BINARY), COLUMN_GET(`j`.`dyn_titles`, '{$i18n->defaultLanguageCode}' AS BINARY)),
        `image_uploader_id` AS `uploaderId`,
        `image_width` AS `width`,
        `image_height` AS `height`,
        `image_filesize` AS `filesize`,
        `image_extension` AS `extension`,
        `image_styles` AS `styles`
      FROM `movies_crew` AS `mc`
      INNER JOIN `jobs` AS `j`
        ON `j`.`id` = `mc`.`job_id`
      WHERE `mc`.`movie_id` = ?"
    ;
    $types = "sd";
    $params = [ $i18n->languageCode, $this->id ];

    if ($limit) {
      // @todo implement limit.
    }
    // @todo implement ordering by name (person and company)!
    return $db->query($query, $types, $params);
  }

  /**
   * Get the sorted movie's directors.
   *
   * @todo Order directors by weight not by name!
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The  sorted movie's directors.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getDirectors() {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `persons`.`id`,
        `persons`.`name`,
        `image_uploader_id` AS `uploaderId`,
        `image_width` AS `width`,
        `image_height` AS `height`,
        `image_filesize` AS `filesize`,
        `image_extension` AS `extension`,
        `image_styles` AS `styles`
      FROM `movies_directors`
        INNER JOIN `persons` ON `persons`.`id` = `movies_directors`.`person_id`
      WHERE `movies_directors`.`movie_id` = ?
      ORDER BY `persons`.`name`{$db->collations[$i18n->languageCode]} ASC",
      "d",
      [ $this->id ]
    )->get_result();
  }

  /**
   * Get only the most basic information about the movie's directors.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return null|array
   *   The sorted numeric array of the movie's directors or <code>NULL</code> if there are no directors.
   *
   *   Every entry contains the offset "id", "name" and "nickname" for the corresponding properties of a person.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getDirectorsLimited() {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `persons`.`id`,
        `persons`.`name`,
        `persons`.`nickname`,
        CASE `persons`.`sex`
          WHEN 1 THEN IFNULL(
            COLUMN_GET(`jobs`.`dyn_names_sex1`, ? AS BINARY),
            COLUMN_GET(`jobs`.`dyn_names_sex1`, '{$i18n->defaultLanguageCode}' AS BINARY)
          )
          WHEN 2 THEN IFNULL(
            COLUMN_GET(`jobs`.`dyn_names_sex2`, ? AS BINARY),
            COLUMN_GET(`jobs`.`dyn_names_sex2`, '{$i18n->defaultLanguageCode}' AS BINARY)
          )
          ELSE IFNULL(
            COLUMN_GET(`jobs`.`dyn_names_sex0`, ? AS BINARY),
            COLUMN_GET(`jobs`.`dyn_names_sex0`, '{$i18n->defaultLanguageCode}' AS BINARY)
          )
        END AS `job_name`
      FROM `movies_directors`
      INNER JOIN `persons`
        ON `persons`.`id` = `movies_directors`.`person_id`
      INNER JOIN `jobs`
        ON `jobs`.`id` = `movies_directors`.`job_id`
      WHERE `movies_directors`.`movie_id` = ?
      ORDER BY `persons`.`name`{$db->collations[$i18n->languageCode]} ASC",
      "sssd",
      [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $this->id ]
    )->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  /**
   * Get the translated and sorted movie's genres.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The translated and sorted movie's genres.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getGenres() {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `genres`.`id`,
        IFNULL(COLUMN_GET(`genres`.`dyn_names`, ? AS CHAR), COLUMN_GET(`genres`.`dyn_names`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `name`
      FROM `movies_genres`
        INNER JOIN `genres` ON `genres`.`id` = `movies_genres`.`genre_id`
      WHERE `movies_genres`.`movie_id` = ?
      ORDER BY `name`{$db->collations[$i18n->languageCode]} ASC",
      "sd",
      [ $i18n->languageCode, $this->id ]
    )->get_result();
  }

  /**
   * Get the mysqli result for the movie's languages.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The mysqli result for the movie's languages.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getLanguages() {
    global $db;
    return $db->query(
      "SELECT `language_code` FROM `movies_languages` WHERE `movie_id` = ?",
      "d",
      [ $this->id ]
    )->get_result();
  }

  /**
   * Get the the movie's trailers as associative array.
   *
   * The key is the already translated text of the trailer, the value is the trailer's url.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   The sorted array of movie's trailers.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getTrailers() {
    global $db, $i18n;
    $result = $db->query(
      "SELECT
        IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS BINARY), COLUMN_GET(`dyn_descriptions`, '{$i18n->defaultLanguageCode}' AS BINARY)) AS `description`,
        `language_code` as `languageCode`,
        `url`,
        `vq`.`name` AS `quality`
      FROM `movies_trailers`
      INNER JOIN `video_qualities` `vq`
        ON `vq`.`id` = `movies_trailers`.`video_quality_id`
      WHERE `movie_id` = ? AND `language_code` IN(?, ?)",
      "sdss",
      [ $i18n->languageCode, $this->id, $i18n->languageCode, "xx" ]
    )->get_result();
    $trailers = null;
    while ($row = $result->fetch_assoc()) {
      $host = str_replace("www.", "", parse_url($row["url"])["host"]);
      if ($row["description"]) {
        $trailers[$i18n->t("{0} – {1} ({2})", [ $row["description"], $host, $row["quality"] ])] = $row["url"];
      }
      else {
        $trailers[$i18n->t("{0} ({1})", [ $host, $row["quality"] ])] = $row["url"];
      }
    }
    if ($trailers) {
      $i18n->getCollator()->ksort($trailers, \Collator::SORT_STRING);
      return $trailers;
    }
  }

  /**
   * Rate this movie.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\User\Session $session
   * @param integer $rating
   *   The user's rating for this movie.
   * @return this
   * @throws \ErrorException
   * @throws \MovLib\Exception\DatabaseException
   */
  public function rate($rating) {
    global $db, $session;

    // Insert or update the user's rating for this movie.
    if ($this->getUserRating() === null) {
      $db->query("INSERT INTO `movies_ratings` SET `movie_id` = ?, `user_id` = ?, `rating` = ?", "ddi", [ $this->id, $session->userId, $rating ])->close();
      $this->votes++;
    }
    else {
      $db->query("UPDATE `movies_ratings` SET `rating` = ? WHERE `movie_id` = ? AND `user_id` = ?", "idd", [ $rating, $this->id, $session->userId ])->close();
    }

    // Update the mean rating of this movie.
    $db->query(
      "UPDATE `movies` SET `mean_rating` = (
        SELECT ROUND(SUM(`mr`.`rating`) / COUNT(`mr`.`rating`), 1) FROM `movies_ratings` AS `mr` WHERE `mr`.`movie_id` = ?
      ), `votes` = ? WHERE `id` = ?",
      "did",
      [ $this->id, $this->votes, $this->id ]
    )->close();

    // Get the updated mean rating for us.
    $this->ratingMean = $db->query("SELECT `mean_rating` FROM `movies` WHERE `id` = ? LIMIT 1", "d", [ $this->id ])->get_result()->fetch_row()[0];

    // Update the old rating with the new rating.
    $this->userRating = $rating;

    return $this;
  }

}
