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
namespace MovLib\Console\Command\Install\Count;

use \MovLib\Console\MySQLi;
use \MovLib\Exception\CountVerificationException;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for entity count commands.
 *
 * NOTE: If you need special count methods, make sure they return associative arrays in the format:
 * <code>[ entity_id1 => count1, entity_id2 => count2, ... ]</code>.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractEntityCountCommand extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The count configuration, filled via {@see \MovLib\Console\Command\Install\AbstractEntityCountCommand::addCountColumn()}.
   *
   * The format looks like this:
   * <code>[ "count_column_name" => (object) [ "method" => "count_method_name", "args" => [ "args", ... ] ] ]</code>
   * The count column has to be specified without the "count_" prefix!
   * Example: Verify the release count with the getCounts() method.
   * <code>[
   *  "releases" => (object) [
   *    "method" => "getCounts",
   *    "args" => [ [ "person_id" ], "release_id", "releases_crew" ]
   *  ]
   * ]</code>
   *
   * @var array
   */
  private $countConfiguration;

  /**
   * The entity's name for the count verification.
   *
   * @var string
   */
  protected $entityName;

  /**
   * The name of the identifier column, defaults to "id".
   *
   * @var string
   */
  protected $idColumn = "id";

  /**
   * The command's mysqli object.
   *
   * @var MovLib\Console\MySQLi
   */
  protected $mysqli;

  /**
   * The table name for the count verification.
   *
   * @var string
   */
  protected $tableName;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add a count column for verification.
   *
   * @param string $name
   *   The name of the column without the "count_" prefix.
   * @param string $method
   *   The name of the method to verify the count of the column with.
   * @param array $args [optional]
   *   The method's arguments.
   * @return this
   */
  final protected function addCountColumn($name, $method, array $args = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($name) && is_string($name), "\$name must be a non-empty string.");
    assert(!empty($method) && is_string($method), "\$method must be a non-empty string.");
    assert(is_callable([ $this, $method ]), "\$method ('{$method}') must be callable.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->countConfiguration[$name] = (object) [
      "method" => $method,
      "args" => (array) $args,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->addOption("seed", null, InputOption::VALUE_NONE);
    $this->mysqli = new MySQLi("movlib");
    return parent::configure();
  }

  /**
   * {@inheritdoc}
   */
  final protected function execute(InputInterface $input, OutputInterface $output) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ "entityName", "tableName" ] as $property) {
      assert(!empty($this->{$property}), "You must initialize the \${$property} property in your class " . static::class . ".");
    }
    assert(!empty($this->countConfiguration), "You haven't added any count columns in your class " . static::class . ".");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $seed = (boolean) $input->getOption("seed");
    $this->writeVerbose("Starting count verification for entity <comment>{$this->entityName}</comment>...");

    $countColumns = array_keys($this->countConfiguration);
    $this->writeVeryVerbose("Following count columns will be verified: " . implode(", ", $countColumns) . ".");
    // Retrieve actual counts under test.
    $countsActual = $this->getActualCounts($countColumns, $this->tableName);

    // No entities in the database, abort.
    if (empty($countsActual)) {
      return $this;
    }

    // Retrieve all expected counts and build update query.
    $countsExpected = [];
    $updateQuery = null;
    foreach ($this->countConfiguration as $countColumn => $config) {
      $countsExpected[$countColumn] = call_user_func_array([ $this, $config->method ], $config->args);
      if ($updateQuery) {
        $updateQuery .= ", ";
      }
      $updateQuery .= "`count_{$countColumn}` = {{ {$countColumn}_value }}";
    }
    $updateQuery = "UPDATE `{$this->tableName}` SET {$updateQuery} WHERE `{$this->idColumn}` = {{ id_value }};";

    // Verify the counts.
    $errors = null;
    $queriesToRun = null;
    foreach ($countsActual as $id => $counts) {
      $updateQueryReplaced = $updateQuery;
      // Verify every column separately.
      foreach ($countColumns as $countColumn) {
        $expected = isset($countsExpected[$countColumn][$id]) ? $countsExpected[$countColumn][$id] : 0;
        // Counts don't match, stack error.
        if ($expected !== $counts[$countColumn]) {
          $errors[$id][] = "{$countColumn}: expected -> {$expected} --- actual -> {$counts[$countColumn]}";
        }
        $updateQueryReplaced = str_replace("{{ {$countColumn}_value }}", $expected, $updateQueryReplaced);
      }
      // If there were errors, stack an update query to fix them.
      if (isset($errors[$id])) {
        $updateQueryReplaced = str_replace("{{ id_value }}", $id, $updateQueryReplaced);
        $queriesToRun .= $updateQueryReplaced;
      }
    }

    // There were count discrepancies, correct and report them according to parameters.
    if ($errors) {
      $this->mysqli->multi_query(rtrim($queriesToRun, ";"));
      $this->write("Count verification failed for entity '{$this->entityName}'!", self::MESSAGE_TYPE_ERROR);
      if (!$seed) {
        $this->log->critical("Count verification failed for entity '{$this->entityName}'!", $errors);
        $this->write("This incident has been reported.");
        throw new CountVerificationException($errors);
      }
    }

    $this->writeVerbose(
      "Count verification for entity <comment>{$this->entityName}</comment> completed successfully!",
      self::MESSAGE_TYPE_INFO
    );
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
  final protected function getActualCounts(array $countColumns, $tableName) {
    $projection = null;
    foreach ($countColumns as $column) {
      $projection .= ", `count_{$column}` AS `{$column}`";
    }
    $result = $this->mysqli->query(<<<SQL
SELECT
  `{$this->idColumn}`{$projection}
FROM `{$tableName}`
ORDER BY `{$this->idColumn}` ASC
SQL
    );

    $countsActual = null;
    while ($row = $result->fetch_object()) {
    $countsActual[$row->{$this->idColumn}] = [];
      foreach ($countColumns as $column) {
        $countsActual[$row->{$this->idColumn}][$column] = (integer) $row->$column;
      }
    }
    $result->free();
    return $countsActual;
  }

  /**
   * Generic count method for retrieving counts from a single table.
   *
   * NOTE: If you need special count methods, make sure they return associative arrays in the format:
   * <code>[ entity_id1 => count1, entity_id2 => count2, ... ]</code>.
   *
   * @param array $groupColumns
   *   The columns to group, the first has to be the entity's identifier.
   * @param string $countColumns
   *   The column to derive the counts from.
   * @param string $tableName
   *   The table to count on.
   * @param string $where [optional]
   *   Optional <code>WHERE</code> clause.
   * @return array
   *   Associative array with the entity's identifiers as keys and the counts as values.
   */
  final protected function getCounts(array $groupColumns, array $countColumns, $tableName, $where = null) {
    $counts = [];
    $idColumn = $groupColumns[0];
    $groupColumns = implode("`, `", $groupColumns);
    $countColumns = implode("`, `", $countColumns);
    $result = $this->mysqli->query(<<<SQL
SELECT
  `{$idColumn}` AS `id`,
  COUNT(DISTINCT `{$countColumns}`) AS `count`
FROM `{$tableName}`
{$where}
GROUP BY `{$groupColumns}`
ORDER BY `id` ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      $counts[$row->id] = (integer) $row->count;
    }
    return $counts;
  }

}
