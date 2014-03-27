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
class History extends \MovLib\Presentation\Person\AbstractBase {

  /**
   * Initialize person history presentation.
   */
  public function init() {
    $this->person = new Person($this->diContainerHTTP);
    $this->person->init((integer) $_SERVER["PERSON_ID"]);
    $this->initPage($this->intl->t("History"));
    $this->pageTitle        = $this->intl->t("History of {0}", [ "<a href='{$this->person->route}'>{$this->person->name}</a>" ]);
    $this->initLanguageLinks("/person/{0}/history", [ $this->person->id ]);
    $this->initPersonBreadcrumb();
    $this->sidebarInit();
  }

  protected function getPageContent() {
    return new \MovLib\Partial\Alert($this->intl->t("The {0} feature isn’t implemented yet.", [ $this->intl->t("person history") ]), $this->intl->t("Check back later"), \MovLib\Partial\Alert::SEVERITY_INFO);
  }
}
