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
namespace MovLib\Presentation\Profile;

use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Data\User\Full as UserFull;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\Button;

/**
 * User deactivated presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Deactivated extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitFormPage;
  use \MovLib\Presentation\Profile\TraitProfile;

  /**
   * Instantiate new user deactivated presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   */
  public function __construct() {
    global $i18n, $session;

    // Redirect the user to the home page if not authenticated.
    if ($session->isAuthenticated === false) {
      throw new RedirectSeeOtherException("/");
    }

    // Instantiate new full user object and check if this user is really deactivated, if not redirect to dashboard.
    $this->user = new UserFull(UserFull::FROM_ID, $session->userId);
    if ($this->user->deactivated === false) {
      throw new Redirect($i18n->r("/my"));
    }

    // Translate and set the page title.
    $this->init($i18n->t("Deactivated"));

    // Let the user know why we are displaying this page.
    $this->alerts .= new Alert(
      $i18n->t("Your account has been deactivated, do you wish to activate it again?"),
      $i18n->t("Account Deactivated"),
      Alert::SEVERITY_INFO
    );

    // The page form to re-activate the account.
    $this->form = new Form($this);
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
   * @param array $errors [optional]
   *   {@inheritdoc}
   * @return this
   */
  public function validate(array $errors = null) {
    global $i18n, $session;

    if (isset($_POST["activate"])) {
      (new UserFull(UserFull::FROM_ID, $session->userId))->reactivate();
      $session->alerts = new Alert(
        $i18n->t("Your account was successfully reactivated. We are very pleased to see you back {0}!", [
          $this->placeholder($session->userName),
        ]),
        $i18n->t("Reactivation Successful"),
        Alert::SEVERITY_SUCCESS
      );
      throw new RedirectSeeOtherException($i18n->r("/my"));
    }
    else {
      $session->destroy();
      throw new RedirectSeeOtherException($i18n->r("/users/login"));
    }

    return $this;
  }

}
