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

use \IntlDateFormatter;
use \MovLib\Data\I18n;
use \MovLib\Data\User;
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
   * @var \MovLib\Presentation\Partial\FormElement\InputImage
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

    $session->checkAuthorization($i18n->t("You need to sign in to access the danger zone."));
    $session->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."));

    $this->init($i18n->t("Account Settings"));
    $this->user                                       = new User(User::FROM_ID, $session->userId);
    $this->avatar                                     = new InputImage("avatar", $this->user);
    $this->realName                                   = new InputText("real_name", $this->user->realName);
    $this->sex                                        = new RadioGroup("sex", $this->user->sex, [ 2 => $i18n->t("Female"), 1 => $i18n->t("Male"), 0 => $i18n->t("Unknown") ]);
    $this->birthday                                   = new InputDate("birthday", $this->user->birthday);
    $this->birthday->max                              = $_SERVER["REQUEST_TIME"] - 1.893e8;   //   6 years
    $this->birthday->min                              = $_SERVER["REQUEST_TIME"] - 3.78683e9; // 120 years
    $this->profile                                    = new Textarea("profile", $this->user->profile);
    $this->profile->attributes["data-allow-external"] = true;
    $this->profile->attributes["data-format"]         = HTML::FORMAT_ANCHORS;
    $this->language                                   = new Select("language", $i18n->getSystemLanguages(), $this->user->systemLanguageCode);
    $this->language->required();
    $this->country                                    = new Select("country", array_column($i18n->getCountries(I18n::KEY_NAME), I18n::KEY_NAME, I18n::KEY_ID), $this->user->getCountryCode());
    $this->timezone                                   = new Select("timezone", $i18n->getTimeZones(), $this->user->timeZoneId);
    $this->timezone->required();
    $this->website                                    = new InputURL("website", $this->user->website);
    $this->website->attributes["data-allow-external"] = true;
    $this->private                                    = new InputCheckbox("private", $this->user->private);
    $this->form = new Form($this, [
      $this->avatar,
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
    global $i18n;

    $avatarTitleArgs                           = $this->formatBytes($this->avatar->maximumFileSize);
    $avatarTitleArgs[]                         = $this->user->imageMinWidth;
    $this->avatar->attributes["title"]         = $i18n->t(
      "The image’s minimum dimensions are {2}x{2} pixels, the maximum allowed size for uploads is {0} {1}, image’s " .
      "that are too large will be converted automatically. Allowed image types are: JPG and PNG", $avatarTitleArgs
    );
    $this->avatar->attributes[]                = "autofocus";
    $this->avatar->label                       = $i18n->t("Avatar");
    $this->birthday->attributes["title"]       = $i18n->t("The date must be between {0} (120 years) and {1} (6 years)", [
      $i18n->formatDate($this->birthday->min, $this->user->timeZoneId, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE),
      $i18n->formatDate($this->birthday->max, $this->user->timeZoneId, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE)
    ]);
    $this->birthday->label                     = $i18n->t("Date of Birth");
    $this->birthday->setHelp($i18n->t("Your birthday will be displayed on your profile page and is used to create demographic evaluations."));
    $this->country->label                      = $i18n->t("Country");
    $this->language->label                     = $i18n->t("Language");
    $this->private->label                      = $i18n->t("Keep my data private!");
    $this->private->setHelp($i18n->t(
        "Check the following box if you’d like to hide your private data on your profile page. Your data will only be " .
        "used by MovLib for anonymous demographical evaluation of usage statistics and ratings. By providing basic data " .
        "like sex and country, scientists around the world are enabled to research the human interests in movies more " .
        "closely. Of course your real name won’t be used for anything!"
    ));
    $this->profile->attributes["placeholder"]  = $i18n->t("Tell others about yourself, what do you do, what do you like, …");
    $this->profile->label                      = $i18n->t("About You");
    $this->realName->attributes["placeholder"] = $i18n->t("Entery our real name");
    $this->realName->label                     = $i18n->t("Real Name");
    $this->sex->legend                         = $i18n->t("Sex");
    $this->sex->setHelp($i18n->t("Your sex will be displayed on your profile page and is used to create demographic evaluations."));
    $this->timezone->label                     = $i18n->t("Time Zone");
    $this->website->label                      = $i18n->t("Website");
    $this->form->attributes["enctype"]         = Form::ENCTYPE_BINARY;

    return
      $this->form->open() .
      "<div class='row'>" .
        "<div class='span span--2'>{$this->getImage($this->user, User::IMAGE_STYLE_DEFAULT, [
          "alt" => $i18n->t("Your current avatar."),
        ])}</div>" .
        "<div class='span span--7'>{$this->avatar}</div>" .
      "</div>" .
        $this->realName .
        $this->sex .
        $this->birthday .
        $this->profile .
        $this->language .
        $this->country .
        $this->timezone .
        $this->website .
        $this->private .
      $this->form->close()
    ;
  }

  /**
   * Validate data that couldn't be auto-validated and update the user's account settings.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   */
  public function validate() {
    global $i18n;
    $this->user->birthday           = $this->birthday->value;
    $this->user->countryId          = $this->country->value;
    $this->user->private            = $this->private->value;
    $this->user->profile            = $this->profile->value;
    $this->user->realName           = $this->realName->value;
    $this->user->sex                = $this->sex->value;
    $this->user->systemLanguageCode = $this->language->value;
    $this->user->timeZoneId         = $this->timezone->value;
    $this->user->website            = $this->website->value;
    $this->user->commit();
    $success                        = new Alert($i18n->t("Your account settings were updated successfully."));
    $success->title                 = $i18n->t("Account Settings Updated Successfully");
    $success->severity              = Alert::SEVERITY_SUCCESS;
    $this->alerts                  .= $success;
    return $this;
  }

}
