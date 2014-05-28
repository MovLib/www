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
use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\TextareaHTMLExtended;
use \MovLib\Partial\Sex;

/**
 * Defines the job create presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Create extends \MovLib\Presentation\AbstractCreatePresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Create";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this
      ->initPage($this->intl->t("Create"))
      ->initCreate(new Job($this->container), $this->intl->t("Jobs"))
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $translations = $this->intl->languageCode != $this->intl->defaultLanguageCode;
    $attributes   = [ "aria-describedby" => [ "job-title-description" ], "required" => true ];
    $form         = new Form($this->container);
    $sex          = new Sex();

    $sex->addInputTextElements($this->container, $form, "title", $this->entity->titles, $attributes);
    if ($translations) {
      $sex->addInputTextElements($this->container, $form, "title-{$this->intl->defaultLanguageCode}", $this->entity->defaultTitles, $attributes, $this->intl->t(
        "{0} ({1})",
        [ 1 => $this->intl->getTranslations("languages")[$this->intl->defaultLanguageCode]->name ]
      ));
    }

    $form
      ->addElement(new TextareaHTMLExtended($this->container, "description", $this->intl->t("Description"), $this->entity->description))
      ->addElement(new InputWikipedia($this->container, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia))
      ->addAction($this->intl->t("Create"), [ "class" => "btn btn-large btn-success" ])
    ;

    $jobTitleDescription = "<p>{$this->intl->t(
      "The unisex job title is used in contexts where no sex is available, like companies. For instance, if you’d " .
      "create the job producer, the unisex name would be production. Male and female title may be the same, we still " .
      "require you to enter the same title into both fields. You acknowledge that the title is actually the same for " .
      "both by doing so."
    )}</p>";
    $form->init([ $this, "submit" ]);

    if ($translations) {
      $jobTitleDescription .= "<p>{$this->intl->t(
        "You’re required to enter the job titles in the default language because we need them as fallback for other " .
        "languages that might be missing the translation."
      )}</p>";
      $formContent = null;
      foreach ([ Sex::UNKNOWN, Sex::MALE, Sex::FEMALE ] as $code) {
        $formContent .= "<div class='r'>";
        foreach ([ "", "-{$this->intl->defaultLanguageCode}" ] as $suffix) {
          $formContent .= "<div class='s s5'>{$form->elements["title{$suffix}-{$code}"]}</div>";
        }
        $formContent .= "</div>";
      }
      $form = "{$form->open()}{$formContent}{$form->elements["description"]}{$form->elements["wikipedia"]}{$form->close()}";
    }

    return "{$this->calloutInfo($jobTitleDescription, null, [ "id" => "job-title-description" ])}{$form}";
  }

  /**
   * Form submit callback.
   */
  public function submit() {
    $this->entity->create($this->session->userId, $this->request->dateTime);
    $this->alertSuccess($this->intl->t("Successfully Created"));
    throw new SeeOtherException($this->intl->r("/job/{0}", $this->entity->id));
  }

}
