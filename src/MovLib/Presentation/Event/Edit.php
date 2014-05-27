<?php

/*!
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
namespace MovLib\Presentation\Event;

use \MovLib\Data\Award\Award;
use \MovLib\Data\Award\AwardSet;
use \MovLib\Data\Event\Event;
use \MovLib\Core\Revision\CommitConflictException;
use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputDateSeparate;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\Select;
use \MovLib\Partial\FormElement\TextareaHTMLExtended;
use \MovLib\Partial\FormElement\TextareaLineArray;
use \MovLib\Partial\FormElement\TextareaLineURLArray;

/**
 * Allows editing of a event's information.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Edit extends \MovLib\Presentation\AbstractEditPresenter {
  use \MovLib\Presentation\Event\EventTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Event($this->container, $_SERVER["EVENT_ID"]);
    $this->entity->award = new Award($this->container, $this->entity->award);
    $pageTitle    = $this->intl->t("Edit {0}", [ $this->entity->name ]);
    return $this
      ->initPage($pageTitle, $pageTitle, $this->intl->t("Edit"))
      ->initEdit($this->entity, $this->intl->t("Events"), $this->getSidebarItems())
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $awardOptions = (new AwardSet($this->container))->loadSelectOptions();
    $form = (new Form($this->container))
      ->addHiddenElement("revision_id", $this->entity->changed->formatInteger())
      ->addElement(new Select($this->container, "award", $this->intl->t("Award"), $awardOptions, $this->entity->award->id, [
        "placeholder" => $this->intl->t("Select the event’s Award."),
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new InputText($this->container, "name", $this->intl->t("Name"), $this->entity->name, [
        "placeholder" => $this->intl->t("Enter the event’s name."),
        "required"    => true,
      ]))
      ->addElement(new TextareaLineArray($this->container, "aliases", $this->intl->t("Alternative Names (line by line)"), $this->entity->aliases, [
        "placeholder" => $this->intl->t("Enter the event’s alternative names here, line by line."),
      ]))
      ->addElement(new InputDateSeparate($this->container, "start-date", $this->intl->t("Start Date"), $this->entity->startDate, [
        "required"    => true,
      ]))
      ->addElement(new InputDateSeparate($this->container, "end-date", $this->intl->t("End Date"), $this->entity->endDate))
      ->addElement(new TextareaHTMLExtended($this->container, "description", $this->intl->t("Description"), $this->entity->description, [
        "data-allow-external" => "true",
        "placeholder"         => $this->intl->t("Describe the event."),
      ]))
      ->addElement(new InputWikipedia($this->container, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia, [
        "placeholder"         => "http://{$this->intl->languageCode}.wikipedia.org/…",
        "data-allow-external" => "true",
      ]))
      ->addElement(new TextareaLineURLArray($this->container, "links", $this->intl->t("Weblinks (line by line)"), $this->entity->links, [
        "placeholder" => $this->intl->t("Enter the event’s related weblinks, line by line."),
      ]))
      ->addAction($this->intl->t("Update"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "submit" ])
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

  /**
   * Submit callback for the event edit form.
   *
   * @throws \MovLib\Exception\RedirectException\SeeOtherException
   *   Always redirects the user back to the edited event.
   */
  public function submit() {
    try {
      $this->entity->commit($this->session->userId, $this->request->dateTime, $this->request->filterInput(INPUT_POST, "revision_id", FILTER_VALIDATE_INT));
      $this->alertSuccess($this->intl->t("Successfully Updated"));
      throw new SeeOtherException($this->entity->route);
    }
    catch (\BadMethodCallException $e) {
      $this->alertError(
        $this->intl->t("Validation Error"),
        $this->intl->t("Seems like you haven’t changed anything, please only submit forms with changes.")
      );
    }
    catch (CommitConflictException $e) {
      $this->alertError(
        $this->intl->t("Conflicting Changes"),
        "<p>{$this->intl->t(
          "Someone else has already submitted changes before you. Copy any unsaved work in the form below and then {0}reload this page{1}.",
          [ "<a href='{$this->request->uri}'>", "</a>" ]
        )}</p>"
      );
    }
  }

}