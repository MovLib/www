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
 * Defines the tagline object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Tagline implements Search\SearchLanguageAnalyzerInterface {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The tagline's comment in the current locale.
   *
   * @var string
   */
  public $comment;

  /**
   * The tagline's comments in all languages.
   *
   * @var array|null
   */
  protected $comments;

  /**
   * Whether the tagline is the movie's display tagline (in the current locale) or not.
   *
   * @var boolean
   */
  public $display = false;

  /**
   * The tagline's display language codes.
   *
   * This numeric array contains all ISO 639-1 language codes for which this tagline is the display tagline.
   *
   * @var array|null
   */
  protected $displayLanguageCodes;

  /**
   * The tagline's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The tagline's ISO alpha-2 language code.
   *
   * @var string
   */
  public $languageCode;

  /**
   * The tagline.
   *
   * @var string
   */
  public $tagline;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new tagline object.
   */
  public function __construct() {
    if (is_string($this->comments)) {
      $this->comments = json_decode($this->comments, true);
    }
  }

  /**
   * Called if this object is serialized.
   *
   * @return array
   *   Array containing the properties that should be serialized.
   */
  public function __sleep() {
    $data = [ "id", "languageCode", "tagline" ];
    !empty($this->comments)             && ($data[] = "comments");
    !empty($this->displayLanguageCodes) && ($data[] = "displayLanguageCodes");
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageCode() {
    return $this->languageCode;
  }

  /**
   * {@inheritdoc}
   */
  public function getText() {
    return $this->tagline;
  }

}
