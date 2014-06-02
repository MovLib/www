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
namespace MovLib\Data\Cast;

use \MovLib\Core\Database\Database;
use \MovLib\Data\Cast\Cast;
use \MovLib\Data\Person\Person;
use \MovLib\Partial\Sex;

/**
 * @todo Description of CastSet
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CastSet extends \MovLib\Data\AbstractEntitySet {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "CastSet";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "job";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "job";

  /**
   * {@inheritdoc}
   */
  public static $tableName = "movies_crew";

  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container, null, null, null);
  }

  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT

{$where} {$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {
    return <<<SQL

SQL;
  }


  final public function loadMovieCast(\MovLib\Data\Movie\Movie $movie) {
    $jobId = Cast::JOB_ID;
    $connection = Database::getConnection();
    $result = $connection->query(<<<SQL
SELECT
  `movies_crew`.`person_id` AS `personId`,
  `persons`.`created` AS `personCreated`,
  `persons`.`changed` AS `personChanged`,
  `persons`.`name` AS `personName`,
  `persons`.`sex` AS `personSex`,
  `persons`.`birthdate` AS `personBirthDate`,
  `persons`.`born_name` AS `personBornName`,
  `persons`.`deathdate` AS `personDeathDate`,
  HEX(`persons`.`image_cache_buster`) AS `personImageCacheBuster`,
  `persons`.`image_extension` AS `personImageExtension`,
  `persons`.`image_filesize` AS `personImageFilesize`,
  `persons`.`image_height` AS `personImageHeight`,
  `persons`.`image_styles` AS `personImageStyles`,
  `persons`.`image_width` AS `personImageWidth`,
  `movies_crew`.`id`,
  `movies_crew`.`job_id` AS `jobId`,
  `crew_alias`.`alias` AS `alias`,
  IFNULL(
    COLUMN_GET(`movies_crew`.`dyn_role`, '{$this->intl->languageCode}' AS BINARY),
    COLUMN_GET(`movies_crew`.`dyn_role`, '{$this->intl->defaultLanguageCode}' AS BINARY)
  ) AS `role`,
  `movies_crew`.`role_id` AS `roleId`,
  `crew_role`.`name` AS `roleName`
FROM `movies_crew`
  INNER JOIN `persons`
    ON `persons`.`id` = `movies_crew`.`person_id`
  LEFT JOIN `persons` AS `crew_role`
    ON `movies_crew`.`role_id` = `crew_role`.`id`
  LEFT JOIN `persons_aliases` AS `crew_alias`
    ON `crew_alias`.`id` = `movies_crew`.`alias_id`
WHERE `movies_crew`.`movie_id` = {$movie->id} AND `movies_crew`.`job_id` = {$jobId} AND `persons`.`deleted` = false
ORDER BY `movies_crew`.`weight` DESC, `persons`.`name`{$connection->collate($this->intl->languageCode)} ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      $row->id       = (integer) $row->id;
      $row->personId = (integer) $row->personId;
      $row->jobId    = (integer) $row->jobId;
      $row->roleId   = (integer) $row->roleId;

      if (empty($this->entities[$row->personId])) {
        $this->entities[$row->personId] = (object) [
          "person"  => new Person($this->container),
          "castSet" => new CastSet($this->container),
        ];
        foreach ([
                    "id", "created", "changed", "name", "sex", "birthDate", "bornName",
                    "deathDate", "imageCacheBuster", "imageExtension", "imageFilesize", "imageHeight",
                    "imageStyles", "imageWidth"
                ] as $property) {
          $rowProperty = "person" . ucfirst($property);
          $this->entities[$row->personId]->person->$property = $row->$rowProperty;
        }
        $reflector = new \ReflectionMethod($this->entities[$row->personId]->person, "init");
        $reflector->setAccessible(true);
        $reflector->invoke($this->entities[$row->personId]->person);
      }

      if (empty($this->entities[$row->personId]->castSet->entities[$row->id])) {
        $this->entities[$row->personId]->castSet->entities[$row->id] = new Cast($this->container, $movie);
        foreach ([ "id", "jobId", "alias", "role", "roleId", "roleName" ] as $property) {
          $this->entities[$row->personId]->castSet->entities[$row->id]->$property = $row->$property;
        }
        $reflector = new \ReflectionMethod($this->entities[$row->personId]->castSet->entities[$row->id], "init");
        $reflector->setAccessible(true);
        $reflector->invoke($this->entities[$row->personId]->castSet->entities[$row->id]);
      }

    }

    return $this;
  }

  public function loadMovieCastLimited(\MovLib\Data\Movie\Movie $movie, $limit = 5) {
    $jobId = Cast::JOB_ID;
    $limit = "LIMIT {$limit}";
    $connection = Database::getConnection();
    $result = $connection->query(<<<SQL
SELECT
  `movies_crew`.`id`,
  `persons`.`id` AS `personId`,
  `persons`.`name` AS `personName`,
  `persons_aliases`.`alias`,
  `movies_crew`.`job_id` AS `jobId`,
  IFNULL(
    COLUMN_GET(`jobs`.`dyn_titles_sex0`, '{$this->intl->languageCode}' AS BINARY),
    COLUMN_GET(`jobs`.`dyn_titles_sex0`, '{$this->intl->defaultLanguageCode}' AS BINARY)
  ) AS `jobTitle`
FROM `movies_crew`
  INNER JOIN `persons`
    ON `persons`.`id` = `movies_crew`.`person_id`
  INNER JOIN `jobs`
    ON `jobs`.`id` = `movies_crew`.`job_id`
  LEFT JOIN `persons_aliases`
    ON `persons_aliases`.`id` = `movies_crew`.`alias_id`
WHERE `movies_crew`.`movie_id` = {$movie->id}
  AND `movies_crew`.`job_id` = {$jobId}
  AND `persons`.`deleted` = false
ORDER BY `persons`.`name`{$connection->collate($this->intl->languageCode)} ASC
{$limit}
SQL
    );
    while ($row = $result->fetch_object()) {
      $row->id       = (integer) $row->id;
      $row->personId = (integer) $row->personId;
      $row->jobId    = (integer) $row->jobId;
      if (empty($this->entities[$row->id])) {
        $this->entities[$row->id] = new Cast($this->container);
        $this->entities[$row->id]->id = $row->id;
        $this->entities[$row->id]->personId = $row->personId;
        $this->entities[$row->id]->personName = $row->personName;
        $this->entities[$row->id]->alias = $row->alias;
        $this->entities[$row->id]->names[Sex::UNKNOWN] = $row->jobTitle;
        $reflector = new \ReflectionMethod($this->entities[$row->id], "init");
        $reflector->setAccessible(true);
        $reflector->invoke($this->entities[$row->id]);
      }
    }
    $result->free();

    return $this;
  }

}
