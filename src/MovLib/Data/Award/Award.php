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
use \MovLib\Data\Route\EntityRoute;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the award entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Award extends \MovLib\Data\AbstractEntity {
  use \MovLib\Data\Award\AwardTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award's aliases.
   *
   * @var null|array
   */
  public $aliases;

  /**
   * The award's description in the current locale.
   *
   * @var null|string
   */
  public $description;

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
   * The award's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The award's series count.
   *
   * @var null|integer
   */
  public $seriesCount = 0;

  /**
   * The award's translated Wikipedia link.
   *
   * @var null|string
   */
  public $wikipedia;


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
  COUNT(DISTINCT `movie_id`) AS `movieCount`
FROM `awards`
  LEFT JOIN `movies_awards` ON `movies_awards`.`award_id` = `awards`.`id`
WHERE `awards`.`id` = ?
GROUP BY `id`,`name`,`links`,`aliases`,`deleted`,`changed`,`created`,`lastEventYear`,`firstEventYear`,`wikipedia`,`description`
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
        $this->movieCount
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
   * {@inheritdoc}
   */
  protected function init() {
    $this->aliases        && ($this->aliases        = unserialize(($this->aliases)));
    $this->links          && ($this->links          = unserialize($this->links));
    $this->firstEventYear && ($this->firstEventYear = new Date($this->firstEventYear));
    $this->lastEventYear  && ($this->lastEventYear  = new Date($this->lastEventYear));
    $this->route          = new EntityRoute($this->intl, "/award/{0}", $this->id, "/awards");
    return parent::init();
  }

}
