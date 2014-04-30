<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2014-present {@link https://movlib.org/ MovLib}.
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
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputDateSeparate;
use \MovLib\Partial\FormElement\InputSex;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\TextareaHTMLExtended;
use \MovLib\Partial\FormElement\TextareaLineURLArray;

/**
 * Allows creating a new person.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Create extends \MovLib\Presentation\AbstractCreatePresenter {

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this
      ->initPage($this->intl->t("Create"))
      ->initCreate(new Person($this->diContainerHTTP), $this->intl->t("Persons"))
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $form = (new Form($this->diContainerHTTP))
      ->addElement(new InputText($this->diContainerHTTP, "name", $this->intl->t("Name"), $this->entity->name, [
        "placeholder" => $this->intl->t("Enter the persons’s name."),
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new InputSex($this->diContainerHTTP, "sex", $this->intl->t("Sex"), $this->entity->sex))
      ->addElement(new InputText($this->diContainerHTTP, "born-name", $this->intl->t("Birth Name"), $this->entity->bornName, [
        "placeholder" => $this->intl->t("Enter the persons’s birth name."),
      ]))
      ->addElement(new InputDateSeparate($this->diContainerHTTP, "birth-date", $this->intl->t("Birthdate"), $this->entity->birthDate, [
        "required"    => true,
      ]))
      ->addElement(new InputDateSeparate($this->diContainerHTTP, "death-date", $this->intl->t("Deathdate"), $this->entity->deathDate))
      ->addElement(new TextareaHTMLExtended($this->diContainerHTTP, "biography", $this->intl->t("Biography"), $this->entity->biography, [
        "data-allow-external" => "true",
        "placeholder"         => $this->intl->t("Describe the person."),
      ]))
      ->addElement(new InputWikipedia($this->diContainerHTTP, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia, [
        "placeholder"         => "http://{$this->intl->languageCode}.wikipedia.org/…",
        "data-allow-external" => "true",
      ]))
      ->addElement(new TextareaLineURLArray($this->diContainerHTTP, "links", $this->intl->t("Weblinks (line by line)"), $this->entity->links, [
        "placeholder" => $this->intl->t("Enter the persons’s related weblinks, line by line."),
      ]))
      ->addAction($this->intl->t("Update"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "valid" ])
    ;
    return
      $form->open() .
      $form->elements["name"] .
      $form->elements["born-name"] .
      $form->elements["sex"] .
      "<div class='r'><div class='s s5'>{$form->elements["birth-date"]}</div><div class='s s5'>{$form->elements["death-date"]}</div></div>" .
      $form->elements["biography"] .
      $form->elements["wikipedia"] .
      $form->elements["links"] .
      $form->close()
    ;
  }

}
