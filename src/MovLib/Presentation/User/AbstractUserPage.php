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

/**
 * Description of AbstractBase
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractUserPage extends \MovLib\Presentation\Page {

  /**
   * The user we are currently displaying.
   *
   * @var \MovLib\Data\User
   */
  protected $user;

  /**
   * @inheritdoc
   */
  protected function init($title) {
    $this->stylesheets[] = "modules/user.css";
    return parent::init($title);
  }

  /**
   * @inheritdoc
   */
  protected function getBreadcrumbTrail() {
    global $i18n, $session;
    if ($session->isLoggedIn === true) {
      return [[ $i18n->r("/users"), $i18n->t("Users"), [ "title" => $i18n->t("Have a look at our user statistics.") ]]];
    }
    return [[ $i18n->r("/user"),  $i18n->t("Profile"), [ "title" => $i18n->t("Go to your profile, get an account summary or manage your settings.") ]]];
  }

}
