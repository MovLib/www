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

use \MovLib\Data\DateTimeZone;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Country;
use \MovLib\Presentation\Partial\Currency;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputCheckbox;
use \MovLib\Presentation\Partial\FormElement\InputDate;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputImage;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputText;
use \MovLib\Presentation\Partial\FormElement\InputURL;
use \MovLib\Presentation\Partial\FormElement\RadioGroup;
use \MovLib\Presentation\Partial\FormElement\Select;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;
use \MovLib\Presentation\Partial\Language;
use \MovLib\Presentation\Partial\FormElement\InputSex;

/**
 * Allows the user to manage his personalized settings.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AccountSettings extends \MovLib\Presentation\Profile\Show {
  use \MovLib\Presentation\TraitForm;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user account settings presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\Session $session
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function __construct() {
    global $i18n, $kernel, $session;

    // Disallow caching of account settings.
    session_cache_limiter("nocache");
    $kernel->cacheable = false;

    $session->checkAuthorization($i18n->t("You need to sign in to access this page."));
    $session->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."));

    $this->init($i18n->t("Account Settings"), "/profile/account-settings", [[ $i18n->r("/profile"), $i18n->t("Profile") ]]);

    if (isset($_GET["delete_avatar"])) {
      $this->user->deleteAvatar()->commit();
      $kernel->alerts .= new Alert(
        $i18n->t("Your avatar image was deleted successfully"),
        $i18n->t("Avatar Deleted Successfully"),
        Alert::SEVERITY_SUCCESS
      );
      throw new SeeOtherRedirect($kernel->requestPath);
    }

//    $this->formAddElement(new InputImage(self::FORM_AVATAR, $i18n->t("Avatar")));

    $this->formAddElement(new InputText("real_name", $i18n->t("Real Name"), $this->user->realName, [
      "#help-popup" => $i18n->t("Your real name will be displayed on your profile page."),
      "placeholder" => $i18n->t("Enter our real name"),
    ]));

    $this->formAddElement(new InputSex("sex", $i18n->t("Sex"), $this->user->sex, [
      "#help-popup" => $i18n->t("Your sex will be displayed on your profile page and is used to create demographic evaluations."),
    ]));

    $birthdateMax = (new \DateTime())->sub(new \DateInterval("P6Y"));
    $birthdateMin = (new \DateTime())->sub(new \DateInterval("P120Y"));
    $this->formAddElement(new InputDate("birthdate", $i18n->t("Date of Birth"), $this->user->birthday, [
      "#help-popup" => $i18n->t("Your birthday will be displayed on your profile page and is used to create demographic evaluations."),
      "max"         => $birthdateMax,
      "min"         => $birthdateMin,
      "title"       => $i18n->t("A birth date must be between {min} (120 years) and {max} (6 years)", [
        "max" => (new \IntlDateFormatter($i18n->locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE))->format($birthdateMax),
        "min" => (new \IntlDateFormatter($i18n->locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE))->format($birthdateMin),
      ]),
    ]));

    $this->formAddElement(new InputURL("website", $i18n->t("Website"), $this->user->website, [
      "#help-popup"         => $i18n->t("Your website will be display on your profile page."),
      "data-allow-external" => "true",
    ]));

    $this->formAddElement(new InputHTML("about_me", $i18n->t("About Me"), $this->user->aboutMe, [
      "placeholder" => $i18n->t("Tell others about yourself, what do you do, what do you like, …"),
    ], [ "blockquote", "external", "headings", "lists", ]));

    $this->formAddElement(Country::getSelectFormElement($this->user->countryCode, [
      "#help-popup" => $i18n->t("Your country will be displayed on your profile page and is used to create demographic evaluations."),
    ]));

    $this->formAddElement(new Select("tzid", $i18n->t("Time Zone"), DateTimeZone::getTranslatedIdentifiers(), $this->user->timeZoneIdentifier, [
      "#help-popup" => $i18n->t("Your time zone will be used to display any time related information correctly."),
    ]));

    $this->formAddElement(Currency::getSelectFormElement($this->user->currencyCode, [
      "#help-popup" => $i18n->t("Your currency will be used to display any money related information correctly."),
    ]));

    $langOptions = null;
    foreach ($kernel->systemLanguages as $code => $locale) {
      $langOptions[$code] = \Locale::getDisplayLanguage($code, $i18n->locale);
    }
    $i18n->getCollator()->asort($langOptions);
    $this->formAddElement(new RadioGroup("language", $i18n->t("System Language"), $langOptions, $this->user->systemLanguageCode, [
      "#help-popup" => $i18n->t(
        "Select your preferred system language, this will be used to redirect you if you visit {sitename} without a " .
        "subdomain and may be from other use in the future.", [ "sitename" => $kernel->siteName ]
      ),
    ]));

    $this->formAddElement(new InputCheckbox("private", $i18n->t("Keep my data private!"), $this->user->private, [
      "#help-popup" => $i18n->t(
        "Check the following box if you’d like to hide your private data on your profile page. Your data will only be " .
        "used by {sitename} for anonymous demographical evaluation of usage statistics and ratings. By providing basic " .
        "data like sex and country, scientists around the world are enabled to research the human interests in movies " .
        "more closely. Of course your real name won’t be used for anything!",
        [ $kernel->siteName ]
      ),
    ]));

    $this->formAddAction($i18n->t("Update"), [ "class" => "btn btn-large btn-success" ]);
    $this->formInit();

    // Display delete button if the user just uploaded a new avatar or one is already present.
//    if ($this->user->imageExists === true) {
//      $this->avatar->inputFileAfter = $this->a("?delete_avatar=true", $i18n->t("Delete"), [ "class" => "btn btn-danger"]);
//    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inhertidoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getBreadcrumbs() {
    global $i18n;
    return [[ $i18n->r("/profile"), $i18n->t("Profile") ]];
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    return $this->formRender();
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function formValid() {
    global $i18n;
//    if ($this->avatar->path) {
//      $this->user->upload($this->avatar->path, $this->avatar->extension, $this->avatar->height, $this->avatar->width);
//    }
//    $this->user->birthday           = $this->birthday->value;
//    $this->user->countryCode        = $this->country->value;
//    $this->user->currencyCode       = $this->currency->value;
//    $this->user->private            = $this->private->value;
//    $this->user->aboutMe            = $this->aboutMe->value;
//    $this->user->realName           = $this->realName->value;
//    $this->user->sex                = $this->sex->value;
//    $this->user->systemLanguageCode = $this->language->value;
//    $this->user->timeZoneIdentifier = $this->timezone->value;
//    $this->user->website            = $this->website->value;
//    $this->user->commit();
//    $this->alerts                  .= new Alert(
//      $i18n->t("Your account settings were updated successfully."),
//      $i18n->t("Account Settings Updated Successfully"),
//      Alert::SEVERITY_SUCCESS
//    );
    return $this;
  }

}
