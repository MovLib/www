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
 * Allows deleting a help article.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractEdit extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;
  use \MovLib\Presentation\Help\HelpTrait;


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
   * @param \MovLib\Data\Help\Article $article
   *   The help article to present.
   */
  public function initHelp(\MovLib\Data\Help\Article $article) {
    $this->entity = $article;
    $pageTitle    = $this->intl->t("Edit {0}", [ $this->entity->title ]);
    return $this
      ->initPage($pageTitle, $pageTitle, $this->intl->t("Edit"))
      ->sidebarInitToolbox($this->entity)
      ->initLanguageLinks("{$this->entity->routeKey}/edit", $this->entity->id)
      ->breadcrumb->addCrumbs($this->getArticleBreadCrumbs());
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return (new Form($this->diContainerHTTP))
      ->addElement(new InputText($this->diContainerHTTP, "title", $this->intl->t("Title"), $this->entity->title, [
        "placeholder" => $this->intl->t("Enter the help article’s title."),
        "autofocus"   => true,
        "required"    => true,
      ]))
      ->addElement(new TextareaHTMLExtended($this->diContainerHTTP, "text", $this->intl->t("Text"), $this->entity->text, [
        "data-allow-external" => "true",
        "placeholder"         => $this->intl->t("The help article."),
        "required"            => true,
      ]))
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
    $this->alertSuccess($this->intl->t("The {$this->entity->singularKey} was updated successfully."));
    throw new SeeOtherException($this->entity->route);
  }

}
