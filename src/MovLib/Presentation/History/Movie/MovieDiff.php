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
namespace MovLib\Presentation\History\Movie;

use \MovLib\Data\History\Movie;

/**
 * The movie history page.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieDiff extends \MovLib\Presentation\History\AbstractHistory {
  use \MovLib\Presentation\History\TraitHistory;
  use \MovLib\Presentation\Movie\TraitMoviePage;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instatiate new movie history diff presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct($context = "history") {
    global $i18n;
    $this->initMovie();
    $this->init($i18n->t("History of {0}", [ $this->title ]));

    $this->historyModel = new Movie($this->model->id, $context);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    $this->addClass("active", $this->secondaryNavigation->menuitems[3][2]);
    return $this->diffPage();
  }

 /**
   * Helper method to generate Liste of changed awards.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed awards.
   */
  private function getAwards($diff) {
    // @todo: implement
  }

  /**
   * Helper method to generate Liste of changed casts.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed casts.
   */
  private function getCast($diff) {
    return $this->diffArray($diff, "\MovLib\Data\Persons");
  }

  /**
   * Helper method to generate Liste of changed countries.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed countries.
   */
  private function getCountries($diff) {
    return $this->diffIds($diff, "\MovLib\Data\Countries");
  }

  /**
   * Helper method to generate Liste of changed crew members.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed crew members.
   */
  private function getCrew($diff) {
    return $this->diffArray($diff, "\MovLib\Data\Persons");
  }

  /**
   * Helper method to generate Liste of changed directors.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed directors.
   */
  private function getDirectors($diff) {
    return $this->diffIds($diff, "\MovLib\Data\Persons");
  }

  /**
   * Helper method to generate Liste of changed genres.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed genres.
   */
  private function getGenres($diff) {
    return $this->diffIds($diff, "\MovLib\Data\Genres");
  }

  /**
   * Helper method to generate Liste of changed languages.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed languages.
   */
  private function getLanguages($diff) {
    return $this->diffIds($diff, "\MovLib\Data\Languages");
  }

  /**
   * Helper method to generate Liste of changed links.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed links.
   */
  private function getLinks($diff) {
    // @todo: implement
  }

  /**
   * Helper method to generate Liste of changed relationships.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed relationships.
   */
  private function getRelationships($diff) {
    // @todo: implement
  }

  /**
   * Helper method to generate Liste of changed styles.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed styles.
   */
  private function getStyles($diff) {
    return $this->diffIds($diff, "\MovLib\Data\Styles");
  }

  /**
   * Helper method to generate Liste of changed taglines.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed taglines.
   */
  private function getTaglines($diff) {
    // @todo: implement
  }

  /**
   * Helper method to generate Liste of changed titles.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed titles.
   */
  private function getTitles($diff) {
    // @todo: implement
  }

  /**
   * Helper method to generate Liste of changed trailers.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed trailers.
   */
  private function getTrailers($diff) {
    // @todo: implement
  }

}
