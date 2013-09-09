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
namespace MovLib\View\HTML\User;

use \MovLib\View\HTML\AbstractPageView;
use \MovLib\View\HTML\Form;

/**
 * User reset password form.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserResetPasswordView extends AbstractPageView {

  /**
   * The user reset password form.
   *
   * @var \MovLib\View\HTML\Form
   */
  private $form;

  /**
   * Instantiate new user reset password view.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param \MovLib\Presenter\UserPresenter $presenter
   *   The presenting presenter.
   * @param \MovLib\View\HTML\Input\MailInput $mail
   *   The mail input.
   */
  public function __construct($presenter, $mail) {
    global $i18n;
    $this->init($presenter, $i18n->t("Reset Password"));
    $this->stylesheets[] = "modules/user.css";
    $this->addClass("input--block-level", $mail->attributes);
    $this->form = new Form("reset-password", $this->presenter, $elements, [ "class" => "span span--6 offset--3" ], [ "class" => "button--success button--large", "value" => $this->title ]);
    $this->form->elements["mail"]->attributes["class"] .= "input--block-level";
    $this->form->elements["mail"]->attributes[] = "autofocus";
  }

  /**
   * @inheritdoc
   */
  public function getContent() {
    return "<div class='container'><div class='row'>{$this->form->open()}<p>{$this->form->elements["mail"]}</p>{$this->form->close(false)}</div></div>";
  }

}
