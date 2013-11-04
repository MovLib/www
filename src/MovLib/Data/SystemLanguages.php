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
namespace MovLib\Data;

/**
 * Contains all system supported languages.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SystemLanguages implements \ArrayAccess, \Countable, \Iterator {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Internal array containing all supported system languages.
   *
   * @var array
   */
  protected $systemLanguages;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Get all supported system languages.
   *
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $kernel;
    foreach ($kernel->systemLanguages as $languageCode => $locale) {
      $this->systemLanguages[$languageCode] = new \MovLib\Data\SystemLanguage($languageCode);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function count() {
    return count($this->systemLanguages);
  }

  /**
   * @inheritdoc
   */
  public function current() {
    return current($this->systemLanguages);
  }

  /**
   * @inheritdoc
   */
  public function key() {
    return key($this->systemLanguages);
  }

  /**
   * @inheritdoc
   */
  public function next() {
    return next($this->systemLanguages);
  }

  /**
   * @inheritdoc
   */
  public function rewind() {
    return reset($this->systemLanguages);
  }

  /**
   * @inheritdoc
   */
  public function valid() {
    return isset($this->systemLanguages[key($this->systemLanguages)]);
  }

  /**
   * @inheritdoc
   */
  public function offsetExists($offset) {
    return isset($this->systemLanguages[$offset]);
  }

  /**
   * @inheritdoc
   */
  public function &offsetGet($offset) {
    return $this->systemLanguages[$offset];
  }

  /**
   * @inheritdoc
   */
  public function offsetSet($offset, $value) {
    $this->systemLanguages[$offset] = $value;
  }

  /**
   * @inheritdoc
   */
  public function offsetUnset($offset) {
    unset($this->systemLanguages[$offset]);
  }

}
