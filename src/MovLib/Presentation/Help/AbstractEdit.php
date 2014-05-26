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

use \MovLib\Data\Revision\RevisionCommitConflictException;
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
abstract class AbstractEdit extends \MovLib\Presentation\AbstractEditPresenter {
  use \MovLib\Presentation\Help\HelpTrait;

  /**
   * @param \MovLib\Data\Help\Article $article
   *   An empty help article instance.
   * @param string $additionalSidebarItems [optional]
   *   Additional items for the sidebar.
   */
  public function initHelpEdit(\MovLib\Data\Help\Article $article, $additionalSidebarItems = []) {
    $this->session->checkAuthorization($this->intl->t(
      "You must be signed in to access this content. Please use the form below to sign in or {0}join {sitename}{1}.",
      [ "<a href='{$this->intl->r("/profile/join")}'>", "</a>", "sitename" => $this->config->sitename ]
    ));
    $this->entity = $article;
    $pageTitle    = $this->intl->t("Edit {0}", [ $this->entity->title ]);
    return $this
      ->initPage($pageTitle, $pageTitle, $this->intl->t("Edit"))
      ->sidebarInitToolbox($this->entity, $additionalSidebarItems)
      ->initLanguageLinks("{$this->entity->routeKey}/edit", $this->entity->id)
      ->breadcrumb->addCrumbs($this->getArticleBreadCrumbs());
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return (new Form($this->diContainerHTTP))
      ->addHiddenElement("revision_id", $this->entity->changed)
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
      ->init([ $this, "submit" ])
    ;
  }

  /**
   * Submit callback for the help article edit form.
   *
   * @throws \MovLib\Exception\RedirectException\SeeOtherException
   *   Always redirects the user back to the edited help article.
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
