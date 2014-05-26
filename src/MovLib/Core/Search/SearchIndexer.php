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
namespace MovLib\Core\Search;

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
final class SearchIndexer {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Data array to collect fields for indexing.
   *
   * @var array
   */
  protected $data;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new search manager.
   *
   * You can leave all parameters empty to instantiate an empty search manager, but if you do so, no indexing will
   * be possible!
   *
   * @param string $index [optional]
   *   The index to use for the operation, will mostly be the entity's plural key.
   * @param string $type [optional]
   *   The type of the document, will mostly be the entity's singular key.
   * @param integer $id [optional]
   *   The entity's identifier.
   */
  public function __construct($index = null, $type = null, $id = null) {
    if (isset($id)) {
      $this->params = [
        "index" => $index,
        "type"  => $type,
        "id"    => $id,
      ];
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- External Methods


  /**
   * Add additional data for search suggestions (autocompletion).
   *
   * This data will be returned in addition to the matched text when suggestions (autocompletion) are made.
   * Very useful when a database access can be prevented because of this data, but it shouldn' be overused however,
   * since it will bloat our index.
   * Please note that the id and type will be included in this additional data automatically.
   *
   * @param string $name
   *   The field name to add, only primitives and numeric array are allowed.
   * @param string $value
   *   The field's value.
   * @return this
   */
  public function addSuggestionData($name, $value) {
    // Guardian pattern.
    if (!empty($value)) {
      $this->addIndexField("analyzeSuggestionData", $name, $value, false, false);
    }
    return $this;
  }

  /**
   * Execute the indexing.
   *
   * The element will either be indexed or deleted, according to its deleted state.
   * Furthermore, the operation execution will be delayed, since the user isn't interested in waiting on our search
   * to index his/her changes.
   *
   * @param \MovLib\Core\Kernel $kernel
   *   The kernel instance.
   * @param \MovLib\Core\Log $log
   *   The current logger instance.
   * @param boolean $deleted
   *   The entity's deleted state. Determines whether the entity will be indexed or removed from the index.
   * @return this
   */
  public function execute(\MovLib\Core\Kernel $kernel, \MovLib\Core\Log $log, $deleted) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($this->params["index"]), "The index name must be set to index a document");
    assert(!empty($this->params["type"]), "The type name must be set to index a document");
    assert(!empty($this->params["id"]), "The id must be set to index a document");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // We can delay the actual indexing until the request was sent to the user because it doesn't matter for the user
    // how fast the created/edited/deleted entity is available in our search.
    $kernel->delayMethodCall("search.execute_indexing", $this, "executeIndexing", [ $log, $deleted, $this->params ]);

    // We've stacked this indexing job and now the caller can start a new one.
    $this->params = null;

    return $this;
  }

  /**
   * Prepare a simple field for indexing (without generating search suggestions).
   *
   * You can use primitives as well as numeric arrays as value.
   *
   * @param string $name
   *   The field's name.
   * @param mixed $value
   *   The field's value. Allowed types are primitives and numeric arrays.
   * @return this
   */
  public function indexSimple($name, $value) {
    // Guardian pattern.
    if (!empty($value)) {
      $this->addIndexField("analyzeSimpleField", $name, $value, false, false);
    }
    return $this;
  }

  /**
   * Prepare a simple field for indexing as search suggestion (without generating a field in the document).
   *
   * You can use primitives as well as numeric arrays as value.
   *
   * @param mixed $value
   *   The suggestion(s). Allowed types are primitives and numeric arrays.
   * @param boolean $humanName
   *   Add additional suggestions for human names or not, defaults to <code>FALSE</code>.
   * @return this
   */
  public function indexSimpleSuggestion($value, $humanName = false) {
    // Guardian pattern.
    if (!empty($value)) {
      $this->addIndexField("analyzeSimpleFieldSuggestion", null, $value, false, $humanName);
    }
    return $this;
  }

  /**
   * Prepare a field with multiple values and language codes for indexing without generating search suggestions.
   *
   * The array may be keyed by language code, otherwise all values must be objects which implement {@see \MovLib\Data\Search\SearchLanguageAnalyzerInterface}.
   * If all values use the same language code, use {@see indexSimple} instead.
   *
   * @param string $name
   *   The field's name.
   * @param array $values
   *   The values to index, the encapsulated values must implement {@see \MovLib\Data\Search\SearchLanguageAnalyzerInterface}.
   * @return this
   */
  public function indexLanguage($name, array $values) {
    // Guardian pattern.
    if (!empty($values)) {
      $this->addIndexField("analyzeLanguageField", $name, $values, false, false);
    }
    return $this;
  }

  /**
   * Prepare a field with multiple values and language codes for indexing and generate search suggestions.
   *
   * The array may be keyed by language code, otherwise all values must be objects which implement {@see \MovLib\Data\Search\SearchLanguageAnalyzerInterface}.
   * If all values use the same language code, use {@see indexSimple} instead.
   *
   * @param string $name
   *   The field's name.
   * @param array $values
   *   The values to index, the encapsulated values must implement {@see \MovLib\Data\Search\SearchLanguageAnalyzerInterface}.
   * @return this
   */
  public function indexLanguageSuggestion($name, array $values) {
    // Guardian pattern.
    if (!empty($values)) {
      $this->addIndexField("analyzeLanguageField", $name, $values, true, false);
    }
    return $this;
  }

  /**
   * Set the search document's index.
   *
   * @param string $index
   *   The search document's index to set.
   * @return this
   */
  public function setIndex($index) {
    $this->params["index"] = $index;
    return $this;
  }

