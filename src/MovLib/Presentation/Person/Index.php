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

use \MovLib\Data\Person\Person;
use \MovLib\Partial\Listing\PersonLifeDateListing;

/**
 * The listing for the latest person additions.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitSidebar;
  use \MovLib\Presentation\TraitPagination;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person object for data retrieval.
   *
   * @var \MovLib\Data\Person\Person
   */
  protected $person;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    return new PersonLifeDateListing(
      $this->diContainerHTTP,
      $this->person->getPersons($this->paginationOffset, $this->paginationLimit)
    );
  }

  /**
   * Initialize latest persons presentation.
   *
   */
  public function init() {
    $this->person = new Person($this->diContainerHTTP);
    $this->initPage($this->intl->t("Persons"));
    $this->initBreadcrumb();
    $this->initLanguageLinks("/persons", null, true);
    $this->sidebarInit([
      [ $this->intl->rp("/persons"), $this->intl->t("Persons"), [ "class" => "ico ico-person" ] ],
      [ $this->intl->r("/person/random"), $this->intl->t("Random") ],
    ]);
    $this->paginationInit($this->person->getTotalCount());
    $this->headingBefore ="<a class='btn btn-large btn-success fr' href='{$this->intl->r(
        "/person/create"
      )}'>{$this->intl->t("Create New Person")}</a>";
  }

}
