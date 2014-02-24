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

use \MovLib\Data\Person\Full as FullPerson;
use \MovLib\Presentation\Error\Gone;

/**
 * Base presenation of person pages.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase extends \MovLib\Presentation\Page{
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person to present.
   *
   * @var \MovLib\Data\Person\Full
   */
  protected $person;

  /**
   * The translated route to the person's edit page.
   *
   * @var string
   */
  protected $routeEdit;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new basic person presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\Gone
   */
  public function __construct() {
    global $i18n, $kernel;

    // Try to load person data.
    $this->person = new FullPerson($_SERVER["PERSON_ID"]);

    // Initialize the page title.
    $this->initPage($this->person->name);

    // Initialize Breadcrumb already with the person route, since all presentations are subpages except for Show.
    $this->initBreadcrumb([[ $i18n->rp("/persons"), $i18n->t("Persons") ], [ $this->person->route, $this->person->name ]]);

    // Initialize edit route, sidebar and schema.
    $routeArgs = [ $this->person->id ];
    $this->routeEdit = $i18n->r("/person/{0}/edit", $routeArgs);
    $this->sidebarInit([
      [ $this->person->route, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
      [ $i18n->r("/person/{0}/discussion", $routeArgs), $i18n->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
      [ $this->routeEdit, $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $i18n->r("/person/{0}/history", $routeArgs), $i18n->t("History"), [ "class" => "ico ico-history" ] ],
      [ $i18n->r("/person/{0}/delete", $routeArgs), $i18n->t("Delete"), [ "class" => "ico ico-delete separator" ] ],
    ]);
    $this->schemaType = "Person";

    // Display Gone page if this person was deleted.
    if ($this->person->deleted === true) {
      // @todo Implement Gone presentation for persons instead of this generic one.
      throw new Gone;
    }
    $kernel->stylesheets[] = "person";
  }
}
