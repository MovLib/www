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
 * Defines the database condition object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Condition extends AbstractQuery {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Condition";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * String containing the compiled conditions.
   *
   * @var string
   */
  protected $conditions;

  /**
   * String containing the types of the values for auto-sanitization by the prepared statement.
   *
   * @var string
   */
  protected $types;

  /**
   * Numeric array containing the values for the fields.
   *
   * @var array
   */
  protected $values;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  public function __toString() {
    return "";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  protected function buildCondition($fieldName, $operator, $conjunction, $clause) {

  }

  public function condition($fieldName, $value, $operator = null, $conjunction = "AND") {
    $placeholder = null;
    if (is_array($value)) {
      !$operator && ($operator = "IN");
      foreach ($value as $v) {
        $placeholder && ($placeholder .= ", ");
        $placeholder .= $this->setValue($v);
      }
      $placeholder = "({$placeholder})";
    }
    else {
      !$operator && ($operator = "=");
      $placeholder .= $this->setValue($value);
    }
    $this->conditions .= $this->conditions ? " {$conjunction} " : " WHERE ";
    $this->conditions .= "{$this->sanitizeFieldName($fieldName)} {$operator} {$placeholder}";
    return $this;
  }

  /**
   * Add custom where condition to query.
   *
   * <b>NOTE</b><br>
   * This will overwrite any previously set conditions. You have to take care that you use the correct aliases, escape
   * field names and insert propert placeholders.
   *
   * @param string $snippet
   *   The where condition without the <code>"WHERE "</code> prefix.
   * @param string $types
   *   The types of the values.
   * @param array $values
   *   The field values.
   * @return this
   */
  public function customCondition($snippet, $types, array $values) {
    // We have to empty any previously set conditions.
    $this->conditions = " WHERE {$snippet}";
    $this->types      = $types;
    $this->values     = $values;
    return $this;
  }

}
