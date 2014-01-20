<?php

/*
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

use \MovLib\Data\Person\Full;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputDate;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputText;
use \MovLib\Presentation\Redirect\SeeOther;

/**
 * Allows the creation of a new person.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 */
class Create extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person's biography html input element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $biography;

  /**
   * The person's birthdate input date element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputDate
   */
  protected $birthDate;

  /**
   * The person's born name input text element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputText
   */
  protected $bornName;

  /**
   * The person's deathdate input date element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputDate
   */
  protected $deathDate;

  /**
   * The person's name input text element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputText
   */
  protected $name;

  /**
   * The person's sex radio group element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\RadioGroup
   */
  protected $sex;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person create presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->initBreadcrumb([ [ $i18n->rp("/persons"), $i18n->t("Persons") ] ]);
    $this->initPage($i18n->t("Create Person"));

    $this->name = new InputText("name", $i18n->t("Name"), [ "placeholder" => $i18n->t("Enter the person's name"), "required" => "required" ]);

    $this->bornName = new InputText("born-name", $i18n->t("Born as"), [ "placeholder" => $i18n->t("Enter the person's birth name") ]);

    $this->birthDate = new InputDate("birthdate", $i18n->t("Date of Birth"));

    $this->deathDate = new InputDate("deathdate", $i18n->t("Date of Death"));

    $this->sex = new \MovLib\Presentation\Partial\FormElement\RadioGroup("sex", $i18n->t("Sex"), [
      2 => $i18n->t("Female"),
      1 => $i18n->t("Male"),
      0 => $i18n->t("Unknown"),
    ], 0);

    $this->biography = new InputHTML("biography", $i18n->t("Biography"), null, [
      "placeholder" => $i18n->t("Enter the person's biography here"),
    ]);
    $this->biography
      ->allowBlockqoutes()
      ->allowImages()
      ->allowLists()
    ;

    $this->form = new Form($this, [
      $this->name,
      $this->bornName,
      $this->birthDate,
      $this->deathDate,
      $this->sex,
      $this->biography,
    ]);

    $this->form->actionElements[] = new \MovLib\Presentation\Partial\FormElement\InputSubmit($i18n->t("Create Person"), [ "class" => "btn btn-large btn-success" ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getContent() {
    return "<div class='c'><div class='r'>{$this->form}</div></div>";
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  protected function valid() {
    global $i18n, $kernel;

    $person = new Full();
    $person->biography = $this->biography->value;
    $person->birthDate = $this->birthDate->value;
    $person->bornName  = $this->bornName->value;
    $person->deathDate = $this->deathDate->value;
    $person->name      = $this->name->value;
    $person->sex       = $this->sex->value;

    $id = $person->create();

    $kernel->alerts .= new Alert(
      $i18n->t(
        "You have successfully created the person “{0}”! If you want to change something, click {1}here{2}.",
        [ $person->name, "<a href='{$i18n->r("/person/{0}/edit", [ $id ])}'>", "</a>" ]
      ),
      $i18n->t("Person created successfully"),
      Alert::SEVERITY_SUCCESS
    );

    throw new SeeOther($i18n->r("/person/{0}", [ $id ]));
  }

}
