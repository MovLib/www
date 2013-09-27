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
namespace MovLib\Presentation\Profile;

use \MovLib\Data\I18n;
use \MovLib\Data\User;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputCheckbox;
use \MovLib\Presentation\Partial\FormElement\InputDate;
use \MovLib\Presentation\Partial\FormElement\InputFile;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputText;
use \MovLib\Presentation\Partial\FormElement\InputURL;
use \MovLib\Presentation\Partial\FormElement\RadioGroup;
use \MovLib\Presentation\Partial\FormElement\Select;
use \MovLib\Presentation\Partial\FormElement\Textarea;
use \MovLib\Presentation\Validation\HTML;

/**
 * Allows the user to manage his personalized settings.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AccountSettings extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\Profile\TraitProfile;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's avatar input file form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputFile
   */
  private $avatar;

  /**
   * The user's birthday input date form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputDate
   */
  private $birthday;

  /**
   * The user's country select form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  private $country;

  /**
   * The presentation's form.
   *
   * @var \MovLib\Presentation\Partial\Form
   */
  private $form;

  /**
   * The user's language select form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  private $language;

  /**
   * The user's private input checkbox form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputCheckbox
   */
  private $private;

  /**
   * The user's profile textarea form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Textarea
   */
  private $profile;

  /**
   * The user's real name input text form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputText
   */
  private $realName;

  /**
   * The user's sex input radio form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\RadioGroup
   */
  private $sex;

  /**
   * The user's timezone select form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  private $timezone;

  /**
   * The user's website input url form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputURL
   */
  private $website;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user account settings presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @throws \MovLib\Exception\UnauthorizedException
   */
  public function __construct() {
    global $i18n, $session;

    // We call both auth-methods the session has to ensure that the error message we display is as accurate as possible.
    $session
      ->checkAuthorization($i18n->t("You need to sign in to access the danger zone."))
      ->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."))
    ;

    // Start rendering the page.
    $this->init($i18n->t("Account Settings"))->user = new User(User::FROM_ID, $session->userId);

    // @todo Avatar Upload (autofocus?)
    $this->avatar = new InputFile();

    $this->realName = new InputText("real_name", $i18n->t("Real Name"), $this->user->realName, [
      "inputmode"   => "latin-name",
      "placeholder" => $i18n->t("Enter your real name"),
    ]);

    $this->sex = (new RadioGroup("sex", $i18n->t("Sex"), $this->user->sex, [
      2 => $i18n->t("Female"),
      1 => $i18n->t("Male"),
      0 => $i18n->t("Unknown"),
    ]))->setHelp($i18n->t("Your sex will be displayed on your profile page and is used to create demographic evaluations."));

    $time        = time();
    $birthdayMax = $time - 1.893e8;   //   6 years
    $birthdayMin = $time - 3.78683e9; // 120 years
    $this->birthday = new InputDate("birthday", $i18n->t("Date of Birth"), [
      "max"   => $birthdayMax,
      "min"   => $birthdayMin,
      "title" => $i18n->t("The date must be between {0} (120 years) and {1} (6 years)", [ $birthdayMin, $birthdayMax ]),
    ], $this->user->birthday);
    $this->birthday->setHelp($i18n->t("Your birthday will be displayed on your profile page and is used to create demographic evaluations."));

    $this->profile = new Textarea("profile", $i18n->t("About You"), $this->user->profile, [
      "placeholder" => $i18n->t("Tell others about yourself, what do you do, what do you like, …"),
    ]);
    $this->profile->attributes["data-format"]         = HTML::FORMAT_ANCHORS;
    $this->profile->attributes["data-allow-external"] = true;

    $this->language = (new Select("language", $i18n->t("Language"), $i18n->getSystemLanguages(), $this->user->systemLanguageCode))->required();

    $this->country = new Select("country", $i18n->t("Country"), array_column($i18n->getCountries(I18n::KEY_NAME), I18n::KEY_NAME, I18n::KEY_CODE), $this->user->getCountryCode());

    // @todo Should we create groups for continents? They look ugly and each it's already sorted alphabetically.
    $this->timezone = (new Select("timezone", $i18n->t("Time Zone"), $i18n->getTimeZones(), $this->user->timeZoneId))->required();

    // We don't validate the existens of the user's website (respectively homepage).
    $this->website = new InputURL("website", $i18n->t("Website"), $this->user->website);
    $this->website->attributes["data-allow-external"] = true;

    // @todo Facebook
    //$this->facebook = ?

    // @todo Google Plus
    //$this->googlePlus = ?

    // @todo Twitter
    //$this->twitter = ?

    // @todo Private Flag
    $this->private = new InputCheckbox("private", $this->user->private);
    $this->private->label = $i18n->t("Keep my data private!");
    $this->private->setHelp($i18n->t(
      "Check the following box if you’d like to hide your private data on your profile page. Your data will only be " .
      "used by MovLib for anonymous demographical evaluation of usage statistics and ratings. By providing basic data " .
      "like sex and country, scientists around the world are enabled to research the human interests in movies more " .
      "closely. Of course your real name won’t be used for anything!"
    ));

    $this->form = new Form($this, [
      $this->realName,
      $this->sex,
      $this->birthday,
      $this->profile,
      $this->language,
      $this->country,
      $this->timezone,
      $this->website,
      $this->private,
    ]);

    $this->form->actionElements[] = new InputSubmit([
      "class" => "button--large button--success",
      "value" => $i18n->t("Update Account Settings"),
    ]);
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
   * @return this
   */
  public function validate() {
    global $i18n;
    $this->user->realName           = $this->realName->value;
    $this->user->sex                = $this->sex->value;
    $this->user->birthday           = $this->birthday->value;
    $this->user->profile            = $this->profile->value;
    $this->user->systemLanguageCode = $this->language->value;
    $this->user->countryId          = $this->country->value;
    $this->user->timeZoneId         = $this->timezone->value;
    $this->user->website            = $this->website->value;
    $this->user->private            = $this->private->value;
    $this->user->commit();
    $success = new Alert($i18n->t("Your account settings were updated successfully."));
    $success->title = $i18n->t("Account Settings Updated Successfully");
    $success->severity = Alert::SEVERITY_SUCCESS;
    $this->alerts .= $success;
    return $this;
  }

}
