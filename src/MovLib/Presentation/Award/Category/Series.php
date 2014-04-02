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
use \MovLib\Data\AwardCategory;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Series with a certain award category associated.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Series extends \MovLib\Presentation\Award\Category\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award category series presentation.
   *
   */
  public function __construct() {
    $this->award         = new Award((integer) $_SERVER["AWARD_ID"]);
    $this->awardCategory = new AwardCategory((integer) $_SERVER["AWARD_CATEGORY_ID"]);
    $routeArgs           = [ $this->awardCategory->awardId, $this->awardCategory->id ];

    if ($this->award->id != $this->awardCategory->awardId) {
      throw new SeeOtherRedirect($this->intl->rp("/award/{0}/category/{1}/series", $routeArgs));
    }

    $this->initPage($this->intl->t("Series with {0}", [ $this->awardCategory->name ]));
    $this->pageTitle     =
      $this->intl->t("Series with {0}", [ "<a href='{$this->awardCategory->route}'>{$this->awardCategory->name}</a>" ])
    ;
    $this->breadcrumbTitle = $this->intl->t("Series");
    $this->initLanguageLinks("/award/{0}/category/{1}/series", [ $this->award->id, $this->awardCategory->id ], true);
    $this->initAwardCategoryBreadcrumb();
    $this->sidebarInit();

    $kernel->stylesheets[] = "award";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


 /**
   * @inheritdoc
   * @return \MovLib\Presentation\Partial\Alert
   */
  protected function getPageContent() {
    return new \MovLib\Presentation\Partial\Alert($this->intl->t("The {0} feature isn’t implemented yet.", [ $this->intl->t("series with award category") ]), $this->intl->t("Check back later"), \MovLib\Presentation\Partial\Alert::SEVERITY_INFO);
  }

}
