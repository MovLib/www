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
namespace MovLib\Presentation\Award\Category;

use \MovLib\Data\Award;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Listing\EntityIndexListing;

/**
 * A category of a certain award.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\Award\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award categories presentation.
   *
   */
  public function __construct() {
    $this->award = new Award((integer) $_SERVER["AWARD_ID"]);
    $this->initPage($this->intl->t("Categories of {0}", [ $this->award->name ]));
    $this->pageTitle       = $this->intl->t("Categories of {0}", [ "<a href='{$this->award->route}'>{$this->award->name}</a>" ]);
    $this->breadcrumbTitle = $this->intl->t("Categories");
    $this->initLanguageLinks("/award/{0}/categories", [ $this->award->id ], true);
    $this->initAwardBreadcrumb();
    $this->sidebarInit();

    $kernel->stylesheets[] = "award";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @return \MovLib\Presentation\Partial\Listing\Movies
   */
  protected function getPageContent() {
    $this->headingBefore =
      "<a class='btn btn-large btn-success fr' href='{$this->intl->r("/award/{0}/category/create", [ $this->award->id ])}'>{$this->intl->t("Create New Category")}</a>"
    ;

    $result      = $this->award->getCategoriesResult();
    $noItemText  = new Alert(
      $this->intl->t(
        "We couldn’t find any categories matching your filter criteria, or there simply aren’t any categories available."
      ), $this->intl->t("No Category"), Alert::SEVERITY_INFO
    );
    $noItemText .=
      $this->intl->t("<p>Would you like to {0}create a new entry{1}?</p>", [ "<a href='{$this->intl->r("/award/{0}/category/creat", [ $this->award->id ])}'>", "</a>" ]);

    $moviesRoute = $this->intl->rp("/award/{0}/category/{1}/movies", [ $this->award->id, "{{ id }}" ]);
    $seriesRoute = $this->intl->rp("/award/{0}/category/{1}/series", [ $this->award->id, "{{ id }}" ]);

    return new EntityIndexListing($result, $noItemText, "AwardCategory", $moviesRoute, $seriesRoute);
  }

}
