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

use \MovLib\Data\Company;
use \MovLib\Partial\Alert;
use \MovLib\Exception\SeeOtherException;

/**
 * Random company presentation.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Random extends \MovLib\Presentation\AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * A random company identifier.
   *
   * @var integer
   */
  private $companyId;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   *
   * @return \MovLib\Partial\Alert
   */
  public function getContent() {
    return new Alert(
      $this->intl->t(
        "There aren’t any companies available."
      ), $this->intl->t("No Companies"), Alert::SEVERITY_INFO
    );
  }

  /**
   * Redirect to random company presentation.
   *
   * @throws \MovLib\Exception\SeeOtherException
   */
  public function init() {
    $this->companyId = (new Company($this->diContainerHTTP))->getRandomCompanyId();
    if (isset($this->companyId)) {
      throw new SeeOtherException($this->intl->r("/company/{0}", [ $this->companyId ]));
    }
  }

}
