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
namespace MovLib\Data\History;

use \MovLib\Data\SystemLanguages;

/**
 * Specialized history model for movie models.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movie extends AbstractHistory {


  /**
   * @inheritdoc
   */
  public function __construct($id, $context = "history") {
    global $kernel;
    parent::__construct($id, $context);
    $this->files = [
      "original_title",
      "runtime",
      "year"
    ];

    foreach ($kernel->systemLanguages as $code => $locale) {
      $this->files[] = "{$code}_synopsis";
    }

    $this->serializedArrays = [
      "titles",
      "taglines",
      "links",
      "trailers",
      "cast",
      "awards",
      "relationships"
    ];

    $this->serializedIds = [
      "genres",
      "styles",
      "languages",
      "crew",
      "directors",
      "countries"
    ];
  }

}
