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
 * Defines the set for search results.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ResultSet implements \ArrayAccess, \Countable, \Iterator {

  /**
   * The total number of results returned by the search.
   *
   * @var integer
   */
  public $numberOfResults = 0;

  /**
   * The search results as numeric array, containing the documents as objects.
   * Possible types at the moment: {@see \MovLib\Core\Search\Result\SearchResult} and {@see \MovLib\Core\Search\Result\SuggestResult}.
   *
   * @var array
   */
  public $results = [];


  // ------------------------------------------------------------------------------------------------------------------- Iterator Methods


  /**
   * Get the current result.
   *
   * @return array|boolean
   *   The current result, or <code>FALSE</code> if there the array is empty or the current result is invalid.
   */
  public function current() {
    return current($this->results);
  }

  /**
   * Get the offset of the current result.
   *
   * @return mixed
   *   The offset of the current result, <code>NULL</code> if there are no results or the current pointer is
   *   beyond the array's elements.
   */
  public function key() {
    return key($this->results);
  }

  /**
   * Get the next result.
   *
   * @return array|boolean
   *   The next result, or <code>FALSE</code> if there is no next result.
   */
  public function next() {
    return next($this->results);
  }

  /**
   * Rewind the iterator to the first result.
   *
   * @return array|boolean
   *   The first result, or <code>FALSE</code> if there are no more results.
   */
  public function rewind() {
    return reset($this->results);
  }

  /**
   * Check if the current position is valid.
   *
   * @return boolean
   *   <code>TRUE</code> if the current position is valid, <code>FALSE</code> otherwise.
   */
  public function valid() {
    return key($this->results) !== null;
  }


  // ------------------------------------------------------------------------------------------------------------------- Countable Methods


  /**
   * Implements <code>count()</code> callback.
   *
   * @return integer
   *   Total count of results in the set.
   */
  public function count() {
    return $this->numberOfResults;
  }


  // ------------------------------------------------------------------------------------------------------------------- Array Access Methods


  /**
   * @return boolean
   */
  public function offsetExists($offset) {
    return isset($this->results[$offset]);
  }

  /**
   * @return mixed
   */
  public function offsetGet($offset) {
    return $this->results[$offset];
  }

  /**
   */
  public function offsetSet($offset, $value) {
    $this->results[$offset] = $value;
  }

  /**
   */
  public function offsetUnset($offset) {
    throw new \RuntimeException("You cannot remove results from a search set.");
  }

}
