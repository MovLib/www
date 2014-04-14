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

use \MovLib\Data\Language;
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
class MovieTitles extends \MovLib\Presentation\AbstractPresenter {
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
  public function init() {
    $this->initMovie();
    $this->init($this->intl->t("Titles of {0}", [ $this->title ]));
    $this->contentBefore = "<div class='c'>";
    $this->contentAfter  = "</div>";
  }

  /**
   * Helper mothod to format comments.
   *
   * @param array
   *   One comment as associative array.
   * @return string
   *   Returns one list item of formatTitles.
   */
  public function formatComments($movieTitleComments) {
    $key = key($movieTitleComments);
    return "($key) : {$movieTitleComments[$key]}";
  }

   /**
   * Helper mothod to format titles.
   *
   * @param \MovLib\Data\Movie\MovieTitle $movieTitle
   *   A MovieTitle.
   * @return string
   *   Returns one list item of MovieTitles page.
   */
  public function formatTitles($movieTitle) {
    $language = new Language(Language::FROM_ID, $movieTitle->languageId);
    $displayTitle = ($movieTitle->isDisplayTitle) ? " ({$this->intl->t("display Title")})" : null;
    $list = new Unordered($movieTitle->dynComments, $this->intl->t("There are no Comments."));
    $list->closure = [ $this, "formatComments" ];

    return "({$language->code}) {$movieTitle->title}{$displayTitle}<div class='well well--small'>{$list}</div>";
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->callout($this->intl->t("The {0} feature isn’t implemented yet.", [ $this->intl->t("history") ]), $this->intl->t("Check back later"), "info");
  }

}
