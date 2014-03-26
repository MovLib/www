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
namespace MovLib\Data\Movie;

/**
 * Description of MovieTitle
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieTitle extends \MovLib\Data\Database {
  
  
  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Load the movie title from ID.
   *
   * @var int
   */
  const FROM_ID = "id";
  
  
  // ------------------------------------------------------------------------------------------------------------------- Properties
  
  
  /**
   * The movie title's language id.
   *
   * @var int
   */
  public $languageId;
  
  /**
   * The movie title's movie id.
   *
   * @var int
   */
  public $movieId;
  
  /**
   * Associative array with comments in defferent languages.
   * 
   * @var array 
   */
  public $dynComments;
  
  /**
   * The movie title's unique identifier.
   *
   * @var int
   */
  public $id;
  
  /**
   * Whether the movie title is the display title or not.
   * 
   * @var boolean 
   */
  public $isDisplayTitle;
  
  /**
   * The movie title's title.
   *
   * @var string
   */
  public $title;
  
  /**
   * The MySQLi bind param types of the columns.
   *
   * @var array
   */
  protected $types = [
    self::FROM_ID   => "i"
  ];
  
  
  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie title.
   *
   * If no <var>$from</var> or <var>$value</var> is given, an empty movie title model will be created.
   *
   * @param string $from [optional]
   *   Defines how the object should be filled with data, use the various <var>FROM_*</var> class constants.
   * @param mixed $value [optional]
   *   Data to identify the movie title, see the various <var>FROM_*</var> class constants.
   * @throws \MovLib\Exception\CountryException
   */
  public function __construct($from = null, $value = null) {
    if ($from && $value) {
      $stmt = $this->query(
        "SELECT
          `id`,
          `language_id` AS `languageId`,
          `movie_id` AS `movieId`,
          `title`,
          `is_display_title` AS `isDisplayTitle`,
          COLUMN_JSON(`dyn_comments`) AS `dynComments`
        FROM `movies_titles`
        WHERE `{$from}` = ?",
        $this->types[$from],
        [ $value ]
      );
      $stmt->bind_result($this->id, $this->languageId, $this->movieId, $this->title, $this->isDisplayTitle, $this->dynComments);
      if (!$stmt->fetch()) {
        throw new CountryException("No movie title for {$from} '{$value}'.");
      }
      $this->dynComments = json_decode($this->dynComments, true);
    }
  }

}
