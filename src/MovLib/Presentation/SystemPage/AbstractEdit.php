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

use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Presentation\Partial\FormElement\InputText;
use \MovLib\Presentation\Partial\FormElement\TextareaHTMLRaw;

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

  /**
   * {@inheritdoc}
   */
  public function initSystemPage($id, $headTitle, $pageTitle = null, $breadcrumbTitle = null) {
    // Don't allow non-admin users to edit this system page.
    $this->session->checkAuthorizationAdmin($this->intl->t(
      "The page you want to edit can only be changed by administrators of {sitename}.",
      [ "sitename" => $this->config->sitename ]
    ));

    // Request authorization from admins who have been logged in for a long time.
    $this->session->checkAuthorizationTimestamp(
      $this->intl->t("Please sign in again to verify the legitimacy of this request.")
    );

    parent::initSystemPage(
      $id,
      $this->intl->t("Edit {0}", $this->placeholder($headTitle)),
      null,
      $this->intl->t("Edit")
    );
    $this->breadcrumb->addCrumb($this->systemPage->route, $headTitle);
    $this->initLanguageLinks("{$this->systemPage->routeKey}/edit");
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $form = (new Form($this->diContainerHTTP))
      ->formAddElement(new InputText("title", $this->intl->t("Title"), $this->systemPage->title, [
        "#help-popup" => $this->intl->t("A system page’s title cannot contain any HTML."),
        "autofocus"   => true,
        "placeholder" => $this->intl->t("Enter the system page title"),
        "required"    => true,
      ]))
      ->formAddElement(new TextareaHTMLRaw("content", $this->intl->t("Content"), $this->systemPage->text, [
        "#help-popup" => $this->intl->t("A system page’s text content can contain any HTML."),
        "placeholder" => $this->intl->t("Enter the system page content"),
        "required"    => true,
        "rows"        => 25,
      ]))
      ->formAddAction($this->intl->t("Update"), [ "class" => "btn btn-large btn-success" ])
      ->formInit([ $this, "valid" ])
    ;

    return "<div class='c'><div class='r'><div class='s s12'>{$form}</div></div></div>";
  }

  /**
   * The form is valid.
   *
   * @throws \MovLib\Exception\RedirectException\SeeOtherException
   */
  public function valid() {
    // Store the changes to the system page.
    $this->systemPage->commit();

    // Let the user know that the system page was update.
    $this->alertSuccess(
      $this->intl->t("Successfully Updated"),
      $this->intl->t("The {title} system page was successfully updated.", [ "title" => $this->placeholder($this->systemPage->title) ])
    );

    // Encourage the user to validate the page.
    $this->alertInfo(
      $this->intl->t("Valid?"),
      $this->intl->t("Please {0}validate your HTML with the W3C Validator{1}.", [
        "<a href='http://validator.w3.org/check?uri=" . rawurlencode("{$this->request->scheme}://{$this->request->hostname}{$this->systemPage->route}") . "'>",
        "</a>"
      ])
    );

    // Redirect to the just updated system page.
    throw new SeeOtherException($this->systemPage->route);
  }

}
