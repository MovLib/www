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

use \MovLib\Data\Company\Company;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Random company presentation.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Random {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * A random company ID.
   *
   * @var integer
   */
  private $companyId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Redirect to random company presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->companyId = Company::getRandomCompanyId();
    if (isset($this->companyId)) {
      throw new SeeOtherRedirect($i18n->r("/company/{0}", [ $this->companyId ]));
    }
    else {
      $kernel->alerts .= new Alert(
        $i18n->t("There is currently no company in our database"),
        $i18n->t("Check back later"),
        Alert::SEVERITY_INFO
      );
      throw new SeeOtherRedirect("/");
    }
  }

}
