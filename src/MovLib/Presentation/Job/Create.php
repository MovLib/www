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
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\TextareaHTML;

/**
 * Defines the job create presentation.
 *
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/job/create
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/job/create
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Create extends \MovLib\Presentation\AbstractPresenter {
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
   * Instantiate new job create presentation.
   */
  public function init() {
    $this->entity = new Job($this->diContainerHTTP);
    $this->initPage($this->intl->t("Create Job"));
    $this->initBreadcrumb([ [ $this->intl->r("/jobs"), $this->intl->t("Jobs") ] ]);
    $this->breadcrumbTitle = $this->intl->t("Create");
    $this->initLanguageLinks("/job/create");
  }

 /**
   * {@inheritdoc}
   */
   public function getContent() {
    return (new Form($this->diContainerHTTP))
      ->addElement(new InputText($this->diContainerHTTP, "name", $this->intl->t("Unisex Name"), $this->entity->name, [
        "#help-popup" => $this->intl->t("The unisex name of the job."),
        "placeholder" => $this->intl->t("Enter the job’s unisex name."),
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new InputText($this->diContainerHTTP, "maleName", $this->intl->t("Male Name"), $this->entity->maleName, [
        "#help-popup" => $this->intl->t("The male name of the job."),
        "placeholder" => $this->intl->t("Enter the job’s male name."),
        "required"    => true,
      ]))
      ->addElement(new InputText($this->diContainerHTTP, "femaleName", $this->intl->t("Female Name"), $this->entity->femaleName, [
        "#help-popup" => $this->intl->t("The female name of the job."),
        "placeholder" => $this->intl->t("Enter the job’s female name."),
        "required"    => true,
      ]))
      ->addElement(new InputWikipedia($this->diContainerHTTP, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia, [
        "#help-popup"         => $this->intl->t("Link to a corresponding Wikipedia Page."),
        "placeholder"         => $this->intl->t("Enter the job’s corresponding Wikipedia link."),
        "data-allow-external" => "true",
      ]))
      ->addElement(new TextareaHTML($this->diContainerHTTP, "description", $this->intl->t("Description"), $this->entity->description, [
        "#help-popup" => $this->intl->t("Description of the job."),
        "placeholder" => $this->intl->t("Describe the job."),
      ], [ "blockquote", "external", "headings", "lists", ]))
      ->addAction($this->intl->t("Create"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "valid" ])
    ;
  }

  /**
   * Auto-validation of the form succeeded.
   *
   * @return this
   */
  public function valid() {
    $this->entity->create();
    $this->alertSuccess($this->intl->t("The job was created successfully."));
    throw new SeeOtherException($this->entity->route);
  }
}
