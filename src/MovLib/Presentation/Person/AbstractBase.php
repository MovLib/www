<?php

/*!
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 *  MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 *  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 *  version.
 *
 *  MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License along with MovLib.
 *  If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Presentation\Person;

/**
 * Base presenation of person pages.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase extends \MovLib\Presentation\AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person to present.
   *
   * @var \MovLib\Data\Person\FullPerson
   */
  protected $person;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  protected function goneGetContent() {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this->person instanceof \MovLib\Data\Person\Person)) {
      throw new \LogicException("\$this->person has to be a valid person object!");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $routeArgs = [ $this->person->id ];

    $this->goneAlertMessage = $this->intl->t(
        "The person and all its content have been deleted. A look at the edit {0}history{2} or {1}discussion{2} " .
        "will explain why that is the case. Please discuss with the person responsible for this deletion before " .
        "you restore this entry from its {0}history{2}.",
        [
          "<a href='{$this->intl->r("/person/{0}/history", $routeArgs)}'>",
          "<a href='{$this->intl->r("/person/{0}/discussion", $routeArgs)}'>",
          "</a>"
        ]
      );
    return $this->traitGetGoneContent();
  }

  protected function initPersonBreadcrumb() {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this->person instanceof \MovLib\Data\Person\Person)) {
      throw new \LogicException("\$this->person has to be a valid person object!");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    return $this->initBreadcrumb([
      [ $this->intl->r("/persons"), $this->intl->t("Persons") ],
      [ $this->person->route, $this->person->name ]
    ]);
  }

  protected function sidebarInit() {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($this->person instanceof \MovLib\Data\Person\Person)) {
      throw new \LogicException("\$this->person has to be a valid person object!");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Compile array once.
    $routeArgs = [ $this->person->id ];

    // Reduce the sidebar if the person was deleted.
    if ($this->person->deleted === true) {
      return $this->traitSidebarInit([
        [ $this->intl->r("/person/{0}/discussion", $routeArgs), $this->intl->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
        [ $this->intl->r("/person/{0}/history", $routeArgs), $this->intl->t("History"), [ "class" => "ico ico-history" ] ]
      ]);
    }

    return $this->traitSidebarInit([
      [ $this->person->route, $this->intl->t("View"), [ "class" => "ico ico-view" ] ],
      [ $this->intl->r("/person/{0}/discussion", $routeArgs), $this->intl->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
      [ $this->intl->r("/person/{0}/edit", $routeArgs), $this->intl->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $this->intl->r("/person/{0}/history", $routeArgs), $this->intl->t("History"), [ "class" => "ico ico-history" ] ],
      [ $this->intl->r("/person/{0}/delete", $routeArgs), $this->intl->t("Delete"), [ "class" => "ico ico-delete separator" ] ],

      [ $this->intl->r("/person/{0}/movies", $routeArgs), "{$this->intl->t("Movies")} <span class='fr'>{$this->intl->format("{0,number}", [ $this->person->getMoviesCount() ])}</span>", [ "class" => "ico ico-movie" ] ],
      [ $this->intl->r("/person/{0}/series", $routeArgs), "{$this->intl->tp(-1, "Series")} <span class='fr'>{$this->intl->format("{0,number}", [ $this->person->getSeriesCount() ])}</span>", [ "class" => "ico ico-series" ] ],
      [ $this->intl->r("/person/{0}/releases", $routeArgs), "{$this->intl->t("Releases")} <span class='fr'>{$this->intl->format("{0,number}", [ $this->person->getReleasesCount() ])}</span>", [ "class" => "ico ico-release separator" ] ],
    ]);
  }

}
