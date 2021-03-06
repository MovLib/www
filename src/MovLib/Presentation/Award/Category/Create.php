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
namespace MovLib\Presentation\Award\Category;

use \MovLib\Core\Routing\Route;
use \MovLib\Data\Award\Award;
use \MovLib\Data\Award\Category;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputInteger;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputWikipedia;
use \MovLib\Partial\FormElement\TextareaHTMLExtended;

/**
 * Allows creating a new award category.
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
    $category             = new Category($this->container);
    $category->award      = new Award($this->container, $_SERVER["AWARD_ID"]);
    $category->awardId    = $category->award->id;
    $category->route      = new Route($this->intl, "/award/{0}/category/create", [ $category->award->id ]);
    $category->set->route = new Route($this->intl, "/award/{0}/categories", [ $category->award->id ]);

    $this->initPage($this->intl->t("Create"), $this->intl->t("Create Category for {0}", [ $category->award->name ]));
    $this->breadcrumb->addCrumbs([
      [ $category->award->set->route, $this->intl->t("Awards") ],
      [ $category->award->route, $category->award->name ],
    ]);
    $this->initCreate($category, $this->intl->t("Categories"));
    $this->initLanguageLinks($this->entity->route->route, $category->award->id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $form = (new Form($this->container))
      ->addElement(new InputText($this->container, "name", $this->intl->t("Name"), $this->entity->name, [
        "placeholder" => $this->intl->t("Enter the category’s name."),
        "autofocus"   => true,
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
      ->addAction($this->intl->t("Create"), [ "class" => "btn btn-large btn-success" ])
    ;

    if ($this->intl->code !== $this->intl->defaultCode) {
      $defaultLanguageArg = [ "default_language" =>  $this->intl->getTranslations("languages")[$this->intl->defaultCode]->name];
      $form
        ->addElement(new InputText($this->container, "default-name", $this->intl->t(
            "Name ({default_language})", $defaultLanguageArg
          ), $this->entity->defaultName, [
          "#help-popup" => $this->intl->t("We always need this information in our main Language ({default_language}).", $defaultLanguageArg),
          "placeholder" => $this->intl->t("Enter the category’s name."),
          "required"    => true,
        ]))
        ->init([ $this, "valid" ])
      ;
      return
        $form->open() .
        "<div class='r'><div class='s s5'>{$form->elements["default-name"]}</div><div class='s s5'>{$form->elements["name"]}</div></div>" .
        "<div class='r'><div class='s s5'>{$form->elements["first-year"]}</div><div class='s s5'>{$form->elements["last-year"]}</div></div>" .
        $form->elements["description"] .
        $form->elements["wikipedia"] .
        $form->close()
      ;
    }
    else {
      $form->init([ $this, "valid" ]);
      return
        $form->open() .
        $form->elements["name"] .
        "<div class='r'><div class='s s5'>{$form->elements["first-year"]}</div><div class='s s5'>{$form->elements["last-year"]}</div></div>" .
        $form->elements["description"] .
        $form->elements["wikipedia"] .
        $form->close()
      ;
    }
  }

}
