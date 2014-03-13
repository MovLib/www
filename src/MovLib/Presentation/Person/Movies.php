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

use \MovLib\Data\Person\FullPerson;
use \MovLib\Presentation\Partial\FormElement\InputSex;
use \MovLib\Presentation\Partial\Listing\Movie\MoviePersonListing;

/**
 * Presentation of a person's movies.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movies extends \MovLib\Presentation\Person\AbstractBase {

  /**
   * Instantiate new person movies presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n;
    $this->person = new FullPerson((integer) $_SERVER["PERSON_ID"]);
    $this->initPage($i18n->t("Movies with {0}", [ $this->person->name ]));
    $this->pageTitle        = $i18n->t(
      "Movies with {0}",
      [ "<a href='{$this->person->route}' property='url'><span property='name'>{$this->person->name}</span></a>" ]
    );
    $this->breadcrumbTitle  = $i18n->t("Movies");
    $this->initLanguageLinks("/person/{0}/movies", [ $this->person->id ], true);
    $this->initPersonBreadcrumb();
    $this->sidebarInit();
    $this->schemaType = "Person";
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;

    $this->person->getMovies();
    return "Under test";

    $listing = new MoviePersonListing($this->person->getMovies());
    if (!($movies = $listing->getListing())) {
      return new Alert($i18n->t("Seems like {person_name} hasn’t worked on any movies."), null, Alert::SEVERITY_INFO);
    }


    // ----------------------------------------------------------------------------------------------------------------- Fetch cast information


    $castResult = $this->person->getMovieCast();
    switch ($this->person->sex) {
      case InputSex::MALE:
        $jobName  = $i18n->t("Actor");
        $roleSelf = $i18n->t("Himself");
        break;

      case InputSex::FEMALE:
        $jobName  = $i18n->t("Actress");
        $roleSelf = $i18n->t("Herself");
        break;

      default:
        $jobName  = $i18n->t("Actor/Actress");
        $roleSelf = $i18n->t("Self");
        break;
    }

    /* @var $cast \MovLib\Data\Movie\Cast */
    while ($cast = $castResult->fetch_object("\\MovLib\\Data\\Movie\\Cast")) {
      $job = $this->a($i18n->r("/job/{0}", [ $cast->jobId ]), $jobName);

      $role = null;
      if ($cast->roleName) {
        $role = $cast->roleName;
      }
      elseif ($cast->role) {
        if ($cast->role === true) {
          $role = $this->a($this->person->route, $roleSelf);
        }
        else {
          $role = $this->a($cast->role->route, $cast->role->name);
        }
      }

      if ($role) {
        $job = $i18n->t("{0} ({1})", [ $jobName, $role ]);
      }

      $movies[$cast->movieId]["#jobs"] .= "<li>{$job}</li>";
    }


    // ----------------------------------------------------------------------------------------------------------------- Fetch crew information


    $crewResult = $this->person->getMovieCrew();
    /* @var $crew \MovLib\Data\Movie\Crew */
    while ($crew = $crewResult->fetch_object("\\MovLib\\Data\\Movie\\Crew")) {
      $movies[$crew->movieId]["#jobs"] .= "<li>{$this->a($i18n->r("/job/{0}", [ $crew->jobId ]), $crew->jobTitle)}</li>";
    }


    // ----------------------------------------------------------------------------------------------------------------- Build the listing


    $list = null;
    foreach ($movies as $id => $html) {
      $list .= "<li class='hover-item s r' typeof='Movie'>{$html["#movie"]}<ul class='no-list jobs s s4 tar'>{$html["#jobs"]}</ul></li>";
    }
    return "<ol class='hover-list no-list'>{$list}</ol>";
  }


}
