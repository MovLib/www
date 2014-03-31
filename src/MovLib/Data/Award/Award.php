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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award's aliases.
   *
   * @var null|array
   */
  public $aliases;

  /**
   * The award's creation timestamp.
   *
   * @var string
   */
  public $created;

  /**
   * The award's deletion state.
   *
   * @var boolean
   */
  public $deleted;

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
   * The award's unique identifier.
   *
   * @var integer
   */
  public $id;

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
   * @var integer
   */
  public $movieCount = 0;

  /**
   * The award's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The award's route in the current locale.
   *
   * @var string
   */
  public $route;

  /**
   * The award's series count.
   *
   * @var integer
   */
  public $seriesCount = 0;

  /**
   * The award's translated Wikipedia link.
   *
   * @var null|string
   */
  public $wikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Initialize


  /**
   * Initialize existing award from unique identifier.
   *
   * @param integer $id
   *   The award's unique identifier to load.
   * @return this
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function init($id) {
    $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `id`,
  `name`,
  `aliases`,
  `first_event_year`,
  `last_event_year`,
  COLUMN_GET(`dyn_descriptions`, '{$this->intl->languageCode}' AS BINARY),
  COLUMN_GET(`dyn_wikipedia`, '{$this->intl->languageCode}' AS BINARY),
  `links`,
  `created`,
  `deleted`
FROM `awards`
WHERE `id` = ?
LIMIT 1
SQL
    );
    $stmt->bind_param("d", $id);
    $stmt->execute();
    $stmt->bind_result(
      $this->id,
      $this->name,
      $this->aliases,
      $this->firstEventYear,
      $this->lastEventYear,
      $this->description,
      $this->wikipedia,
      $this->links,
      $this->created,
      $this->deleted
    );
    $found = $stmt->fetch();
    $stmt->close();
    if ($found === null) {
      throw new NotFoundException("Couldn't find award for '{$id}'!");
    }

    // @todo Store counts as columns in table.
    $this->movieCount = $this->getCount("movies_awards", "DISTINCT `movie_id`");

    return $this->initFetchObject();
  }

  /**
   * Initialize after instantiation via PHP's built in <code>\mysqli_result::fetch_object()}
   */
  public function initFetchObject() {
    $this->unserialize([ &$this->aliases, &$this->links ]);
    $this->toDates([ &$this->firstEventYear, &$this->lastEventYear ]);
    $this->route = $this->intl->r("/award/{0}", $this->id);
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getPluralName() {
    return "awards";
  }

  /**
   * {@inheritdoc}
   */
  public function getSingularName() {
    return "award";
  }

}
