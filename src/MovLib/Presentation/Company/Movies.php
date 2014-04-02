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

use \MovLib\Data\Company;
use \MovLib\Partial\Listing\CompanyMovieListing;

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


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @return \MovLib\Presentation\Partial\Listing\CompanyMovieListing
   */
  protected function getPageContent() {
    return new CompanyMovieListing($this->diContainerHTTP, $this->company->getMovieResult());
  }

  /**
   * Instantiate new company movie presentation.
   */
  public function init() {
    $this->company = (new Company($this->diContainerHTTP))->init((integer) $_SERVER["COMPANY_ID"]);
    $this->initPage($this->intl->t("Movies from {0}", [ $this->company->name ]));
    $this->pageTitle       = $this->intl->t("Movies from {0}", [ "<a href='{$this->company->route}'>{$this->company->name}</a>" ]);
    $this->breadcrumbTitle = $this->intl->t("Movies");
    $this->initLanguageLinks("/company/{0}/movies", [ $this->company->id ], true);
    $this->initCompanyBreadcrumb();
    $this->sidebarInit();
  }

}
