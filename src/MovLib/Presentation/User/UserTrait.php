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
 * The user trait provides several methods that are shared among user specific presentations.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait UserTrait {

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
  protected function getBreadcrumbs() {
    global $i18n, $session;
    $breadcrumbs = [[ $i18n->r("/users"), $i18n->t("Users"), [ "title" => $i18n->t("Have a look at our user statistics.") ]]];
    $userRoute = $i18n->r("/user");
    if ($session->isAuthenticated === true && strpos($_SERVER["PATH_INFO"], $userRoute) !== false) {
      $breadcrumbs[] = [ $i18n->r("/user"), $i18n->t("Profile"), [ "title" => $i18n->t("Go to your personal profile page.") ]];
    }
    return $breadcrumbs;
  }

  /**
   * @inheritdoc
   */
  protected function getSecondaryNavigationMenuitems() {
    global $i18n;
    return [
      [
        $i18n->r("/user"),
        "<i class='icon icon--info-circled'></i> {$i18n->t("Profile")}",
        [ "class" => "separator", "title" => $i18n->t("Check out our account summary.") ]
      ],
      [
        $i18n->r("/user/account-settings"),
        "<i class='icon icon--user'></i> {$i18n->t("Account")}",
        [ "title" => $i18n->t("Manage your basic account settings.") ]
      ],
      [
        $i18n->r("/user/notification-settings"),
        "<i class='icon icon--bell'></i> {$i18n->t("Notifications")}",
        [ "title" => $i18n->t("Manage your notification settings.") ]
      ],
      [
        $i18n->r("/user/email-settings"),
        "<i class='icon icon--mail'></i> {$i18n->t("Email")}",
        [ "title" => $i18n->t("Change your email address.") ]
      ],
      [
        $i18n->r("/user/password-settings"),
        "<i class='icon icon--lock'></i> {$i18n->t("Password")}",
        [ "title" => $i18n->t("Change your password.") ]
      ],
      [
        $i18n->r("/user/danger-zone-settings"),
        "<i class='icon icon--alert'></i> {$i18n->t("Danger Zone")}",
        [ "class" => "delete", "title" => $i18n->t("Manage your sessions and/or deactivate your account.") ]
      ],
    ];
  }

}
