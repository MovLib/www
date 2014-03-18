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

use \MovLib\Data\Company\Company;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Listing\Company as CompanyPartial;

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
class Index extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;
  use \MovLib\Presentation\TraitPagination;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new latest companies presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->initPage($i18n->t("Companies"));
    $this->initBreadcrumb();
    $this->initLanguageLinks("/companies", null, true);
    $this->paginationInit(Company::getTotalCount());
    $this->sidebarInit([
      [ $kernel->requestPath, $this->title, [ "class" => "ico ico-company" ] ],
      [ $i18n->r("/company/random"), $i18n->t("Random") ],
    ]);
  }

  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n;

    $this->headingBefore =
      "<a class='btn btn-large btn-success fr' href='{$i18n->r("/company/create")}'>{$i18n->t("Create New Company")}</a>"
    ;

    $result      = Company::getCompanies($this->paginationOffset, $this->paginationLimit);
    $noItemText  = new Alert(
      $i18n->t(
        "We couldn’t find any company matching your filter criteria, or there simply aren’t any companies available."
      ), $i18n->t("No Companies"), Alert::SEVERITY_INFO
    );
    $noItemText .=
      $i18n->t("<p>Would you like to {0}create a new entry{1}?</p>", [ "<a href='{$i18n->r("/company/create")}'>", "</a>" ]);

    return new CompanyPartial($result, $noItemText);
  }

}
