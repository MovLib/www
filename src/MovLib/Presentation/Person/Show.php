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
use \MovLib\Partial\Alert;
use \MovLib\Partial\Date;
use \MovLib\Partial\FormElement\InputSex;
use \MovLib\Partial\Place;
use \MovLib\Partial\InfoboxTrait;
use \MovLib\Partial\Sex;

/**
 * Presentation of a single person.
 *
 * @link http://schema.org/Person
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/person/{id}
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/person/{id}
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/person/{id}
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/person/{id}
 *
 * @property \MovLib\Data\Person\Person $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\AbstractShowPresenter {
  use \MovLib\Partial\ContentSectionTrait;


  // ------------------------------------------------------------------------------------------------------------------- Initialization Methods.


  /**
   * Initialize person presentation.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function init() {
    $this->initShow(new Person($this->diContainerHTTP, (integer) $_SERVER["PERSON_ID"]), $this->intl->t("Persons"), $this->intl->t("Person"), "Person", null);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function getPlural() {
    return "persons";
  }

  /**
   * {@inheritdoc}
   */
  protected function getSingular() {
    return "person";
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->headingBefore = "<div class='r'><div class='s s10'>";

    $this->pageTitle = "<span property='name'>{$this->entity->name}</span>";

    if ($this->entity->sex !== Sex::UNKNOWN) {
      if ($this->entity->sex === Sex::MALE) {
        $sexTitle = $this->intl->t("Male");
      }
      if ($this->entity->sex === Sex::FEMALE) {
        $sexTitle = $this->intl->t("Female");
      }
      $this->pageTitle .=  "<sup class='ico ico-sex{$this->entity->sex} sex sex-{$this->entity->sex}' content='{$sexTitle}' property='gender' title='{$sexTitle}'></sup>";
    }

    $infos = new InfoboxTrait($this->intl);
    $this->entity->bornName && $infos->add($this->intl->t("Born as"), "<span property='additionalName'>{$this->entity->bornName}</span>");

    if ($this->entity->birthDate) {
    $birthDateFormatted = "<a href='{$this->intl->rp("/year/{0}/persons", $this->entity->birthDate->year)}'>{$this->dateFormat($this->entity->birthDate, [ "property" => "birthDate" ])}</a>";
      if ($this->entity->deathDate) {

      }
      else {

      }

      $infos->add($this->intl->t("Date of Birth"), $birthinfo);
    }

//    ($birthplace = $this->entity->getBirthPlace()) && $infos->add($this->intl->t("Place of Birth"), $birthplace);

//    ($deathplace = $this->entity->getDeathPlace()) && $infos->add($this->intl->t("Place of Death"), $deathplace);
    $this->entity->wikipedia && $infos->addWikipedia($this->entity->wikipedia);

    $this->headingAfter .= "{$infos}</div>{$this->img($this->entity->imageGetStyle(), [], true, [ "class" => "s s2" ])}</div>";

    if (($content = $this->getContentSections())) {
      return $content;
    }

    return new Alert(
      "<p>{$this->intl->t("{sitename} doesn’t have further details about this person.", [ "sitename" => $this->config->sitename ])}</p>" .
      "<p>{$this->intl->t("Would you like to {0}add additional information{1}?", [ "<a href='{$this->intl->r("/person/{0}/edit", $this->entity->id)}'>", "</a>" ])}</p>",
      $this->intl->t("No Info")
    );
  }

}
