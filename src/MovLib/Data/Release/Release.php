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
namespace MovLib\Data\Release;

use \MovLib\Data\Revision;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Represents a single release.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Release extends \MovLib\Data\AbstractEntity {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Release";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The entity type used to store revisions.
   *
   * @var int
   */
  const REVISION_ENTITY_TYPE = 3;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The release's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The release's last edit timestamp.
   *
   * @var integer
   */
  public $changed;

  /**
   * The release's creation timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * The release's ISO-3166-alpha2 country code of the production country.
   *
   * @var string
   */
  public $countryCode;

  /**
   * The release's edition.
   *
   * @var string
   */
  public $edition;

  /**
   * The release's identifiers (aside of catalog numbers).
   *
   * @var \MovLib\Stub\Data\Release\Identifier
   */
  public $identifiers;

  /**
   * The release's labels.
   *
   * Associative containing the release's label identifiers as keys and {@see \MovLib\Stub\Data\Release\Label} objects
   * as values.
   *
   * @var array
   */
  public $labels;

  /**
   * The release's media.
   *
   * Associative array containing the media identifiers as keys and {@see \MovLib\Data\Medium\Medium} objects as values.
   *
   * @var array
   */
  public $media;

  /**
   * The release's translated notes.
   *
   * @var string
   */
  public $notes;

  /**
   * The release's publishing date for rental.
   *
   * @var string
   */
  public $publishingDateRental;

  /**
   * The release's publishing date for sale.
   *
   * @var string
   */
  public $publishingDateSale;

  /**
   * The release's title.
   *
   * @var string
   */
  public $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new event object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The event's unique identifier to instantiate, defaults to <code>NULL</code> (no event will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null) {
    parent::__construct($container);
    if ($id) {
      $stmt = Database::getConnection()->prepare(<<<SQL
SELECT
  `release`.`changed`,
  `release`.`created`,
  `release`.`country_code`,
  IFNULL(
    COLUMN_GET(`release`.`dyn_notes`, ? AS BINARY),
    COLUMN_GET(`release`.`dyn_notes`, {$this->intl->defaultLanguageCode} AS BINARY)
  ) AS `notes`,
  `release`.`title`,
  `release`.`publishing_date_rental`,
  `release`.`publishing_date_sale`,
  `release`.`edition`,
  `release`.`bin_identifiers`,
  `releases_media`.`medium_id`,
  `media`.`changed` AS `medium_changed`,
  `media`.`created` AS `medium_created`,
  `media`.`bootleg` AS `medium_bootleg`,
  IFNULL(
    COLUMN_GET(`media`.`dyn_notes`, ? AS BINARY),
    COLUMN_GET(`media`.`dyn_notes`, {$this->intl->defaultLanguageCode} AS BINARY)
  ) AS `medium_notes`,
  `media`.`bin_format` AS `medium_format`,
  `media_movies`.`movie_id`,
  `movies`.`deleted` AS `movie_deleted`,
  `movies`.`year` AS `movie_year`,
  `movies`.`mean_rating` AS `movie_mean_rating`,
  IFNULL(`dt`.`title`, `ot`.`title`) AS `movie_display_title`,
  IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `movie_display_title_language_code`,
  `ot`.`title` AS `movie_original_title`,
  `ot`.`language_code` AS `movie_original_title_language_code`,
  `releases_labels`.`company_id`,
  `companies`.`name` AS `company_name`
FROM `releases`
INNER JOIN `releases_media`
  ON `releases_media`.`release_id` = ?
INNER JOIN `media`
  ON `media`.`id` = `releases_media`.`medium_id`
LEFT JOIN `media_movies`
  ON `media_movies`.`medium_id` = `media`.`id`
LEFT JOIN `movies` FORCE INDEX (movies_deleted)
  ON `movies`.`id` = `media_movies`.`movie_id`
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
LEFT JOIN `releases_labels`
  ON `releases_labels`.`release_id` = ?
LEFT JOIN `companies`
  ON `companies`.`id` = `releases_label`.`company_id`
WHERE `releases`.`id` = ?
SQL
      );
      $stmt->bind_param("ssd", $this->intl->languageCode, $this->intl->languageCode, $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->changed,
        $this->created,
        $this->countryCode,
        $this->notes,
        $this->title,
        $this->publishingDateRental,
        $this->publishingDateSale,
        $this->edition,
        $this->identifiers
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Release {$id}");
      }
    }
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->pluralKey   = "releases";
    $this->singularKey = "release";
    return parent::init();
  }

}
