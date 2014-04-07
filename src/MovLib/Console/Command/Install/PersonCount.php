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
namespace MovLib\Console\Command\Install;

use \MovLib\Exception\CountVerificationException;

/**
 * Count verification for persons.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PersonCount extends \MovLib\Console\Command\Install\AbstractEntityCountCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("entity-count-person");
    return parent::configure();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityName() {
    return "Person";
  }

  /**
   * {@inheritdoc}
   */
  protected function verifyCounts() {
    $result = $this->mysqli->query(<<<SQL
SELECT
  `id`,
  `award_count` AS `awardCount`,
  `movie_count` AS `movieCount`,
  `release_count` AS `releaseCount`,
  `series_count` AS `seriesCount`
FROM `persons`
ORDER BY `id` ASC
SQL
    );

    $personCountsActual = null;
    while ($row = $result->fetch_object()) {
      $personCountsActual[$row->id] = (object) [
        "award"   => (integer) $row->awardCount,
        "movie"   => (integer) $row->movieCount,
        "release" => (integer) $row->releaseCount,
        "series"  => (integer) $row->seriesCount,
      ];
    }
    $result->free();
    if (!$personCountsActual) {
      return;
    }

    $awardCountsExpected = $this->getAwardCounts();
    $movieCountsExpected = $this->getMovieCounts();
    $releaseCountsExpected = $this->getCounts([ "person_id" ], "release_id", "releases_crew");
    $seriesCountsExpected = $this->getSeriesCounts();
    $errors = null;
    $queries = null;

    // Verify the counts and schedule actions.
    foreach ($personCountsActual as $id => $counts) {
      $awardCountExpected = isset($awardCountsExpected[$id]) ? $awardCountsExpected[$id] : 0;
      $movieCountExpected = isset($movieCountsExpected[$id]) ? $movieCountsExpected[$id] : 0;
      $releaseCountExpected = isset($releaseCountsExpected[$id]) ? $releaseCountsExpected[$id] : 0;
      $seriesCountExpected = isset($seriesCountsExpected[$id]) ? $seriesCountsExpected[$id] : 0;

      if ($counts->award !== $awardCountExpected) {
        $errors[$id][] = "award: expected {$awardCountExpected} -> actual {$counts->award}";
      }
      if ($counts->movie !== $movieCountExpected) {
        $errors[$id][] = "movie: expected {$movieCountExpected} -> actual {$counts->movie}";
      }
      if ($counts->release !== $releaseCountExpected) {
        $errors[$id][] = "release: expected {$releaseCountExpected} -> actual {$counts->release}";
      }
      if ($counts->series !== $seriesCountExpected) {
        $errors[$id][] = "series: expected {$seriesCountExpected} -> actual {$counts->series}";
      }

      if (isset($errors[$id])) {
        $queries .= "UPDATE `persons` SET `award_count` = {$awardCountExpected}, `movie_count` = {$movieCountExpected}, `release_count` = {$releaseCountExpected}, `series_count` = {$seriesCountExpected} WHERE `id` = {$id};";
      }
    }

    if ($errors) {
      $this->mysqli->multi_query(rtrim($queries, ";"));
      throw new CountVerificationException($errors);
    }
  }

  /**
   * Get the award counts of persons.
   *
   * @return array
   *   Associative array with the person identifiers as keys and the award counts as values.
   */
  private function getAwardCounts() {
    $awardKeys   = [];
    $awardCounts = [];

    // Get the awards.
    $result = $this->mysqli->query(<<<SQL
SELECT
  `persons`.`id`,
  `movies_awards`.`award_id` AS `movieAwardId`,
  `movies_awards`.`award_category_id` AS `movieAwardCategoryId`,
  `movies_awards`.`event_id` AS `movieEventId`,
  `series_awards`.`award_id` AS `seriesAwardId`,
  `series_awards`.`award_category_id` AS `seriesAwardCategoryId`,
  `series_awards`.`event_id` AS `seriesEventId`,
  `persons_awards`.`award_id` AS `personAwardId`,
  `persons_awards`.`award_category_id` AS `personAwardCategoryId`,
  `persons_awards`.`event_id` AS `personEventId`
FROM `persons`
LEFT JOIN `movies_awards`
  ON `persons`.`id` = `movies_awards`.`person_id`
  AND `movies_awards`.`won` = true
LEFT JOIN `series_awards`
  ON `persons`.`id` = `series_awards`.`person_id`
  AND `series_awards`.`won` = true
LEFT JOIN `persons_awards`
  ON `persons`.`id` = `persons_awards`.`person_id`
  AND `persons_awards`.`won` = true
WHERE `movies_awards`.`person_id` IS NOT NULL
  OR `series_awards`.`person_id` IS NOT NULL
  OR `persons_awards`.`person_id` IS NOT NULL
ORDER BY `persons`.`id` ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      foreach ([
          "{$row->movieAwardId}-{$row->movieAwardCategoryId}-{$row->movieEventId}",
          "{$row->seriesAwardId}-{$row->seriesAwardCategoryId}-{$row->seriesEventId}",
          "{$row->personAwardId}-{$row->personAwardCategoryId}-{$row->personEventId}"
        ] as $key) {
        if ($key != "--" && empty($awardKeys[$row->id][$key])) {
          $awardKeys[$row->id][$key] = true;
          $awardCounts[$row->id]     = isset($awardCounts[$row->id]) ? $awardCounts[$row->id] + 1 : 1;
        }
      }
    }
    $result->free();

    return $awardCounts;
  }

  /**
   * Get the movie counts of persons.
   *
   * @return array
   *   Associative array with the person identifiers as keys and the movie counts as values.
   */
  private function getMovieCounts() {
    $movieKeys   = [];
    $movieCounts = [];

    $result = $this->mysqli->query(<<<SQL
SELECT
  `persons`.`id`,
  `movies_directors`.`movie_id` AS `directorMovieId`,
  `movies_cast`.`movie_id` AS `castMovieId`,
  `movies_crew`.`movie_id` AS `crewMovieId`
FROM `persons`
LEFT JOIN `movies_directors`
  ON `movies_directors`.`person_id` = `persons`.`id`
LEFT JOIN `movies_cast`
  ON `movies_cast`.`person_id` = `persons`.`id`
LEFT JOIN `movies_crew`
  ON `movies_crew`.`person_id` = `persons`.`id`
WHERE `movies_directors`.`person_id` IS NOT NULL
  OR `movies_cast`.`person_id` IS NOT NULL
  OR `movies_crew`.`person_id` IS NOT NULL
ORDER BY `persons`.`id` ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      foreach ([ $row->directorMovieId, $row->castMovieId, $row->crewMovieId ] as $movieId) {
        if (isset($movieId) && empty($movieKeys[$row->id][$movieId])) {
          $movieKeys[$row->id][$movieId] = true;
          $movieCounts[$row->id]         = isset($movieCounts[$row->id]) ? $movieCounts[$row->id] + 1 : 1;
        }
      }
    }
    $result->free();

    return $movieCounts;
  }

  /**
   * Get the series counts of persons.
   *
   * @return array
   *   Associative array with the person identifiers as keys and the series counts as values.
   */
  private function getSeriesCounts() {
    $seriesKeys   = [];
    $seriesCounts = [];

    $result = $this->mysqli->query(<<<SQL
SELECT
  `persons`.`id`,
  `movies_directors`.`movie_id` AS `directorSeriesId`,
  `movies_cast`.`movie_id` AS `castSeriesId`,
  `movies_crew`.`movie_id` AS `crewSeriesId`
FROM `persons`
LEFT JOIN `episodes_directors`
  ON `episodes_directors`.`person_id` = `persons`.`id`
LEFT JOIN `episodes_cast`
  ON `episodes_cast`.`person_id` = `persons`.`id`
LEFT JOIN `episodes_crew`
  ON `episodes_crew`.`person_id` = `persons`.`id`
WHERE `episodes_directors`.`person_id` IS NOT NULL
  OR `episodes_cast`.`person_id` IS NOT NULL
  OR `episodes_crew`.`person_id` IS NOT NULL
ORDER BY `persons`.`id` ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      foreach ([ $row->directorSeriesId, $row->castSeriesId, $row->crewSeriesId ] as $seriesId) {
        if (isset($seriesId) && empty($seriesKeys[$row->id][$seriesId])) {
          $seriesKeys[$row->id][$seriesId] = true;
          $seriesCounts[$row->id]         = isset($seriesCounts[$row->id]) ? $seriesCounts[$row->id] + 1 : 1;
        }
      }
    }
    $result->free();

    return $seriesCounts;
  }

}
