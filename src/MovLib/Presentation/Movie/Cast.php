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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Cast\CastSet;

/**
 * Presentation of single movie's cast.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Cast extends \MovLib\Presentation\Movie\AbstractMoviePresenter {

  /**
   * Initialize new movie cast presentation.
   *
   */
  public function init() {
    $this->initMoviePresenation(
      $this->intl->t("Cast of {title}"),
      $this->intl->t("Cast of {title}"),
      $this->intl->t("Cast"))
    ;
  }

  public function getContent() {
    $set     = new CastSet($this->diContainerHTTP);
    $listing = null;

    /* @var $moviePerson \MovLib\Stub\Data\Movie\MoviePerson */
    foreach ($set->loadMovieCast($this->entity) as $personId => $moviePerson) {
      $roles = null;
      /* @var $cast \MovLib\Data\Cast\Cast */
      foreach ($moviePerson->castSet as $crewId => $cast) {
        if ($roles) {
          $roles .= ", ";
        }

        if ($cast->role) {
          $roles .= $cast->role;
        }
        elseif ($cast->roleId === $moviePerson->person->id) {
          $roles .= "<a href='{$this->intl->r($moviePerson->person->routeKey, $cast->roleId)}'>{$cast->roleTitleSelf}</a>";
        }
        elseif ($cast->roleId && $cast->roleName) {
          $roles .= "<a href='{$this->intl->r($moviePerson->person->routeKey, $cast->roleId)}'>{$cast->roleName}</a>";
        }
      }
      if ($roles) {
        $roles = "<small>{$this->intl->t("as {roles}", [ "roles" => $roles ])}</small>";
      }

      $listing .=
        "<li class='hover-item r'>" .
          "<div typeof='Person'>" .
            "<div class='s s1' property='image'>{$this->img($moviePerson->person->imageGetStyle("s1"))}</div>" .
            "<div class='s s9'>" .
              "<h2 class='para' property='name'><a href='{$this->intl->r("/person/{0}", $moviePerson->person->id)}'>{$moviePerson->person->name}</a></h2>" .
              $roles .
            "</div>" .
          "</div>" .
        "</li>"
      ;
    }

    if ($listing) {
      return "<ol class='hover-list no-list'>{$listing}</ol>";
    }

    return $this->callout(
      "<p>{$this->intl->t("We couldn’t find the cast for this movie.")}</p>",
      $this->intl->t("No Cast"),
      "info"
    );
  }

}
