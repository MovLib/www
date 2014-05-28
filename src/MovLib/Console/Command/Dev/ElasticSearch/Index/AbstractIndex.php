<?php

/* !
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
namespace MovLib\Console\Command\Dev\ElasticSearch\Index;

use \Elasticsearch\Client;

/**
 * Defines the base class for all ElasticSearch index definitions.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractIndex {

  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractIndex";

  // @codingStandardsIgnoreEnd

  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The configuration instance.
   *
   * @var \MovLib\Core\Config
   */
  protected $config;

  /**
   * The index's name.
   *
   * @var string
   */
  public $name;

  /**
   * The index's type mappings.
   *
   * Consists of {@see \MovLib\Console\Command\Dev\ElasticSearch\Mapping\AbstractMapping} objects.
   *
   * @var array
   */
  protected $mappings = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods

  /**
   * Instantiate new index.
   *
   * @param \MovLib\Core\Config $config
   *   The configuration instance.
   * @param string $name
   *   The index's name.
   */
  public function __construct(\MovLib\Core\Config $config, $name) {
    $this->config = $config;
    $this->name   = $name;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add a type mapping to the index.
   *
   * @param \MovLib\Console\Command\Dev\ElasticSearch\Mapping\AbstractMapping $mapping
   *   The mapping to add.
   * @return this
   */
  final protected function addMapping(\MovLib\Console\Command\Dev\ElasticSearch\Mapping\AbstractMapping $mapping) {
    $this->mappings[$mapping->name] = $mapping;
    return $this;
  }


  /**
   * Create the index in ElasticSearch.
   *
   * @return this
   */
  final public function create() {
    $definition = [
      "index" => $this->name,
    ];

    // Add mappings if there are any.
    foreach ($this->mappings as $typeName => $mapping) {
      $definition["body"]["mappings"][$typeName] = $mapping->getDefinition();
    }

    (new Client())->indices()->create($definition);

    return $this;
  }

  /**
   * Delete the index from ElasticSearch.
   *
   * @return this
   */
  final public function delete() {
    // Safeguard.
    assert(!empty($this->name), "The index name cannot be empty");

    try {
      (new Client())->indices()->delete([ "index" => $this->name ]);
    }
    catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
      // Ignore this state, the index wasn't present in the first place.
    }

    return $this;
  }

}
