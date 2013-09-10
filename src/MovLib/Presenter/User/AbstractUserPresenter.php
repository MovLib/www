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

use \MovLib\Presenter\AbstractPresenter;

/**
 * Abstract base class for all user presenter.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractUserPresenter extends AbstractPresenter {

  /**
   * The user model of the currently logged in user.
   *
   * @var \MovLib\Model\UserModel
   */
  public $profile;

  /**
   * @inheritdoc
   */
  public function getBreadcrumb() {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      return [[ $i18n->r("/user"), $i18n->t("Your Profile"), [ "title" => $i18n->t("Go to your personal profile page.") ] ]];
    }
    return [[ $i18n->r("/users"), $i18n->t("Users"), [ "title" => $i18n->t("Have a look at our user statistics.") ] ]];
  }

  /**
   * Get the secondary navigation points.
   *
   * @see \MovLib\View\HTML\TraitSecondaryNavigationView
   * @return array
   *   The secondary navigation points.
   */
  public function getSecondaryNavigationPoints() {
    global $i18n;
    return [
      [ $i18n->r("/user"), "<i class='icon icon--info-circled'></i> {$i18n->t("Profile")}", [
        "class" => "menuitem--separator",
        "title" => $i18n->t("Check out our account summary."),
      ]],
      [ $i18n->r("/user/account-settings"), "<i class='icon icon--user'></i> {$i18n->t("Account")}", [
        "title" => $i18n->t("Manage your basic account settings."),
      ]],
      [ $i18n->r("/user/notification-settings"), "<i class='icon icon--bell'></i> {$i18n->t("Notifications")}", [
        "title" => $i18n->t("Manage your notification settings."),
      ]],
      [ $i18n->r("/user/mail-settings"), "<i class='icon icon--mail'></i> {$i18n->t("Mail")}", [
        "title" => $i18n->t("Change your email address."),
      ]],
      [ $i18n->r("/user/password-settings"), "<i class='icon icon--lock'></i> {$i18n->t("Password")}", [
        "title" => $i18n->t("Change your password."),
      ]],
      [ $i18n->r("/user/danger-zone-settings"), "<i class='icon icon--alert'></i> {$i18n->t("Danger Zone")}", [
        "class" => "menuitem--delete",
        "title" => $i18n->t("Manage your sessions and/or deactivate your account."),
      ]],
    ];
  }

}
