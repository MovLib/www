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
namespace MovLib\Presentation\Genres;

use \MovLib\Data\Genre;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Lists\Genres AS GenresPartial;

/**
 * List of all genres.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitPagination;
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new genres show presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->initPage($i18n->t("Genres"));
    $this->initBreadcrumb();
    $this->initLanguageLinks("/genres", null, true);
    $this->paginationInit(Genre::getTotalCount());
    $this->sidebarInit([
      [ $kernel->requestPath, $this->title ],
    ]);
    $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$i18n->r("/genre/create")}'>{$i18n->t("Create New Genre")}</a>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    return "<div id='filter' class='tar'>{$i18n->t("Filter")}</div>" . new GenresPartial(
      Genre::getGenres($this->paginationOffset, $this->paginationLimit),
      new Alert(
        $i18n->t(
          "We couldn’t find any genre matching your filter criteria, or there simply aren’t any genres available. Would you like to {0}create a new entry{1}?",
          [
            "<a href='{$i18n->r("/genre/create")}'>",
            "</a>"
          ]
        ),
        $i18n->t("No genres"),
        Alert::SEVERITY_INFO
      ),
      null,
      null,
      10,
      true
    );
  }
}
