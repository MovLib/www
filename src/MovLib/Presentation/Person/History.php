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

use \MovLib\Data\Person\Person;

/**
 * A person's history
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 */
class History extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;

  /**
   * The person to discuss.
   *
   * @var \MovLib\Data\Person\Person
   */
  protected $person;

  public function __construct() {
    global $i18n;

    $this->person = new Person($_SERVER["PERSON_ID"]);
    $this->initBreadcrumb([[ $i18n->rp("/persons"), $i18n->t("Persons") ], [ $this->person->route, $this->person->name ]]);
    $this->initPage($i18n->t("History of {0}", [ $this->person->name ]));
    $routeArgs = [ $this->person->id ];
    $this->initLanguageLinks("/person/{0}/discussion", $routeArgs);
    $this->initSidebar([
      [ $this->person->route, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
      [ $i18n->r("/person/{0}/discussion", $routeArgs), $i18n->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
      [ $i18n->r("/person/{0}/edit", $routeArgs), $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $i18n->r("/person/{0}/history", $routeArgs), $i18n->t("History"), [ "class" => "ico ico-history separator" ] ],
    ]);
  }

  protected function getPageContent() {
    global $i18n;
    return new \MovLib\Presentation\Partial\Alert($i18n->t("The {0} feature isn’t implemented yet.", [ $i18n->t("person history") ]), $i18n->t("Check back later"), \MovLib\Presentation\Partial\Alert::SEVERITY_INFO);
  }
}
