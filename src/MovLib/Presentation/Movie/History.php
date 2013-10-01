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
namespace MovLib\Presentation\Movie;

/**
 * The movie history page.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class History extends \MovLib\Presentation\Movie\AbstractMoviePage {
  use \MovLib\Presentation\TraitHistory;


  /**
   * Instatiate new movie history presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->initMovie();
    $this->init($i18n->t("History of {0}", [ $this->title ]));

    $this->historyModel = new \MovLib\Data\History\Movie($this->model->id);
  }

  private function getDiff($head, $ref, $filename) {
    $processAsArray = [
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
      "directors"
    ];

    if (in_array($filename, $processAsArray)) {
      $methodeName = "get" . ucfirst($filename);
      return (new \ReflectionMethod($this, $methodeName))->invoke($this, $head, $ref, $filename);
    }
    return $this->diffToHtml($head, $ref, $filename);
  }

}
