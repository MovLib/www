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
 * Represents a single job.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Job {


  // ------------------------------------------------------------------------------------------------------------------- Properties

  /**
   * The job's unique ID.
   *
   * @var integer
   */
  public $id;

  /**
   * The job's creation timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * The job's translated description.
   *
   * @var string
   */
  public $description;

  /**
   * The job's translated title.
   *
   * @var string
   */
  public $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new job.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $crewId [optional]
   *   The unique crew's identifier to load, omit to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($jobId = null) {
    global $db, $i18n;

    // Try to load the cast for the given identifier.
    if ($jobId) {
      $stmt = $db->query(
        "SELECT
          `created`,
          COLUMN_GET(`dyn_descriptions`, ? AS BINARY),
          IFNULL(COLUMN_GET(`dyn_titles`, ? AS BINARY), COLUMN_GET(`dyn_titles`, ? AS BINARY))
          FROM `jobs`
          WHERE `id` = ?",
        "ssssd",
        [ $i18n->languageCode, $i18n->languageCode, $i18n->defaultLanguageCode, $jobId ]
      );
      $stmt->bind_result(
        $this->created,
        $this->description,
        $this->title
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
      $this->id = $jobId;
    }
  }

}
