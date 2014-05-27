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
namespace MovLib\Core\Search\Result;

/**
 * Defines a single suggestion result.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SuggestResult {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Additional fields (payload) returned by the suggester as associative array.
   *
   * @var array
   */
  public $additionalData = [];

  /**
   * The document's identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The document's term that matched the query.
   *
   * @var string
   */
  public $match;

  /**
   * The document's type.
   *
   * @var string
   */
  public $type;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new suggestion result.
   *
   * @param array $document
   *   The suggestion document as returned by ElasticSearch.
   */
  public function __construct(array $document) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($document["text"]), "The text field of the document must be present, this seems like a malformed ElasticSearch suggestion document");
    assert(!empty($document["payload"]), "The payload field of the document must be present, this seems like a malformed ElasticSearch suggestion document (wrong indexing or mapping?)");
    assert(!empty($document["payload"]["id"]), "The id field of the document must be present in the payload, this seems like a malformed ElasticSearch suggestion document (wrong indexing?)");
    assert(!empty($document["payload"]["type"]), "The id field of the document must be present in the payload, this seems like a malformed ElasticSearch suggestion document (wrong indexing?)");
    // @codeCoverageIgnoreEnd
    // @devEnd

    $this->match = $document["text"];
    foreach ($document["payload"] as $fieldName => $fieldValue) {
      if ($fieldName == "id") {
        $this->id = (integer) $fieldValue;
      }
      elseif ($fieldName == "type") {
        $this->type = $fieldValue;
      }
      else {
        $this->additionalData[$fieldName] = $fieldValue;
      }
    }
  }

}
