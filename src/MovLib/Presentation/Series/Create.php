<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Series;

use \MovLib\Data\Series\Series;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputInteger;
use \MovLib\Partial\FormElement\Select;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\TextareaHTML;
use \MovLib\Partial\Language;

/**
 * Allows creating a series.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Create extends \MovLib\Presentation\AbstractCreatePresenter {
  use \MovLib\Presentation\Series\SeriesTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this
      ->initPage($this->intl->t("Create"))
      ->initCreate(new Series($this->diContainerHTTP), $this->intl->tp("Series"))
    ;
  }

  /**
   * {@inheritdoc}
   */
   public function getContent() {
    $form = (new Form($this->diContainerHTTP))
      ->addElement(new InputText($this->diContainerHTTP, "original-title", $this->intl->t("Original Title"), $this->entity->originalTitle, [
        "placeholder" => $this->intl->t("The original title of the series."),
        "required"    => true,
        "autofocus"   => true,
      ]))
      ->addElement((new Language($this->diContainerHTTP))->getSelectFormElement($this->entity->originalTitleLanguageCode, [
        "required"    => true,
      ], "original-title-language-code", $this->intl->t("Original Title Language")))

      ->addElement(new InputInteger($this->diContainerHTTP, "start-year", $this->intl->t("Start Year"), $this->entity->startYear->year, [
        "placeholder" => $this->intl->t("yyyy"),
        "required"    => true,
        "min"         => 1000,
        "max"         => 9999
      ]))
      ->addElement(new InputInteger($this->diContainerHTTP, "end-year", $this->intl->t("End Year"), $this->entity->endYear->year, [
        "placeholder" => $this->intl->t("yyyy"),
        "min"         => 1000,
        "max"         => 9999
      ]))
      ->addElement(new Select($this->diContainerHTTP, "status", $this->intl->t("Status"), $this->getStatusArray(), $this->entity->status, [
        "required"    => true,
      ]))
      ->addElement(new TextareaHTML($this->diContainerHTTP, "synopsis", $this->intl->t("Synopsis"), $this->entity->synopsis, [
        "placeholder" => $this->intl->t("Write a synopsis."),
      ], [ "blockquote", "external", "headings", "lists", ]))
      ->addElement(new InputWikipedia($this->diContainerHTTP, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia, [
        "placeholder"         => "http://{$this->intl->languageCode}.wikipedia.org/...",
        "data-allow-external" => "true",
      ]))
      ->addAction($this->intl->t("Create"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "valid" ])
    ;
    return
      $form->open() .
      "<div class='r'>" .
        "<div class='s s7'>{$form->elements["original-title"]}</div>" .
        "<div class='s s3'>{$form->elements["original-title-language-code"]}</div>" .
      "</div>" .
      "<div class='r'>" .
        "<div class='s s2'>{$form->elements["start-year"]}</div>" .
        "<div class='s s2'>{$form->elements["end-year"]}</div>" .
        "<div class='s s3 o3'>{$form->elements["status"]}</div>" .
      "</div>" .
      $form->elements["synopsis"] .
      $form->elements["wikipedia"] .
      $form->close()
    ;
  }

}
