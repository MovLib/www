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
namespace MovLib\Presentation\User;

use \MovLib\Presentation\Form;
use \MovLib\Presentation\FormElement\InputEmail;
use \MovLib\Presentation\FormElement\InputSubmit;

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
   * @var \MovLib\Presentation\FormElement\InputEmail
   */
  private $email;

  /**
   * The page's form element.
   *
   * @var \MovLib\Presentation\Form;
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

    // @todo max-length
    $this->email = new InputEmail([ "autofocus", "class" => "input--block-level" ]);
    $this->email->required();

    $this->form = new Form($this, [ $this->email ]);
    $this->form->attributes["class"] = "span span--6 offset--3";

    $this->form->actionElements[] = new InputSubmit([
      "class" => "button--large button--success",
      "title" => $i18n->t("Click here to request a password reset for the entered email address."),
      "value" => $i18n->t("Request Password Reset"),
    ]);
  }

  /**
   * @inheritdoc
   */
  public function getContent() {
    return "<div class='container'><div class='row'>{$this->form->open()}<p>{$this->email}</p>{$this->form->close(false)}</div></div>";
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
    return $this;
  }

}
