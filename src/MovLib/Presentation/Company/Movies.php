<?php

/*!
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 *  MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 *  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 *  version.
 *
 *  MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License along with MovLib.
 *  If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Presentation\Company;

use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Listing\Movie\MoviePersonListing;

/**
 * Movies of a company.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movies extends \MovLib\Presentation\Company\AbstractBase {

  /**
   * Instantiate new movies presentation of a company.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;

    parent::__construct();

    $this->initLanguageLinks("/company/{0}/movies", [ $this->company->id ], true);
    $this->breadcrumbTitle = $i18n->t("Movies");

    $title  = $i18n->t("Movies of {company_name}");
    $search = "{company_name}";
    $this->initPage(str_replace($search, $this->company->name, $title));
    $this->pageTitle = str_replace($search, "<a href='{$this->company->route}'>{$this->company->name}</a>", $title);
  }

  /**
   * @return \MovLib\Presentation\Partial\Lists\Movies
   */
  protected function getPageContent() {
    global $i18n;
    $movies = (new MoviePersonListing($this->company->getMovieResult()))->getListing();

    if (empty($movies)) {
      return new Alert($i18n->t("There are no movies this company was involved!"), $i18n->t("No Movies"), Alert::SEVERITY_INFO);
    }

    $list = null;
    foreach ($movies as $id => $html) {
      $list .= "<li class='li s r' typeof='Movie'>{$html["#movie"]}<ul class='no-list jobs s s4 tar'>{$html["#jobs"]}</ul></li>";
    }
    return "<ol class='hover-list no-list'>{$list}</ol>";
  }
}
