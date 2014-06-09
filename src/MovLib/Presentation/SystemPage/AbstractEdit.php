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
namespace MovLib\Presentation\SystemPage;

use \MovLib\Core\Revision\CommitConflictException;
use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\TextareaHTMLRaw;

/**
 * Allows administrators to edit system pages.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractEdit extends \MovLib\Presentation\SystemPage\AbstractShow {

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
  public function initSystemPage($id, $headTitle, $pageTitle = null, $breadcrumbTitle = null) {
    // Don't allow non-admin users to edit this system page.
    $this->session->checkAuthorizationAdmin($this->intl->t(
      "This page can only be changed by administrators of {sitename}.",
      [ "sitename" => $this->config->sitename ]
    ));

    // Request authorization from admins who have been logged in for a long time.
    $this->session->checkAuthorizationTime(
      $this->intl->t("Please sign in again to verify the legitimacy of this request.")
    );

    parent::initSystemPage(
      $id,
      $this->intl->t("Edit {0}", $headTitle),
      $this->intl->t("Edit {0}", $this->placeholder($headTitle)),
      $this->intl->t("Edit")
    );
    $this->breadcrumb->addCrumb($this->entity->route, $headTitle);
    $this->initLanguageLinks("{$this->entity->route->route}/edit");
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $form = (new Form($this->container))
      ->addHiddenElement("revision_id", $this->entity->changed->formatInteger())
      ->addElement(new TextareaHTMLRaw($this->container,"content", $this->intl->t("Content"), $this->entity->text, [
        "placeholder" => $this->intl->t("Enter the system page content"),
        "required"    => true,
        "rows"        => 25,
      ]))
      ->addAction($this->intl->t("Update"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "submit" ])
    ;

    return "<div class='c'><div class='r'><div class='s s12'>{$form}</div></div></div>";
  }

  /**
   * Submit callback for the system page  edit form.
   *
   * @throws \MovLib\Exception\RedirectException\SeeOtherException
   *   Always redirects the user back to the edited system page.
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
