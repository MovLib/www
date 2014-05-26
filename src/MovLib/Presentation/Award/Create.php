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
namespace MovLib\Presentation\Award;

use \MovLib\Data\Award\Award;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\TextareaHTMLExtended;
use \MovLib\Partial\FormElement\TextareaLineArray;
use \MovLib\Partial\FormElement\TextareaLineURLArray;

/**
 * Allows creating of an new award.
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
      ->initCreate(new Award($this->container), $this->intl->t("Awards"))
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return (new Form($this->container))
      ->addElement(new InputText($this->container, "name", $this->intl->t("Name"), $this->entity->name, [
        "placeholder" => $this->intl->t("Enter the award’s name."),
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new TextareaLineArray($this->container, "aliases", $this->intl->t("Alternative Names (line by line)"), $this->entity->aliases, [
        "placeholder" => $this->intl->t("Enter the award’s alternative names here, line by line."),
      ]))
      ->addElement(new TextareaHTMLExtended($this->container, "description", $this->intl->t("Description"), $this->entity->description, [
        "data-allow-external" => "true",
          "placeholder"         => $this->intl->t("Describe the award."),
      ]))
      ->addElement(new InputWikipedia($this->container, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia, [
        "placeholder"         => "http://{$this->intl->languageCode}.wikipedia.org/..",
        "data-allow-external" => "true",
      ]))
      ->addElement(new TextareaLineURLArray($this->container, "links", $this->intl->t("Weblinks (line by line)"), $this->entity->links, [
        "placeholder" => $this->intl->t("Enter the award’s related weblinks, line by line."),
      ]))
      ->addAction($this->intl->t("Create"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "valid" ])
    ;
  }

}
