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
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->award = new Award((integer) $_SERVER["AWARD_ID"]);
    $this->initPage($i18n->t("Categories of {0}", [ $this->award->name ]));
    $this->pageTitle       = $i18n->t("Categories of {0}", [ "<a href='{$this->award->route}'>{$this->award->name}</a>" ]);
    $this->breadcrumbTitle = $i18n->t("Categories");
    $this->initLanguageLinks("/award/{0}/categories", [ $this->award->id ], true);
    $this->initAwardBreadcrumb();
    $this->sidebarInit();

    $kernel->stylesheets[] = "award";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @return \MovLib\Presentation\Partial\Listing\Movies
   */
  protected function getPageContent() {
    global $i18n;
    $this->headingBefore =
      "<a class='btn btn-large btn-success fr' href='{$i18n->r("/award/{0}/category/create", [ $this->award->id ])}'>{$i18n->t("Create New Category")}</a>"
    ;

    $result      = $this->award->getCategoriesResult();
    $noItemText  = new Alert(
      $i18n->t(
        "We couldn’t find any categories matching your filter criteria, or there simply aren’t any categories available."
      ), $i18n->t("No Category"), Alert::SEVERITY_INFO
    );
    $noItemText .=
      $i18n->t("<p>Would you like to {0}create a new entry{1}?</p>", [ "<a href='{$i18n->r("/award/{0}/category/creat", [ $this->award->id ])}'>", "</a>" ]);

    $moviesRoute = $i18n->rp("/award/{0}/category/{1}/movies", [ $this->award->id, "{{ id }}" ]);
    $seriesRoute = $i18n->rp("/award/{0}/category/{1}/series", [ $this->award->id, "{{ id }}" ]);

    return new EntityIndexListing($result, $noItemText, "AwardCategory", $moviesRoute, $seriesRoute);
  }

}
