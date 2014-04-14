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
 * Allows deletion of a person's information.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Delete extends \MovLib\Presentation\AbstractPresenter {

  /**
   * Initialize person delete presentation.
   */
  public function init() {
    $this->person = new Person($this->diContainerHTTP, (integer) $_SERVER["PERSON_ID"]);
    $this->initPage($this->intl->t("Delete"));
    $this->pageTitle        = $this->intl->t("Delete {0}", [ "<a href='{$this->person->route}'>{$this->person->name}</a>" ]);
    $this->initLanguageLinks("/person/{0}/delete", [ $this->person->id ]);
    $this->breadcrumb
      ->addCrumb($this->person->routeIndex, $this->intl->t("Persons"))
      ->addCrumb($this->person->route, $this->person->name)
    ;
    $this->contentBefore = "<div class='c'>";
    $this->contentAfter  = "</div>";
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->callout($this->intl->t("The {0} feature isn’t implemented yet.", [ $this->intl->t("delete person") ]), $this->intl->t("Check back later"), "info");
  }

}
