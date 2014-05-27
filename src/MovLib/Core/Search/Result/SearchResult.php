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
 * Defines a single search result.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SearchResult {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The document's contents (fields) as associative array.
   *
   * @var array
   */
  public $contents;

  /**
   * The document's identifier.
   *
   * @var integer
   */
  public $id;

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
   *   The document as returned by ElasticSearch.
   */
  public function __construct(array $document) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($document["_id"]), "The _id field of the document must be present, this seems like a malformed ElasticSearch document");
    assert(!empty($document["_type"]), "The _type field of the document must be present, this seems like a malformed ElasticSearch document");
    assert(!empty($document["_source"]), "The _source field of the document must be present, this seems like a malformed ElasticSearch document");
    // @codeCoverageIgnoreEnd
    // @devEnd

    $this->id       = (integer) $document["_id"];
    $this->type     = $document["_type"];
    $this->contents = (object) $document["_source"];

    if (isset($this->contents->suggest)) {
      // @todo Do something :P
    }
  }

}
