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
use \MovLib\Presentation\Partial\Listing\EntityIndexListing;

/**
 * List of all jobs.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitPagination;
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new jobs show presentation.
   *
   */
  public function __construct() {
    $this->initPage($this->intl->t("Jobs"));
    $this->initBreadcrumb();
    $this->initLanguageLinks("/jobs", null, true);
    $this->paginationInit(Job::getTotalCount());
    $this->sidebarInit([
      [ $kernel->requestPath, $this->title, [ "class" => "ico ico-job" ] ],
      [ $this->intl->r("/job/random"), $this->intl->t("Random") ],
    ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    $this->headingBefore =
      "<a class='btn btn-large btn-success fr' href='{$this->intl->r("/job/create")}'>{$this->intl->t("Create New Job")}</a>"
    ;

    $result      = Job::getJobs($this->paginationOffset, $this->paginationLimit);
    $noItemText  = new Alert(
      $this->intl->t(
        "We couldn’t find any job matching your filter criteria, or there simply aren’t any jobs available."
      ), $this->intl->t("No Jobs"), Alert::SEVERITY_INFO
    );
    $noItemText .=
      $this->intl->t("<p>Would you like to {0}create a new entry{1}?</p>", [ "<a href='{$this->intl->r("/job/create")}'>", "</a>" ]);

    $moviesRoute = $this->intl->rp("/job/{0}/movies", [ "{{ id }}" ]);
    $seriesRoute = $this->intl->rp("/job/{0}/series", [ "{{ id }}" ]);

    return new EntityIndexListing($result, $noItemText, "Job", $moviesRoute, $seriesRoute);
  }
}