  /**
   * Set the search document's type.
   *
   * @param string $type
   *   The search document's type to set.
   * @return this
   */
  public function setType($type) {
    $this->params["type"] = $type;
    return $this;
  }

  /**
   * Set the search document's unique identifier.
   *
   * @param mixed $id
   *   The search document's unique identifier.
   * @return this
   */
  public function setIdentifier($id) {
    $this->params["id"] = $id;
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Internal Methods


  /**
   * Add a field for indexing.
   *
   * @param string $analyzer
   *   The analyzer method to use.
   * @param string $name
   *   The field's name.
   * @param mixed $value
   *   The field's value.
   * @param boolean $suggest
   *   Generate autocomplete suggestions for this field or not.
   * @param boolean $humanName
   *   Add additional suggestions for human names or not.
   * @return this
   */
  protected function addIndexField($analyzer, $name, $value, $suggest, $humanName) {
    $fieldConfig = (object) [
      "name"      => $name,
      "value"     => $value,
      "suggest"   => $suggest,
      "humanName" => $humanName,
    ];
    // Name may be null for analyzers which only generate suggestions.
    if (empty($name)) {
      $this->data[$analyzer][] = $fieldConfig;
    }
    else {
      $this->data[$analyzer][$name] = $fieldConfig;
    }
    return $this;
  }

  /**
   * Analyze a language dependent field and add it to the request body (including suggestions).
   *
   * @param array $body
   *   The request body for the indexing operation.
   * @param \MovLib\Stub\Data\Search\IndexFieldConfig $field
   * @return this
   */
  protected function analyzeLanguageField(&$body, $field) {
    foreach ($field->value as $languageCode => $text) {
      if ($text instanceof \MovLib\Data\Search\LanguageAnalyzerInterface) {
        $languageCode = $text->getLanguageCode();
        $text         = $text->getText();
      }

      if (empty($text)) {
        continue;
      }
      // Put every value to a separate field with the language code as suffix.
      // This has to be done in order to analyze the field with a language specific analyzer.
      $body["{$field->name}_{$languageCode}"] = $text;

      if ($field->suggest === true) {
        $body["suggest"]["input"][] = $text;
      }
    }

    return $this;
  }

  /**
   * Analyze a simple field and add it to the request body (including suggestions).
   *
   * @param array $body
   *   The request body for the indexing operation.
   * @param \MovLib\Stub\Data\Search\IndexFieldConfig $field
   * @return this
   */
  protected function analyzeSimpleField(&$body, $field) {
    foreach ((array) $field->value as $v) {
      // Ingnore empty elements.
      if (empty($v)) {
        continue;
      }
      $body[$field->name][] = $v;
    }

    return $this;
  }

  /**
   * Analyze suggestions for a simple field and add it to the request body (including suggestions).
   *
   * @param array $body
   *   The request body for the indexing operation.
   * @param \MovLib\Stub\Data\Search\IndexFieldConfig $field
   * @return this
   */
  protected function analyzeSimpleFieldSuggestion(&$body, $field) {
    foreach ((array) $field->value as $v) {
      // Ingnore empty elements.
      if (empty($v)) {
        continue;
      }

      // Add the suggestion for every element.
      $body["suggest"]["input"][] = $v;
      // Generate a suggestion in the form [lastname], [firstname] to improve suggestions.
      if ($field->humanName === true) {
        $explodedIndexValue = explode(" ", $v);
        if (count($explodedIndexValue) > 1) {
          $body["suggest"]["input"][] = array_pop($explodedIndexValue) . ", " . implode(" ", $explodedIndexValue);
        }
      }

    }
  }

  /**
   * Analyze suggestion payload data add it to the request body (including suggestions).
   *
   * @param array $body
   *   The request body for the indexing operation.
   * @param \MovLib\Stub\Data\Search\IndexFieldConfig $field
   * @return this
   */
  protected function analyzeSuggestionData(&$body, $field) {
    if (is_array($field->value)) {
      foreach ($field->value as $v) {
        if (empty($v)) {
          continue;
        }
        $body["suggest"]["payload"][$field->name][] = $v;
      }
    }
    else {
      $body["suggest"]["payload"][$field->name] = $field->value;
    }
    return $this;
  }

  /**
   * Execute the indexing.
   *
   * Do not use this method directly, since it will execute the operation immediately. Use {@see execute} instead.
   * The element will either be indexed or deleted, according to its deleted state.
   *
   * @param \MovLib\Core\Log $log
   *   The current logger instance.
   * @param boolean $deleted
   *   The entity's deleted state. Determines whether the entity will be indexed or removed from the index.
   * @param array $definition
   *   Definition of the entity that should be indexed.
   * @return this
   */
  protected function executeIndexing(\MovLib\Core\Log $log, $deleted, array $definition) {
    static $elasticClient = null;
    if (!$elasticClient) {
      $elasticClient = new ElasticClient();
    }

    // Remove the element from the index.
    if ($deleted === true) {
      try {
        $elasticClient->delete($definition);
      }
      // The element wasn't in the index in the first place, log as info.
      catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
        $log->info($e);
      }
    }
    // Insert or update the element in the index.
    else {
      $definition["body"] = [];
      // Build the request body with the analyzers.
      foreach ($this->data as $analyzer => $fields) {
        foreach ($fields as $fieldConfig) {
          $this->{$analyzer}($definition["body"], $fieldConfig);
        }
      }

      // Finally index the document.
      $elasticClient->index($definition);
    }

    return $this;
  }

}
