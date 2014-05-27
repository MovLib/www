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

use \Elasticsearch\Client;
use \MovLib\Core\Search\Result\SearchResult;
use \MovLib\Core\Search\Result\SuggestResult;

/**
 * Defines the search class in charge of searching and making suggestions.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Search {

  /**
   * Execute a fuzzy search on one or more indexes.
   *
   * @param string $query
   *   The query string to search for.
   * @param string|array $indexes
   *   The index(es) to search in. Supply them as numeric array or comma separated string (without whitespace).
   * @param string|array $types [optional]
   *   The document types to search for. Supply them as numeric array or comma separated string (without whitespace). Defaults to no type restrictions.
   * @param array $fields
   *   The document field names to search in, defaults to all fields (which might impact performance).
   * @return array
   *   The search results.
   * @throws \Elasticsearch\Common\Exceptions\*Exception
   *   The most common is {@see \Elasticsearch\Common\Exceptions\Missing404Exception}, which might indicate missing indexes.
   */
  public function fuzzySearch($query, $indexes, $types = null, array $fields = null) {
    $result = [];

    // Convert indexes and types to ElasticSearch syntax if provided as array.
    if (is_array($indexes)) {
      $indexes = implode(",", $indexes);
    }
    if (is_array($types)) {
      $types = implode(",", $types);
    }

    $params = [
      "index" => $indexes,
      "body"  => [
        "query" => [
          "fuzzy_like_this" => [
            "like_text"       => $query,
            "max_query_terms" => 25,
            "min_similarity"  => 0.5
          ],
        ],
      ],
    ];

    if (!empty($types)) {
      $params["type"] = $types;
    }
    if (!empty($fields)) {
      $params["body"]["query"]["fuzzy_like_this"]["fields"] = $fields;
    }

    $searchResult = (new Client())->search($params);

    // We have results, build the set.
    if (isset($searchResult["hits"]["total"]) && $searchResult["hits"]["total"] > 0) {
      foreach ($searchResult["hits"]["hits"] as $document) {
        $doc = new SearchResult($document);
        $result[$doc->index][$doc->type][$doc->id] = $doc;
      }
    }

    return $result;
  }

  /**
   * Execute a fuzzy suggestion search (e.g. autocompletion).
   *
   * Please note that the field used for suggestion will always be "suggest" as indexed by
   * {@see \MovLib\Core\Search\SearchIndexer}.
   *
   * @param string $query
   *   The query string to search for.
   * @param string|array $indexes
   *   The index(es) to search in. Supply them as numeric array or comma separated string (without whitespace).
   * @return array
   *   The suggestion results.
   * @throws \Elasticsearch\Common\Exceptions\*Exception
   *   The most common is {@see \Elasticsearch\Common\Exceptions\Missing404Exception}, which might indicate missing
   *   indexes.
   */
  public function fuzzySuggest($query, $indexes) {
    $result = [];

    // Convert indexes to ElasticSearch syntax if provided as array.
    if (is_array($indexes)) {
      $indexes = implode(",", $indexes);
    }

    $suggestResult = (new Client())->suggest([
      "index" => $indexes,
      "body"  => [
        "fuzzySuggest" => [
          "text" => $query,
          "completion" => [
            "field" => "suggest",
            "fuzzy" => true,
          ],
        ],
      ],
    ]);

    // We have results, build the set.
    if (isset($suggestResult["fuzzySuggest"][0]["options"]) && ($result->numberOfResults = count($suggestResult["fuzzySuggest"][0]["options"])) > 0) {
      foreach ($suggestResult["fuzzySuggest"][0]["options"] as $document) {
        $doc = new SuggestResult($document);
        // Prevent duplicated results, since there is no way in ElasticSearch to do so without losing the matched text.
        if (empty($result[$doc->type][$doc->id])) {
          $result[$doc->type][$doc->id] = $doc;
        }
      }
    }

    return $result;
  }

}
