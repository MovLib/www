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
 * Allows the creation of a new series.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Create extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitForm;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new series create presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;

    $this->initPage($i18n->t("Create Series"));
    $this->initBreadcrumb([ [ $i18n->rp("/series"), $i18n->t("Series") ] ]);
    $this->breadcrumbTitle = $i18n->t("Create");
    $this->initLanguageLinks("/series/create");

    $kernel->stylesheets[] = "series";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getContent() {
    global $i18n;
    return new Alert(
      $i18n->t("The create series feature isn’t implemented yet."),
      $i18n->t("Check back later"),
      Alert::SEVERITY_INFO
    );
  }

  /**
   * @inheritdoc
   */
  protected function formValid() {
    return $this;
  }

}
