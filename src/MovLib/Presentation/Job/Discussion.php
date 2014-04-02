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

/**
 * A job's discussion.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Discussion extends \MovLib\Presentation\Job\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new job discussion presentation.
   *
   */
  public function __construct() {
    $this->job = new Job((integer) $_SERVER["JOB_ID"]);
    $this->initPage($this->intl->t("Discussion"));
    $this->pageTitle = $this->intl->t("Discussion of {0}", [ "<a href='{$this->job->route}'>{$this->job->name}</a>" ]);
    $this->initLanguageLinks("/job/{0}/discussion", [ $this->job->id ]);
    $this->initJobBreadcrumb();
    $this->sidebarInit();

    $kernel->stylesheets[] = "job";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @return \MovLib\Presentation\Partial\Alert
   */
  protected function getPageContent() {
    return new \MovLib\Presentation\Partial\Alert($this->intl->t("The {0} feature isn’t implemented yet.", [ $this->intl->t("discuss job") ]), $this->intl->t("Check back later"), \MovLib\Presentation\Partial\Alert::SEVERITY_INFO);
  }

}
