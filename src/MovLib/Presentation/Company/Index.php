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
use \MovLib\Partial\Alert;
use \MovLib\Partial\Listing\CompanyIndexListing;

/**
 * The latest companies.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitSidebar;
  use \MovLib\Presentation\TraitPagination;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company to present.
   *
   * @var \MovLib\Data\Company\Company
   */
  protected $company;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    $this->headingBefore =
      "<a class='btn btn-large btn-success fr' href='{$this->intl->r("/company/create")}'>{$this->intl->t("Create New Company")}</a>"
    ;

    $result      = $this->company->getCompanies($this->paginationOffset, $this->paginationLimit);
    $noItemText  = new Alert(
      $this->intl->t(
        "We couldn’t find any company matching your filter criteria, or there simply aren’t any companies available."
      ), $this->intl->t("No Companies"), Alert::SEVERITY_INFO
    );
    $noItemText .=
      $this->intl->t("<p>Would you like to {0}create a new entry{1}?</p>", [ "<a href='{$this->intl->r("/company/create")}'>", "</a>" ]);

    return new CompanyIndexListing($this->diContainerHTTP, $result, $noItemText);
  }

  /**
   * Instantiate new latest companies presentation.
   */
  public function init() {
    $this->company = new Company($this->diContainerHTTP);

    $this->initPage($this->intl->t("Companies"));
    $this->initBreadcrumb();
    $this->initLanguageLinks("/companies", null, true);
    $this->paginationInit($this->company->getTotalCount());
    $this->sidebarInit([
      [ $this->intl->rp("/companies"), $this->title, [ "class" => "ico ico-company" ] ],
      [ $this->intl->r("/company/random"), $this->intl->t("Random") ],
    ]);
  }

}
