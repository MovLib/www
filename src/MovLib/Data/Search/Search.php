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
namespace MovLib\Data\Search;

use \Elasticsearch\Client as ElasticClient;

/**
 * Defines the Search class in charge of searching and indexing.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Search {

  /**
   * The parameters for the operation.
   *
   * @var array
   */
  protected $params;

  /**
   * The request body of the operation.
   *
   * @var array
   */
  protected $body;

  /**
   * The operation to perform.
   *
   * @var string
   */
  protected $operation;

  public function __construct($id, $index, $type) {
    $this->params = [
      "index" => $index,
      "type"  => $type,
      "id"    => $id,
    ];
  }

  /**
   * Prepare the element for deletion.
   */
  public function delete() {
    $this->operation = "delete";
    return $this;
  }

  /**
   * Prepare a simple field for indexing.
   *
   * You can use primitives as well as numeric arrays as value.
   *
   * @param string $name
   *   The field's name.
   * @param mixed $value
   *   The field's value. Allowed types are primitives and numeric arrays.
   * @param boolean $suggest [optional]
   *   Generate suggestions for this field for autocompletion or not, defaults to <code>FALSE</code>.
   * @return $this
   */
  public function indexSimple($name, $value, $suggest = false) {
    $this->operation = "index";

    // ElasticSearch can deal with numeric arrays, so simply put the value to the field.
    $this->body[$name] = $value;

    if ($suggest === true) {
      // Generate suggestions for all values.
      if (is_array($value)) {
        $this->body["suggest"]["input"] = array_merge($this->body["suggest"]["input"], array_values($value));
      }
      else {
        $this->body["suggest"]["input"][] = $value;
      }
    }

    return $this;
  }

  /**
   * Prepare an associative array keyed by language code for indexing.
   *
   * @param string $name
   *   The field's name.
   * @param mixed $value
   *   The field's value. Allowed types are: primitives, numeric arrays and associative arrays with language code as key.
   * @param boolean $suggest [optional]
   *   Generate suggestions for this field for autocompletion or not, defaults to <code>FALSE</code>.
   * @return $this
   */
  public function indexLanguageKeyedArray($name, array $values, $suggest = false) {
    $this->operation = "index";

    foreach ($values as $languageCode => $value) {
      // Put every value to a separate field with the language code as suffix.
      // This has to be done in order to analyze the field with a language specific analyzer.
      $this->body["{$name}_{$languageCode}"] = $value;
    }

    if ($suggest === true) {
      $this->body["suggest"]["input"] = array_merge($this->body["suggest"]["input"], array_values($values));
    }

    return $this;
  }

  /**
   * Prepare a field with multiple values and language codes for indexing.
   *
   * All values must implement {@see \MovLib\Data\Search\SearchLanguageAnalyzerInterface}.
   * If all values use the same language code, use {@see indexSimple} instead.
   * If the values are already keyed by language code, use {@see indexLanguageKeyedArray} instead.
   *
   * @param string $name
   *   The field's name.
   * @param mixed $values
   *   The values to index, must be iterable and the encapsulated values
   *   must implement {@see \MovLib\Data\Search\SearchLanguageAnalyzerInterface}.
   * @param boolean $suggest [optional]
   *   Generate suggestions for this field for autocompletion or not, defaults to <code>FALSE</code>.
   * @return $this
   */
  public function indexLanguageDependentArray($name, $values, $suggest = false) {
    $this->operation = "index";

    /* @var $value \MovLib\Data\Search\SearchLanguageAnalyzerInterface */
    foreach ($values as $value) {
      // @devStart
      // @codeCoverageIgnoreStart
      assert(
        is_a($value, "\\MovLib\\Data\\Search\\SearchLanguageAnalyzerInterface"),
        "All values must implement the \\MovLib\\Data\\Search\\SearchLanguageAnalyzerInterface in order for the indexing to work"
      );
      // @codeCoverageIgnoreEnd
      // @devEnd
      $text = $value->getText();
      // Put every value to a separate field with the language code as suffix.
      // This has to be done in order to analyze the field with a language specific analyzer.
      $this->body["{$name}_{$value->getLanguageCode()}"] = $text;
      if ($suggest === true) {
        $this->body["suggest"]["input"][] = $text;
      }
    }

    return $this;
  }

  public function __destruct() {
    $elasticClient = new ElasticClient();
    if ($this->operation == "index") {
      $this->params["body"] = $this->body;
    }
    $elasticClient->{$this->operation}($this->params);
  }

}
