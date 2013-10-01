<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Data\History;

/**
 * Specialized history model for movie models.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Movie extends AbstractHistory {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Names of files with serialized content.
   *
   * @var array
   */
  public $serializedFiles;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __construct($id, $context = "history") {
    parent::__construct($id, $context);
    $this->serializedFiles = [
      "titles",
      "taglines",
      "links",
      "trailers",
      "cast",
      "crew",
      "awards",
      "relationships",
      "genres",
      "styles",
      "languages",
      "countries",
      "directors",
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function writeFiles(array $data) {
    foreach ([ "original_title", "runtime", "year" ] as $key) {
      if (isset($data[$key])) {
        $this->writeToFile($key, $data[$key]);
      }
    }

    foreach ($GLOBALS["movlib"]["locales"] as $languageCode => $locale) {
      if (isset($data["{$languageCode}_synopsis"])) {
        $this->writeToFile("{$languageCode}_synopsis", $data["{$languageCode}_synopsis"]);
      }
    }

    $c = count($this->serializedFiles);
    for ($i = 0; $i < $c; ++$i) {
      if (isset($data[$this->serializedFiles[$i]])) {
        $this->writeToFile($this->serializedFiles[$i], serialize($data[$this->serializedFiles[$i]]));
      }
    }

    return $this;
  }

}
