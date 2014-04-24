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

use \MovLib\Data\Award\Award;

/**
 * Allows the creation of a new award category.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Create extends \MovLib\Presentation\AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award category create presentation.
   *
   */
  public function init() {
    $award = new Award($this->diContainerHTTP, $_SERVER["AWARD_ID"]);
    $this->initPage($this->intl->t("Create"));
    $this->initBreadcrumb([
      [ $this->intl->r("/awards"), $this->intl->t("Awards") ],
      [ $this->intl->r("/award/{0}/", [ $award->id ]), $award->name ],
      [ $this->intl->r("/award/{0}/categories", [ $award->id ]), $this->intl->t("Categories") ],
    ]);
    $this->breadcrumbTitle = $this->intl->t("Create");
    $this->initLanguageLinks("/award/{0}/category/create", $award->id);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return "<div class='c'>{$this->checkBackLater($this->intl->t("create award category"))}</div>";
  }

}
