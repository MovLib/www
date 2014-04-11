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
use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\TextareaHTML;

/**
 * Defines the genre edit presentation.
 *
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/genre/{id}/edit
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/genre/{id}/edit
 *
 * @property \MovLib\Data\Genre\Genre $entity
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Edit extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity to present.
   *
   * @var \MovLib\Data\AbstractEntity
   */
  protected $entity;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Genre($this->diContainerHTTP, $_SERVER["GENRE_ID"]);
    $pageTitle    = $this->intl->t("Edit {0}", [ $this->entity->name ]);
    return $this
      ->initPage($pageTitle, $pageTitle, $this->intl->t("Edit"))
      ->sidebarInitToolbox($this->entity)
      ->initLanguageLinks("/{$this->entity->singularKey}/{0}/edit", $this->entity->id)
      ->breadcrumb->addCrumbs([
        [ $this->intl->rp("/genres"), $this->intl->t("Genres") ],
        [ $this->entity->route, $this->entity->name ]
      ])
    ;

  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return (new Form($this->diContainerHTTP))
      ->addElement(new InputText($this->diContainerHTTP, "name", $this->intl->t("Name"), $this->entity->name, [
        "#help-popup" => $this->intl->t("The name of the genre."),
        "placeholder" => $this->intl->t("Enter the genre’s name."),
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new InputWikipedia($this->diContainerHTTP, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia, [
        "#help-popup"         => $this->intl->t("Link to a corresponding Wikipedia Page."),
        "placeholder"         => $this->intl->t("Enter the genre’s corresponding Wikipedia link."),
        "data-allow-external" => "true",
      ]))
      ->addElement(new TextareaHTML($this->diContainerHTTP, "description", $this->intl->t("Description"), $this->entity->description, [
        "#help-popup" => $this->intl->t("Description of the genre."),
        "placeholder" => $this->intl->t("Describe the genre."),
      ], [ "blockquote", "external", "headings", "lists", ]))
      ->addAction($this->intl->t("Update"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "valid" ])
    ;
  }

  /**
   * Auto-validation of the form succeeded.
   *
   * @return this
   */
  public function valid() {
    $this->entity->commit();
    $this->alertSuccess($this->intl->t("The genre was updated successfully."));
    throw new SeeOtherException($this->entity->route);
  }
}
