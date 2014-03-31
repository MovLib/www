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

use \MovLib\Data\Company\CompanySet;
use \MovLib\Partial\Alert;

/**
 * Defines the company index presentation.
 *
 * @link http://schema.org/Corporation
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/companies
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/companies
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/companies
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/companies
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {
  use \MovLib\Partial\DateTrait;
  use \MovLib\Partial\CompanyTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this->initIndex(
      new CompanySet($this->diContainerHTTP),
      $this->intl->t("Create New Company"),
      $this->intl->t("Companies"),
      "companies",
      "company"
    );
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Company\Company $company {@inheritdoc}
   */
  protected function formatListingItem($company) {
    $companyDates = $this->dateFormatFromTo(
      $company->foundingDate,
      $company->defunctDate,
      [ "property" => "foundingDate", "title" => $this->intl->t("Founding Date") ],
      [ "property" => "defunctDate", "title" => $this->intl->t("Defunct Date") ],
      true
    );
    if ($companyDates) {
      $companyDates = "<small>{$companyDates}</small>";
    }
    return
      "<li class='hover-item r'>" .
        "<article typeof='Company'>" .
          "<a class='no-link s s1' href='{$company->imageRoute}'>" .
            "<img alt='' src='{$this->getExternalURL("asset://img/logo/vector.svg")}' width='60' height='60'>" .
          "</a>" .
          "<div class='s s9'>" .
            "<div class='fr'>" .
              "<a class='ico ico-movie label' href='{$this->intl->rp("/company/{0}/movies", $company->id)}' title='{$this->intl->t("Movies")}'>{$company->movieCount}</a>" .
              "<a class='ico ico-series label' href='{$this->intl->rp("/company/{0}/series", $company->id)}' title='{$this->intl->t("Series")}'>{$company->seriesCount}</a>" .
              "<a class='ico ico-release label' href='{$this->intl->rp("/company/{0}/releases", $company->id)}' title='{$this->intl->t("Releases")}'>{$company->releaseCount}</a>" .
            "</div>" .
            "<h2 class='para'><a href='{$company->route}' property='url'><span property='name'>{$company->name}</span></a></h2>" .
            $companyDates .
          "</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return new Alert(
      "<p>{$this->intl->t("We couldn’t find any companies matching your filter criteria, or there simply aren’t any companies available.")}</p>" .
      "<p>{$this->intl->t("Would you like to {0}create a company{1}?", [ "<a href='{$this->intl->r("/company/create")}'>", "</a>" ])}</p>",
      $this->intl->t("No Companies")
    );
  }

}
