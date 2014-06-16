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
namespace MovLib\Presentation\Award\Category;

use \MovLib\Data\Award\AwardSet;
use \MovLib\Data\Award\Category;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputInteger;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\Select;
use \MovLib\Partial\FormElement\TextareaHTMLExtended;

/**
 * Allows editing of a award category's information.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Edit extends \MovLib\Presentation\AbstractEditPresenter {
  use \MovLib\Presentation\Award\Category\CategoryTrait;

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Edit";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Category($this->container, $_SERVER["CATEGORY_ID"]);
    $pageTitle    = $this->intl->t("Edit {0}", [ $this->entity->lemma ]);
    $this->initPage($pageTitle, $pageTitle, $this->intl->t("Edit"));
    $this->breadcrumb->addCrumbs([
      [ $this->entity->award->set->route, $this->intl->t("Awards") ],
      [ $this->entity->award->route, $this->entity->award->name ],
    ]);
    $this->initEdit($this->entity, $this->intl->t("Categories"), $this->getSidebarItems());
    $this->initLanguageLinks("{$this->entity->route->route}/edit", $this->entity->route->args);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $awardOptions = (new AwardSet($this->container))->loadSelectOptions();
    $form = (new Form($this->container))
      ->addElement(new Select($this->container, "award", $this->intl->t("Award"), $awardOptions, $this->entity->award->id, [
        "placeholder" => $this->intl->t("Select the category’s Award."),
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new InputText($this->container, "name", $this->intl->t("Name"), $this->entity->name, [
        "placeholder" => $this->intl->t("Enter the category’s name."),
        "required"    => true,
      ]))
      ->addElement(new InputInteger($this->container, "first-year", $this->intl->t("First Year"), $this->entity->firstYear->year, [
        "placeholder" => $this->intl->t("yyyy"),
        "required"    => true,
        "min"         => 1000,
        "max"         => 9999
      ]))
      ->addElement(new InputInteger($this->container, "last-year", $this->intl->t("Last Year"), $this->entity->lastYear->year, [
        "placeholder" => $this->intl->t("yyyy"),
        "min"         => 1000,
        "max"         => 9999
      ]))
      ->addElement(new TextareaHTMLExtended($this->container, "description", $this->intl->t("Description"), $this->entity->description, [
        "data-allow-external" => "true",
        "placeholder"         => $this->intl->t("Describe the category."),
      ]))
      ->addElement(new InputWikipedia($this->container, "wikipedia", $this->intl->t("Wikipedia"), $this->entity->wikipedia, [
        "placeholder"         => "http://{$this->intl->code}.wikipedia.org/…",
        "data-allow-external" => "true",
      ]))
      ->addAction($this->intl->t("Update"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "valid" ])
    ;
    return
      $form->open() .
      $form->elements["award"] .
      $form->elements["name"] .
      "<div class='r'><div class='s s5'>{$form->elements["first-year"]}</div><div class='s s5'>{$form->elements["last-year"]}</div></div>" .
      $form->elements["description"] .
      $form->elements["wikipedia"] .
      $form->close()
    ;
  }

}
