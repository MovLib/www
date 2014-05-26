<?php

/* !
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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Crew\CrewSet;
use \MovLib\Partial\Sex;

/**
 * Presentation of a single movie's crew.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Crew extends \MovLib\Presentation\Movie\AbstractMoviePresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Crew";
  // @codingStandardsIgnoreEnd

  /**
   * Initialize new movie crew presentation.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function init() {
    $this->initMoviePresenation(
      $this->intl->t("Crew of {title}"),
      $this->intl->t("Crew of {title}"),
      $this->intl->t("Crew")
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $set     = new CrewSet($this->container);
    $listing = null;

    /* @var $movieCrew \MovLib\Stub\Data\Movie\MovieCrew */
    foreach ($set->loadMovieCrew($this->entity) as $jobId => $movieCrew) {
      $listing .= "<dt><a class='no-link' href='{$this->intl->r("/job/{0}", $jobId)}'>{$movieCrew->job->names[Sex::UNKNOWN]}</a></dt>";
      /* @var $crew \MovLib\Data\Crew\Crew */
      foreach ($movieCrew->crewSet as $crewId => $crew) {
        if (isset($crew->person)) {
          $entityType  = "Person";
          $entityName  = $crew->person->name;
          $entityRoute = $crew->person->route;
        }
        else {
          $entityType  = "Company";
          $entityName  = $crew->company->name;
          $entityRoute = $crew->company->route;
        }
        $listing .= "<dd typeof='{$entityType}'><a href='{$entityRoute}' property='url'><span property='name'>{$entityName}</span></a></dd>";
      }
    }

    if ($listing) {
      return "<dl class='dl-big'>{$listing}</dl>";
    }

    return $this->calloutInfo("<p>{$this->intl->t("We couldn’t find the crew for this movie.")}</p>", $this->intl->t("No Crew"));
  }

}
