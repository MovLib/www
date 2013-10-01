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
namespace MovLib\Presentation\History;

use \MovLib\Data\Genre;
use \MovLib\Presentation\Partial\Lists;

/**
 * The movie history page.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieHistoryDiff extends \MovLib\Presentation\Movie\AbstractMoviePage {
  use \MovLib\Presentation\History\TraitHistory;


  /**
   * Instatiate new movie history diff presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct($context = "history") {
    global $i18n;
    $this->initMovie();
    $this->init($i18n->t("History of {0}", [ $this->title ]));

    $this->historyModel = new \MovLib\Data\History\Movie($this->model->id, $context);
  }

  /**
   *
   * @param string $head
   *   Hash of git commit (newer one).
   * @param sting $ref
   *   Hash of git commit (older one).
   * @param string $filename
   *   Name of file in repository.
   * @return mixed
   */
  private function getDiff($head, $ref, $filename) {
    if (in_array($filename, $this->historyModel->serializedFiles)) {
      $diff = $this->historyModel->getArrayDiff($head, $ref, $filename);
      $methodName = ucfirst($filename);
      return $this->{"get{$methodName}"}($diff);
    }
    return $this->diffToHtml($head, $ref, $filename);
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    $this->addClass("active", $this->secondaryNavigation->menuitems[3][2]);
    return $this->getDiffContent();
  }

  /**
   * Helper function to generate Liste of changed genres.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param array $diff
   *   Associative array with added and removed genres.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of genres.
   */
  private function getGenres($diff) {
    global $i18n;
    $genres = [];
    foreach ($diff as $key => $genreIds) {
      if (!empty($genreIds)) {
        $genreNames = (new Genre())->getGenreNames($genreIds);
        foreach ($genreIds as $id) {
          $genres[] = "<p>{$this->a($i18n->r("/genre/{0}", [ $id ]), $i18n->t("{0}", [ $genreNames[$id] ]), [
            "class" => $key,
            "title" => $i18n->t("Description of {0}", [ $genreNames[$id] ])
          ])}</p>";
        }
      }
    }
    return (new Lists($genres, ""))->toHtmlList();
  }

  private function getTitles($diff) {
    
  }

}
