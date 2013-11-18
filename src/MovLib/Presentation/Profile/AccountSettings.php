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

use \MovLib\Data\Countries;
use \MovLib\Data\DateTimeZone;
use \MovLib\Data\SystemLanguages;
use \MovLib\Data\User\Full as UserFull;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputCheckbox;
use \MovLib\Presentation\Partial\FormElement\InputDate;
use \MovLib\Presentation\Partial\FormElement\InputImage;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputText;
use \MovLib\Presentation\Partial\FormElement\InputURL;
use \MovLib\Presentation\Partial\FormElement\RadioGroup;
use \MovLib\Presentation\Partial\FormElement\Select;
use \MovLib\Presentation\Partial\FormElement\InputHTML;

/**
 * Allows the user to manage his personalized settings.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AccountSettings extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\TraitFormPage;
  use \MovLib\Presentation\Profile\TraitProfile;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's avatar input file form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputImage
   */
  protected $avatar;

  /**
   * The user's birthday input date form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputDate
   */
  protected $birthday;

  /**
   * The user's country select form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $country;

  /**
   * The user's language select form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $language;

  /**
   * The user's private input checkbox form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputCheckbox
   */
  protected $private;

  /**
   * The user's about me input HTML form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $aboutMe;

  /**
   * The user's real name input text form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputText
   */
  protected $realName;

  /**
   * The user's sex input radio form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\RadioGroup
   */
  protected $sex;

  /**
   * The user's timezone select form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $timezone;

  /**
   * The user's website input url form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputURL
   */
  protected $website;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user account settings presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @throws \MovLib\Exception\Client\UnauthorizedException
   */
  public function __construct() {
    global $i18n, $session;

    $session->checkAuthorization($i18n->t("You need to sign in to access the danger zone."));
    $session->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."));

    $this->init($i18n->t("Account Settings"));
    $this->user = new UserFull(UserFull::FROM_ID, $session->userId);

    $this->realName = new InputText("real_name", $i18n->t("Real Name"), [
      "placeholder" => $i18n->t("Entery our real name"),
      "value"       => $this->user->realName,
    ]);

    $this->avatar = new InputImage("avatar", $i18n->t("Avatar"), $this->user);

    $this->sex = new RadioGroup("sex", $i18n->t("Sex"), [
      2 => $i18n->t("Female"),
      1 => $i18n->t("Male"),
      0 => $i18n->t("Unknown"),
    ], $this->user->sex, $i18n->t("Your sex will be displayed on your profile page and is used to create demographic evaluations."));

    $birthdayMax = $_SERVER["REQUEST_TIME"] - 1.893e8;   //   6 years
    $birthdayMin = $_SERVER["REQUEST_TIME"] - 3.78683e9; // 120 years
    $this->birthday = new InputDate("birthday", $i18n->t("Date of Birth"), [
      "max"   => $birthdayMax,
      "min"   => $birthdayMin,
      "title" => $i18n->t("The date must be between {0} (120 years) and {1} (6 years)", [
        $i18n->formatDate($birthdayMin, $this->user->timeZoneIdentifier, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE),
        $i18n->formatDate($birthdayMax, $this->user->timeZoneIdentifier, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE)
      ]),
      "value" => $this->user->birthday,
    ], $i18n->t("Your birthday will be displayed on your profile page and is used to create demographic evaluations."));

    $this->aboutMe  = new InputHTML("about_me", $i18n->t("About Me"), $this->user->aboutMe, $i18n->t("Tell others about yourself, what do you do, what do you like, …"));

    $this->language = new Select("language", $i18n->t("System Language"), (new SystemLanguages())->orderByName(), $this->user->systemLanguageCode);
    $this->country  = new Select("country", $i18n->t("Country"), (new Countries())->orderByName()->reindex("id"), $this->user->countryId);
    $this->timezone = new Select("time_zone_id", $i18n->t("Time Zone"), DateTimeZone::getTranslatedIdentifiers(), $this->user->timeZoneIdentifier);
    $this->website  = new InputURL("website", $i18n->t("Website"), [ "data-allow-external" => true, "value" => $this->user->website ]);
    $this->private  = new InputCheckbox("private", $i18n->t("Keep my data private!"), [ "value" => $this->user->private ], $i18n->t(
      "Check the following box if you’d like to hide your private data on your profile page. Your data will only be " .
      "used by MovLib for anonymous demographical evaluation of usage statistics and ratings. By providing basic data " .
      "like sex and country, scientists around the world are enabled to research the human interests in movies more " .
      "closely. Of course your real name won’t be used for anything!"
    ));

    $this->form = new Form($this, [
      $this->realName,
      $this->avatar,
      $this->sex,
      $this->birthday,
      $this->aboutMe,
      $this->language,
      $this->country,
      $this->timezone,
      $this->website,
      $this->private,
    ]);

    $this->form->actionElements[] = new InputSubmit($i18n->t("Update Account Settings"), [ "class" => "button button--large button--success" ]);
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    return $this->form;
  }

  /**
   * Validate data that couldn't be auto-validated and update the user's account settings.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param array $errors
   *   {@inheritdoc}
   * @return this
   */
  public function validate(array $errors = null) {
    global $i18n;
    if ($this->checkErrors($errors) === false) {
      $this->user->birthday           = $this->birthday->value;
      $this->user->countryId          = $this->country->value;
      $this->user->private            = $this->private->value;
      $this->user->aboutMe            = $this->aboutMe->value;
      $this->user->realName           = $this->realName->value;
      $this->user->sex                = $this->sex->value;
      $this->user->systemLanguageCode = $this->language->value;
      $this->user->timeZoneIdentifier         = $this->timezone->value;
      $this->user->website            = $this->website->value;
      $this->user->commit();
      $this->alerts                  .= new Alert(
        $i18n->t("Your account settings were updated successfully."),
        $i18n->t("Account Settings Updated Successfully"),
        Alert::SEVERITY_SUCCESS
      );
    }
    return $this;
  }

}
