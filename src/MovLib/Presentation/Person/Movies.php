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
namespace MovLib\Presentation\Person;

use \MovLib\Partial\Genre;
use \MovLib\Data\Cast\Cast;
use \MovLib\Data\Movie\MovieJobSet;

/**
 * Presentation of a person's movies.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movies extends \MovLib\Presentation\Person\AbstractPersonPresenter {
  use \MovLib\Presentation\Movie\MovieTrait;

  /**
   * Initialize person movies presentation.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function init() {
    $this->initPersonPresentation(
      $this->intl->t("Movies with {name}"),
      $this->intl->t("Movies with {name}"),
      $this->intl->t("Movies")
    );
  }

  public function getContent() {
    $set = new MovieJobSet($this->diContainerHTTP);
    /* @var $jobs \MovLib\Stub\Data\Movie\MoviePersonJobs */
    $listing = null;
    foreach ($set->loadEntitiesByPerson($this->entity) as $movieId => $jobs) {
      $jobList = null;

      $cast        = null;
      $castJobName = null;
      $castRoute   = null;
      /* @var $castJob \MovLib\Data\Cast\Cast */
      foreach ($jobs->cast as $id => $castJob) {
        if (!$castJobName) {
          $castJobName = $castJob->names[$this->entity->sex];
          $castRoute   = $castJob->route;
        }
        if ($cast) {
          $cast .= ", ";
        }
        // Simple role string.
        if ($castJob->role) {
          $cast .= $castJob->role;
        }
        // Person is playing him/herself.
        elseif ($castJob->roleId === $this->entity->id) {
          $cast .= "<a href='{$this->entity->route}'>{$castJob->roleTitleSelf}</a>";
        }
        // Person is playing another person.
        elseif ($castJob->roleId && $castJob->roleName) {
          $cast .= "<a href='{$this->intl->r($this->entity->routeKey, $castJob->roleId)}'>{$castJob->roleName}</a>";
        }
      }
      if ($cast) {
        $jobList .= "<li>{$this->intl->t(
          "{job} as {roles}",
          [ "job" => "<a href='{$castRoute}'>{$castJobName}</a>", "roles" => $cast ]
        )}</li>";
      }

      /* @var $crewJob \MovLib\Data\Crew\Crew */
      foreach ($jobs->crew as $id => $crewJob) {
        $jobList .= "<li><a href='{$crewJob->route}'>{$crewJob->names[$this->entity->sex]}</a></li>";
      }

      if ($jobList) {
        $jobList = "<ul class='no-list small'>{$jobList}</ul>";
      }
      $listing .=
        "<li class='hover-item r'>" .
          "<article typeof='Movie'>" .
            "<div class='s s1' property='image'>{$this->img($jobs->movie->imageGetStyle("s1"))}</div>" .
            "<div class='s s8'>" .
              "<h2 class='para'>{$this->getStructuredDisplayTitle($jobs->movie)}</h2>" .
              $this->getStructuredOriginalTitle($jobs->movie, "small") .
              (new Genre($this->diContainerHTTP))->getLabels($jobs->genreSet, [ "class" => "small cf mb10" ]) .
              $jobList .
            "</div>" .
            "<div class='s s1 rating-mean tac'>{$this->intl->format("{0,number}", $jobs->movie->meanRating)}</div>" .
          "</article>" .
        "</li>"
      ;
    }
    if ($listing) {
      return "<ol class='hover-list no-list'>{$listing}</ol>";
    }

    return $this->callout(
      "<p>{$this->intl->t("We couldn’t find any movies this person has worked on.")}</p>",
      $this->intl->t("No Movies"),
      "info"
    );
  }

}
