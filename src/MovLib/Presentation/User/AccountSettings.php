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

use \MovLib\Data\User;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputCheckbox;
use \MovLib\Presentation\Partial\FormElement\InputDate;
use \MovLib\Presentation\Partial\FormElement\InputFile;
use \MovLib\Presentation\Partial\FormElement\InputRadio;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputText;
use \MovLib\Presentation\Partial\FormElement\InputUrl;
use \MovLib\Presentation\Partial\FormElement\Select;
use \MovLib\Presentation\Partial\FormElement\Textarea;

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
  use \MovLib\Presentation\User\UserTrait;


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
   * @var \MovLib\Presentation\Partial\FormElement\InputRadio
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
   * @var \MovLib\Presentation\Partial\FormElement\InputUrl
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

    // @todo Avatar Upload
    $this->avatar = new InputFile();

    $this->realName = new InputText("real_name", [
      "placeholder" => $i18n->t("Enter your real name"),
      "title"       => $i18n->t("Please enter your real name in this field."),
    ], $this->user->realName);
    $this->realName->label = $i18n->t("Real Name");

    // @todo System Language (ID)
    $this->language = new Select();

    // @todo Timezone Selection
    $this->timezone = new Select();

    // @todo Dynamic Profile
    $this->profile = new Textarea();

    // @todo Sex
    $this->sex = new InputRadio();

    // @todo Country (ID)
    $this->country = new Select();

    // @todo Date of Birth
    $this->birthday = new InputDate();

    // We don't validate the existens of the user's website (respectively homepage).
    $this->website = new InputUrl("website", [
      "title" => $i18n->t("Please enter your website address in this field. The address must start with either HTTP or HTTPS."),
    ], $this->user->website);
    $this->website->label = $i18n->t("Website");

    // @todo Facebook
    //$this->facebook = ?

    // @todo Google Plus
    //$this->googlePlus = ?

    // @todo Twitter
    //$this->twitter = ?

    // @todo Private Flag
    $this->private = new InputCheckbox();

    $this->form = new Form($this, [
      $this->realName,
      $this->website,
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
   * @return this
   */
  public function validate() {

  }

}
