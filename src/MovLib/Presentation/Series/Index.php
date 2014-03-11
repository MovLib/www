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
 * The latest series.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;

  public function __construct() {
    global $i18n;
    $this->initPage($i18n->t("Series"));
    $this->initLanguageLinks("/series", null, true);
    $this->initBreadcrumb();
    $this->sidebarInit([
      [ $i18n->rp("/series"), $i18n->t("Series"), [ "class" => "ico ico-series" ] ],
      [ $i18n->rp("/series/charts"), $i18n->t("Charts") ],
      [ $i18n->r("/series/random"), $i18n->t("Random") ],
    ]);
  }

  protected function getPageContent() {
    global $i18n;
    $this->headingBefore =
      "<a class='btn btn-large btn-success fr' href='{$i18n->r("/series/create")}'>{$i18n->t("Create New Series")}</a>"
    ;
    return new Alert(
      $i18n->t("The serials feature isn’t implemented yet."),
      $i18n->t("Check back later"),
      Alert::SEVERITY_INFO
    );
  }

}
