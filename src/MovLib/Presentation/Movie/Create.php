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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Movie\Movie;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputInteger;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\TextareaHTMLExtended;
use \MovLib\Partial\Language;

/**
 * Allows creating a movie.
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
      ->initPage($this->intl->t("Create new movie"))
      ->initCreate(new Movie($this->diContainerHTTP), $this->intl->t("Movies"))
    ;
  }

  /**
   * {@inheritdoc}
   */
   public function getContent() {
    return (new Form($this->diContainerHTTP))
      ->addElement(new InputText($this->diContainerHTTP, "original-title", $this->intl->t("Original Title"), $this->entity->originalTitle, [
        "#prefix"   => "<div class='r'><div class='s s7'>",
        "#suffix"   => "</div>",
        "autofocus" => true,
        "required"  => true,
      ]))
      ->addElement((new Language($this->diContainerHTTP))->getSelectFormElement($this->entity->originalTitleLanguageCode, [
        "#prefix"     => "<div class='s s3'>",
        "#suffix"     => "</div></div>",
        "#help-popup" => $this->intl->t("The original title’s language."),
        "required"    => true,
      ]))
      ->addElement(new InputInteger($this->diContainerHTTP, "year", $this->intl->t("Release"), $this->entity->year->year, [
        "#field_suffix" => " {$this->intl->t("Year")}",
        "class"         => "s2",
        "min"           => 1890,
        "max"           => date("Y") + 5
      ]))
      ->addElement(new InputInteger($this->diContainerHTTP, "runtime", $this->intl->t("Approximate runtime"), $this->entity->runtime, [
        "#field_suffix" => " {$this->intl->t("Minutes")}",
        "class"         => "s2",
        "min"           => 0,
        "max"           => 279620, /* 16777215 seconds */
      ]))
      ->addElement(new TextareaHTMLExtended($this->diContainerHTTP, "synopsis", $this->intl->t("Synopsis"), $this->entity->synopsis))
      ->addElement(new InputWikipedia($this->diContainerHTTP, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia, [
        "placeholder"         => "http://{$this->intl->languageCode}.wikipedia.org/…",
        "data-allow-external" => "true",
      ]))
      ->addAction($this->intl->t("Create"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "submit" ])
    ;
  }

  /**
   * Submit callback for creation form.
   *
   * @return this
   */
  public function submit() {


    return $this;
  }

}
