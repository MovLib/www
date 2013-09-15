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

use \MovLib\Exception\RedirectException;
use \MovLib\Data\User;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\Button;

/**
 * User deactivated presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Deactivated extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\User\UserTrait;

  /**
   * The presentation's form.
   *
   * @var \MovLib\Presentation\Partial\Form
   */
  private $form;

  /**
   * Instantiate new user deactivated presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   */
  public function __construct() {
    global $i18n, $session;
    if ($session->isAuthenticated === false) {
      throw new RedirectException("/", 302);
    }
    $this->user = new User(User::FROM_ID, $session->userId);
    if ($this->user->deactivated === false) {
      throw new RedirectException($i18n->r("/my"), 302);
    }
    $this->init($i18n->t("Deactivated"));
    $info = new Alert($i18n->t("Your account has been deactivated, do you wish to activate it again?"));
    $info->title = $i18n->t("Account Deactivated");
    $info->severity = Alert::SEVERITY_INFO;
    $this->alerts .= $info;
    $this->form = new Form($this, []);
    $this->form->actionElements[] = new Button("activate", "Yes", [
      "class" => "button--success button--large",
      "title" => $i18n->t("Click here if you wish to reactivate your account."),
    ]);
    $this->form->actionElements[] = new Button("deactivate", "No", [
      "class" => "button--danger button--large",
      "title" => $i18n->t("Click here if you want to keep it deactivated."),
    ]);
  }

  /**
   * @inheritdoc
   */
  public function getContent() {
    return "<div class='container'><div class='row'>{$this->form}</div></div>";
  }

  /**
   * Activate the account again or keep it deactivated, depending on the button the user clicked.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return this
   */
  public function validate() {
    global $i18n, $session;
    if (isset($_POST["activate"])) {
      (new User(User::FROM_ID, $session->userId))->reactivate();
      $success = new Alert($i18n->t("Your account was successfully reactivated. We are very pleased to see you back {0}!", [
        $this->placeholder($session->userName),
      ]));
      $success->title = $i18n->t("Reactivation Successful");
      $success->severity = Alert::SEVERITY_SUCCESS;
      $session->alerts .= $success;
      throw new RedirectException($i18n->r("/my"), 302);
    }
    else {
      $session->destroy();
      throw new RedirectException("/", 302);
    }
    return $this;
  }

}
