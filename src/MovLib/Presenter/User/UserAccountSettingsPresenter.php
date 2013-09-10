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

use \MovLib\Model\UserModel;
use \MovLib\Presenter\User\AbstractUserPresenter;
use \MovLib\View\HTML\Input\CheckboxInput;
use \MovLib\View\HTML\Input\DateInput;
use \MovLib\View\HTML\Input\FileInput;
use \MovLib\View\HTML\Input\RadioInput;
use \MovLib\View\HTML\Input\SelectInput;
use \MovLib\View\HTML\Input\SubmitInput;
use \MovLib\View\HTML\Input\TextInput;
use \MovLib\View\HTML\Input\TextareaInput;
use \MovLib\View\HTML\Input\UrlInput;
use \MovLib\View\HTML\User\UserAccountSettingsView;

/**
 * Handles user account settings validation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserAccountSettingsPresenter extends AbstractUserPresenter {

  /**
   * Instantiate new user account settings presenter.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   */
  public function __construct() {
    global $i18n, $user;
    $this->checkAuthorization();
    $userModel = new UserModel(UserModel::FROM_ID, $user->id);

    $avatar = new FileInput("avatar", $i18n->t("Avatar"), $userModel);
    $avatar->help = $i18n->t("");

    $realNameLabel = $i18n->t("Real Name");
    $realName = new TextInput("real_name", $realNameLabel, [
      "class"       => "input--block-level",
      "placeholder" => $realNameLabel,
      "title"       => $i18n->t("Please enter your {0} in this field.", [ $realNameLabel ]),
    ]);
    $realName->help = $i18n->t("Your {0} will be displayed on your profile page.", $realNameLabel);

    $sex = new RadioInput("sex", $i18n->t("Sex"), [
      0 => $i18n->t("Unknown"),
      1 => $i18n->t("Male"),
      2 => $i18n->t("Female"),
    ]);
    $sex->help = $i18n->t("Your {0} will be displayed on your profile page and is used to create demographic evaluations.", [ $sex->label ]);

    $countryOptions = [];
    $country = new SelectInput("country", $i18n->t("Country"), $countryOptions, [ "class" => "input--block-level" ]);
    $country->help = $i18n->t("Your {0} will be displayed on your profile page and is used to create demographic evaluations.", [ $country->label ]);

    $timezone = new SelectInput("timezone", $i18n->t("Time Zone"), timezone_identifiers_list(), [ "class" => "input--block-level" ]);
    $timezone->help = $i18n->t("The {0} is used to display the correct dates and times.", [ $timezone->label ]);

    $localeOptions = [];
    $locale = new SelectInput("locale", $i18n->t("Language"), $localeOptions, [ "class" => "input--block-level" ]);
    $locale->help = $i18n->t("Please select your preferred display language.", [ $locale->label ]);

    $profile = new TextareaInput("profile", $i18n->t("About You"), [ "placeholder" => $i18n->t("This text will appear on your profile page.") ]);
    $profile->help = $i18n->t("Write something About You and tell other people which movies you like, where you hang out, …");

    $birthdate = new DateInput("birthdate", $i18n->t("Date of Birth"), [ "class" => "input--block-level" ]);
    $birthdate->help = $i18n->t("Your {0} will be displayed on your profile page and is used to create demographic evaluations.", [ $birthdate->label ]);

    $website = new UrlInput("website", $i18n->t("Website"), [ "class" => "input--block-level" ]);
    $website->help = $i18n->t("Your {0} will be displayed on your profile page.", [ $website->label ]);

    $private = new CheckboxInput("private", $i18n->t("Keep my data private!"));
    $private->help = $i18n->t(
      "Check the following box if you’d like to hide your private data on your profile page. Your data will only be " .
      "used by MovLib for anonymous demographical evaluation of usage statistics and ratings. By providing basic data " .
      "like sex and country, scientists around the world are enabled to research the human interests in movies more " .
      "closely. Of course your real name won’t be used for anything!"
    );

    new UserAccountSettingsView($this, [
      new FileInput("avatar", $i18n->t("avatar"), $userModel),
      new TextInput("real_name", $i18n->t("real name")),
      new RadioInput("sex", $i18n->t("sex"), [ $i18n->t("Unknown"), $i18n->t("Male"), $i18n->t("Female") ]),
      new SelectInput("country", $i18n->t("country"), []),
      new SelectInput("timezone", $i18n->t("time zone"), timezone_identifiers_list()),
      new SelectInput("locale", $i18n->t("language"), []),
      new TextareaInput("profile", $i18n->t("profile")),
      new DateInput("birthdate", $i18n->t("date of birth")),
      new UrlInput("website", $i18n->t("website")),
      new CheckboxInput("private", $i18n->t("private")),
    ]);
  }

  public function validate() {

  }

}
