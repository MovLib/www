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
namespace MovLib\Presentation\Genre;

use \MovLib\Data\Genre\Genre;
use \MovLib\Data\Revision\RevisionCommitConflictException;
use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\TextareaHTMLExtended;

/**
 * Defines the genre edit presentation.
 *
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/genre/{id}/edit
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/genre/{id}/edit
 *
 * @property \MovLib\Data\Genre\Genre $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Edit extends \MovLib\Presentation\AbstractEditPresenter {

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Genre($this->diContainerHTTP, $_SERVER["GENRE_ID"]);
    return $this
      ->initPage($this->intl->t("Edit {0}", [ $this->entity->name ]), null, $this->intl->t("Edit"))
      ->initEdit($this->entity, $this->intl->t("Genres"))
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return (new Form($this->diContainerHTTP))
      ->addHiddenElement("revision_id", $this->entity->changed)
      ->addElement(new InputText($this->diContainerHTTP, "name", $this->intl->t("Name"), $this->entity->name, [
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new TextareaHTMLExtended($this->diContainerHTTP, "description", $this->intl->t("Description"), $this->entity->description, [
        "data-allow-external" => "true",
      ]))
      ->addElement(new InputWikipedia($this->diContainerHTTP, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia))
      ->addAction($this->intl->t("Update"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "submit" ])
    ;
  }

  /**
   * Submit callback for the genre edit form.
   *
   * @throws \MovLib\Exception\RedirectException\SeeOtherException
   *   Always redirects the user back to the edited genre.
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
    catch (RevisionCommitConflictException $e) {
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
