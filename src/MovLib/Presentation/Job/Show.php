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

use \MovLib\Presentation\Partial\Alert;
use \MovLib\Data\Job;

/**
 * Presentation of a single job.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright Â© 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Job\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new job presentation.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    $this->job = new Job((integer) $_SERVER["JOB_ID"]);
    $this->initPage($this->job->name);
    $this->initLanguageLinks("/job/{0}", [ $this->job->id]);
    $this->initBreadcrumb([[ $this->intl->rp("/jobs"), $this->intl->t("Jobs") ]]);
    $this->sidebarInit();

    $kernel->stylesheets[] = "job";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    // Enhance the page title with microdata.
    $this->schemaType = "Intangible";
    $this->pageTitle  = "<span property='name'>{$this->job->name}</span>";

    if ($this->job->deleted === true) {
      return $this->goneGetContent();
    }

    $this->pageTitle .=
      " <span class='small'>(" .
        "<span property='alternateName' class='ico ico-sex1 sex sex-1' title='{$this->intl->t("male")}'>{$this->job->maleName}</span>, " .
        "<span property='alternateName' class='ico ico-sex2 sex sex-2' title='{$this->intl->t("female")}'>{$this->job->femaleName}</span>" .
      ")</span>"
    ;


    // ----------------------------------------------------------------------------------------------------------------- Build page sections.


    $content = null;

    // Biography section.
    if ($this->job->description) {
      $content .= $this->getSection("description", $this->intl->t("Description"), $this->htmlDecode($this->job->description));
    }

    if ($content) {
      return $content;
    }

    return new Alert(
      $this->intl->t(
        "{sitename} has no further details about this job.",
        [ "sitename"    => $this->config->sitename ]
      ),
      $this->intl->t("No Data Available"),
      Alert::SEVERITY_INFO
    );
  }

  /**
   * Construct a section in the main content and add it to the sidebar.
   *
   * @param string $id
   *   The section's unique identifier.
   * @param string $title
   *   The section's translated title.
   * @param string $content
   *   The section's content.
   * @return string
   *   The section ready for display.
   */
  protected function getSection($id, $title, $content) {
    // Add the section to the sidebar as anchor.
    $this->sidebarNavigation->menuitems[] = [ "#{$id}", $title ];

    return "<div id='{$id}'><h2>{$title}</h2>{$content}</div>";
  }

}
