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

namespace MovLib\Presentation\Person\Photo;

use \MovLib\Data\Person\Person;

/**
 * Image details presentation for a person's photo.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;

  /**
   * The person we are working with.
   *
   * @var \MovLib\Data\Person\Person
   */
  protected $person;

  /**
   * Instantiate new Person Photo presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Exception\DatabaseException
   */
  public function __construct() {
    global $i18n;

    // Try to load person data.
    $this->person = new Person($_SERVER["PERSON_ID"]);

    // Initialize language links.
    $routeArgs = [ $this->person->id ];
    $this->initLanguageLinks("/person/{0}/photo", $routeArgs);

    // Initialize page.
    $this->initPage($i18n->t("Photo of {person_name}", [ "person_name" => $this->person->name ]));
    $this->pageTitle = $i18n->t("Photo of {person_name}", [ "person_name" => "<a href='{$this->person->route}'>{$this->person->name}</a>" ]);

    // Initialize breadcrumbs.
    $this->initBreadcrumb([
      [ $i18n->rp("/persons"), $i18n->t("Persons") ],
      [ $this->person->route, $this->person->name ],
    ]);

    // Initialize sidebar navigation.
    $this->initSidebar([
        [ $i18n->r("/person/{0}/photo", [ $this->person->id ]), $i18n->t("View"), [ "class" => "ico ico-view" ] ],
        [ $i18n->r("/person/{0}/photo/edit", [ $this->person->id ]), $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
        [ $i18n->r("/person/{0}/photo/history", [ $this->person->id ]), $i18n->t("History"), [ "class" => "ico ico-history" ] ],
        [ $i18n->r("/person/{0}/photo/delete", [ $this->person->id ]), $i18n->t("Delete"), [ "class" => "ico ico-delete" ] ],
      ]);
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    // @todo: No photo -> display upload link and no sidebar.
    return new \MovLib\Presentation\Partial\Alert($i18n->t("The {0} feature isn’t implemented yet.", [ $i18n->t("show person photo") ]), $i18n->t("Check back later"), \MovLib\Presentation\Partial\Alert::SEVERITY_INFO);
  }
}
