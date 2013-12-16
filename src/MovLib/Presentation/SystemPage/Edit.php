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
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputText;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Allows administrators to edit system pages.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Edit extends \MovLib\Presentation\SystemPage\Show {
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The page's text input html form element.
   *
   * @var type
   */
  protected $inputPageText;

  /**
   * The page's title input text form element.
   *
   * @var type
   */
  protected $inputPageTitle;

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

    $this->systemPage = new SystemPage($_SERVER["ID"]);
    $this->initPage($i18n->t("Edit {0}", [ $this->systemPage->title ]));
    $this->initBreadcrumb([[ $this->systemPage->route, $this->systemPage->title ]], $i18n->t("Edit"));
    $this->initLanguageLinks("{$this->systemPage->route}/edit");

    $this->inputPageTitle = new InputText("page_title", $i18n->t("Page Title"), [ "required" => "required", "value" => $this->systemPage->title ]);

    $this->inputPageText  = new InputHTML(
      "page-text",
      $i18n->t("Page Text"),
      $this->systemPage->text,
      [ "placeholder" => $i18n->t("Enter text for this system page"), "required" => "required" ]
    );
    $this->inputPageText
      ->allowBlockqoutes()
      ->allowExternalLinks()
      ->allowHeadings(2)
      ->allowImages()
      ->allowLists()
    ;

    $this->form                   = new Form($this, [ $this->inputPageTitle, $this->inputPageText]);
    $this->form->actionElements[] = new InputSubmit($i18n->t("Update {0}", [ $this->systemPage->title ]), [ "class" => "btn btn-large btn-success" ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  public function getBreadcrumbs() {
    global $i18n;
    return [[ $i18n->r($this->systemPage->route), $this->systemPage->title ]];
  }

  /**
   * @inheritdoc
   */
  public function getContent() {
    return "<div class='c'><div class='r'><div class='s s12'>{$this->form}</div></div></div>";
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function validate(array $errors = null) {
    global $i18n, $kernel;

    if ($this->checkErrors($errors) === false) {
      $this->systemPage->title = $this->inputPageTitle->value;
      $this->systemPage->text  = $this->inputPageText->value;
      $this->systemPage->commit();

      $kernel->alerts .= new Alert(
        $i18n->t("You successfully updated the system page {0}.", [ $this->systemPage->title ]),
        $i18n->t("{0} updated successfully", [ $this->systemPage->title ]),
        Alert::SEVERITY_SUCCESS
      );
      throw new SeeOtherRedirect($i18n->r($this->systemPage->route));
    }

    return $this;
  }

}
