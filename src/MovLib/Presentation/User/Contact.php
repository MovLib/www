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
namespace MovLib\Presentation\User;

use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputText;

/**
 * The user's movie collection page.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Contact extends \MovLib\Presentation\User\AbstractUserPage {
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The email's subject.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputText
   */
  protected $subject;

  /**
   * The email's body.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $message;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   *
   * Instantiate new user collection presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct(){
    global $i18n;
    $this->init();
    $this->initPage($i18n->t("Contact {0}", [ $this->user->name ]));
    $this->initLanguageLinks("/user/{0}/contact", [ $this->user->name ]);
    $this->pageTitle       = $i18n->t("Contact {username}", [ "username" => "<a href='{$this->user->route}'>{$this->user->name}</a>" ]);
    $this->breadcrumbTitle = $i18n->t("Contact");
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   */
  protected function getPageContent(){
    global $i18n, $kernel, $session;

    // Only authenticated users can contact other users.
    if ($session->isAuthenticated === false) {
      return new Alert(
        $i18n->t("You need to {0}sign in{1} or {2}joing {sitename}{1} to contact other users.", [
          "<a href='{$i18n->r("/profile/sign-in")}'>", "</a>", "<a href='{$i18n->r("/profile/join")}'>", "sitename" => $kernel->siteName
        ]),
        $i18n->t("Authentication Required"),
        Alert::SEVERITY_INFO
      );
    }

    $this->subject = new InputText("subject", $i18n->t("Subject"), [
      "placeholder" => $i18n->t("This will appear as title in {username}’s inbox", [ "username" => $this->user->name ]),
      "required",
    ]);
    $this->message = new InputHTML("message", $i18n->t("Message"), null, []);
    $this->form = new Form($this, [ $this->subject, $this->message ]);
    $this->form->actionElements[] = new InputSubmit($i18n->t("Send"), [ "class" => "btn btn-success btn-large" ]);

    return $this->form;
  }

  /**
   * @inheritdoc
   */
  public function validate(array $errors = null) {
    global $i18n;
    if ($this->checkErrors($errors) === false) {
      $this->alerts .= new Alert($i18n->t("Not implemented yet!"));
    }
    return $this;
  }

}
