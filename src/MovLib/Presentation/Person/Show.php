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

use \MovLib\Data\Person\Full as FullPerson;
use \MovLib\Exception\Client\ErrorNotFoundException;

/**
 * Presentation of a single person.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person to present.
   *
   * @var \MovLib\Data\Person\Full
   */
  protected $person;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Exception\Client\ErrorNotFoundException
   * @throws \LogicException
   */
  public function __construct() {
    global $i18n;
    try {
      $this->person                = new FullPerson($_SERVER["PERSON_ID"]);
      $this->schemaType            = "Person";
      $this->headingSchemaProperty = "name";
      $this->init($this->person->name);
      $this->initSidebar([]);

      // Display gone page if this person was deleted.
      if ($this->person->deleted === true) {
        // @todo Implement gone presentation for persons.
        throw new \LogicException("Not implemented yet!");
      }

      $photo = $this->getImage(
        $this->person->displayPhoto->getStyle(),
        $i18n->rp("/person/{0}/photos", [ $this->person->id ]),
        [ "itemprop" => "image" ]
      );

      $this->headingBefore = "<div class='r'><div class='s s10'>";
      $this->headingAfter  = "</div><div class='s s2'>{$photo}</div></div>";
    }
    catch (\OutOfBoundsException $e) {
      throw new ErrorNotFoundException("Couldn't find person for identifier '{$_SERVER["PERSON_ID"]}'");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {}

}
