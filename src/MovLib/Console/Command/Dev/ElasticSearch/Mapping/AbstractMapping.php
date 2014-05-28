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
namespace MovLib\Console\Command\Dev\ElasticSearch\Mapping;

/**
 * Defines the base class for all ElasticSearch type mappings.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractMapping {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The default analyzer to use.
   *
   * @var string
   */
  const DEFAULT_ANALYZER = "simple";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Language code to ElasticSearch analyzer mapping.
   *
   * @var array
   */
  protected static $languageAnalyzers = [
    "ar" => "arabic",
    "hy" => "armenian",
    "eu" => "basque",
    "bg" => "bulgarian",
    "ca" => "catalan",
    "zh" => "chinese",
    "cs" => "czech",
    "da" => "danish",
    "nl" => "dutch",
    "en" => "english",
    "fi" => "finnish",
    "fr" => "french",
    "gl" => "galician",
    "de" => "german",
    "el" => "greek",
    "hi" => "hindi",
    "hu" => "hungarian",
    "id" => "indonesian",
    "it" => "italian",
    "no" => "norwegian",
    "fa" => "persian",
    "pt" => "portuguese",
    "ro" => "romanian",
    "ru" => "russian",
    "es" => "spanish",
    "sv" => "swedish",
    "tr" => "turkish",
    "th" => "thai"
  ];

  /**
   * Flag to determine whether the body of the document should be included in the search.
   *
   * Normally this should be enabled, it can be turned off for increasing performance however.
   *
   * @var boolean
   */
  protected $enabled = true;

  /**
   * The mapping's name.
   *
   * @var string
   */
  public $name;

  /**
   * The configuration instance.
   *
   * @var \MovLib\Core\Config
   */
  protected $config;

  /**
   * The mapping's properties ready for export.
   *
   * @var array
   */
  protected $properties = [];

  /**
   * Flag to determine whether to add a field for suggestions (for e.g. autocompletion) or not.
   *
   * Normally, this will be enabled.
   *
   * @var boolean
   */
  protected $hasSuggestions;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new mapping.
   *
   * @param \MovLib\Core\Config $config
   *   The configuration instance.
   * @param string $name
   *   The mapping's name.
   * @param boolean $suggestions [optional]
   *   Add a field for suggestions (for e.g. autocompletion) or not, defaults to <code>TRUE</code>.
   */
  public function __construct(\MovLib\Core\Config $config, $name, $suggestions = true) {
    $this->name = $name;
    $this->config = $config;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add a language dependent string field to the mapping.
   *
   * This will actually generate a field for every language we support in the form [field_name]_[language_code].
   * The fields generated this will always be analyzed and tokenized for searching.
   * Please note that ElasticSearch can deal with arrays on all fields.
   *
   * @param string $fieldName
   *   The field's name.
   * @return this
   */
  final protected function addLanguageDependentField($fieldName) {
    // Add a field for each language for analyzing purposes.
    foreach ($this->config->locales as $code => $locale) {
      // Set the correct ElasticSearch analyzer for the language.
      if (isset(self::$languageAnalyzers[$code])) {
        $analyzer = self::$languageAnalyzers[$code];
      }
      else {
        $analyzer = self::DEFAULT_ANALYZER;
      }

      $this->properties["{$fieldName}_{$code}"] = [
        "type"     => "string",
        "analyzer" => $analyzer,
      ];
    }

    return $this;
  }

  /**
   * Add a number field to the mapping.
   *
   *
   * @param string $fieldName
   *   The field's name.
   * @param string $type [optional]
   *   The field's data type. Supported values are "float", "double", "integer", "long", "short", "byte".
   *   Defaults to "integer".
   * @return this
   */
  final protected function addNumberField($fieldName, $type = "integer") {
    $this->properties[$fieldName] = [

    ];

    return $this;
  }

  /**
   * Add a string field to the mapping. Does not analyze the field for a specific language.
   *
   * Please note that ElasticSearch can deal with arrays on all fields.
   *
   * @param string $fieldName
   *   The field's name.
   * @param boolean $analyze
   *   Analyze (tokenize) the field for searching or not, defaults to <code>TRUE</code>.
   * @return this
   */
  final protected function addStringField($fieldName, $analyze = true) {
    $this->properties[$fieldName] = [
      "type"     => "string",
      "analyzer" => self::DEFAULT_ANALYZER,
    ];

    if ($analyze === false) {
      $this->properties[$fieldName]["index"] = "not_analyzed";
    }

    return $this;
  }

  /**
   * Get the mapping definition for putting it to the index.
   *
   * @return array
   *   The mapping definition.
   */
  final public function getDefinition() {
    // Safeguard to avoid empty definitions.
    assert(
      !empty($this->properties) || $this->hasSuggestions,
      "You have to add fields or at least enable suggestions for entities without own fields"
    );
    assert(!array_key_exists("suggest", $this->properties), "The field name 'suggest' is reserved for suggestions");

    // Add a suggestion field if desired.
    if ($this->hasSuggestions === true) {
      $this->properties["suggest"] = [
        "type"     => "completion",
        "analyzer" => "simple",
        "payloads" => true,
      ];
    }

    return [
      "_source" => [ "enabled" => $this->enabled ],
      "properties" => $this->properties,
    ];
  }

}
