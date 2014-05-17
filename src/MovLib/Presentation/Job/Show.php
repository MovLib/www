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

use \MovLib\Data\Job\Job;
use \MovLib\Partial\Sex;

/**
 * Presentation of a single job.
 *
 * @property \MovLib\Data\Job\Job $entity
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\AbstractShowPresenter {
  use \MovLib\Presentation\Job\JobTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Job($this->diContainerHTTP, $_SERVER["JOB_ID"]);
    $this
      ->initPage($this->entity->name)
      ->initShow($this->entity, $this->intl->t("Jobs"), "Job", null, $this->getSidebarItems())
    ;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->infoboxBefore .= "<p>";
    foreach ([ Sex::MALE => $this->intl->t("Male"), Sex::FEMALE => $this->intl->t("Female") ] as $code => $title) {
      $this->infoboxBefore .= "<span property='alternateName' class='ico ico-sex{$code} sex-{$code}' title='{$title}'> {$this->entity->names[$code]}</span> ";
    }
    $this->infoboxBefore .= "</p>";

    $this->entity->description && $this->sectionAdd($this->intl->t("Description"), $this->entity->description);

    if ($this->sections) {
      return $this->sections;
    }

    return $this->calloutInfo($this->intl->t(
      "We don’t have any further details about this job, could you {0}help us?{1}",
      [ "<a href='{$this->intl->r("/job/{0}/edit", $this->entity->id)}'>", "</a>" ]
    ));
  }

}
