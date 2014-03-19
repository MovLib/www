<?php

/* !
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

use \MovLib\Data\Person\FullPerson;
use \MovLib\Presentation\Partial\Listing\PersonMovieListing;

/**
 * Presentation of a person's movies.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movies extends \MovLib\Presentation\Person\AbstractBase {

  /**
   * Instantiate new person movies presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n;
    $this->person = new FullPerson((integer) $_SERVER["PERSON_ID"]);
    $this->initPage($i18n->t("Movies with {0}", [ $this->person->name ]));
    $this->pageTitle        = $i18n->t(
      "Movies with {0}",
      [ "<a href='{$this->person->route}' property='url'><span property='name'>{$this->person->name}</span></a>" ]
    );
    $this->breadcrumbTitle  = $i18n->t("Movies");
    $this->initLanguageLinks("/person/{0}/movies", [ $this->person->id ], true);
    $this->initPersonBreadcrumb();
    $this->sidebarInit();
    $this->schemaType = "Person";
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    return new PersonMovieListing($this->person->getMovies());
  }


}
