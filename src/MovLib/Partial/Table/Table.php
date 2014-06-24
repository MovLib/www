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
namespace MovLib\Partial\Table;

use \MovLib\Partial\HTMLAttributes;

/**
 * Defines the table partial object
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Table {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Table";

  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The table's attributes.
   *
   * @var \MovLib\Partial\HTMLAttributes
   */
  public $attributes;

  /**
   * The table's colgroups as numeric array.
   *
   * @var array
   */
  public $colGroups = [];

  /**
   * The table's header.
   *
   * @var array
   */
  public $header = [];

  /**
   * The table's rows.
   *
   * @var array
   */
  public $rows = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new table.
   *
   * @param array $tableAttributes [optional]
   *   The table's attributes, defaults to no attributes.
   * @param array $headerCells [optional]
   *   The table's header as numeric array of cells.
   *   Each cell must be an associative array with the "#content" offset determining its content. All other offsets will
   *   be treated as its attributes.
   * @param array $headerAttributes [optional]
   *   The table header's attributes, defaults to no attributes.
   */
  public function __construct(array $tableAttributes = [], array $headerCells = null, array $headerAttributes = []) {
    $this->attributes = new HTMLAttributes($tableAttributes);

    $cells = [];
    foreach ((array) $headerCells as $cell) {
      if (array_key_exists("#content", $cell)) {
        $content = $cell["#content"];
        unset($cell["#content"]);
      }
      else {
        $content = "";
      }
      $cells[] = [
        "attributes" => new HTMLAttributes($cell),
        "content"    => $content,
      ];
    }
    $this->header = [
      "attributes" => new HTMLAttributes($headerAttributes),
      "cells"      => $cells,
    ];
  }

  /**
   * Render the HTML table.
   *
   * @return string
   *   The rendered HTML table.
   */
  public function __toString() {
    $colGroups = null;
    foreach ($this->colGroups as $colGroup) {
      $cols = null;
      foreach ($colGroup["cols"] as $colDef) {
        $cols .= "<col{$colDef}>";
      }
      if ($cols) {
        $colGroups .= "<colgroup{$colGroup["attributes"]}>{$cols}</colgroup>";
      }
    }

    $header = null;

    foreach ($this->header["cells"] as $cell) {
      $header .= "<th{$cell["attributes"]}>{$cell["content"]}</th>";
    }
    if ($header) {
      $header = "<thead{$this->header["attributes"]}><tr>{$header}</tr></thead>";
    }

    $body = null;
    foreach ($this->rows as $row) {
      $rowRendered = null;
      foreach ($row["cells"] as $cell) {
        $rowRendered .= "<td{$cell["attributes"]}>{$cell["content"]}</td>";
      }
      if ($rowRendered) {
        $body .= "<tr{$row["attributes"]}>{$rowRendered}</tr>";
      }
    }

    return "<table{$this->attributes}>{$colGroups}{$header}<tbody>{$body}</tbody></table>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add a new row to the table.
   *
   * @param array $cells
   *   The row's cells as numeric array.
   *   Each cell must be an associative array with the "#content" offset determining its content. All other offsets will
   *   be treated as its attributes.
   * @param array $attributes [optional]
   *   The row's attributes, defaults to no attributes.
   * @return $this
   */
  public function addRow(array $cells, array $attributes = []) {

    $rowCells = [];
    foreach ((array) $cells as $cell) {
      if (array_key_exists("#content", $cell)) {
        $content = $cell["#content"];
        unset($cell["#content"]);
      }
      else {
        $content = "";
      }
      $rowCells[] = [
        "attributes" => new HTMLAttributes($cell),
        "content"    => $content,
      ];
    }
    $this->rows[] = [
      "attributes" => new HTMLAttributes($attributes),
      "cells"      => $rowCells,
    ];

    return $this;
  }

  /**
   * Add a new colgroup definition to the table.
   *
   * @param array $cols
   *   The cols as numeric array containing the col attributes as associative arrays.
   * @param array $attributes
   *   The colgroup's attributes, defaults to no attributes.
   * @return $this
   */
  public function addColGroup(array $cols, array $attributes = []) {
    $colDefs = [];
    foreach ($cols as $col) {
      $colDefs[] = new HTMLAttributes($col);
    }

    $this->colGroups[] = [
      "attributes" => new HTMLAttributes($attributes),
      "cols"       => $colDefs,
    ];

    return $this;
  }

}
