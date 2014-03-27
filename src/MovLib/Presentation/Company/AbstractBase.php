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

/**
 * Base presenation of all company pages.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitGone {
    goneGetContent as private traitGetGoneContent;
  }
  use \MovLib\Presentation\TraitSidebar {
    sidebarInit as traitSidebarInit;
  }


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company to present.
   *
   * @var \MovLib\Data\Company
   */
  protected $company;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Build content for gone page.
   *
   * @return $this
   * @throws \LogicException
   */
  protected function goneGetContent() {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this->company instanceof \MovLib\Data\Company)) {
      throw new \LogicException($this->intl->t("\$this->company has to be a valid company object!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $routeArgs = [ $this->company->id ];

    $this->goneAlertMessage = $this->intl->t(
        "The company and all its content have been deleted. Take a look at the {0}history{2} or {1}discussion{2} page " .
        "for further information. Please discuss with the person responsible for this deletion before " .
        "you restore this entry from its {0}history{2}.",
        [
          "<a href='{$this->intl->r("/company/{0}/history", $routeArgs)}'>",
          "<a href='{$this->intl->r("/company/{0}/discussion", $routeArgs)}'>",
          "</a>"
        ]
      );
    return $this->traitGetGoneContent();
  }

  /**
   * Init company breadcrumb.
   *
   * @return $this
   * @throws \LogicException
   */
  protected function initCompanyBreadcrumb() {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this->company instanceof \MovLib\Data\Company)) {
      throw new \LogicException($this->intl->t("\$this->company has to be a valid company object!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    return $this->initBreadcrumb([
      [ $this->intl->rp("/companies"), $this->intl->t("Companies") ],
      [ $this->company->route, $this->company->name ]
    ]);
  }

  /**
   * Init company sidebar.
   *
   * @return $this
   * @throws \LogicException
   */
  protected function sidebarInit() {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this->company instanceof \MovLib\Data\Company)) {
      throw new \LogicException($this->intl->t("\$this->company has to be a valid company object!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Compile array once.
    $routeArgs = [ $this->company->id ];

    // Reduce the sidebar if the company was deleted.
    if ($this->company->deleted === true) {
      return $this->traitSidebarInit([
        [ $this->company->route, $this->intl->t("View"), [ "class" => "ico ico-view" ] ],
        [ $this->intl->r("/company/{0}/discussion", $routeArgs), $this->intl->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
        [ $this->intl->r("/company/{0}/history", $routeArgs), $this->intl->t("History"), [ "class" => "ico ico-history" ] ]
      ]);
    }

    return $this->traitSidebarInit([
      [ $this->company->route, $this->intl->t("View"), [ "class" => "ico ico-view" ] ],
      [ $this->intl->r("/company/{0}/discussion", $routeArgs), $this->intl->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
      [ $this->intl->r("/company/{0}/edit", $routeArgs), $this->intl->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $this->intl->r("/company/{0}/history", $routeArgs), $this->intl->t("History"), [ "class" => "ico ico-history" ] ],
      [ $this->intl->r("/company/{0}/delete", $routeArgs), $this->intl->t("Delete"), [ "class" => "ico ico-delete separator" ] ],

      [ $this->intl->rp("/company/{0}/movies", $routeArgs), "{$this->intl->t("Movies")} <span class='fr'>{$this->intl->format("{0,number}", [ $this->company->getMoviesCount() ])}</span>", [ "class" => "ico ico-movie" ] ],
      [ $this->intl->rp("/company/{0}/series", $routeArgs), "{$this->intl->t("Series")} <span class='fr'>{$this->intl->format("{0,number}", [ $this->company->getSeriesCount() ])}</span>", [ "class" => "ico ico-series" ] ],
      [ $this->intl->rp("/company/{0}/releases", $routeArgs), "{$this->intl->t("Releases")} <span class='fr'>{$this->intl->format("{0,number}", [ $this->company->getReleasesCount() ])}</span>", [ "class" => "ico ico-release separator" ] ],
    ]);
  }

}
