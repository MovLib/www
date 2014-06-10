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
final class Condition extends AbstractBase {


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
   * {@inheritdoc}
   */
  public $types;

  /**
   * {@inheritdoc}
   */
  public $values;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __toString() {
    if ($this->conditions) {
      return $this->conditions;
    }
    return "";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add condition.
   *
   * @param string $fieldName
   *   The name of the field.
   * @param mixed $value
   *   The value of the field.
   * @param string $operator
   *   The operator to use.
   * @param string $conjunction
   *   The conjunction to use.
   * @return this
   */
  public function condition($fieldName, $value, $operator, $conjunction) {
    $placeholder = null;
    if (is_array($value)) {
      $operator || ($operator = "IN");
      foreach ($value as $v) {
        $placeholder && ($placeholder .= ", ");
        $placeholder .= $this->getPlaceholder($v);
      }
      $placeholder = "({$placeholder})";
    }
    else {
      $operator || ($operator = "=");
      $placeholder .= $this->getPlaceholder($value);
    }
    $this->conditions .= $this->conditions ? " {$conjunction} " : " WHERE ";
    $this->conditions .= "{$this->sanitizeFieldName($fieldName)} {$operator} {$placeholder}";
    return $this;
  }

  /**
   * Add custom condition to query.
   *
   * @param string $snippet
   *   The custom condition.
   * @param string $types
   *   The types of the values.
   * @param array $values
   *   The field values.
   * @return this
   */
  public function customCondition($snippet, $types, array $values) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(empty($this->conditions), "You can only use one of both, custom condition or conditions set via methods.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    $this->conditions = $snippet;
    $this->types     .= $types;
    $this->values     = array_merge((array) $this->values, $values);

    return $this;
  }

}
