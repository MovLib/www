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
namespace MovLib\Presentation\Job;

use \MovLib\Data\Job;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Random job presentation.
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
   * A random job identifier.
   *
   * @var integer
   */
  private $jobId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Redirect to random job presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->jobId = Job::getRandomJobId();
    if (isset($this->jobId)) {
      throw new SeeOtherRedirect($i18n->r("/job/{0}", [ $this->jobId ]));
    }
    else {
      $kernel->alerts .= new Alert(
        $i18n->t("There is currently no job in our database."),
        $i18n->t("Check back later"),
        Alert::SEVERITY_INFO
      );
      throw new SeeOtherRedirect("/");
    }
  }

}