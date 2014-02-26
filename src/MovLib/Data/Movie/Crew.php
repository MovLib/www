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

/**
 * Represents a single cast appearance of a person or company.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Crew extends \MovLib\Data\Job {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The crew's alias.
   * @var string
   */
  public $alias;

  /**
   * The crew company's unique ID.
   *
   * @var integer
   */
  public $companyId;

  /**
   * The crew's unique ID.
   *
   * @var integer
   */
  public $id;

  /**
   * The crew job's unique ID.
   *
   * @var integer
   */
  public $jobId;

  /**
   * The crew's translated job title.
   *
   * @var string
   */
  public $jobTitle;

  /**
   * The movie's unique ID this crew has worked on.
   *
   * @var integer
   */
  public $movieId;

  /**
   * The crew person's unique ID.
   *
   * @var integer
   */
  public $personId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new crew.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $crewId [optional]
   *   The unique crew's identifier to load, omit to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($crewId = null) {
    global $db, $i18n;

    // Try to load the cast for the given identifier.
    if ($crewId) {
      $stmt = $db->query(
        "SELECT
          `mc`.`id`,
          `mc`.`movie_id`,
          `mc`.`job_id`,
          `mc`.`alias_id`,
          `mc`.`company_id`,
          `mc`.`person_id`,
          IFNULL(COLUMN_GET(`j`.`dyn_titles`, ? AS BINARY), COLUMN_GET(`j`.`dyn_titles`, ? AS BINARY))
        FROM `movies_crew` AS `mc`
        INNER JOIN `jobs` AS `j`
          ON `j`.`id` = `mc`.`job_id`
        WHERE `mc`.`id` = ?",
        "ssd",
        [ $i18n->languageCode, $i18n->defaultLanguageCode, $crewId ]
      );
      $stmt->bind_result(
        $this->id,
        $this->movieId,
        $this->jobId,
        $this->alias,
        $this->companyId,
        $this->personId,
        $this->jobTitle
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
      $this->id = $crewId;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get all crew appearances of a person.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $personId
   *   The person's unique ID.
   * @return \mysqli_result
   *   The person's crew appearances.
   */
  public static function getPersonCrew($personId) {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `mc`.`id`,
        `mc`.`movie_id` AS `movieId`,
        `mc`.`job_id` AS `jobId`,
        `mc`.`alias_id` AS `alias`,
        `mc`.`person_id` AS `personId`,
        IFNULL(COLUMN_GET(`j`.`dyn_titles`, ? AS BINARY), COLUMN_GET(`j`.`dyn_titles`, ? AS BINARY)) AS `jobTitle`
      FROM `movies_crew` AS `mc`
      INNER JOIN `jobs` AS `j`
        ON `j`.`id` = `mc`.`job_id`
      WHERE `mc`.`person_id` = ?",
      "ssd",
      [ $i18n->languageCode, $i18n->defaultLanguageCode, $personId ]
    )->get_result();
  }

}
