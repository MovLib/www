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

use \MovLib\Exception\LanguageException;

/**
 * Represents a single language.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Language extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Load the country from ID.
   *
   * @var int
   */
  const FROM_ID = "id";

  /**
   * Load the country from the code.
   *
   * @var string
   */
  const FROM_CODE = "iso_alpha-2";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The language's unique identifier.
   *
   * @var int
   */
  public $id;

  /**
   * The language's translated name.
   *
   * @var string
   */
  public $name;

  /**
   * The language's ISO alpha-2 code.
   *
   * @var string
   */
  public $code;

  /**
   * The MySQLi bind param types of the columns.
   *
   * @var array
   */
  protected $types = [
    self::FROM_ID   => "i",
    self::FROM_CODE => "s"
  ];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new language.
   *
   * If no <var>$from</var> or <var>$value</var> is given, an empty language model will be created.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $from [optional]
   *   Defines how the object should be filled with data, use the various <var>FROM_*</var> class constants.
   * @param mixed $value [optional]
   *   Data to identify the language, see the various <var>FROM_*</var> class constants.
   * @throws \MovLib\Exception\LanguageException
   */
  public function __construct($from = null, $value = null) {
    global $i18n;
    if ($from && $value) {
      $namePart = "";
      if ($i18n->languageCode != $i18n->defaultLanguageCode) {
        $namePart = "COLUMN_GET(`dyn_translations`, '{$i18n->languageCode}' AS CHAR(255)) AS";
      }
      $stmt = $this->query(
        "SELECT
          `language_id`,
          `iso_alpha-2`,
          {$namePart} `name`
        FROM `languages`
        WHERE `{$from}` = ?",
        $this->types[$from],
        [ $value ]
      );
      $stmt->bind_result($this->id, $this->code, $this->name);
      if (!$stmt->fetch()) {
        throw new LanguageException("No country for {$from} '{$value}'.");
      }
    }
  }

}
