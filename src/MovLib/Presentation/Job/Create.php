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
namespace MovLib\Presentation\Job;

use \MovLib\Data\Job\Job;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\TextareaHTML;
use \MovLib\Partial\Sex;

/**
 * Defines the job create presentation.
 *
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/job/create
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/job/create
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
      ->initCreate(new Job($this->diContainerHTTP), $this->intl->tp("Jobs"))
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $form = (new Form($this->diContainerHTTP))
      ->addElement(new InputText($this->diContainerHTTP, "name", $this->intl->t("Unisex Name"), $this->entity->names[Sex::UNKNOWN], [
        "placeholder" => $this->intl->t("Enter the job’s unisex name."),
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new InputText($this->diContainerHTTP, "male-name", $this->intl->t("Male Name"), $this->entity->names[Sex::MALE], [
        "placeholder" => $this->intl->t("Enter the job’s male name."),
        "required"    => true,
      ]))
      ->addElement(new InputText($this->diContainerHTTP, "female-name", $this->intl->t("Female Name"), $this->entity->names[Sex::FEMALE], [
        "placeholder" => $this->intl->t("Enter the job’s female name."),
        "required"    => true,
      ]))
      ->addElement(new TextareaHTML($this->diContainerHTTP, "description", $this->intl->t("Description"), $this->entity->description, [
        "placeholder" => $this->intl->t("Describe the job."),
      ], [ "blockquote", "external", "headings", "lists", ]))
      ->addElement(new InputWikipedia($this->diContainerHTTP, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia, [
        "placeholder"         => $this->intl->t("Enter the job’s corresponding Wikipedia link."),
        "data-allow-external" => "true",
      ]))
      ->addAction($this->intl->t("Create"), [ "class" => "btn btn-large btn-success" ])
    ;

    if ($this->intl->languageCode !== $this->intl->defaultLanguageCode) {
      $defaultLanguageArg = [ "default_language" =>  $this->intl->getTranslations("languages")[$this->intl->defaultLanguageCode]->name];
      $form
        ->addElement(new InputText($this->diContainerHTTP, "default-name", $this->intl->t(
            "Unisex Name ({default_language})", $defaultLanguageArg
          ), $this->entity->defaultNames[Sex::UNKNOWN], [
          "#help-popup" => $this->intl->t("We always need this information in our main Language ({default_language}).", $defaultLanguageArg),
          "placeholder" => $this->intl->t("Enter the job’s unisex name."),
          "autofocus"   => true,
          "required"    => true,
        ]))
        ->addElement(new InputText($this->diContainerHTTP, "default-male-name", $this->intl->t(
            "Male Name ({default_language})", $defaultLanguageArg
          ), $this->entity->defaultNames[Sex::MALE], [
          "#help-popup" => $this->intl->t("We always need this information in our main Language ({default_language}).", $defaultLanguageArg),
          "placeholder" => $this->intl->t("Enter the job’s male name."),
          "required"    => true,
        ]))
        ->addElement(new InputText($this->diContainerHTTP, "default-female-name", $this->intl->t(
            "Female Name ({default_language})", $defaultLanguageArg
          ), $this->entity->defaultNames[Sex::FEMALE], [
          "#help-popup" => $this->intl->t("We always need this information in our main Language ({default_language}).", $defaultLanguageArg),
          "placeholder" => $this->intl->t("Enter the job’s female name."),
          "required"    => true,
        ]))
        ->init([ $this, "valid" ])
      ;
      return
        $form->open() .
        "<div class='r'>" .
          "<div class='s s5'>{$form->elements["default-name"]}</div>" .
          "<div class='s s5'>{$form->elements["name"]}</div>" .
        "</div>" .
        "<div class='r'>" .
          "<div class='s s5'>{$form->elements["default-male-name"]}</div>" .
          "<div class='s s5'>{$form->elements["male-name"]}</div>" .
        "</div>" .
        "<div class='r'>" .
          "<div class='s s5'>{$form->elements["default-female-name"]}</div>" .
          "<div class='s s5'>{$form->elements["female-name"]}</div>" .
        "</div>" .

        $form->elements["description"] .
        $form->elements["wikipedia"] .
        $form->close()
      ;
    }
    else {
      return $form->init([ $this, "valid" ]);
    }
  }

}
