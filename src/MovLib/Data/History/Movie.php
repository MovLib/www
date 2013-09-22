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
 * Description of MovieHistoryModel
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Movie extends AbstractHistory {

  /**
   * Implementation ob abstract method <code>writeFiles()</code>.
   *
   * Write files to repository if offset exists in $data.
   *
   * @param array $data
   *   Associative array with data to store (use file names as keys).
   * @return this
   * @throws \MovLib\Exception\FileSystemException
   */
  public function writeFiles(array $data) {
    foreach (["original_title", "runtime", "year"] as $offset) {
      if (isset($data[$offset])) {
        $this->writeToFile($offset, $data[$offset]);
      }
    }

    foreach ($GLOBALS["movlib"]["locales"] as $language => $value) {
      if (isset($data["{$language}_synopsis"])) {
        $this->writeToFile("{$language}_synopsis", $data["{$language}_synopsis"]);
      }
    }

    $relatedData = ["titles", "taglines", "links", "trailers", "cast", "crew", "awards",
      "relationships", "genres", "styles", "languages", "countries", "directors"];
    foreach ($relatedData as $offset) {
      if (isset($data[$offset])) {
        $this->writeToFile($offset, serialize($data[$offset]));
      }
    }

    return $this;
  }

}
