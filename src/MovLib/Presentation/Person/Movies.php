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

use \MovLib\Data\Person\Person;
use \MovLib\Data\Movie\MovieJobSet;

/**
 * Presentation of a person's movies.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movies extends \MovLib\Presentation\AbstractIndexPresenter {

  /**
   * Initialize person movies presentation.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function init() {
    $this->person = new Person($this->diContainerHTTP, (integer) $_SERVER["PERSON_ID"]);
//    $this->initPage($this->intl->t("Movies with {0}", [ $this->person->name ]));
//    $this->pageTitle        = $this->intl->t(
//      "Movies with {0}",
//      [ "<a href='{$this->person->route}' property='url'><span property='name'>{$this->person->name}</span></a>" ]
//    );
//    $this->breadcrumbTitle  = $this->intl->t("Movies");
//    $this->initLanguageLinks("/person/{0}/movies", [ $this->person->id ], true);
//    $this->initPersonBreadcrumb();
//    $this->sidebarInit();
//    $this->schemaType = "Person";
    // @todo: Replace with the real set!
    $set = new MovieJobSet($this->diContainerHTTP);
    $set->loadEntitiesByPerson($this->person);
    $this->initIndex($set, "Fix me!", "Fix me!");
  }

  /**
   * {@inheritdoc}
   */
  protected function formatListingItem(\MovLib\Data\AbstractEntity $item, $delta) {

  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return $this->callout(
      "<p>{$this->intl->t("We couldn’t find any movies this person has worked on.")}</p>",
      $this->intl->t("No Movies"),
      "info"
    );
  }

}
