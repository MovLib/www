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
namespace MovLib\Console\Command\Dev;

use \Elasticsearch\Client;
use \MovLib\Console\MySQLi;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Seed import for elastic search.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SeedElastic extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The name of the ElasticSearch index.
   *
   * @var string
   */
  const INDEX_NAME = "movlib";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The ElasticSearch client.
   *
   * @var \Elasticsearch\Client
   */
  protected $elasticClient;

  /**
   * The database connection.
   *
   * @var \MovLib\Console\MySQLi
   */
  protected $mysqli;


  // ------------------------------------------------------------------------------------------------------------------- Command Methods


  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("seed-elasticsearch");
    $this->setDescription("Seed elasticsearch");
    $this->addArgument("type", InputArgument::OPTIONAL | InputArgument::IS_ARRAY, "The entity types to import.", [ "indices", "all" ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->elasticClient = new Client();
    $this->mysqli        = new MySQLi("movlib");

    $entities = $input->getArgument("type");

    if (array_search("indices", $entities) !== false) {
      $this->writeVerbose("Creating index", self::MESSAGE_TYPE_COMMENT);
      $this->createIndex();
      $this->writeVerbose("Index created successfully!", self::MESSAGE_TYPE_INFO);
      $entities = [ "all" ];
    }

    if (in_array("all", $entities)) {
      $entities = [ "movie", "person" ];
    }

    if ($this->elasticClient->indices()->exists([ "index" => self::INDEX_NAME ]) === false) {
      $this->writeDebug("Index does not exist, creating...", self::MESSAGE_TYPE_INFO);
      $this->createIndex();
    }

    $this->writeVerbose("Indexing database data", self::MESSAGE_TYPE_COMMENT);
    foreach ($entities as $entity) {
      if (method_exists($this, "{$entity}Index") && method_exists($this, "{$entity}Mapping")) {
        $this->writeDebug("Deleting <comment>{$entity}</comment> data...");
        $this->deleteByType($entity);

        $this->writeDebug("Creating type mapping for <comment>{$entity}</comment>...");
        $this->elasticClient->indices()->putMapping([
          "index" => self::INDEX_NAME,
          "type" => $entity,
          "body" => [ $entity => [
            "_source" => [ "enabled" => true ],
            "properties" => $this->{$entity . "Mapping"}(),
          ],
        ]]);

        $this->writeDebug("Indexing <comment>{$entity}</comment> data...");
        $this->{$entity . "Index"}();
      }
      else {
        $this->write("Entity type '{$entity}' is not supported!", self::MESSAGE_TYPE_ERROR);
      }
    }
    return $this->writeVerbose("Indexing completed successfully!", self::MESSAGE_TYPE_INFO);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Index documents via the bulk index API.
   *
   * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-bulk.html#docs-bulk Bulk index API
   *
   * @param string $type
   *   The document type to index.
   * @param array $docs
   *   The documents to index.
   *
   *   Associative array containing the identifiers as keys and the fields of the documents as
   *   associative array in the values.
   * @return $this
   */
  protected function bulkIndex($type, $docs) {
    if (!empty($docs)) {
      $requestBody = [];
      foreach ($docs as $id => $fields) {
        // This is the only correct way due to lack of usability in the ElasticSearch Bulk API!
        // http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-bulk.html#docs-bulk
        $requestBody[] = [ "index" => [ "_index" => self::INDEX_NAME, "_type" => $type, "_id" => $id ] ];
        $requestBody[] = [ "doc" => $fields ];
      }
      // Finally index all the data.
      $this->elasticClient->bulk([ "body" => $requestBody ]);
    }
    return $this;
  }

  /**
   * Create configuration for indices and type mappings.
   *
   * @return $this
   */
  protected function createIndex() {
    // Delete all indexes.
    $this->writeDebug("Deleting old indices...");
    $this->elasticClient->indices()->delete([ "index" => "_all" ]);

    // Create movlib index.
    $this->writeDebug("Creating index...");
    $this->elasticClient->indices()->create([ "index" => self::INDEX_NAME ]);

    return $this;
  }

  /**
   * Delete mapping and all documents with the specified type.
   *
   * @param string $type
   *   The document type to delete.
   * @return $this
   */
  protected function deleteByType($type) {
    try {
      $this->elasticClient->indices()->deleteMapping([ "index" => self::INDEX_NAME, "type" => $type ]);
    }
    catch(\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
      $this->writeDebug("Type <comment>{$type}</comment> already deleted, proceeding...");
    }
    return $this;
  }

  /**
   * Index all movies from the database.
   *
   * @return $this
   */
  protected function movieIndex() {
    $movies = null;

    $result = $this->mysqli->query(<<<SQL
SELECT
  `movies`.`id`,
  `movies`.`year`,
  `movies_titles`.`title`,
  `movies_display_titles`.`title_id` AS `displayTitle`,
  `movies_original_titles`.`title_id` AS `originalTitle`,
  `movies_genres`.`genre_id` AS `genreId`
FROM `movies`
INNER JOIN `movies_titles`
  ON `movies_titles`.`movie_id` = `movies`.`id`
LEFT JOIN `movies_display_titles`
  ON `movies_display_titles`.`title_id` = `movies_titles`.`id`
LEFT JOIN `movies_original_titles`
  ON `movies_original_titles`.`title_id` = `movies_titles`.`id`
LEFT JOIN `movies_genres`
  ON `movies_genres`.`movie_id` = `movies`.`id`
ORDER BY `movies`.`id` ASC
SQL
    );

    // Aggregate all movie information needed for indexing.
    while ($row = $result->fetch_object()) {
      $movies[$row->id]["year"]     = $row->year;
      if ($row->originalTitle) {
        $movies[$row->id]["original_title"] = $row->title;
      }
      elseif ($row->displayTitle) {
        $movies[$row->id]["display_titles"][] = $row->title;
      }
      else {
        $movies[$row->id]["titles"][] = $row->title;
      }
      if ($row->genreId) {
        $movies[$row->id]["genres"][] = $row->genreId;
      }
    }

    // Index all movies with the bulk API.
    $this->bulkIndex("movie", $movies);

    return $this;
  }

  /**
   * Create mapping for type "movie".
   *
   * @return array
   *   The property mapping.
   */
  protected function movieMapping() {
    return [
      "titles"         => [ "type" => "string", "analyzer" => "simple" ],
      "display_titles" => [ "type" => "string", "analyzer" => "simple", "boost" => 2.0 ],
      "original_title" => [ "type" => "string", "analyzer" => "simple", "boost" => 2.0 ],
      "year"           => [ "type" => "short" ],
      "genres"         => [ "type" => "integer" ],
    ];
  }

  /**
   * Index all persons from the database.
   *
   * @return $this
   */
  protected function personIndex() {
    $persons = null;

    $result = $this->mysqli->query(<<<SQL
SELECT
  `persons`.`id`,
  `persons`.`name`,
  `persons`.`born_name` AS `bornName`,
  `persons_aliases`.`alias`
FROM `persons`
LEFT JOIN `persons_aliases`
  ON `persons_aliases`.`person_id` = `persons`.`id`
ORDER BY `persons`.`id` ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      $persons[$row->id]["name"] = $row->name;
      if (!empty($row->bornName)) {
        $persons[$row->id]["born_name"] = $row->bornName;
      }
      if (!empty($row->alias)) {
        $persons[$row->id]["aliases"][] = $row->alias;
      }
    }

    $this->bulkIndex("person", $persons);

    return $this;
  }

  /**
   * Create mapping for type "person".
   *
   * @return array
   *   The property mapping
   */
  protected function personMapping() {
    return [
      "name"      => [ "type" => "string", "analyzer" => "simple", "boost" => 2.0 ],
      "born_name" => [ "type" => "string", "analyzer" => "simple", "boost" => 2.0 ],
      "aliases"   => [ "type" => "string", "analyzer" => "simple" ],
    ];
  }

}
