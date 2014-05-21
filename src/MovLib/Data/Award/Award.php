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
namespace MovLib\Data\Award;

use \MovLib\Data\Date;
use \MovLib\Data\Revision;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the award entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Award extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The entity type used to store revisions.
   *
   * @var int
   */
  const REVISION_ENTITY_TYPE = 6;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award's aliases.
   *
   * @var null|array
   */
  public $aliases;

  /**
   * The award's category count.
   *
   * @var null|integer
   */
  public $categoryCount;

  /**
   * The award's company count.
   *
   * @var null|integer
   */
  public $companyCount;

  /**
   * The award's description in the current locale.
   *
   * @var null|string
   */
  public $description;

  /**
   * The award's event count.
   *
   * @var null|integer
   */
  public $eventCount;

  /**
   * The awards events.
   *
   * @var \MovLib\Data\Event\EventSet
   */
  public $events;

  /**
   * The award's first event year.
   *
   * @var null|\MovLib\Data\Date
   */
  public $firstEventYear;

  /**
   * The award's last event year.
   *
   * @var null|\MovLib\Data\Date
   */
  public $lastEventYear;

  /**
   * The award's person count.
   *
   * @var null|integer
   */
  public $personCount;

  /**
   * The award's weblinks.
   *
   * @var null|array
   */
  public $links;

  /**
   * The award's movie count.
   *
   * @var null|integer
   */
  public $movieCount;

  /**
   * The award's name.
   *
   * @var string
   */
  public $name;

  /**
   * The award's series count.
   *
   * @var null|integer
   */
  public $seriesCount;

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "awards";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "award";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The award's unique identifier to instantiate, defaults to <code>NULL</code> (no award will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `awards`.`id` AS `id`,
  `awards`.`changed` AS `changed`,
  `awards`.`created` AS `created`,
  `awards`.`deleted` AS `deleted`,
  `awards`.`name` AS `name`,
  `awards`.`first_event_year` AS `firstEventYear`,
  `awards`.`last_event_year` AS `lastEventYear`,
  COLUMN_GET(`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR) AS `description`,
  `awards`.`links` AS `links`,
  COLUMN_GET(`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR) AS `wikipedia`,
  `awards`.`aliases` AS `aliases`,
  `awards`.`count_movies` AS `movieCount`,
  `awards`.`count_series` AS `seriesCount`,
  `awards`.`count_persons` AS `personCount`,
  `awards`.`count_companies` AS `companyCount`,
  `awards`.`count_categories` AS `categoryCount`,
  `awards`.`count_events` AS `eventCount`
FROM `awards`
  LEFT JOIN `movies_awards` ON `movies_awards`.`award_id` = `awards`.`id`
WHERE `awards`.`id` = ?
LIMIT 1
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
        $this->firstEventYear,
        $this->lastEventYear,
        $this->description,
        $this->links,
        $this->wikipedia,
        $this->aliases,
        $this->movieCount,
        $this->seriesCount,
        $this->personCount,
        $this->companyCount,
        $this->categoryCount,
        $this->eventCount
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Award {$id}");
      }
    }
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Update the award.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function commit() {
    $this->aliases = empty($this->aliases)? serialize([]) : serialize(explode("\n", $this->aliases));
    $this->links   = empty($this->links)? serialize([]) : serialize(explode("\n", $this->links));

    $stmt = $this->getMySQLi()->prepare(<<<SQL
UPDATE `awards` SET
  `aliases`          = ?,
  `dyn_descriptions` = COLUMN_ADD(`dyn_descriptions`, '{$this->intl->languageCode}', ?),
  `dyn_wikipedia`    = COLUMN_ADD(`dyn_wikipedia`, '{$this->intl->languageCode}', ?),
  `name`             = ?,
  `links`            = ?
WHERE `id` = {$this->id}
SQL
    );
    $stmt->bind_param(
      "sssss",
      $this->aliases,
      $this->description,
      $this->wikipedia,
      $this->name,
      $this->links
    );
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * Create new new award.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function create() {
    $this->aliases = empty($this->aliases)? serialize([]) : serialize(explode("\n", $this->aliases));
    $this->links   = empty($this->links)? serialize([]) : serialize(explode("\n", $this->links));

    $stmt = $this->getMySQLi()->prepare(<<<SQL
INSERT INTO `awards` (
  `aliases`,
  `dyn_descriptions`,
  `dyn_image_descriptions`,
  `dyn_wikipedia`,
  `name`,
  `links`
) VALUES (
  ?,
  COLUMN_CREATE('{$this->intl->languageCode}', ?),
  '',
  COLUMN_CREATE('{$this->intl->languageCode}', ?),
  ?,
  ?
);
SQL
    );
    $stmt->bind_param(
      "sssss",
      $this->aliases,
      $this->description,
      $this->wikipedia,
      $this->name,
      $this->links
    );

    $stmt->execute();
    $this->id = $stmt->insert_id;
    $stmt->close();

    return $this->init();
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->aliases        && ($this->aliases        = unserialize($this->aliases));
    $this->links          && ($this->links          = unserialize($this->links));
    $this->firstEventYear && ($this->firstEventYear = new Date($this->firstEventYear));
    $this->lastEventYear  && ($this->lastEventYear  = new Date($this->lastEventYear));
    return parent::init();
  }

}
