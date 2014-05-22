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
   */
  public function getNoItemsContent() {
    return $this->calloutInfo(
      "<p>{$this->intl->t("We couldn’t find any person matching your filter criteria, or there simply aren’t any persons available.")}</p>" .
      "<p>{$this->intl->t("Would you like to {0}create an person{1}?", [ "<a href='{$this->intl->r("/person/create")}'>", "</a>" ])}</p>",
      $this->intl->t("No Persons")
    );
  }

}
