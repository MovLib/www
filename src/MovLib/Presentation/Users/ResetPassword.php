<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Users;

use \MovLib\Data\Delayed\Mailer;
use \MovLib\Presentation\Email\User\ResetPassword as ResetPasswordEmail;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;

/**
 * User reset password presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ResetPassword extends \MovLib\Presentation\Page {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The email input form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputEmail
   */
  private $email;

  /**
   * The page's form element.
   *
   * @var \MovLib\Presentation\Partial\Form;
   */
  private $form;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new user reset password presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->init($i18n->t("Reset Password"));

    $this->email = new InputEmail("email", $i18n->t("Email Address"), [
      "autofocus",
      "placeholder" => $i18n->t("Enter your email address"),
    ]);

    $this->form = new Form($this, [ $this->email ]);
    $this->form->attributes["class"] = "span span--6 offset--3";

    $this->form->actionElements[] = new InputSubmit([
      "title" => $i18n->t("Click here to request a password reset for the entered email address."),
      "value" => $i18n->t("Request Password Reset"),
    ]);
  }

  /**
   * @inheritdoc
   */
  public function getContent() {
    return "<div class='container'><div class='row'>{$this->form}</div></div>";
  }

  /**
   * Validation callback after auto-validation of form has succeeded.
   *
   * The redirect exception is thrown if the supplied data is valid. The user will be redirected to her or his personal
   * dashboard.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return this
   * @throws \MovLib\Exception\RedirectException
   */
  public function validate() {
    global $i18n;
    Mailer::stack(new ResetPasswordEmail($this->email->value));
    $success           = new Alert($i18n->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->email->value) ]));
    $success->title    = $i18n->t("Successfully Requested Password Reset");
    $success->severity = Alert::SEVERITY_SUCCESS;
    $this->alerts .= $success;
    return $this;
  }

}
