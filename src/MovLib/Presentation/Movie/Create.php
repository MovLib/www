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
use \MovLib\Exception\RedirectException\SeeOtherException;

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
      ->initPage($this->intl->t("Create New Movie"))
      ->initCreate(new Movie($this->container), $this->intl->t("Movies"))
    ;
  }

  /**
   * {@inheritdoc}
   */
   public function getContent() {
    return (new Form($this->container))
      ->addElement(new InputText($this->container, "original-title", $this->intl->t("Original Title"), $this->entity->originalTitle, [
        "#prefix"   => "<div class='r'><div class='s s7'>",
        "#suffix"   => "</div>",
        "autofocus" => true,
        "required"  => true,
      ]))
      ->addElement((new Language($this->container))->getSelectFormElement($this->entity->originalTitleLanguageCode, [
        "#prefix"     => "<div class='s s3'>",
        "#suffix"     => "</div></div>",
        "#help-popup" => $this->intl->t("The original title’s language."),
        "required"    => true,
      ]))
      ->addElement(new InputInteger($this->container, "year", $this->intl->t("Release Year"), $this->entity->year, [
        "#prefix"   => "<div class='r'><div class='s s3'>",
        "#suffix"   => "</div>",
        "min"   => 1890,
        "max"   => date("Y") + 5
      ]))
      ->addElement(new InputInteger($this->container, "runtime", $this->intl->t("Runtime"), $this->entity->runtime, [
        "#prefix"     => "<div class='s s7'>",
        "#suffix"     => "</div></div>",
        "#field_suffix" => " {$this->intl->t("Seconds")}",
        "min"           => 0,
        "max"           => 16777215,
      ]))
      ->addElement(new TextareaHTMLExtended($this->container, "synopsis", $this->intl->t("Synopsis"), $this->entity->synopsis))
      ->addElement(new InputWikipedia($this->container, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia, [
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
    $this->entity->create($this->session->userId, $this->request->dateTime);
    $this->alertSuccess($this->intl->t("Successfully Created"));
    throw new SeeOtherException($this->entity->route);
  }

}
