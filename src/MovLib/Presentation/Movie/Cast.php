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

use \MovLib\Data\Movie\FullMovie;
use \MovLib\Data\Person\Person;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\FormElement\InputSex;
use \MovLib\Presentation\Partial\Listing\Persons;

/**
 * Presentation of single movie's cast.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Cast extends \MovLib\Presentation\Movie\AbstractBase {

  /**
   * Instantiate new movie cast presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->movie = new FullMovie($_SERVER["MOVIE_ID"]);
    $this->initPage($i18n->t("Cast"));
    $this->initBreadcrumb();
    $this->pageTitle = $i18n->t(
      "Cast of {0}",
      [ "<a href='{$this->movie->route}' property='url'><span property='name'>{$this->movie->displayTitleWithYear}</span></a>" ]
    );
    $this->initLanguageLinks("/movie/{0}/cast", [ $this->movie->id ]);
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
    $content = null;

    // Build the directors section.
    $this->sidebarNavigation->menuitems[] = [ "#directors", $i18n->t("Directors") ];
    $directors = new Persons($this->movie->getDirectors(), $i18n->t(
      "No directors assigned yet, {0}add directors{1}?",
      [ "<a href='{$i18n->r("/movie{0}/edit", [ $this->movie->id ])}'>", "</a>" ]
    ), [ "property" => "director" ]);
    $content .= "<div id='directors'><h2>{$i18n->t("Directors")}</h2>{$directors}</div>";

    // Build the cast section.
    $this->sidebarNavigation->menuitems[] = [ "#cast", $i18n->t("Cast") ];
    $cast                                 = null;
    $castResult                           = $this->movie->getCast();
    $personRoute                          = $i18n->r("/person/{0}");
    $roleHimself                          = $i18n->t("Himself");
    $roleHerself                          = $i18n->t("Herself");
    $roleSelf                             = $i18n->t("Self");
    /* @var $person \MovLib\Data\Person\Person */
    foreach ($castResult as $id => $person) {
      $roles = null;
      $c = count($person->roles);
      for ($i = 0; $i < $c; ++$i) {
        if ($roles) {
          $roles .= ", ";
        }

        $role = $person->roles[$i]["name"];
        if ($person->roles[$i]["id"]) {
          if ($person->roles[$i]["id"] === $person->id) {
            switch ($person->sex) {
              case InputSex::MALE:
                $role = $roleHimself;
                break;
              case InputSex::FEMALE:
                $role = $roleHerself;
                break;
              default:
                $role = $roleSelf;
                break;
            }
          }
          $route = str_replace("{0}", $person->roles[$i]["id"], $personRoute);
          $role  = "<a href='{$route}'>{$role}</a>";
        }
        $roles .= $role;
      }
      if ($roles) {
        $roles = "<small>{$i18n->t(
          "{begin_emphasize}as{end_emphasize} {roles}",
          [ "roles" => $roles, "begin_emphasize" => "<em>", "end_emphasize" => "</em>" ]
        )}</small>";
      }

      $cast .=
        "<li class='hover-item r s' property='actor' typeof='Person'>" .
          $this->getImage(
            $person->getStyle(Person::STYLE_SPAN_01),
            $person->route,
            null,
            [ "class" => "s s1 tac" ]
          ) .
          "<div class='s s9'>" .
            "<p><a href='{$person->route}' property='url'><span property='name'>$person->name</span></a></p>{$roles}" .
          "</div>" .
        "</li>"
      ;
    }
    if ($castResult) {
      $cast = "<ol class='hover-list no-list'>{$cast}</ol>";
    }
    else {
      $cast = new Alert($i18n->t("No cast assigned yet, {0}add cast{1}?", [
        "<a href='{$i18n->r("/movie{0}/edit", [ $this->movie->id ])}'>",
        "</a>" ]),
      null, Alert::SEVERITY_INFO);
    }
    $content .= "<div id='cast'><h2>{$i18n->t("Cast")}</h2>{$cast}</div>";

    return $content;
  }

}
