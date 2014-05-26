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
namespace MovLib\Presentation\Genre;

use \MovLib\Data\Genre\Genre;
use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\TextareaHTMLExtended;

/**
 * Defines the genre create presentation.
 *
 * @property \MovLib\Data\Genre\Genre $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Create extends \MovLib\Presentation\AbstractCreatePresenter {

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this
      ->initPage($this->intl->t("Create"))
      ->initCreate(new Genre($this->container), $this->intl->t("Genres"))
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $nameLabel = $this->intl->t("Name");
    $form = (new Form($this->container))
      ->addElement(new InputText($this->container, "name", $nameLabel, $this->entity->name, [
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new TextareaHTMLExtended($this->container, "description", $this->intl->t("Description"), $this->entity->description, [
        "data-allow-external" => "true",
      ]))
      ->addElement(new InputWikipedia($this->container, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia))
      ->addAction($this->intl->t("Create"), [ "class" => "btn btn-large btn-success" ])
    ;

    if ($this->intl->languageCode !== $this->intl->defaultLanguageCode) {
      $nameLabel = $this->intl->t("{0} ({1})", [
        $nameLabel,
        $this->intl->getTranslations("languages")[$this->intl->defaultLanguageCode]->name,
      ]);
      $form
        ->addElement(new InputText($this->container, "default-name", $nameLabel, $this->entity->defaultName, [
          "autofocus"   => true,
          "required"    => true,
        ]))
        ->init([ $this, "submit" ])
      ;
      return
        $form->open() .
        "<div class='r'>" .
          "<div class='s s5'>{$form->elements["default-name"]}</div>" .
          "<div class='s s5'>{$form->elements["name"]}</div>" .
        "</div>" .
        $form->elements["description"] .
        $form->elements["wikipedia"] .
        $form->close()
      ;
    }
    else {
      return $form->init([ $this, "submit" ]);
    }
  }

  /**
   * Form submit callback.
   */
  public function submit() {
    $this->entity->create($this->session->userId, $this->request->dateTime);
    $this->alertSuccess($this->intl->t("Successfully Created"));
    throw new SeeOtherException($this->intl->r("/genre/{0}", $this->entity->id));
  }

}
