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
namespace MovLib\Data\Genre;

use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision entity object for genre entities.
 *
 * @property \MovLib\Data\Genre\Genre $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class GenreRevision extends \MovLib\Data\Revision\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The genre entity type identifier.
   *
   * @var integer
   */
  const ENTITY_ID = 9;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing all the genre's localized names, keyed by language code.
   *
   * @var array
   */
  public $names;

  /**
   * Associative array containing all the genre's localized descriptions, keyed by language code.
   *
   * @var array
   */
  public $descriptions;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new genre revision.
   *
   * @param integer $genreId [optional]
   *   The genre's unique identifier, defaults to <code>NULL</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no genre exists for the given genre identifier.
   */
  public function __construct($genreId = null) {
    if ($genreId) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `id`,
  `user_id`,
  `changed`,
  `deleted`,
  COLUMN_JSON(`dyn_descriptions`),
  COLUMN_JSON(`dyn_names`),
  COLUMN_JSON(`dyn_wikipedia`)
FROM `genres`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $genreId);
      $stmt->execute();
      $stmt->bind_result(
        $this->entityId,
        $this->userId,
        $this->id,
        $this->deleted,
        $this->descriptions,
        $this->names,
        $this->wikipediaLinks
      );
      $found = $stmt->fetch();
      $stmt->close();

      if (!$found) {
        throw new NotFoundException("Couldn't find Genre {$genreId}");
      }

      $this->jsonDecode($this->descriptions, $this->names, $this->wikipediaLinks);
    }
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    return array_merge(parent::__sleep(), [ "descriptions", "names", "wikipediaLinks" ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function indexSearch() {

    $elasticClient = new \Elasticsearch\Client();
    // Remove from index if the genre was deleted.
    $params = [ "index" => "genres", "type" => "genre", "id" => $this->entityId, "refresh" => true ];
    if ($this->deleted === true) {
      try {
        $elasticClient->delete($params);
      }
      catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
        // The document was already gone, just ignore this state.
      }
    }
    else {
      // @devStart
      // @codeCoverageIgnoreStart
      assert(!empty($this->names), "You cannot index a genre without names.");
      assert(isset($this->entityId), "You cannot index a genre without an identifier.");
      // @codeCoverageIgnoreEnd
      // @devEnd
      $params["body"] = $this->names;
      $params["body"]["suggest"]["input"] = array_values($this->names);
      $params["body"]["suggest"]["output"] = $this->names[$this->intl->defaultLanguageCode];
      $params["body"]["suggest"]["payload"] = $this->names;
      $params["body"]["suggest"]["payload"]["genreId"] = $this->entityId;
      $elasticClient->index($params);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntity(\MovLib\Data\AbstractEntity $entity, $languageCode, &...$dynamicProperties) {
    return parent::setEntity(
      $entity,
      $languageCode,
      $entity->name,
      $this->names,
      $entity->description,
      $this->descriptions,
      $entity->wikipedia,
      $this->wikipediaLinks
    );
  }

}
