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
   * Associative array containing composition objects.
   *
   * @var array
   */
  protected $compositions = [];

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
   * The select's order by clause.
   *
   * Associative array where the key is the field to order and the value the direction.
   *
   * @var array
   */
  protected $orderBy = [];

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
    // PROJECTION
    $fields = null;
    foreach ($this->fields as $field) {
      $fields && ($fields .= ",");
      $fields .= $field["expression"];
      isset($field["as"]) && ($fields .= " AS `{$field["as"]}`");
    }

    // ORDER BY
    $orderBy = null;
    foreach ($this->orderBy as $field => $direction) {
      $orderBy && ($orderBy .= ",");
      $orderBy = "{$this->sanitizeFieldName($field)} {$direction}";
    }
    $orderBy && ($orderBy = " ORDER BY {$orderBy}");

    return "SELECT {$fields} FROM {$this->table}{$this->conditions}{$orderBy}{$this->limit}";
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


  // ------------------------------------------------------------------------------------------------------------------- Fetch Methods


  /**
   * Add composite object to the final fetched object(s).
   *
   * This method allows you to instantiate a different object from various fields from the projection and therefore
   * create a sub object within the fetched object(s). This is something that PHP's built-in methods don't support. We
   * know that we should follow the {@link https://en.wikipedia.org/wiki/Composition_over_inheritance Composite Reuse
   * Principle} and this method allows us to do exactly that.
   *
   * You have to pass the field's name as array to select a field that should be exported to a composite object. The
   * first offset must match the property name of the composite object in your class and the second offset the name of
   * the property in the composite class. Have a look at the following example:
   *
   * <ul>
   *   <li>Your object is stored in <code>$object</code> and is an instance of <code>\Object</code>.</li>
   *   <li>Your composite object is stored in <code>$obj->composite</code> and is an instance of <code>\Composite</code>.</li>
   *   <li>Your select query is <code>SELECT `id`, `compositeId` FROM `table`</code>.</li>
   * </ul>
   *
   * With the above lemma the final code would look like:
   * <pre>
   * var_dump((new Select(Database::getConnection()))
   *   ->select("id")
   *   ->addComposite("composite", "\\Composite")
   *   ->select([ "composite", "id" ])
   *   ->from("table")
   *   ->fetchObject("\\Object")
   * );
   * </pre>
   *
   * You resulting object will be correctly exported (above <code>var_dump()</code> output):
   * <pre>
   * object(Object)#2 (2) {
   *   ["id"]=>
   *   int(1)
   *   ["composite"]=>
   *   object(Composite)#3 (1) {
   *     ["id"]=>
   *     int(1)
   *   }
   * }
   * </pre>
   *
   * On a final side note, it doesn't matter in which order you call any of the methods. The complete objects is built
   * in a fashion that you can call any method at any time.
   *
   * @param string $propertyName
   *   The name of the property in the fetched object that will contain the instantiated object.
   * @param string $class
   *   Canonical absolute class name of the object to instantiate.
   * @param array $args [optional]
   *   Additional arguments that should be passed to the constructor of <var>$class</var>.
   * @return this
   */
  public function addComposite($propertyName, $class, array $args = []) {
    $reflector = new \ReflectionClass($class);
    $this->compositions[$propertyName] = (object) [ // \MovLib\Stub\Core\Database\Query\Composite
      "args"        => $args,
      "constructor" => $reflector->getConstructor(),
      "instance"    => $reflector->newInstanceWithoutConstructor(),
      "reflector"   => $reflector,
    ];
    return $this;
  }

  /**
   * Fetch the results from the query.
   *
   * @param mixed $object
   *   The object to export the results to. This instance only acts as template if <var>$clone</var> is set to
   *   <code>TRUE</code>.
   * @param boolean $clone
   *   Whether to clone the given instance for each result row or not.
   * @return array
   *   An array containing as many objects as the query returned results.
   * @throws \mysqli_sql_exception
   */
  protected function fetch($object, \ReflectionMethod $constructor = null, array $args = []) {
    // First thing is of course preparation and execution.
    $stmt = $this->connection->prepare($this);

    // @todo We have to overhaul the conditions object so this mess isn't necessary!
    $types = $values = null;
    if ($this->types && $this->values) {
      $types  = implode($this->types);
      $values = $this->values;
    }
    if ($this->conditions) {
      $types .= $this->conditions->types;
      $values = array_merge((array) $values, $this->conditions->values);
    }
    if ($types && $values) {
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

    // Start consuming the results.
    while ($stmt->fetch()) {
      // Cloning is faster than instantiating, but only clone if we have a constructor to call afterwards.
      //
      // @todo Does really every object have a constructor that we should clone? Note that the getConstructor() method
      //       will return NULL in case the class has none and we wouldn't clone at this point, thus overwriting the
      //       previously exported object because we'd be working with a simple reference. I couldn't find any class of
      //       ours that doesn't have a constructor and I keep it like this for now.
      $instance = $constructor ? clone $object : $object;

      // We have to create clones of each composite object for our actual instance.
      /* @var $composite \MovLib\Stub\Core\Database\Query\Composite */
      foreach ($this->compositions as $compositeProperty => $composite) {
        $instance->$compositeProperty = clone $composite->instance;
      }

      // Export all fields from the projection to the new instance.
      foreach ($result as $key => $value) {
        // NULL is NULL, no need to do anything!
        if ($value === null) {
          continue;
        }

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

        // Export to composite object if desirable, only works one level deep.
        if ($this->fields[$key]["property"] === (array) $this->fields[$key]["property"]) {
          $instance->{$this->fields[$key]["property"][0]}->{$this->fields[$key]["property"][1]} = $value;
        }
        // If not export to the property of the object.
        else {
          $instance->{$this->fields[$key]["property"]} = $value;
        }
      }

      // Invoke the objects constructor after exporting all values.
      $constructor && $constructor->invokeArgs($instance, $args);

      // Same is true for all our composite objects.
      /* @var $composite \MovLib\Stub\Core\Database\Query\Composite */
      foreach ($this->compositions as $compositeProperty => $composite) {
        $composite->constructor->invokeArgs($instance->$compositeProperty, $composite->args);
      }

      // Finally export the instance and we're done.
      $objects[] = $instance;
    }
    $stmt->close();

    return $objects;
  }

  /**
   * Fetch the first row from the result into the given object.
   *
   * <b>NOTE</b><br>
   * This method automatically adds a <code>"LIMIT 1"</code> claus to the query, overwriting any previously set limits.
   *
   * @param mixed $object
   *   The object to export the fields from the query to.
   * @return mixed
   *   <var>$object</var> with the fields exported to.
   * @throws \mysqli_sql_exception
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If the select query returned no results.
   */
  public function fetchInto($object) {
    // We were asked to fetch into a single object, let's help the query optimizer by adding a limit 1 clause and
    // possibly speed things up a little bit.
    $this->limit(1);

    // The code to fetch an object isn't trivial, therefore it's better to keep it in a single place and life with the
    // overhead of the additional method call.
    $objects = $this->fetch($object);

    // Check if we got our desired object with the fields exported to it. Note that it doesn't matter that we already
    // had an instance. The fetch method iterates over the result and only adds the object back to the returned array
    // if there was a single row in the result.
    if (isset($objects[0])) {
      return $objects[0];
    }

    // No need for the caller to check the returned value, directly throw the desired not found exception. This is the
    // exception callers want in 99% of all cases. Note that this is very different to the fetch objects method of our
    // class which silently returns an empty array.
    throw new NotFoundException("Couldn't find " . $object::name . " with the given conditions.");
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
    $reflector   = new \ReflectionClass($class);
    $constructor = $reflector->getConstructor();
    $object      = $this->fetchInto($reflector->newInstanceWithoutConstructor());
    $constructor && $constructor->invokeArgs($object, $args);
    return $object;
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
    $reflector = new \ReflectionClass($class);
    return $this->fetch($reflector->newInstanceWithoutConstructor(), $reflector->getConstructor(), $args);
  }


  // ------------------------------------------------------------------------------------------------------------------- Table Methods


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
   * Add join clause to query.
   *
   * @param string $tableName
   *   The table's name to join with.
   * @param string $alias [optional]
   *   The alias for the joined table, defaults to <code>NULL</code> (no alias).
   * @param string $type [optional]
   *   The join type, one of <code>"INNER "</code>, <code>"LEFT "</code>, <code>"RIGHT "</code> and defaults to
   *   <code>NULL</code>. You should use the provided inner, left, and right join method of this class instead of
   *   passing the third parameter.
   * @return this
   */
  public function join($tableName, $alias = null, $type = null) {
    $this->joins[] = [
      "alias" => $alias,
      "name"  => $tableName,
      "join"  => " {$type}JOIN {$this->getTable($tableName, $alias)}",
    ];
    return $this;
  }

  /**
   * Inner join with the given table.
   *
   * @param string $tableName
   *   The table's name to join with.
   * @param string $alias [optional]
   *   The alias for the joined table, defaults to <code>NULL</code> (no alias).
   * @return this
   */
  public function innerJoin($tableName, $alias = null) {
    return $this->join($tableName, $alias, "INNER ");
  }

  /**
   * Left join with the given table.
   *
   * @param string $tableName
   *   The table's name to join with.
   * @param string $alias [optional]
   *   The alias for the joined table, defaults to <code>NULL</code> (no alias).
   * @return this
   */
  public function leftJoin($tableName, $alias = null) {
    return $this->join($tableName, $alias, "LEFT ");
  }

  /**
   * Right join with the given table.
   *
   * @param string $tableName
   *   The table's name to join with.
   * @param string $alias [optional]
   *   The alias for the joined table, defaults to <code>NULL</code> (no alias).
   * @return this
   */
  public function rightJoin($tableName, $alias = null) {
    return $this->join($tableName, $alias, "RIGHT ");
  }


  // ------------------------------------------------------------------------------------------------------------------- Special Select Clause Methods


  /**
   * Limit the result from the query.
   *
   * @todo We have to move this to the conditions object, otherwise it's not possible for e.g. the pagination to add
   *       limit and offset.
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
   * Add <code>ORDER BY</code> clause to select query.
   *
   * @param string $field
   *   The field's name to order by.
   * @param string $direction [optional]
   *   The field's order, defaults to <code>"ASC"</code>.
   * @return this
   */
  public function orderBy($field, $direction = "ASC") {
    // @devStart
    // @codeCoverageIgnoreStart
    assert($direction === "ASC" || $direction === "DESC", "Allowed values for order are ASC or DESC.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->orderBy[$field] = $direction;
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Projection Methods


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
   * @param array|string $fieldName
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
   * @param array|string $fieldName
   *   The name of the field to select, including the alias if applicable. The field's name must be an array if it's for
   *   a composite object. {@see Select::addComposite()} for an in-depth discussion.
   * @param array $options [optional]
   *   Additional options for the selected field, possible options are:
   *   <ul>
   *     <li><code>"as"</code> An alias for the selected field, usually not necessary because this class performs and
   *     internal matching based on the order.</li>
   *     <li><code>"callback"</code> A callable that retrieves the selected field's value as first argument and that has
   *     to return the value.</li>
   *     <li><code>"class"</code> Canonical absolute class name of the class that should be instantiated with the value
   *     of the selected field as first argument. The resulting object will be the new value.</li>
   *     <li><code>"expression"</code> This is the string that will be included in the actual query itself, default is
   *     to simply escape the passed field's name.</li>
   *     <li><code>"name"</code> = <var>$fieldName</var></li>
   *     <li><code>"property"</code> The name of the property of the object that will be fetched, defaults to simply
   *     camelCase the <var>$fieldName</var>.</li>
   *   </ul>
   * @return this
   */
  public function select($fieldName, array $options = []) {
    $this->fields[] = $options + [
      "expression" => $this->sanitizeFieldName($fieldName),
      "name"       => $fieldName,
      "property"   => $this->sanitizePropertyName($fieldName),
    ];
    return $this;
  }

  /**
   * Select dynamic column field from result.
   *
   * @param array|string $fieldName
   *   The name of the field to select, including the alias if applicable and without the <code>"dyn_"</code> prefix.
   *   The field's name must be an array if it's for a composite object. {@see Select::addComposite()} for an in-depth
   *   discussion.
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
   *   <code>"expression"</code> offset is handled differently by this method and shouldn't be overwritten.
   * @return this
   */
  public function selectDynamic($fieldName, $key, $dataType = "CHAR(255)", array $options = []) {
    // Determine the next insert index, we need to know the index because we have to add the key type and parameter to
    // the same offset in the other arrays, otherwise it's not possible for us to allow proper removal.
    $index = count($this->fields);

    $this->fields[$index] = $options + [
      "expression" => "COLUMN_GET({$this->sanitizeDynamicFieldName($fieldName)}, ? AS {$dataType})",
      "name"       => $fieldName,
      "property"   => $this->sanitizePropertyName($fieldName),
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
   * @param array|string $fieldName
   *   The name of the field to select, including the alias if applicable and without the <code>"dyn_"</code> prefix.
   *   The field's name must be an array if it's for a composite object. {@see Select::addComposite()} for an in-depth
   *   discussion.
   * @param array $options [optional]
   *   Additional options for the selected field, {@see Select::select()} for an in-depth discussion. Note that the
   *   <code>"expression"</code> offset is handled differently by this method and shouldn't be overwritten. Also note
   *   that the <code>"callback"</code> offset will be set to decode the JSON string and that the <code>"class"</code>
   *   offset always overwrites the <code>"callback"</code> offset. This means that you <code>"class"</code> will get
   *   the JSON string passed as first argument, not the resulting associative array that the callback would produce
   *   otherwise.
   * @return this
   */
  public function selectDynamicJSON($fieldName, array $options = []) {
    $this->fields[] = $options + [
      "callback"   => [ $this, "dynamicDecode" ],
      "expression" => "COLUMN_JSON({$this->sanitizeDynamicFieldName($fieldName)})",
      "name"       => $fieldName,
      "property"   => $this->sanitizePropertyName($fieldName),
    ];
    return $this;
  }

  /**
   * Select a field with a custom expression.
   *
   * <b>IMPORTANT NOTE</b><br>
   * You have to take care of proper escaping of all values all by yourself if you use this method!
   *
   * @param array|string $fieldName
   *   The name of the field to select, including the alias if applicable. The field's name must be an array if it's for
   *   a composite object. {@see Select::addComposite()} for an in-depth discussion.
   * @param string $expression
   *   The custom expression that should be used to select the field. Again, it's important that you take care of proper
   *   escaping of all values within the expression. This includes proper escaping of field names etc.!
   * @param array $options [optional]
   *   Additional options for the selected field, {@see Select::select()} for an in-depth discussion.
   * @return this
   */
  public function selectExpression($fieldName, $expression, array $options = []) {
    $this->fields[] = $options + [
      "expression" => $expression,
      "name"       => $fieldName,
      "property"   => $this->sanitizePropertyName($fieldName),
    ];
    return $this;
  }

  /**
   * Select a field and use the <code>IFNULL()</code> function to decide which value.
   *
   * <b>IMPORTANT NOTE</b><br>
   * You have to take care of proper escaping of all values all by yourself if you use this method!
   *
   * @param array|string $fieldName
   *   The name of the field to select, including the alias if applicable. The field's name must be an array if it's for
   *   a composite object. {@see Select::addComposite()} for an in-depth discussion.
   * @param string $expression1
   *   The first expression to select the field, this is used as result if it's not <code>NULL</code>.
   * @param string $expression2
   *   The second expression to select the field in case <var>$expression1</var> was <code>NULL</code>.
   * @param array $options [optional]
   *   Additional options for the selected field, {@see Select::select()} for an in-depth discussion. Note that the
   *   <code>"expression"</code> offset is handled differently by this method by surrounding <var>$expression1</var> and
   *   <var>$expression2</var> with the desired <code>IFNULL()</code> function.
   * @return this
   */
  public function selectIfNull($fieldName, $expression1, $expression2, array $options = []) {
    $this->fields[] = $options + [
      "expression" => "IFNULL({$expression1}, {$expression2})",
      "name"       => $fieldName,
      "property"   => $this->sanitizePropertyName($fieldName),
    ];
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Condition Methods


  /**
   * Set conditions on select query.
   *
   * <b>NOTE</b><br>
   * This will overwrite any previously set conditions of this query.
   *
   * @param \MovLib\Core\Database\Query\Condition $conditions
   *   The conditions to set.
   * @return this
   */
  public function setConditions(\MovLib\Core\Database\Query\Condition $conditions) {
    $this->conditions = $conditions;
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


  // ------------------------------------------------------------------------------------------------------------------- Helper Methods


  /**
   * Sanitize a database field name to use as property name.
   *
   * <b>NOTE</b><br>
   * This method is <i>not</i> binary safe because field and property names shouldn't contain any multibyte strings. If
   * you need to camel case in a binary safe fashion use {@see \MovLib\Component\String::camelCase()}.
   *
   * @param array|string $fieldName
   *   The field's name to sanitize.
   * @return string
   *   The sanitized field name for use as property name.
   */
  protected function sanitizePropertyName($fieldName) {
    static $dot = ".", $underscore = "_", $space = " ";

    // Might be a field name for a composite object, we need to sanitize both in this case.
    if ($fieldName === (array) $fieldName) {
      $fieldName[0] = $this->sanitizePropertyName($fieldName[0]);
      $fieldName[1] = $this->sanitizePropertyName($fieldName[1]);
      return $fieldName;
    }

    // The field name may contain an alias, which we have to remove for the property.
    if (strpos($fieldName, $dot) !== false) {
      $fieldName = substr($fieldName, strrpos($fieldName, $dot) + 1);
    }

    // Only start below function call chain if we actually need to.
    if (strpos($fieldName, $underscore) === false) {
      return $fieldName;
    }

    // Replace underscore with space for ucword(), lowercase the first character again, remove spaces and we're done.
    return str_replace($space, "", lcfirst(ucwords(strtr($fieldName, $underscore, $space))));
  }

}
