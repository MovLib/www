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
namespace MovLib\Console\Command\Install;

use \MovLib\Console\MySQLi;
use \MovLib\Exception\CountVerificationException;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for entity count commands.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractEntityCountCommand extends \MovLib\Console\Command\AbstractCommand {

  /**
   * The command's mysqli object.
   *
   * @var MovLib\Console\MySQLi
   */
  protected $mysqli;

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->addOption("seed", null, InputOption::VALUE_NONE);
    $this->mysqli = new MySQLi("movlib");
  }

  /**
   * {@inheritdoc}
   */
  final protected function execute(InputInterface $input, OutputInterface $output) {
    $seed       = (boolean) $input->getOption("seed");
    $entityName = $this->getEntityName();

    try {
      $this->verifyCounts();
    }
    catch (CountVerificationException $e) {
      $this->write("Count verification failed for entity '{$entityName}'!", self::MESSAGE_TYPE_ERROR);
      if (!$seed) {
        $this->log->critical("Count verification failed for entity '{$entityName}'!", $e->getCounts());
        $this->write("This incident has been reported.");
        throw $e;
      }
    }

    return $this;
  }

  /**
   * Get the actual counts stored for an entity.
   *
   * @param array $countColumns
   *   The field names to retrieve (without '_count').
   * @param string $tableName
   *   The table to retrieve data from.
   * @return array
   *   Associative array with the entity's identifier as keys and an object containing the counts.
   *   E.g. if you pass "award" as column name the object will contain the property of the same name.
   */
  protected function getActualCounts(array $countColumns, $tableName) {
    $projection = null;
    foreach ($countColumns as $column) {
      $projection .= ", `{$column}_count` AS `{$column}`";
    }
    $result = $this->mysqli->query(<<<SQL
SELECT
  `id`{$projection}
FROM `{$tableName}`
ORDER BY `id` ASC
SQL
    );

    $countsActual = null;
    while ($row = $result->fetch_assoc()) {
      $countsActual[$row["id"]] = new \stdClass();
      foreach ($countColumns as $column) {
        $countsActual[$row["id"]]->{$column} = (integer) $row[$column];
      }
    }
    $result->free();
    return $countsActual;
  }

  /**
   * Get the counts from a single table.
   *
   * @param array $groupColumns
   *   The columns to group, the first has to be the entity's identifier.
   * @param string $countColumn
   *   The column to derive the counts from.
   * @param string $tableName
   *   The table to count on.
   * @return array
   *   Associative array with the entity's identifiers as keys and the counts as values.
   */
  protected function getCounts(array $groupColumns, $countColumn, $tableName) {
    $counts = [];
    $idColumn = $groupColumns[0];
    $groupColumns = implode("`, `", $groupColumns);
    $result = $this->mysqli->query(<<<SQL
SELECT
  `{$idColumn}` AS `id`,
  COUNT(DISTINCT `{$countColumn}`) AS `count`
FROM `{$tableName}`
GROUP BY `{$groupColumns}`
ORDER BY `id` ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      $counts[$row->id] = (integer) $row->count;
    }
    return $counts;
  }

  /**
   * Get the entity name for displaying output messages.
   *
   * @return string
   *   The entity name.
   */
  abstract protected function getEntityName();

  /**
   * Verify the counts for this entity.
   *
   * @throws \MovLib\Exception\CountVerificationException
   *   Is thrown when count differences appear.
   */
  abstract protected function verifyCounts();

}
