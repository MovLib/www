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
 * Defines the title object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Title implements Search\SearchLanguageAnalyzerInterface {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The title's comment in the current locale.
   *
   * @var string
   */
  public $comment;

  /**
   * The title's comments in all languages.
   *
   * @var array
   */
  protected $comments;

  /**
   * Whether the title is the movie's display title (in the current locale) or not.
   *
   * @var boolean
   */
  public $display = false;

  /**
   * The title's display language codes.
   *
   * This numeric array contains all ISO 639-1 language codes for which this title is the display title.
   *
   * @var array|null
   */
  protected $displayLanguageCodes;

  /**
   * The title's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The title's ISO alpha-2 language code.
   *
   * @var string
   */
  public $languageCode;

  /**
   * Whether this title is the original title of the movie it belongs to or not.
   *
   * @var boolean|null
   */
  public $original = false;

  /**
   * The title.
   *
   * @var string
   */
  public $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new title object.
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
    $data = [ "id", "languageCode", "title" ];
    !empty($this->comments)             && ($data[] = "comments");
    !empty($this->displayLanguageCodes) && ($data[] = "displayLanguageCodes");
    $this->original                     && ($data[] = "original");
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
    return $this->title;
  }

}
