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
 * Handling of one or more award categories.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AwardCategory extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award's unique identifier this category belongs to.
   *
   * @var integer
   */
  public $awardId;

  /**
   * The award category's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The award category's description in the current display language.
   *
   * @var string
   */
  public $description;

  /**
   * The award category's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The award category's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The translated route of this award category.
   *
   * @var string
   */
  public $route;

  /**
   * The route key of this award category.
   *
   * @var string
   */
  public $routeKey;

  /**
   * The award category’s translated Wikipedia link.
   *
   * @var string
   */
  public $wikipedia;

  /**
   * The first year there was an award in this category.
   *
   * @var integer
   */
  public $firstAwardingYear;

  /**
   * The last year there was an award in this category.
   *
   * @var integer
   */
  public  $lastAwardingYear;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award category.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id [optional]
   *   The award category's unique identifier, omit to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    global $db, $i18n;
    if ($id) {
      $query = self::getQuery();
      $stmt = $db->query("
        {$query}
        WHERE
          `id` = ?
        LIMIT 1",
        "ssd",
        [ $i18n->languageCode, $i18n->languageCode, $id ]
      );
      $stmt->bind_result(
        $this->awardId,
        $this->id,
        $this->deleted,
        $this->name,
        $this->description,
        $this->firstAwardingYear,
        $this->lastAwardingYear
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
   * The count of movies with this award category.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMoviesCount() {
    global $db;
    return $db->query(
      "SELECT count(DISTINCT `movie_id`) as `count` FROM `movies_awards` WHERE `award_category_id` = ?", "d", [ $this->id ]
    )->get_result()->fetch_assoc()["count"];
  }

 /**
   * Get the mysqli result for all movies that are of this award.
   *
  *  @todo Implement
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The mysqli result for all movies that are of this award.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMoviesResult() {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `movies`.`year` AS `year`,
        IFNULL(`dt`.`title`, `ot`.`title`) AS `displayTitle`,
        IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `displayTitleLanguageCode`,
        `ot`.`title` AS `originalTitle`,
        `ot`.`language_code` AS `originalTitleLanguageCode`,
        `p`.`poster_id` AS `displayPoster`
        FROM `movies_awards` as `ma`
        LEFT JOIN `awards_categories` as `mac`
          ON `ma`.award_category_id = `mac`.`id`
        LEFT JOIN `awards`
          ON `mac`.`award_id` = `awards`.`id`
        LEFT JOIN `movies` AS `movies`
          ON `movies`.`id` = `movies`.`id`
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
      WHERE `ma`.`award_id` = ? AND `ma`.`award_category_id` = ?
      ORDER BY `movies`.`year` DESC",
      "ssdd",
      [ $i18n->languageCode, $i18n->languageCode, $this->awardId, $this->id ]
    )->get_result();
  }

  /**
   * Get the default query.
   *
   * @global \MovLib\Data\I18n $i18n
   * @staticvar string $query
   *   Used to cache the default query.
   * @return string
   *   The default query.
   */
  protected static function getQuery() {
    global $i18n;
    static $query = null;
    if (!$query) {
      $query =
        "SELECT
          `award_id` AS `awardId`,
          `id`,
          `deleted`,
          IFNULL(COLUMN_GET(`dyn_names`, ? AS CHAR), COLUMN_GET(`dyn_names`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `name`,
          IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_descriptions`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `description`,
          `first_awarding_year` AS `firstAwardingYear`,
          `last_awarding_year` AS `lastAwardingYear`
        FROM `awards_categories`"
      ;
    }
    return $query;
  }

  /**
   * The count of movies with this award category.
   *
   * @todo Implement when series are implemented.
   * @global \MovLib\Data\Database $db
   * @return integer
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getSeriesCount() {
    global $db;
    return 0;
  }

  /**
   * Initialize award.
   *
   * @global type $i18n
   */
  protected function init() {
    global $i18n;

    $this->deleted  = (boolean) $this->deleted;
    $this->routeKey = "/award/{0}/category/{1}";
    $this->route    = $i18n->r($this->routeKey, [ $this->awardId, $this->id ]);
  }

}
