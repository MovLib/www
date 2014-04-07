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

use \MovLib\Data\Date;
use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Partial\Alert;
use \MovLib\Partial\Country;
use \MovLib\Partial\Currency;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputCheckbox;
use \MovLib\Partial\FormElement\InputDate;
use \MovLib\Partial\FormElement\InputImage;
use \MovLib\Partial\FormElement\InputSex;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Partial\FormElement\InputURL;
use \MovLib\Partial\FormElement\RadioGroup;
use \MovLib\Partial\FormElement\Select;
use \MovLib\Partial\FormElement\TextareaHTML;

/**
 * Defines the profile account settings presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class AccountSettings extends \MovLib\Presentation\Profile\AbstractProfilePresenter {

  /**
   * {@inheritdoc}
   * @throws \MovLib\Exception\ClientException\UnauthorizedException
   *   If the user isn't signed in at all or the authentication has exceeded the grace time.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If the user with the unique identifier from the session couldn't be found in the database.
   */
  public function init() {
    return $this->initProfilePresentation(
      $this->intl->t("You must be signed in to edit your account settings."),
      $this->intl->t("Account Settings"),
      "/profile/account-settings",
      true,
      $this->intl->t("Please sign in again to verify the legitimacy of this request.")
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $deleteAvatarKey = $this->intl->r("delete_avatar");
    if ($this->request->filterInput(INPUT_GET, $deleteAvatarKey, FILTER_VALIDATE_BOOLEAN) === true) {
      $this->user->deleteAvatar($this->session);
      $this->alerts .= new Alert(
        $this->intl->t("Your avatar image was deleted successfully."),
        $this->intl->t("Avatar Deleted Successfully"),
        Alert::SEVERITY_SUCCESS
      );
      throw new SeeOtherException($this->request->path);
    }

    // Display delete button if the user just uploaded a new avatar or one is already present.
    $inputFileAfter = null;
    if ($this->user->imageExists) {
      $inputFileAfter = "<a class='btn btn-danger' href='{$this->request->path}?{$deleteAvatarKey}=1'>{$this->intl->t("Delete")}</a>";
    }

    $birthdateMax = (new Date())->sub(new \DateInterval("P6Y"));
    $birthdateMin = (new Date())->sub(new \DateInterval("P120Y"));

    $languageOptions = [];
    foreach ($this->config->locales as $code => $locale) {
      $languageOptions[$code] = \Locale::getDisplayLanguage($locale, $this->intl->locale);
    }
    (new \Collator($this->intl->locale))->asort($languageOptions);

    return (new Form($this->diContainerHTTP))
      ->addElement(new InputImage($this->diContainerHTTP, "avatar", $this->intl->t("Avatar"), $this->user, null, $inputFileAfter))
      ->addElement(new InputText($this->diContainerHTTP, "real_name", $this->intl->t("Real Name"), $this->user->realName, [
        "#help-popup" => $this->intl->t("Your real name will be displayed on your profile page."),
        "placeholder" => $this->intl->t("Enter our real name"),
      ]))
      ->addElement(new InputSex($this->diContainerHTTP, "sex", $this->intl->t("Sex"), $this->user->sex, [
        "#help-popup" => $this->intl->t("Your sex will be displayed on your profile page and is used to create demographic evaluations."),
      ]))
      ->addElement(new InputDate($this->diContainerHTTP, "birthdate", $this->intl->t("Date of Birth"), $this->user->birthdate, [
        "#help-popup" => $this->intl->t("Your birthday will be displayed on your profile page and is used to create demographic evaluations."),
        "max"         => $birthdateMax,
        "min"         => $birthdateMin,
        "title"       => $this->intl->t(
          "A birth date must be between {min} (120 years) and {max} (6 years)",
          [ "max" => $birthdateMax->formatIntl($this->intl->locale), "min" => $birthdateMin->formatIntl($this->intl->locale), ]
        ),
      ]))
      ->addElement(new InputURL($this->diContainerHTTP, "website", $this->intl->t("Website"), $this->user->website, [
        "#help-popup"         => $this->intl->t("Your website will be display on your profile page."),
        "data-allow-external" => "true",
      ]))
      ->addElement(new TextareaHTML($this->diContainerHTTP, "about_me", $this->intl->t("About Me"), $this->user->aboutMe, [
        "placeholder" => $this->intl->t("Tell others about yourself, what do you do, what do you like, …"),
      ], [ "blockquote", "external", "headings", "lists", ]))
      ->addElement((new Country())->getSelectFormElement($this->diContainerHTTP, $this->user->countryCode, [
        "#help-popup" => $this->intl->t("Your country will be displayed on your profile page and is used to create demographic evaluations."),
      ]))
      ->addElement(new Select($this->diContainerHTTP, "tzid", $this->intl->t("Time Zone"), $this->intl->getTranslations("timezones"), $this->user->timezone, [
        "#help-popup" => $this->intl->t("Your time zone will be used to display any time related information correctly."),
      ]))
      ->addElement((new Currency())->getSelectFormElement($this->diContainerHTTP, $this->user->currencyCode, [
        "#help-popup" => $this->intl->t("Your currency will be used to display any money related information correctly."),
      ]))
      ->addElement(new RadioGroup($this->diContainerHTTP, "language", $this->intl->t("System Language"), $languageOptions, $this->user->languageCode, [
        "#help-popup" => $this->intl->t(
          "Select your preferred system language, this will be used to redirect you if you visit {sitename} without a " .
          "subdomain and may be from other use in the future.",
          [ "sitename" => $this->config->sitename ]
        ),
      ]))
      ->addElement(new InputCheckbox($this->diContainerHTTP, "private", $this->intl->t("Keep my data private!"), $this->user->private, [
        "#help-popup" => $this->intl->t(
          "Check the following box if you’d like to hide your private data on your profile page. Your data will only be " .
          "used by {sitename} for anonymous demographical evaluation of usage statistics and ratings. By providing basic " .
          "data like sex and country, scientists around the world are enabled to research the human interests in movies " .
          "more closely. Of course your real name won’t be used for anything!",
          [ $this->config->sitename ]
        ),
      ]))
      ->addAction($this->intl->t("Update"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "valid" ])
    ;
  }

  /**
   * Auto-validation of the form succeeded.
   *
   * @return this
   */
  public function valid() {
//    if ($this->avatar->path) {
//      $this->user->upload($this->avatar->path, $this->avatar->extension, $this->avatar->height, $this->avatar->width);
//    }
    $this->user->updateAccount();
    $this->alerts .= new Alert(
      $this->intl->t("Your account settings were updated successfully."),
      $this->intl->t("Account Settings Updated Successfully"),
      Alert::SEVERITY_SUCCESS
    );
    return $this;
  }

}
