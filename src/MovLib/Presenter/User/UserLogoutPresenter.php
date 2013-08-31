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
namespace MovLib\Presenter\User;

use \MovLib\Presenter\User\UserLoginPresenter;
use \MovLib\View\HTML\User\UserLoginView;

/**
 * Handles user log outs.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserLogoutPresenter extends UserLoginPresenter {

  /**
   * Instantiate new user logout presenter.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   */
  public function __construct() {
    global $i18n, $user;
    $this->init();
    if ($user->isLoggedIn === true) {
      $user->destroySession();
      $this->view->setAlert([
        "title"   => $i18n->t("You’ve been logged out successfully."),
        "message" => $i18n->t("We hope to see you again soon."),
      ], UserLoginView::ALERT_SEVERITY_SUCCESS);
    }
  }

}
