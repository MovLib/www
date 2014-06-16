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
namespace MovLib\Data\Director;

use \MovLib\Core\Database\Database;
use \MovLib\Core\Database\Query\Select;
use \MovLib\Data\Director\Director;
use \MovLib\Partial\Sex;

/**
 * @todo Description of DirectorSet
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class DirectorSet extends \MovLib\Data\AbstractEntitySet {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "DirectorSet";
  // @codingStandardsIgnoreEnd

  public static $tableName = "";

  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container, "Directors", "Director", $container->intl->tp(-1, "Directors", "Director"));
  }

  public function loadMovieDirectorsLimited(\MovLib\Data\Movie\Movie $movie, $limit = 5) {
    $jobId = Director::JOB_ID;
    $limit = "LIMIT {$limit}";
    $collation = Select::$collations[$this->intl->locale];
    $result = Database::getConnection()->query(<<<SQL
SELECT
  `movies_crew`.`id` AS `id`,
  `persons`.`id` AS `personId`,
  `persons`.`name` AS `personName`,
  `persons_aliases`.`alias`,
  `movies_crew`.`job_id` AS `jobId`,
  IFNULL(
    COLUMN_GET(`jobs`.`dyn_titles_sex0`, '{$this->intl->code}' AS BINARY),
    COLUMN_GET(`jobs`.`dyn_titles_sex0`, '{$this->intl->defaultCode}' AS BINARY)
  ) AS `jobTitle`,
  `jobs`.`created`,
  `jobs`.`changed`
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
ORDER BY `persons`.`name`{$collation} ASC
{$limit}
SQL
    );
    while ($row = $result->fetch_object()) {
      $row->id       = (integer) $row->id;
      $row->personId = (integer) $row->personId;
      $row->jobId    = (integer) $row->jobId;
      if (empty($this->entities[$row->id])) {
        $this->entities[$row->id] = new Director($this->container);
        $this->entities[$row->id]->id = $row->id;
        $this->entities[$row->id]->personId = $row->personId;
        $this->entities[$row->id]->personName = $row->personName;
        $this->entities[$row->id]->alias = $row->alias;
        $this->entities[$row->id]->names[Sex::UNKNOWN] = $row->jobTitle;
        $this->entities[$row->id]->created = $row->created;
        $this->entities[$row->id]->changed = $row->changed;
        $reflector = new \ReflectionMethod($this->entities[$row->id], "init");
        $reflector->setAccessible(true);
        $reflector->invoke($this->entities[$row->id]);
      }
    }
    $result->free();

    return $this;
  }

  protected function getEntitiesQuery($where = null, $orderBy = null) {

  }

  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {

  }

}
