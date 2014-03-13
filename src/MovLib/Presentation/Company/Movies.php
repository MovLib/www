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
namespace MovLib\Presentation\Company;

use \MovLib\Data\Company\FullCompany;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Listing\Movies as MoviesPartial;

/**
 * Movies with a certain company associated.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movies extends \MovLib\Presentation\Company\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new company movie presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->company = new FullCompany((integer) $_SERVER["COMPANY_ID"]);
    $this->initPage($i18n->t("Movies from {0}", [ $this->company->name ]));
    $this->pageTitle       = $i18n->t("Movies from {0}", [ "<a href='{$this->company->route}'>{$this->company->name}</a>" ]);
    $this->breadcrumbTitle = $i18n->t("Movies");
    $this->initLanguageLinks("/company/{0}/movies", [ $this->company->id ], true);
    $this->initCompanyBreadcrumb();
    $this->sidebarInit();

    $kernel->stylesheets[] = "company";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @return \MovLib\Presentation\Partial\Listing\Movies
   */
  protected function getPageContent() {
    global $i18n;
    return new MoviesPartial($this->company->getMovieResult(), (new Alert($i18n->t("Check back later"), $i18n->t("No movies found."), Alert::SEVERITY_INFO))->__toString());
  }

}
