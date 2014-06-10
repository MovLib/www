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
namespace MovLib\Core\Database\Query;

use \MovLib\Component\String;
use \MovLib\Core\Database\Query\Condition;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the select statement object.
 *
 * <b>NOTE</b><br>
 * Using this object together with e.g. its {@see Select::fetchObjects()} method to select and auto instantiate
 * objects is circa twice as slow as directly using PHP's built-in {@see \mysqli} object together with its
 * {@see \mysqli_result::fetch_object()} method. You should use the built-in classes and methods if your goal is trivial
 * and no special type handling or alteration of your query is required. In other cases feel free to use it, than the
 * performance impact isn't as great as one would think.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Select extends AbstractQuery {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Select";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * Numeric array used to map database field types to custom classes for auto instantiation.
   *
   * <b>NOTES</b>
   * <ul>
   *   <li>This array contains only the most important data types that we want to automatically transform upon fetching
   *   (this is also the reason why most are commented out).</li>
   *   <li>The <var>MYSQLI_*</var> constants were only used if the aren't ambigious because of their naming.</li>
   *   <li>The <i>boolean</i> is the only primitive value that we handle on it's own via a cast, have a look at the
   *   actual implementation in {@see Select::fetchObjects()}. All other primitives are already of correct type because
   *   of the usage of {@see \mysqli_stmt::bind_result()} in our implementation.</li>
   *   <li>Although we can easily determine the data type <code>251</code> (JSON encoded string), we can't easily decode
   *   it because it must be passed to a callback instead of instantiating a new class. This fact and the fact that
   *   callers will use the dedicated {@see Select::selectDynamicJSON()} method makes easier for us to simply stack a
   *   callback method.</li>
   * </ul>
   *
   * @link https://mariadb.com/kb/en/data-types/
   * @var array
   */
  protected static $dataTypes = [
    MYSQLI_TYPE_DATE     => "\\MovLib\\Component\\Date",
    MYSQLI_TYPE_DATETIME => "\\MovLib\\Component\\DateTime",
    //MYSQLI_TYPE_TIME    => "\\MovLib\\Component\\Time",
    //MYSQLI_TYPE_YEAR    => "\\MovLib\\Component\\Year",
  ];

  /**
   * Associative array containing collations for order by clauses.
   *
   * @var array
   */
  public static $collations = [
    null    => null, // Used for performance reasons.
    "en_US" => null,
    "de_AT" => " COLLATE utf8mb4_german2_ci",
  ];


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The update's conditions instantiated on demand.
   *
   * @var \MovLib\Core\Database\Query\Condition|null
   */
  protected $conditions;

  /**
   * The select query's limit clause, if any.
   *
   * @var null|string
   */
  protected $limit;

  /**
   * The select query's projection fields.
   *
   * @var array
   */
  protected $fields;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $fields = null;
    foreach ($this->fields as $projected) {
      $fields && ($fields .= ",");
      $fields .= $projected["field"];
    }
    return "SELECT {$fields} FROM {$this->table}{$this->conditions}{$this->limit}";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @todo Move this method back to {@see \MovLib\Core\Data\Connection} and allow direct usage without an instance of
   *       this class. The currently implemented dynamicDecode method gets the properties passed by reference, we don't
   *       want that for this version of the method because passing a copy is faster.
   */
  public function dynamicDecode($json) {
    // If the string is empty or equals an empty JSON object, simply return.
    if (empty($json) || $json === "{}") {
      return null;
    }

    // Try to decode the JSON string, note that we decode the string to an associative array and there's no choice
    // possible at this point.
    return json_decode($json, true);
  }

  /**
   * Fetch the first row from the result as object of <var>$class</var>.
   *
   * <b>NOTE</b><br>
   * This method automatically adds a <code>"LIMIT 1"</code> clause to the query, overwriting any previously set limits.
   *
   * @param string $class
   *   Canonical absolute class name of the objects that should be fetched for each row.
   * @param array $args [optional]
   *   Additional arguments that should be passed to the constructor of <var>$class</var>, defaults to an empty array.
   * @return mixed
   *   The
   * @throws \mysqli_sql_exception
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If the select query returned no results.
   */
  public function fetchObject($class, array $args = []) {
    // We were asked to fetch a single object, let's help the query optimizer by adding a limit 1 clause and possibly
    // speed things up a little bit.
    $this->limit(1);

    // The code to fetch an object isn't trivial, therefore it's better to keep it in a single place and life with the
    // overhead of the additional method call at this point.
    $objects = $this->fetchObjects($class, $args);

    // Check if we got our desired object and return it if we did.
    if ($objects[0]) {
      return $objects[0];
    }

    // No need for the caller to check the returned value, directly throw the desired not found exception. This is the
    // exception callers want in 99% of all cases. Note that this is very different to the fetch objects method of our
    // class which silently returns an empty array.
    throw new NotFoundException("Couldn't find " . $class::name . " with the given conditions.");
  }

  /**
   * Fetch all rows from the result as objects of <var>$class</var>.
   *
   * <b>NOTE</b><br>
   * This method works similar to PHP's built-in and very powerful {@see \mysqli_result::fetch_object()} method, but
   * there are a few differences:
   *
   * <ol>
   *   <li>Only public properties will be exported to class scope.</li>
   *   <li>The constructor is called before the properties are exported.</li>
   *   <li>Primitives are always automatically exported in their correct type.</li>
   *   <li>Some objects are automatically instantiated based on the type of a field.</li>
   *   <li>If a class or callback was defined while a field was added, that is used and overwrites any of the internal
   *   smart guesses that were explained in point 3 and 4.</li>
   * </ol>
   *
   * @param string $class
   *   Canonical absolute class name of the objects that should be fetched for each row.
   * @param array $args [optional]
   *   Additional arguments that should be passed to the constructor of <var>$class</var>, defaults to an empty array.
   * @return array
   *   All rows from the result as objects of <var>$class</var>, an empty array is returned if the query returned no
   *   results.
   * @throws \mysqli_sql_exception
   */
  public function fetchObjects($class, array $args = []) {
    // First thing is of course preparation and execution.
    $stmt = $this->connection->prepare($this);

    // Bind parameters if we have any.
    if ($this->types && $this->values) {
      $types  = implode($this->types);
      $values = $this->values;
      if ($this->conditions) {
        $types .= $this->conditions->types;
        $values = array_merge($this->values, $this->conditions->values);
      }
      // @codingStandardIgnoreStart
      $stmt->bind_param($types, ...$values);
      // @codingStandardIgnoreEnd
    }

    $stmt->execute();

    // Now we want to bind the result to an array for easy iterating.
    $result = array_fill(0, count($this->fields), null);
    // @codingStandardIgnoreStart
    $stmt->bind_result(...$result);

    // We also need the meta data for each bound field to auto determine as many things as possible.
    $metadata  = $stmt->result_metadata();
    $fieldInfo = $metadata->fetch_fields();
    // @codingStandardIgnoreEnd
    $metadata->free();

    // We'll use this variable to collect all the objects.
    $objects = [];

    // We instantiate the class once and simply clone this instance within the loop for each exported object.
    $instance = new $class(...$args);

    // Start consuming the results.
    while ($stmt->fetch()) {
      // Cloning is faster than instantiating.
      $object = clone $instance;

      // Export all fields from the projection to the new instance.
      foreach ($result as $key => $value) {
        // Check if a custom class is desired for this value.
        if (isset($this->fields[$key]["class"])) {
          $value = new $this->fields[$key]["class"]($value);
        }
        // Check if a callback is desired for this value.
        elseif (isset($this->fields[$key]["callback"])) {
          $value = $this->fields[$key]["callback"]($value);
        }
        // A special case that we can handle with a simple cast, note that we check the length as well at this point
        // because otherwise we wouldn't be able to use the TINYINT data type in our database design. The TINYINT data
        // type has a default length of 4 and that's the only way we can distinguish these two.
        elseif ($fieldInfo[$key]->type === 1 && $fieldInfo[$key]->length === 1) {
          $value = (boolean) $value;
        }
        // Check if we know the class for this kind of data type.
        elseif (isset(self::$dataTypes[$fieldInfo[$key]->type])) {
          $value = new self::$dataTypes[$fieldInfo[$key]->type]($value);
        }

        // Finally export the value to the object. Note that we only support public properties, anything else would
        // break encapsulation.
        $object->{$this->fields[$key]["property"]} = $value;
      }

      // Last but not least add the instance to our return array.
      $objects[] = $object;
    }
    $stmt->close();

    return $objects;
  }

  /**
   * Set the table to select from.
   *
   * @param string $tableName
   *   The name of the table to select from.
   * @param string $tableAlias [optional]
   *   The alias of the table to select from, defaults to <code>NULL</code> in which case you'll have to use the full
   *   table name or make all fields unambigous.
   * @return this
   */
  public function from($tableName, $tableAlias = null) {
    $this->table = $this->getTable($tableName, $tableAlias);
    return $this;
  }

  /**
   * Limit the result from the query.
   *
   * @param integer $rowCount
   *   The desired row count. <b>NOTE:</b> If you simply want to jump over the first result but get all the other
   *   results simply pass <var>PHP_INT_MAX</var> (that's the actual official solution to this problem).
   * @param integer $offset [optional]
   *   The desired offset to start selecting rows from, defaults to <code>NULL</code>.
   * @return this
   */
  public function limit($rowCount, $offset = null) {
    $this->limit = " LIMIT {$rowCount}";
    $offset && ($this->limit .= " OFFSET {$offset}");
    return $this;
  }

  /**
   * Remove a previously selected field from the projection.
   *
   * @param integer $index
   *   The index of the field to remove.
   * @return this
   */
  public function removeField($index) {
    foreach ([ $this->fields, $this->types, $this->values ] as $property) {
      if (isset($property[$index])) {
        unset($property[$index]);
      }
    }
    return $this;
  }

  /**
   * Remove a previously selected field from the projection by its name.
   *
   * @see Select::removeField()
   * @param string $fieldName
   *   The name of the field to remove.
   * @return this
   */
  public function removeFieldByName($fieldName) {
    foreach ($this->fields as $index => $field) {
      if ($field["name"] === $fieldName) {
        $this->removeField($index);
        break;
      }
    }
    return $this;
  }

  /**
   * Select field from result.
   *
   * @param string $fieldName
   *   The name of the field to select, including the alias if applicable.
   * @param array $options [optional]
   *   Additional options for the selected field, possible options are:
   *   <ul>
   *     <li><code>"callback"</code> A callable that retrieves the selected field's value as first argument and that has
   *     to return the value.</li>
   *     <li><code>"class"</code> Canonical absolute class name of the class that should be instantiated with the value
   *     of the selected field as first argument. The resulting object will be the new value.</li>
   *     <li><code>"field"</code> This is the string that will be included in the actual query itself, default is to
   *     simply escape the passed field's name.</li>
   *     <li><code>"name"</code> = <var>$fieldName</var></li>
   *     <li><code>"property"</code> The name of the property of the object that will be fetched, defaults to simply
   *     camelCase the <var>$fieldName</var>.</li>
   *   </ul>
   * @return this
   */
  public function select($fieldName, array $options = []) {
    $this->fields[] = $options + [
      "field"    => $this->sanitizeFieldName($fieldName),
      "name"     => $fieldName,
      "property" => String::camelCase($fieldName),
    ];
    return $this;
  }

  /**
   * Select dynamic column field from result.
   *
   * @param string $fieldName
   *   The dynamic column's name, without the <code>"dyn_"</code> prefix.
   * @param string $key
   *   The key of the dynamic column to select.
   * @param string $dataType [optional]
   *   The data type of the selected key, defaults to <code>"CHAR(255)"</code>.
   *
   *   <b>NOTE</b><br>
   *   Using <code>"CHAR"</code> without specifying the length of the string may lead to serious performance degredation
   *   because MariaDB reports the string with a length of 16,777,216 Bytes and the driver may try to allocate that
   *   much. Therefore, always specify the length if possible.
   *
   *   An overview of all available data types for dynamic columns is available at {@link https://mariadb.com/kb/en/dynamic-columns/#datatypes}.
   * @param array $options [optional]
   *   Additional options for the selected field, {@see Select::select()} for an in-depth discussion. Note that the
   *   <code>"field"</code> offset is handled differently by this method and shouldn't be overwritten.
   * @return this
   */
  public function selectDynamic($fieldName, $key, $dataType = "CHAR(255)", array $options = []) {
    // Determine the next insert index, we need to know the index because we have to add the key type and parameter to
    // the same offset in the other arrays, otherwise it's not possible for us to allow proper removal.
    $index = count($this->fields);

    $this->fields[$index] = $options + [
      "field"    => "COLUMN_GET({$this->sanitizeDynamicFieldName($fieldName)}, ? AS {$dataType})",
      "name"     => $fieldName,
      "property" => String::camelCase($fieldName),
    ];

    // A dynamic column key can only be a string or an integer, this check is therefore more than enough.
    $this->types[$index]  = $key === (string) $key ? "s" : "d";
    $this->values[$index] = $key;

    return $this;
  }

  /**
   * Select dynamic column field with the <code>COLUMN_JSON()</code> function.
   *
   * <b>NOTE</b><br>
   * The returned JSON string is automatically decoded with the {@see Select::dynamicDecode()} method.
   *
   * @param string $fieldName
   *   The dynamic column's name, without the <code>"dyn_"</code> prefix.
   * @param array $options [optional]
   *   Additional options for the selected field, {@see Select::select()} for an in-depth discussion. Note that the
   *   <code>"field"</code> offset is handled differently by this method and shouldn't be overwritten. Also note that
   *   the <code>"callback"</code> offset will be set to decode the JSON string and that the <code>"class"</code>
   *   offset always overwrites the <code>"callback"</code> offset. This means that you <code>"class"</code> will get
   *   the JSON string passed as first argument, not the resulting associative array that the callback would produce
   *   otherwise.
   * @return this
   */
  public function selectDynamicJSON($fieldName, array $options = []) {
    $this->fields[] = $options + [
      "callback" => [ $this, "dynamicDecode" ],
      "field"    => "COLUMN_JSON({$this->sanitizeDynamicFieldName($fieldName)})",
      "name"     => $fieldName,
      "property" => String::camelCase($fieldName),
    ];
    return $this;
  }

  /**
   * Select a field with a custom expression.
   *
   * <b>IMPORTANT NOTE</b><br>
   * You have to take care of proper escaping of all values all by yourself if you use this method!
   *
   * @param string $fieldName
   *   The name of the field to select.
   * @param string $expression
   *   The custom expression that should be used to select the field. Again, it's important that you take care of proper
   *   escaping of all values within the expression. This includes proper escaping of field names etc.!
   * @param array $options [optional]
   *   Additional options for the selected field, {@see Select::select()} for an in-depth discussion.
   * @return this
   */
  public function selectExpression($fieldName, $expression, array $options = []) {
    $this->fields[] = $options + [
      "field"    => $expression,
      "name"     => $fieldName,
      "property" => String::camelCase($fieldName),
    ];
    return $this;
  }

  /**
   * Select a field and use the <code>IFNULL()</code> function to decide which value.
   *
   * <b>IMPORTANT NOTE</b><br>
   * You have to take care of proper escaping of all values all by yourself if you use this method!
   *
   * @param string $fieldName
   *   The name of the field to select.
   * @param string $expression1
   *   The first expression to select the field, this is used as result if it's not <code>NULL</code>.
   * @param string $expression2
   *   The second expression to select the field in case <var>$expression1</var> was <code>NULL</code>.
   * @param array $options [optional]
   *   Additional options for the selected field, {@see Select::select()} for an in-depth discussion. Note that the
   *   <code>"field"</code> offset is handled differently by this method by surrounding <var>$expression1</var> and
   *   <var>$expression2</var> with the desired <code>IFNULL()</code> function.
   * @return this
   */
  public function selectIfNull($fieldName, $expression1, $expression2, array $options = []) {
    $this->fields[] = $options + [
      "field"    => "IFNULL({$expression1}, {$expression2})",
      "name"     => $fieldName,
      "property" => String::camelCase($fieldName),
    ];
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Condition Methods


  public function setConditions(\MovLib\Core\Database\Query\Condition $conditions) {
    // @todo Implement
    return $this;
  }

  /**
   * Add where condition to select query.
   *
   * @param string $fieldName
   *   The field's name to evaluate.
   * @param mixed $value
   *   The value the field should have.
   * @param string $operator [optional]
   *   The operator that should be used to compare the field's value against the given <var>$value</var>. If no
   *   operator is passed (<code>NULL</code> default) <code>"="</code> is used and <code>"IN"</code> if <var>$value</var>
   *   is an array.
   * @param string $conjunction [optional]
   *   The conjunction that should be used for this condition, defaults to <code>"AND"</code>.
   * @return this
   */
  public function where($fieldName, $value, $operator = null, $conjunction = "AND") {
    if (!$this->conditions) {
      $this->conditions = new Condition();
    }
    $this->conditions->condition($fieldName, $value, $operator, $conjunction);
    return $this;
  }

}
