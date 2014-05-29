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
use \MovLib\Core\Search\SearchIndexer;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines the Elasticsearch importer.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SeedElastic extends \MovLib\Console\Command\AbstractCommand {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "SeedElastic";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("seed-elastic");
    $this->setDescription("Seed ElasticSearch indexes");
    $this->addArgument("index", InputArgument::OPTIONAL | InputArgument::IS_ARRAY, "The indexes and data to import.", [ "all" ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $indexes         = (array) $input->getArgument("index");
    $entitiesToIndex = [];
    $indexNamespace  = "\\MovLib\\Console\\Command\\Dev\ElasticSearch\\Index\\";

    // Add all available index definitions if no argument or argument "all" was supplied.
    if (array_search("all", $indexes) !== false) {
      $indexes = null;
      $this->writeVerbose("Found special argument <comment>all</comment>, creating and indexing all entities in ElasticSearch");
      /* @var $fileInfo \SplFileInfo */
      foreach (new \DirectoryIterator("dr://src/MovLib/Console/Command/Dev/ElasticSearch/Index") as $fileInfo) {
        if ($fileInfo->isFile() && $fileInfo->getExtension() == "php") {
          $className = $fileInfo->getBasename(".php");
          $reflector = new \ReflectionClass("{$indexNamespace}{$className}");
          if ($reflector->isAbstract() !== false) {
            continue;
          }
          $indexes[] = $className;
        }
      }
    }

    $elasticClient = new Client();

    // Create all indexes.
    foreach ($indexes as $indexName) {
      $indexClass = $indexNamespace . ucfirst($indexName);
      $index = new $indexClass($this->config);

      // Delete the index.
      $this->writeVerbose("Deleting index <comment>{$index->name}</comment>");
      $index->delete();

      // Create index and mappings.
      $this->writeVerbose("Creating index <comment>{$index->name}</comment> and its mappings");
      $index->create();

      // Add the mapping types to the entities which have to be indexed.
      foreach ($index->mappings as $mapping) {
        $entitiesToIndex[$mapping->name] = ucfirst($mapping->name);
      }
    }
    $this->writeVerbose("Index(es) created successfully", self::MESSAGE_TYPE_INFO);

    // Index all entities constructed earlier.
    foreach ($entitiesToIndex as $entityClassName) {
      $setClass      = "\\MovLib\\Data\\{$entityClassName}\\{$entityClassName}Set";
      $revisionClass = "\\MovLib\\Data\\{$entityClassName}\\{$entityClassName}Revision";

      if (!class_exists($setClass)) {
        $this->write("Cannot index {$entityClassName}, class {$setClass} does not exist!", self::MESSAGE_TYPE_ERROR);
      }
      if (!class_exists($revisionClass)) {
        $this->write("Cannot index {$entityClassName}, class {$setClass} does not exist!", self::MESSAGE_TYPE_ERROR);
      }

      $this->writeVerbose("Indexing data for entity <comment>{$entityClassName}</comment>");

      /* @var $set \MovLib\Data\AbstractEntitySet */
      $set = new $setClass($this->container);
      $set->loadOrdered("`id`", 0, $limit = $set->getTotalCount());

      /* @var $entity \MovLib\Data\AbstractEntity */
      foreach ($set as $id => $entity) {
        // If this class is not indexable, stop the operation.
        if (!method_exists($entity, "indexSearch")) {
          $this->write("Entity {$entityClassName} is not indexable, aborting...", self::MESSAGE_TYPE_ERROR);
          break;
        }

        // Set the identifier.

        // @todo This is very inefficient, rework this when there is time.
        $revision = new $revisionClass($entity->id);
        $reflector = new \ReflectionMethod($entity, "indexSearch");
        $reflector->setAccessible(true);
        $reflector->invoke($entity, $revision);
      }
    }

    $this->writeVerbose("All documents were successfully indexed, all done!", self::MESSAGE_TYPE_INFO);

    return 0;
  }

}
