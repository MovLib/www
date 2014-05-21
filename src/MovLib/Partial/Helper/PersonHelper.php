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
namespace MovLib\Partial\Helper;

use \MovLib\Partial\Date;

/**
 * Person Helper Methodes.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PersonHelper extends \MovLib\Core\Presentation\DependencyInjectionBase {
  use \MovLib\Partial\PersonTrait;

  /**
   * Get a person listing.
   *
   * @param \MovLib\Data\Person\PersonSet $personSet
   *
   * @return string
   *   The person listing.
   */
  public function getListing(\MovLib\Data\Person\PersonSet $personSet) {
    $items = null;
    foreach ($personSet->entities as $id => $person) {
      if (($bornName = $this->getPersonBornName($person))) {
        $bornName = "<small>{$bornName}</small>";
      }

      $bioDates = (new Date($this->intl, $this->presenter))->formatFromTo(
        $person->birthDate,
        $person->deathDate,
        [ "property" => "birthDate", "title" => $this->intl->t("Date of Birth") ],
        [ "property" => "deathDate", "title" => $this->intl->t("Date of Death") ]
      );
      if ($bioDates) {
        $bioDates = "<small>{$bioDates}</small>";
      }
      $route = $person->route;
      $items .=
        "<li class='hover-item r'>" .
          "<article typeof='Person'>" .
            $this->img($person->imageGetStyle("s1"), [], $route, [ "class" => "s s1" ]) .
            "<div class='s s9'>" .
              "<div class='fr'>" .
                "<a class='ico ico-movie label' href='{$this->intl->r("/person/{0}/movies", [ $id ])}' title='{$this->intl->t("Movies")}'>{$person->countMovies}</a>" .
                "<a class='ico ico-series label' href='{$this->intl->r("/person/{0}/series", [ $id ])}' title='{$this->intl->tp(-1, "Series")}'>{$person->countSeries}</a>" .
                "<a class='ico ico-release label' href='{$this->intl->r("/person/{0}/releases", [ $id ])}' title='{$this->intl->t("Releases")}'>{$person->countReleases}</a>" .
                "<a class='ico ico-award label' href='{$this->intl->r("/person/{0}/awards", [ $id ])}' title='{$this->intl->t("Awards")}'>{$person->countAwards}</a>" .
              "</div>" .
              "<h2 class='para'>" .
                "<a href='{$route}' property='url'><span property='name'>{$person->name}</span></a>" .
              "</h2>" .
            "{$bornName}{$bioDates}</div>" .
          "</article>" .
        "</li>"
      ;
    }
    return "<ol class='hover-list no-list'>{$items}</ol>";
  }
}
