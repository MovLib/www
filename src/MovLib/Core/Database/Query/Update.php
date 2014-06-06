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

/**
 * Defines the database update object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Update extends AbstractQuery {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Update";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The update's conditions instantiated on demand.
   *
   * @var \MovLib\Core\Database\Condition|null
   */
  protected $conditions;

  /**
   * String containing all fields (including their placeholders) to set.
   *
   * @var string
   */
  protected $setClause;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return "UPDATE {$this->table}{$this->setClause}{$this->conditions}";
  }


  // ------------------------------------------------------------------------------------------------------------------- Field Methods


  /**
   * Used internally to add fields in a unified fashion.
   *
   * @param string $name
   *   The field's name.
   * @param string $placeholder
   *   The field values's placeholder.
   * @param string $type [optional]
   *   The field value's type.
   * @param mixed $value [optional]
   *   The field's value.
   * @return this
   */
  protected function addField($name, $placeholder, $type = null, $value = null) {
    $this->setClause .= $this->setClause ? ", " : " SET ";
    $this->setClause .= "{$name} = {$placeholder}";
    if ($type && $value) {
      $this->types   .= $type;
      $this->values[] = $value;
    }
    return $this;
  }

  /**
   * Set field to value.
   *
   * @param string $fieldName
   *   The field's name.
   * @param mixed $value
   *   The field's value.
   * @param string $expression [optional]
   *   Any kind of expression, note that you'll have to escape field names yourself and that the placeholder
   *   <code>"?"</code> must be included.
   * @return this
   */
  public function set($fieldName, $value, $expression = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(is_null($expression) || strpos($expression, "?") !== false, "Your expression must include the value placeholder (?).");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // We always have to call this method because it also determines the type of the placeholder value.
    $placeholder = $this->getPlaceholder($value);

    return $this->addField($this->sanitizeFieldName($fieldName), $expression ?: $placeholder);
  }

  /**
   * Set field to value if it differs to the old value.
   *
   * @param string $fieldName
   *   The field's name.
   * @param mixed $value
   *   The field's value. <b>NOTE</b> that arrays are automatically serialized.
   * @param mixed $oldValue
   *   The field's old value. <b>NOTE</b> that arrays are automatically serialized.
   * @param string $expression [optional]
   *   Any kind of expression, note that you'll have to escape field names yourself and that the placeholder
   *   <code>"?"</code> must be included.
   * @return this
   */
  public function setConditional($fieldName, $value, $oldValue, $expression = null) {
    is_array($value)    && ($value = serialize($value));
    is_array($oldValue) && ($oldValue = serialize($oldValue));
    if ($value == $oldValue) {
      return $this;
    }
    return $this->set($fieldName, $value, $expression);
  }

  /**
   * Set dynamic column.
   *
   * This will use the <code>"COLUMN_CREATE()"</code> function and overwrite any data previously stored in the dynamic
   * column. This means that your <var>$values</var> must contain absolutely all values that you want to set.
   *
   * @param string $fieldName
   *   The name of the dynamic field to set.
   * @param array $values
   *   The values of the dynamic field to set.
   * @return this
   */
  public function setDynamic($fieldName, array $values) {
    $placeholder = $this->dynamicColumnCreate($fieldName, $values);
    return $this->addField($fieldName, $placeholder);
  }

  /**
   * Set dynamic column key value if it differs from the old value.
   *
   * @param string $fieldName
   *   The name of the dynamic field to set.
   * @param array|string $keys
   *   Either an array or a string, if an array is given all keys will be checked.
   * @param array|null $values
   *   The new values for the dynamic column.
   * @param array|null $oldValues
   *   The old values of the dynamic column.
   * @return this
   */
  public function setDynamicConditional($fieldName, $keys, $values, $oldValues) {
    // Nothing to do if we have no valus at all to compare.
    if (!$values && !$oldValues) {
      return $this;
    }

    // Prepare variables to collect the two actions that we can perform.
    $updates = $deletes = null;

    // Go through all keys and compare the values.
    foreach ((array) $keys as $key) {
      // array_key_exists() tells us if the key exists, even if it's value is NULL (in contrast to isset()).
      $new = array_key_exists($key, (array) $values);
      $old = array_key_exists($key, (array) $oldValues);

      // We want an update if we have a new value that differs from the old value, or if we have a new value but no old
      // value. In any case, the new value must evaluate to true for an update.
      if ((($new && $old && $values[$key] != $old[$key]) || ($new && !$old)) && ($values[$key] == true || $values[$key] == "0")) {
        $updates && ($updates .= ", ");
        $updates .= "{$this->getPlaceholder($key)}, {$this->getPlaceholder($values[$key])}";
      }
      // We want to delete if we have an old value but no new value or if the new value evaluates to false.
      elseif ((!$new && $old) || ($new && $values[$key] == false)) {
        $deletes && ($deletes .= ", ");
        $deletes .= $this->getPlaceholder($key);
      }
    }

    // There's only something to do for us if we have at least a single delete or a single update to perform.
    if ($updates || $deletes) {
      // Sanitize the field's name.
      $fieldName = $this->sanitizeDynamicFieldName($fieldName);

      // We execute deletes first, this will return a valid dynamic column blob for the udpates.
      if ($deletes) {
        $placeholder = "COLUMN_DELETE({$fieldName}, {$deletes})";
      }
      // If we have no deletions, use the field's name to get the valid dynamic column blob for the additions.
      else {
        $placeholder = $fieldName;
      }

      if ($updates) {
        $placeholder = "COLUMN_ADD({$placeholder}, {$updates})";
      }

      $this->addField($fieldName, $placeholder);
    }

    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Convenience Methods


  /**
   * Increment a field.
   *
   * @param string $fieldName
   *   The field's name to increment.
   * @param float|integer $add [optional]
   *   The amount to add, defaults to <code>1</code>.
   * @return this
   */
  public function increment($fieldName, $add = 1) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(is_numeric($add), "The value to add must be numeric.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $fieldName = $this->sanitizeFieldName($fieldName);
    return $this->addField($fieldName, "({$fieldName} + ?)", "d", $add);
  }

  /**
   * Decrement a field.
   *
   * @param string $fieldName
   *   The field's name to decrement.
   * @param float|integer $substract [optional]
   *   The amount to substract, defaults to <code>1</code>.
   * @return this
   */
  public function decrement($fieldName, $substract = 1) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(is_numeric($substract), "The value to substract must be numeric.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $fieldName = $this->sanitizeFieldName($fieldName);
    return $this->addField($fieldName, "({$fieldName} - ?)", "d", $substract);
  }


  // ------------------------------------------------------------------------------------------------------------------- Condition Methods


  /**
   * Add where condition to update query.
   *
   * @param string $fieldName
   *   The field's name to evaluate.
   * @param mixed $value
   *   The value the field should have.
   * @param string $operator [optional]
   *   The operator that should be used to compare the field's value against the given value. If no operator is passed
   *   (<code>NULL</code> default) <code>"AND"</code> is used if the value is atomic and <code>"IN"</code> is used if
   *   the value is an array.
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


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Execute the update against the database.
   *
   * @return integer
   *   The number of affected rows.
   */
  public function execute() {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($this->table), "You must set the table name in order to execute an UPDATE query.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Add the conditions types and values to our types and values if we have any.
    if ($this->conditions) {
      $this->types .= $this->conditions->types;
      $this->values = array_merge((array) $this->values, (array) $this->conditions->values);
    }

    $stmt = $this->connection->prepare($this);
    // @codingStandardsIgnoreStart
    $this->values && $stmt->bind_param($this->types, ...$this->values);
    // @codingStandardsIgnoreEnd
    $stmt->execute();
    // @codingStandardsIgnoreStart
    $affectedRows = $stmt->affected_rows;
    // @codingStandardsIgnoreEnd
    $stmt->close();
    return $affectedRows;
  }

  /**
   * Set the table to operate on.
   *
   * @internal
   *   This method is kept protected to allow concrete classes to redefine the name of it for their public interface.
   *   The purpose of this is to create more human readable public interfaces and make development even more fluent.
   * @param string $name
   *   The table's name.
   * @param string $alias [optional]
   *   An aliad for the table for referencing, defaults to <code>NULL</code> and no alias will be assigned.
   * @return this
   */
  public function table($name, $alias = null) {
    $this->table = $this->getTable($name, $alias);
    return $this;
  }

}
