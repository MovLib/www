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
namespace MovLib\Presentation\Event;

use \MovLib\Data\Award\AwardSet;
use \MovLib\Data\Event\Event;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputDateSeparate;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\Select;
use \MovLib\Partial\FormElement\TextareaHTML;
use \MovLib\Partial\FormElement\TextareaLineArray;
use \MovLib\Partial\FormElement\TextareaLineURLArray;

/**
 * Allows creating a new event.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Create extends \MovLib\Presentation\AbstractCreatePresenter {
  use \MovLib\Presentation\Event\EventTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this
      ->initPage($this->intl->t("Create"))
      ->initCreate(new Event($this->diContainerHTTP), $this->intl->tp(-1, "Events", "Event"))
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $awardOptions = (new AwardSet($this->diContainerHTTP))->loadSelectOptions();
    $form = (new Form($this->diContainerHTTP))
      ->addElement(new Select($this->diContainerHTTP, "award", $this->intl->t("Award"), $awardOptions, $this->entity->award->id, [
        "placeholder" => $this->intl->t("Select the event’s Award."),
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new InputText($this->diContainerHTTP, "name", $this->intl->t("Name"), $this->entity->name, [
        "placeholder" => $this->intl->t("Enter the event’s name."),
        "required"    => true,
      ]))
      ->addElement(new TextareaLineArray($this->diContainerHTTP, "aliases", $this->intl->t("Alternative Names (line by line)"), $this->entity->aliases, [
        "placeholder" => $this->intl->t("Enter the event’s alternative names here, line by line."),
      ]))
      ->addElement(new InputDateSeparate($this->diContainerHTTP, "start-date", $this->intl->t("Start Date"), $this->entity->startDate, [
        "required"    => true,
      ]))
      ->addElement(new InputDateSeparate($this->diContainerHTTP, "end-date", $this->intl->t("End Date"), $this->entity->endDate))
      ->addElement(new TextareaHTML($this->diContainerHTTP, "description", $this->intl->t("Description"), $this->entity->description, [
        "placeholder" => $this->intl->t("Describe the event."),
      ], [ "blockquote", "external", "headings", "lists", ]))
      ->addElement(new InputWikipedia($this->diContainerHTTP, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia, [
        "placeholder"         => "http://{$this->intl->languageCode}.wikipedia.org/..",
        "data-allow-external" => "true",
      ]))
      ->addElement(new TextareaLineURLArray($this->diContainerHTTP, "links", $this->intl->t("Weblinks (line by line)"), $this->entity->links, [
        "placeholder" => $this->intl->t("Enter the event’s related weblinks, line by line."),
      ]))
      ->addAction($this->intl->t("Create"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "valid" ])
    ;
    return
      $form->open() .
      $form->elements["award"] .
      $form->elements["name"] .
      $form->elements["aliases"] .
      "<div class='r'><div class='s s5'>{$form->elements["start-date"]}</div><div class='s s5'>{$form->elements["end-date"]}</div></div>" .
      $form->elements["description"] .
      $form->elements["wikipedia"] .
      $form->elements["links"] .
      $form->close()
    ;
  }

}
