<?php

/*!
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 *  MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 *  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 *  version.
 *
 *  MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License along with MovLib.
 *  If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Presentation\Company;

/**
 * Allows deleting a company
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Delete extends \MovLib\Presentation\Company\AbstractBase {

  /**
   * Instantiate new delete company presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;

    parent::__construct();

    $this->breadcrumbTitle = $i18n->t("Delete");

    $title  = $i18n->t("Delete {company_name}");
    $search = "{company_name}";
    $this->initPage(str_replace($search, $this->company->name, $title));
    $this->pageTitle = str_replace($search, "<a href='{$this->company->route}'>{$this->company->name}</a>", $title);
  }

  protected function getPageContent() {
    global $i18n;
    return new \MovLib\Presentation\Partial\Alert($i18n->t("The {0} feature isn’t implemented yet.", [ $i18n->t("delete company") ]), $i18n->t("Check back later"), \MovLib\Presentation\Partial\Alert::SEVERITY_INFO);
  }
}
