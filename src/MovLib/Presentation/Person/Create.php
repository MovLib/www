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

use \MovLib\Data\Person\FullPerson;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputCheckbox;
use \MovLib\Presentation\Partial\FormElement\InputDateSeparate;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputLinesText;
use \MovLib\Presentation\Partial\FormElement\InputLinesURL;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputText;
use \MovLib\Presentation\Partial\FormElement\InputURL;
use \MovLib\Presentation\Partial\FormElement\RadioGroup;
use \MovLib\Presentation\Redirect\SeeOther;

/**
 * Allows the creation of a new person.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Create extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitForm;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person's aliases textarea input element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputLinesText
   */
  protected $inputAliases;

  /**
   * The person's biography html input element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $inputBiography;

  /**
   * The person's birthdate input date element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputDate
   */
  protected $inputBirthDate;

  /**
   * The person's born name input text element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputText
   */
  protected $inputBornName;

  /**
   * Checkbox to confirm that this person is new, in case of similar existing persons.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputCheckbox
   */
  protected $inputConfirmation;

  /**
   * The person's deathdate input date element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputDate
   */
  protected $inputDeathDate;

  /**
   * The person's external links textarea input element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputLinesURL
   */
  protected $inputLinks;

  /**
   * The person's name input text element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputText
   */
  protected $inputName;

  /**
   * The person's sex radio group element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\RadioGroup
   */
  protected $inputSex;

  /**
   * The person's localized Wikipedia URL input element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputURL
   */
  protected $inputWikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Initialize person create presentation.
   *
   */
  public function init() {
    $this->initPage($this->intl->t("Create Person"));
    $this->initBreadcrumb([ [ $this->intl->rp("/persons"), $this->intl->t("Persons") ] ]);

    $this->inputName      = new InputText("name", $this->intl->t("Name"), [ "placeholder" => $this->intl->t("Enter the person’s name"), "required" => true ]);
    $this->inputBornName  = new InputText("born-name", $this->intl->t("Born as"), [ "placeholder" => $this->intl->t("Enter the person’s birth name") ]);
    $dateOptions          = [ "year_max" => date("Y"), "year_min" => 1800 ];
    $this->inputBirthDate = new InputDateSeparate("birthdate", $this->intl->t("Date of Birth"), null, [ "class" => "s s6" ], $dateOptions);
    $this->inputDeathDate = new InputDateSeparate("deathdate", $this->intl->t("Date of Death"), null, [ "class" => "s s6" ], $dateOptions);
    $this->inputSex       = new RadioGroup("sex", $this->intl->t("Sex"), [ 2 => $this->intl->t("Female"), 1 => $this->intl->t("Male"), 0 => $this->intl->t("Unknown") ], 0);
    $this->inputWikipedia = new InputURL("wikipedia", $this->intl->t("Wikipedia URL"), [ "data-allow-external" => true ]);
    $this->inputAliases   = new InputLinesText("aliases", $this->intl->t("Additional Names"), [ "placeholder" => $this->intl->t("Please supply one name per line") ]);
    $this->inputBiography = new InputHTML("biography", $this->intl->t("Biography"), null, [ "placeholder" => $this->intl->t("Enter the person’s biography here") ]);
    $this->inputBiography->allowBlockqoutes()->allowImages()->allowLists();
    $this->inputLinks     = new InputLinesURL("links", $this->intl->t("External Links"), [ "data-allow-external" => true, "placeholder" => $this->intl->t("Please supply one URL per line") ]);
    $this->form           = new Form($this, [
      $this->inputName,
      $this->inputBiography,
      $this->inputBornName,
      $this->inputBirthDate,
      $this->inputDeathDate,
      $this->inputSex,
      $this->inputAliases,
      $this->inputWikipedia,
      $this->inputLinks,
    ]);
    $this->form->actionElements[] = new InputSubmit($this->intl->t("Create Person"), [ "class" => "btn btn-large btn-success", "id" => "submit-create" ]);
    $this->form->actionElements[] = new InputSubmit($this->intl->t("Create and Upload Image"), [ "class" => "btn btn-large btn-success", "id" => "submit-upload" ]);
    $kernel->stylesheets[] = "person";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getContent() {
    // @todo: Continue with form building and date styling.
    return
      "<div class='c'>{$this->form->open()}" .
        $this->inputName .
        $this->inputBiography .
        $this->inputBornName .
        "<div class='r'>{$this->inputBirthDate}{$this->inputDeathDate}</div>" .
        $this->inputSex .
        $this->inputAliases .
        $this->inputWikipedia .
        $this->inputLinks .
      "{$this->form->close()}</div>"
    ;
  }

  /**
   * @inheritdoc
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  protected function valid() {
    $person            = new FullPerson();
    $person->aliases   = $this->inputAliases->value;
    $person->biography = $this->inputBiography->value;
    $person->birthDate = $this->inputBirthDate->value;
    $person->bornName  = $this->inputBornName->value;
    $person->deathDate = $this->inputDeathDate->value;
    $person->links     = $this->inputLinks->value;
    $person->name      = $this->inputName->value;
    $person->sex       = $this->inputSex->value;
    $person->wikipedia = $this->inputWikipedia->value;

    $person->create();

    $kernel->alerts .= new Alert(
      $this->intl->t(
        "If you want, you can {0}upload a photo{1} right away.",
        [ "<a href='{$person->displayPhoto->route}'>", "</a>" ]
      ),
      $this->intl->t("Person Created Successfully"),
      Alert::SEVERITY_SUCCESS
    );

    // Redirect to Show presentation or upload form, depending on the button clicked.
    if ($_POST["submit"] == $this->intl->t("Create Person")) {
      $route = $person->route;
    }
    else {
      $route = $person->displayPhoto->route;
    }

    throw new SeeOther($route);
  }

  /**
   * @inheritdoc
   */
  public function validate($errors) {
    // Guardian pattern.
    if ($this->checkErrors($errors) === true) {
      return $this;
    }

    $searchResult = null;
    if (!isset($_POST["confirmation"])) {
      // @todo: Search for persons with name and born name in aliases and other names.
    }

    if ($searchResult && !isset($_POST["confirmation"])) {
      $this->form->elements[] = new InputCheckbox("confirmation", $this->intl->t("I confirm that this person is new"));
      return $this;
    }

    // Save new person.
    $this->valid();
    return $this;
  }

}
