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

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractEntityCountCommand";
  // @codingStandardsIgnoreEnd


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
  public $entityName;

  /**
   * The name(s) of the identifier column(s), defaults to "id".
   *
   * Compound identifiers will be imploded with the glue "-".
   * Example: <code>[ "id1", "id2" ]</code> will be "id1-id2".
   * Please make sure to comply with this format in your queries. You can use the database function
   * {@link http://dev.mysql.com/doc/refman/5.7/en/string-functions.html#function_concat-ws CONCAT_WS}
   * to accomplish this in most cases.
   *
   * @var string|array
   */
  protected $idColumns = "id";

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
   * @param callable $countCallback
   *   The callback to verify the count of the column with.
   * @param array $args [optional]
   *   The method's arguments.
   * @return this
   */
  final protected function addCountColumn($name, callable $countCallback, array $args = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($name) && is_string($name), "\$name must be a non-empty string");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->countConfiguration[$name] = (object) [
      "callback" => $countCallback,
      "args" => (array) $args,
    ];
  }

  /**
   * Aggregate the results of a simple count query.
   *
   * The projection may only contain an identifier and a count field.
   *
   * @param string $query
   *   The query to execute.
   * @param string $idColumn
   *   The name of the identifier column in the projection, defaults to <code>"id"</code>.
   * @param string $countColumn
   *   The name of the count column in the projection, defaults to <code>"count"</code>
   * @return array
   *   Associative array with the identifiers as keys and the counts as values.
   */
  final protected function aggregateSimpleQuery($query, $idColumn = "id", $countColumn = "count"){
    $counts = [];
    $result = $this->mysqli->query($query);
    while ($row = $result->fetch_object()) {
      $counts[$row->$idColumn] = (integer) $row->$countColumn;
    }
    $result->free();
    return $counts;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($this->entityName), "You must initialize the \$entityName property in your class " . static::class);
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (empty($this->getName())) {
      $this->setName("entity-count-" . sanitize_filename($this->entityName));
    }
    $this->addOption("seed", null, InputOption::VALUE_NONE);
    // Make sure the id columns form an array, since a simple string is also possible for convenience.
    $this->idColumns = (array) $this->idColumns;
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
      assert(!empty($this->{$property}), "You must initialize the \${$property} property in your class " . static::class);
    }
    assert(!empty($this->countConfiguration), "You haven't added any count columns in your class " . static::class);
    // @codeCoverageIgnoreEnd
    // @devEnd
    $seed = (boolean) $input->getOption("seed");
    $this->writeVerbose("Starting count verification for entity <comment>{$this->entityName}</comment>...");

    $countColumns = array_keys($this->countConfiguration);
    $this->writeVeryVerbose("Following count columns will be verified: <comment>" . implode("</comment>, <comment>", $countColumns) . "</comment>.");
    // Retrieve actual counts under test.
    $countsActual = $this->getActualCounts($countColumns, $this->tableName);

    // No entities in the database, abort.
    if (empty($countsActual)) {
      $this->writeVerbose("No counts to verify, aborting.", self::MESSAGE_TYPE_INFO);
      return $this;
    }

    // Retrieve all expected counts and build update query.
    $countsExpected    = [];
    $updateQuery       = null;
    $queryPlaceholders = [];
    foreach ($this->countConfiguration as $countColumn => $config) {
      $countsExpected[$countColumn] = call_user_func_array($config->callback, $config->args);
      if ($updateQuery) {
        $updateQuery .= ", ";
      }
      $updateQuery .= "`count_{$countColumn}` = {{ {$countColumn}_value }}";
    }
    $where = null;
    foreach ($this->idColumns as $idColumn) {
      if ($where) {
        $where .= " AND ";
      }
      $placeholder         = "{{ {$idColumn} }}";
      $queryPlaceholders[] = $placeholder;
      $where              .= "`{$idColumn}` = {$placeholder}";
    }
    $updateQuery = "UPDATE `{$this->tableName}` SET {$updateQuery} WHERE {$where};";

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
        $updateQueryReplaced = str_replace($queryPlaceholders, explode("-", $id), $updateQueryReplaced);
        $queriesToRun .= $updateQueryReplaced;
      }
    }

    // There were count discrepancies, correct and report them according to parameters.
    if ($errors) {
      $this->mysqli->multi_query(rtrim($queriesToRun, ";"));
      $this->write("Count verification failed for entity '{$this->entityName}', updating...");
      if (!$seed) {
        throw new CountVerificationException($errors, "Count verification failed for entity '{$this->entityName}'");
      }
    }

    $this->writeVerbose(
      "Count verification for entity <comment>{$this->entityName}</comment> completed successfully!",
      self::MESSAGE_TYPE_INFO
    );

    return 0;
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
    $idColumns = implode("`, `", $this->idColumns);
    $projection = null;
    foreach ($countColumns as $column) {
      $projection .= ", `count_{$column}` AS `{$column}`";
    }
    $result = $this->mysqli->query(<<<SQL
SELECT
  CONCAT_WS('-', `{$idColumns}`) AS `id`{$projection}
FROM `{$tableName}`
ORDER BY `{$idColumns}`
SQL
    );

    $countsActual = null;
    while ($row = $result->fetch_object()) {
    $countsActual[$row->id] = [];
      foreach ($countColumns as $column) {
        $countsActual[$row->id][$column] = (integer) $row->$column;
      }
    }
    $result->free();
    return $countsActual;
  }

  /**
   * Get the awardee counts of an award entity (of a single type, e.g. person).
   *
   * @param string $entityIdColumn
   *   The name of the entity identifier column in the intermediate tables (e.g. award_id).
   * @param string $entityTable
   *   The name of the entity table (e.g. awards).
   * @param string $awardeeIdColumn
   *   The name of the awardee identifier column in the intermediate tables (e.g. person_id).
   * @param string $awardeeTable
   *   The name of the awardee table (e.g. persons).
   * @return array
   *   Associative array with the award identifiers as keys and the awardee counts as values.
   */
  protected function getAwardeeCounts($entityIdColumn, $entityTable, $awardeeIdColumn, $awardeeTable) {
    return $this->aggregateSimpleQuery(<<<SQL
SELECT
  `{$entityTable}`.`id`,
  COUNT(DISTINCT `{$awardeeTable}`.`id`) AS `count`
FROM `{$entityTable}`
LEFT JOIN `movies_awards`
  ON `movies_awards`.`{$entityIdColumn}` = `{$entityTable}`.`id`
LEFT JOIN `series_awards`
  ON `series_awards`.`{$entityIdColumn}` = `{$entityTable}`.`id`
INNER JOIN `{$awardeeTable}`
  ON `movies_awards`.`{$awardeeIdColumn}` = `{$awardeeTable}`.`id`
  OR `series_awards`.`{$awardeeIdColumn}` = `{$awardeeTable}`.`id`
WHERE (`movies_awards`.`{$entityIdColumn}` IS NOT NULL AND `movies_awards`.`won` > 0)
  OR (`series_awards`.`{$entityIdColumn}` IS NOT NULL AND `series_awards`.`won` > 0)
GROUP BY `{$entityTable}`.`id`
ORDER BY `{$entityTable}`.`id` ASC
SQL
    );
  }

  /**
   * Generic count method for retrieving counts from a single table.
   *
   * NOTE: If you need special count methods, make sure they return associative arrays in the format:
   * <code>[ entity_id1 => count1, entity_id2 => count2, ... ]</code>.
   *
   * @param string|array $idColumns
   *   The entity's identifier column(s).
   * @param string|array $groupColumns
   *   The column(s) to group, the identifier columns will be included automatically.
   * @param string|array $countColumns
   *   The column(s) to derive the counts from.
   * @param string $tableName
   *   The table to count on.
   * @param string $where [optional]
   *   Optional <code>WHERE</code> clause without the WHERE keyword.
   * @return array
   *   Associative array with the entity's identifiers as keys and the counts as values.
   */
  final protected function getCounts($idColumns, $groupColumns, $countColumns, $tableName, $where = null) {
    $counts       = [];
    $idColumns    = (array) $idColumns;
    $groupColumns = array_merge($idColumns, (array) $groupColumns);
    $idColumns    = implode("`, `", $idColumns);
    $groupColumns = implode("`, `", $groupColumns);
    $countColumns = implode("`, `", (array) $countColumns);
    if ($where) {
      $where = "WHERE {$where}";
    }
    $result = $this->mysqli->query(<<<SQL
SELECT
  CONCAT_WS('-',`{$idColumns}`) AS `id`,
  COUNT(DISTINCT `{$countColumns}`) AS `count`
FROM `{$tableName}`
{$where}
GROUP BY `{$groupColumns}`
ORDER BY `{$idColumns}`
SQL
    );

    while ($row = $result->fetch_object()) {
      $counts[$row->id] = (integer) $row->count;
    }
    return $counts;
  }

  /**
   * Get the release counts of entities.
   *
   * @param string|array $idColumns
   *   The identifier column name(s) in the intermediate table.
   * @param string $intermediateTable
   *   The name of the intermediate table to start with (e.g. media_movies).
   * @return array
   *   Associative array with the entity identifiers as keys and the release counts as values.
   */
  protected function getReleaseCounts($idColumns, $intermediateTable) {
    $idColumnsQuery = null;
    foreach ((array) $idColumns as $idColumn) {
      if ($idColumnsQuery) {
        $idColumnsQuery .= ", ";
      }
      $idColumnsQuery = "`{$intermediateTable}`.`{$idColumn}`";
    }
    return $this->aggregateSimpleQuery(<<<SQL
SELECT
  CONCAT_WS('-', {$idColumnsQuery}) AS `id`,
  COUNT(DISTINCT `releases`.`id`) AS `count`
FROM `{$intermediateTable}`
INNER JOIN `media`
  ON `media`.`id` = `{$intermediateTable}`.`medium_id`
INNER JOIN `releases_media`
  ON `releases_media`.`medium_id` = `media`.`id`
INNER JOIN `releases`
  ON `releases`.`id` = `releases_media`.`release_id`
WHERE `releases`.`deleted` = false
GROUP BY {$idColumnsQuery}
ORDER BY {$idColumnsQuery}
SQL
    );
  }

}
