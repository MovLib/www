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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Movie;
use \MovLib\Data\Movie\MovieTitles as MovieTitlesModel;
use \MovLib\Presentation\Partial\Lists\Unordered;

/**
 * Page to show movie titles.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieTitles extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\Movie\TraitMoviePage;
  
  /**
   * The movie titles to display.
   *
   * @var \MovLib\Data\Movie\MovieTitles
   */
  protected $movieTitles;
  
  /**
   * Instatiate new movie titles presentation.
   */
  public function __construct() {
    global $i18n;
    $this->initMovie();
    $this->init($i18n->t("History of {0}", [ $this->title ]));
    $this->movieTitles = new MovieTitlesModel($_SERVER["MOVIE_ID"]);
  }
  
   /**
   * Helper mothod to format titles.
   * 
   * @param \MovLib\Data\Movie\MovieTitle $movieTitle
   *   A MovieTitle.
   * @return string
   *   Returns one list item MovieTitles page.
   */
  public function formatTitles($movieTitle) {
    var_dump($movieTitle);
    exit();
    return $movieTitle->title;
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {  
    global $i18n;
    $list = new Unordered($this->movieTitles->orderById(),
      $i18n->t("There are no titles.")
    );
    $list->closure = [ $this, "formatTitles" ];
    
    return $list;
  }
  
}
