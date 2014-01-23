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
use \MovLib\Presentation\Partial\FormElement\InputCheckbox;
use \MovLib\Presentation\Partial\FormElement\InputDateSeparate;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
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
class Create extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person's aliases textarea input element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputLinesText
   */
  protected $aliases;

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
   * Checkbox to confirm that this person is new, in case of similar existing persons.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputCheckbox
   */
  protected $confirmation;

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

  /**
   * The person's localized Wikipedia URL input element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputURL
   */
  protected $wikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person create presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n, $kernel;
    $kernel->stylesheets[] = "person";
    $this->initBreadcrumb([ [ $i18n->rp("/persons"), $i18n->t("Persons") ] ]);
    $this->initPage($i18n->t("Create Person"));

    $this->name = new InputText("name", $i18n->t("Name"), [ "placeholder" => $i18n->t("Enter the person's name"), "required" => "required" ]);

    $this->bornName = new InputText("born-name", $i18n->t("Born as"), [ "placeholder" => $i18n->t("Enter the person's birth name") ]);

    $now = date("Y-m-d");

    $this->birthDate = new InputDateSeparate("birthdate", $i18n->t("Date of Birth"), [ "class" => "s s6", "min" => "1800-01-01", "max" => $now ], [ "class" => "s s2" ]);

    $this->deathDate = new InputDateSeparate("deathdate", $i18n->t("Date of Death"), [ "class" => "s s6", "min" => "1800-01-01", "max" => $now ], [ "class" => "s s2" ]);

    $this->sex = new RadioGroup("sex", $i18n->t("Sex"), [
        2 => $i18n->t("Female"),
        1 => $i18n->t("Male"),
        0 => $i18n->t("Unknown"),
      ], 0
    );

    $this->wikipedia = new InputURL("wikipedia", $i18n->t("Wikipedia URL"), [ "data-allow-external" => true ]);

    $this->aliases = new \MovLib\Presentation\Partial\FormElement\InputLinesText("aliases", $i18n->t("Additional Names"), [ "placeholder" => $i18n->t("Please supply one name per line") ]);

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
      $this->wikipedia,
      $this->aliases,
      $this->biography,
    ]);

    $this->form->actionElements[] = new InputSubmit($i18n->t("Create Person"), [ "class" => "btn btn-large btn-success", "id" => "submit-create" ]);
    $this->form->actionElements[] = new InputSubmit($i18n->t("Create and Upload Image"), [ "class" => "btn btn-large btn-success", "id" => "submit-upload" ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getContent() {
    // @todo: Continue with form building and date styling.
    return
      "<div class='c'>{$this->form->open()}" .
        $this->name .
        "<div class='r'>{$this->birthDate}{$this->deathDate}</div>" .
      "{$this->form->close()}</div>"
    ;
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

    $person            = new Full();
    $person->aliases   = $this->aliases->value;
    $person->biography = $this->biography->value;
    $person->birthDate = $this->birthDate->value;
    $person->bornName  = $this->bornName->value;
    $person->deathDate = $this->deathDate->value;
    $person->name      = $this->name->value;
    $person->sex       = $this->sex->value;
    $person->wikipedia = $this->wikipedia->value;

    $person->create();

    $kernel->alerts .= new Alert(
      $i18n->t(
        "If you want, you can {0}upload a photo{1} right away.",
        [ "<a href='{$person->displayPhoto->route}'>", "</a>" ]
      ),
      $i18n->t("Person Created Successfully"),
      Alert::SEVERITY_SUCCESS
    );

    // Redirect to Show presentation or upload form, depending on the button clicked.
    if ($_POST["submit"] == $i18n->t("Create Person")) {
      $route = $person->route;
    }
    else {
      $route = $person->displayPhoto->route;
    }

    throw new SeeOther($route);
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  public function validate($errors) {
    global $i18n;

    // Guardian pattern.
    if ($this->checkErrors($errors) === true) {
      return $this;
    }

    $searchResult = null;
    if (!isset($_POST["confirmation"])) {
      // @todo: Search for persons with name and born name in aliases and other names.
    }

    if ($searchResult && !isset($_POST["confirmation"])) {
      $this->form->elements[] = new InputCheckbox("confirmation", $i18n->t("I confirm that this person is new"));
      return $this;
    }

    // Save new person.
    $this->valid();
    return $this;
  }

}
