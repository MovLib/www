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

use \MovLib\Data\Movie\FullMovie;
use \MovLib\Presentation\Partial\Alert;

/**
 * Presentation of a single movie's crew.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Crew extends \MovLib\Presentation\Movie\AbstractBase {

  /**
   * Instantiate new movie crew presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n, $cache;

    $cache->cacheable = false;
    $cache->delete();
    $this->movie = new FullMovie($_SERVER["MOVIE_ID"]);
    $this->initPage($i18n->t("Crew"));
    $this->initBreadcrumb();
    $this->pageTitle = $i18n->t(
      "Crew of {0}",
      [ "<a href='{$this->movie->route}' property='url'><span property='name'>{$this->movie->displayTitleWithYear}</span></a>" ]
    );
    $this->initLanguageLinks("/movie/{0}/crew", [ $this->movie->id ]);
    $this->initSidebar();
  }

  /**
   * @inheritdoc
   *
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n;
    $this->schemaType = "Movie";

    if (($crewResult = $this->movie->getCrew())) {
      $crew = null;
      /* @var $movieCrew \MovLib\Stub\Data\Movie\MovieCrew */
      foreach ($crewResult as $movieCrew) {
        $crew .= "<dt><a href='{$movieCrew->job->route}'>{$movieCrew->job->title}</a></dt>";
        /* @var $entity \MovLib\Stub\Data\Entity */
        foreach ($movieCrew->members as $entity) {
          $crew .=
            "<dd typeof='{$entity->type}'>" .
              "<a href='{$entity->route}' property='url'><span property='name'>{$entity->name}</span></a>" .
            "</dd>";
        }
      }
      if ($crew) {
        return "<dl>{$crew}</dl>";
      }
    }

    return new Alert(
      $i18n->t("No crew assigned yet, please edit this page to provide this information."),
      $i18n->t("No Crew"),
      Alert::SEVERITY_INFO
    );
  }

}
