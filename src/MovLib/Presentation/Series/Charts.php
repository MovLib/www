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
namespace MovLib\Presentation\Series;

use \MovLib\Presentation\Partial\Alert;

/**
 * Series charts presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Charts extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  public function __construct() {
    $this->initPage($this->intl->t("Series Charts"));
    $this->initBreadcrumb([ [ $this->intl->rp("/series"), $this->intl->t("Series") ] ]);
    $this->breadcrumbTitle = $this->intl->t("Charts");
    $this->initLanguageLinks("/series/charts", null, true);
    $this->sidebarInit([
      [ $this->intl->rp("/series"), $this->intl->t("Series"), [ "class" => "ico ico-series" ] ],
      [ $this->intl->rp("/series/charts"), $this->intl->t("Charts") ],
      [ $this->intl->r("/series/random"), $this->intl->t("Random") ],
    ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the presentation's page content.
   *
   * @return string
   *   The presentation's page content.
   */
  protected function getPageContent() {
    return new Alert(
      $this->intl->t("The series charts feature isn’t implemented yet."),
      $this->intl->t("Check back later"),
      Alert::SEVERITY_INFO
    );
  }

}
