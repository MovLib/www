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
namespace MovLib\Presentation\Person;

use \MovLib\Data\Person\PersonSet;
use \MovLib\Partial\Alert;
use \MovLib\Partial\Date;

/**
 * Defines the person index listing.
 *
 * @link http://schema.org/Person
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/persons
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/persons
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/persons
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/persons
 *
 * @property \MovLib\Data\Person\PersonSet $set
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {
  use \MovLib\Partial\PersonTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initIndex(new PersonSet($this->diContainerHTTP), $this->intl->t("Persons"), $this->intl->t("Create New Person"));
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Person\Person $person {@inheritdoc}
   */
  protected function formatListingItem(\MovLib\Data\AbstractEntity $person, $id) {
    if (($bornName = $this->getPersonBornName($person))) {
      $bornName = "<small>{$bornName}</small>";
    }

    $bioDates = (new Date($this->intl, $this))->formatFromTo(
      $person->birthDate,
      $person->deathDate,
      [ "property" => "birthDate", "title" => $this->intl->t("Date of Birth") ],
      [ "property" => "deathDate", "title" => $this->intl->t("Date of Death") ]
    );
    if ($bioDates) {
      $bioDates = "<small>{$bioDates}</small>";
    }
    $route = $person->route;
    return
      "<li class='hover-item r'>" .
        "<article typeof='Person'>" .
          $this->img($person->imageGetStyle("s1"), [], $route, [ "class" => "s s1" ]) .
          "<div class='s s9'>" .
            "<div class='fr'>" .
              "<a class='ico ico-movie label' href='{$this->intl->rp("/person/{0}/movies", [ $id ])}' title='{$this->intl->t("Movies")}'>{$person->countMovies}</a>" .
              "<a class='ico ico-series label' href='{$this->intl->rp("/person/{0}/series", [ $id ])}' title='{$this->intl->t("Series")}'>{$person->countSeries}</a>" .
              "<a class='ico ico-release label' href='{$this->intl->rp("/person/{0}/releases", [ $id ])}' title='{$this->intl->t("Releases")}'>{$person->countReleases}</a>" .
              "<a class='ico ico-award label' href='{$this->intl->rp("/person/{0}/awards", [ $id ])}' title='{$this->intl->t("Awards")}'>{$person->countAwards}</a>" .
            "</div>" .
            "<h2 class='para'>" .
              "<a href='{$route}' property='url'><span property='name'>{$person->name}</span></a>" .
            "</h2>" .
          "{$bornName}{$bioDates}</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return new Alert(
      "<p>{$this->intl->t(
        "We couldn’t find any persons matching your filter criteria, or there simply aren’t any persons available."
      )}</p><p>{$this->intl->t(
        "Would you like to {0}create a person{1}?",
        [ "<a href='{$this->intl->r("/person/create")}'>", "</a>" ]
      )}</p>",
      $this->intl->t("No Persons")
    );
  }

}
