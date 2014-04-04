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

use \MovLib\Data\Job\JobSet;
use \MovLib\Partial\Alert;

/**
 * Defines the job index presentation.
 *
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/jobs
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/jobs
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/jobs
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/jobs
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this->initIndex(
      new JobSet($this->diContainerHTTP),
      $this->intl->t("Create New Job"),
      $this->intl->t("Jobs"),
      "jobs",
      "job"
    );
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Job\Job $job {@inheritdoc}
   */
  public function formatListingItem(\MovLib\Data\AbstractEntity $job, $delta) {
    return
      "<li class='hover-item r'>" .
        "<article>" .
          "<div class='s s10'>" .
            "<div class='fr'>" .
              "<a class='ico ico-movie label' href='{$this->intl->rp("/job/{0}/movies", $job->id)}' title='{$this->intl->t("Movies")}'>{$job->movieCount}</a>" .
              "<a class='ico ico-series label' href='{$this->intl->rp("/job/{0}/series", $job->id)}' title='{$this->intl->t("Series")}'>{$job->seriesCount}</a>" .
            "</div>" .
            "<h2 class='para'><a href='{$job->route}' property='url'><span property='name'>{$job->name}</span></a></h2>" .
          "</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return new Alert(
      "<p>{$this->intl->t("We couldn’t find any jobs matching your filter criteria, or there simply aren’t any jobs available.")}</p>" .
      "<p>{$this->intl->t("Would you like to {0}create an job{1}?", [ "<a href='{$this->intl->r("/job/create")}'>", "</a>" ])}</p>",
      $this->intl->t("No Jobs")
    );
  }

}
