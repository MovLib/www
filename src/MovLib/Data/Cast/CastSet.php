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

use \MovLib\Data\Cast\Cast;
use \MovLib\Data\Person\Person;

/**
 * @todo Description of CastSet
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CastSet extends \MovLib\Data\AbstractSet {

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "job";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "job";

  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT

{$where} {$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractSet $set, $in) {
    return <<<SQL

SQL;
  }


  final public function loadMovieCast(\MovLib\Data\Movie\Movie $movie) {
    $jobId = Cast::JOB_ID;
    $result = $this->getMySQLi()->query(<<<SQL
SELECT
  `movies_crew`.`person_id` AS `personId`,
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
  `movies_crew`.`created`,
  `movies_crew`.`changed`,
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
ORDER BY `movies_crew`.`weight` DESC, `persons`.`name`{$this->collations[ $this->intl->languageCode ]} ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      $row->id       = (integer) $row->id;
      $row->personId = (integer) $row->personId;
      $row->jobId    = (integer) $row->jobId;
      $row->roleId   = (integer) $row->roleId;

      if (empty($this->entities[$row->personId])) {
        $this->entities[$row->personId] = (object) [
          "person"  => new Person($this->diContainer),
          "castSet" => new CastSet($this->diContainer),
        ];
        foreach ([
                    "id", "name", "sex", "birthDate", "bornName",
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
        $this->entities[$row->personId]->castSet->entities[$row->id] = new Cast($this->diContainer, $movie);
        foreach ([ "id", "created", "changed", "jobId", "alias", "role", "roleId", "roleName" ] as $property) {
          $this->entities[$row->personId]->castSet->entities[$row->id]->$property = $row->$property;
        }
        $reflector = new \ReflectionMethod($this->entities[$row->personId]->castSet->entities[$row->id], "init");
        $reflector->setAccessible(true);
        $reflector->invoke($this->entities[$row->personId]->castSet->entities[$row->id]);
      }

    }

    return $this;
  }

}
