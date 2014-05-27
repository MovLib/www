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
namespace MovLib\Presentation\Help;

use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\TextareaHTMLExtended;

/**
 * Allows creating of a new a help article.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractCreate extends \MovLib\Presentation\AbstractCreatePresenter {
  use \MovLib\Presentation\Help\HelpTrait;

  /**
   * @param \MovLib\Data\Help\Article $article
   *   An empty help article instance.
   * @param \MovLib\Data\Help\Category $category
   *   The category the article should belong to.
   * @param \MovLib\Data\Help\SubCategory $subCategory [optional]
   *   The sub category the article should belong to.
   */
  public function initHelpCreate(\MovLib\Data\Help\Article $article, \MovLib\Data\Help\Category $category, \MovLib\Data\Help\SubCategory $subCategory = null) {
    $this->session->checkAuthorization($this->intl->t(
      "You must be signed in to access this content. Please use the form below to sign in or {0}join {sitename}{1}.",
      [ "<a href='{$this->intl->r("/profile/join")}'>", "</a>", "sitename" => $this->config->sitename ]
    ));
    $this->entity              = $article;
    $this->entity->category    = $category;
    $this->entity->subCategory = $subCategory;

    $pageTitle    = $this->intl->t("Create Help Article");
    $this->initPage($pageTitle, $pageTitle, $this->intl->t("Create"));

    if (isset($this->entity->subCategory)) {
      $this->sidebarInit([
        [ $this->entity->subCategory->route, $this->entity->subCategory->title, [ "class" => "ico {$this->entity->subCategory->icon}"] ],
      ]);
      $this->initLanguageLinks("{$this->entity->subCategory->routeKey}/create", $this->entity->subCategory->routeArgs);
      $this->breadcrumb->addCrumbs([
        [ $this->intl->r("/help"), $this->intl->t("Help") ],
        [ $this->intl->r($this->entity->category->routeKey), $this->entity->category->title ],
        [ $this->intl->r($this->entity->subCategory->routeKey), $this->entity->subCategory->title ],
      ]);
    }
    else {
      $this->sidebarInit([
        [ $this->entity->category->route, $this->entity->category->title, [ "class" => "ico {$this->entity->category->icon}"] ],
      ]);
      $this->initLanguageLinks("{$this->entity->category->routeKey}/create", $this->entity->category->routeArgs);
      $this->breadcrumb->addCrumbs([
        [ $this->intl->r("/help"), $this->intl->t("Help") ],
        [ $this->intl->r($this->entity->category->routeKey), $this->entity->category->title ],
      ]);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $form = (new Form($this->container))
      ->addElement(new InputText($this->container, "title", $this->intl->t("Title"), $this->entity->title, [
        "placeholder" => $this->intl->t("Enter the help article’s title."),
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new TextareaHTMLExtended($this->container, "text", $this->intl->t("Text"), $this->entity->text, [
        "data-allow-external" => "true",
        "placeholder"         => $this->intl->t("The help article."),
        "required"            => true,
      ]))
      ->addAction($this->intl->t("Create"), [ "class" => "btn btn-large btn-success" ])
    ;
    if ($this->intl->languageCode !== $this->intl->defaultLanguageCode) {
      $defaultLanguageArg = [ "default_language" =>  $this->intl->getTranslations("languages")[$this->intl->defaultLanguageCode]->name];
      $form
        ->addElement(new InputText($this->container, "default-title", $this->intl->t("Title ({default_language})", $defaultLanguageArg), $this->entity->defaultTitle, [
          "#help-popup" => $this->intl->t("We always need this information in our main Language ({default_language}).", $defaultLanguageArg),
          "placeholder" => $this->intl->t("Enter the help article’s title."),
          "autofocus"   => true,
          "required"    => true,
        ]))
        ->init([ $this, "submit" ])
      ;
      return
        $form->open() .
        "<div class='r'>" .
          "<div class='s s5'>{$form->elements["default-title"]}</div>" .
          "<div class='s s5'>{$form->elements["title"]}</div>" .
        "</div>" .
        $form->elements["text"] .
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
    throw new SeeOtherException($this->entity->route);
  }

}
