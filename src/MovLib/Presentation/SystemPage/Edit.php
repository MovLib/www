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

use \MovLib\Data\SystemPage;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\FormElement\TextareaHTMLRaw;
use \MovLib\Presentation\Partial\FormElement\InputText;
use \MovLib\Presentation\Redirect\SeeOther;

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
class Edit extends \MovLib\Presentation\SystemPage\Show {
  use \MovLib\Presentation\TraitForm;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The system page to present.
   *
   * @var \MovLib\Data\SystemPage
   */
  protected $systemPage;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new system page edit presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @throws \MovLib\Presentation\Error\Forbidden
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function __construct() {
    global $i18n, $kernel, $session;

    // Don't allow non-admin users to edit this system page.
    $session->checkAuthorizationAdmin($i18n->t("The page you want to edit can only be changed by administrators of {0}.", [ $kernel->siteName ]));

    // Request authorization from admins who have been logged in for a long time.
    $session->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."));

    $this->systemPage      = new SystemPage((integer) $_SERVER["ID"]);
    $this->initPage($i18n->t("Edit {0}", [ $this->systemPage->title ]));
    $this->initBreadcrumb([[ $i18n->r($this->systemPage->route), $this->systemPage->title ]]);
    $this->breadcrumbTitle = $i18n->t("Edit");
    $this->initLanguageLinks("{$this->systemPage->route}/edit");

    $this->formAddElement(new InputText("title", $i18n->t("Title"), $this->systemPage->title, [
      "#help-popup" => $i18n->t("A system page’s title cannot contain any HTML."),
      "autofocus"   => true,
      "placeholder" => $i18n->t("Enter the system page title"),
      "required"    => true,
    ]));

    $this->formAddElement(new TextareaHTMLRaw("content", $i18n->t("Content"), $this->systemPage->text, [
      "#help-popup" => $i18n->t("A system page’s text content can contain any HTML."),
      "placeholder" => $i18n->t("Enter the system page content"),
      "required"    => true,
      "rows"        => 25,
    ]));

    $this->formAddAction($i18n->t("Update"), [ "class" => "btn btn-large btn-success" ]);
    $this->formInit();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getContent() {
    return "<div class='c'><div class='r'><div class='s s12'>{$this->formRender()}</div></div></div>";
  }

  /**
   * The submitted form has no auto-validation errors, continue normal program flow.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * \MovLib\Presentation\Redirect\SeeOther
   */
  protected function formValid() {
    global $i18n, $kernel;

    // Store the changes to the system page.
    $this->systemPage->commit();

    // Let the user know that the system page was update.
    $kernel->alerts .= new Alert(
      $i18n->t("The {title} system page was successfully updated.", [ "title" => $this->placeholder($this->systemPage->title) ]),
      $i18n->t("Successfully Updated"),
      Alert::SEVERITY_SUCCESS
    );

    // Encourage the user to validate the page.
    $kernel->alerts .= new Alert(
      $i18n->t("Please {0}validate your HTML with the W3C validator{1}.", [
        "<a href='http://validator.w3.org/check?uri=" . rawurlencode("{$kernel->scheme}://{$kernel->hostname}{$this->systemPage->route}") . "'>", "</a>"
      ]),
      $i18n->t("Valid?"),
      Alert::SEVERITY_INFO
    );

    // Redirect to the just updated system page.
    throw new SeeOther($i18n->r($this->systemPage->route));
  }

}
