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
namespace MovLib\Presentation\User;

use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\Time;
use \MovLib\Data\User\Full as UserFull;
use \MovLib\Exception\Client\ErrorNotFoundException;
use \MovLib\Exception\Client\RedirectPermanentException;
use \MovLib\Exception\UserException;
use \MovLib\Presentation\Partial\Country;

/**
 * @todo Description of Show
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\User\TraitUser;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user we are currently displaying.
   *
   * @var \MovLib\Data\Full
   */
  protected $user;

  /**
   * Route to profile account settings.
   *
   * @var string
   */
  protected $routeAccountSettings;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @throws \MovLib\Exception\NotFoundException
   * @throws \MovLib\Exception\Client\RedirectPermanentException
   */
  public function __construct() {
    global $i18n, $kernel, $session;
    try {
      $this->user = new UserFull(UserFull::FROM_NAME, $_SERVER["USER_NAME"]);
      if ($this->user->route != $kernel->requestURI) {
        throw new RedirectPermanentException($this->user->route);
      }
      $this->init($this->user->name);
      $this->routeAccountSettings = $i18n->r("/profile/account-settings");

      // http://schema.org/Person
      $this->schemaType = "Person";

      // Mark the username as additional name.
      $this->headingSchemaProperty = "additionalName";

      // Wrap the complete header content in a row and the heading itself in a span.
      $this->headingBefore = "<div class='row'><div class='span span--10'>";

      // Create user info.
      $personalData = null;

      // Format the user's birthday if available.
      if ($this->user->birthday) {
        $date = new Date($this->user->birthday);
        $personalData[] = "<time itemprop='birthDate' datetime='{$date->format()}'>{$date->getAge()}</time>";
      }
      if ($this->user->sex > 0) {
        $gender     = $this->user->sex === 1 ? $i18n->t("Male") : $i18n->t("Female");
        $personalData[] = "<span itemprop='gender'>{$gender}</span>";
      }
      if ($this->user->countryId) {
        $country    = new Country(Country::FROM_ID, $this->user->countryId);
        $personalData[] = "<span itemprop='nationality'>{$country}</span>";
      }

      // Link the user's real name to the website if we have both properties.
      if ($this->user->realName) {
        // http://microformats.org/wiki/rel-me
        // http://microformats.org/wiki/rel-nofollow
        if ($this->user->website) {
          array_unshift($personalData, "<a href='{$this->user->website}' itemprop='url name' rel='me nofollow' target='_blank'>{$this->user->realName}</a>");
        }
        else {
          array_unshift($personalData, "<span itemprop='name'>{$this->user->realName}</span>");
        }
      }
      // If not use the hostname.
      elseif ($this->user->website) {
        $hostname = parse_url($this->user->website, PHP_URL_HOST);
        $personalData[] = "<br><a href='{$this->user->website}' itemprop='url' rel='nofollow' target='_blank'>{$hostname}</a>";
      }

      if ($personalData) {
        $personalData = implode(", ", $personalData);
        $personalData = "<p>{$personalData}</p>";
      }

      $avatar = $this->getImage($this->user->getImageStyle(), false, [ "itemprop" => "image" ]);
      if ($session->userId === $this->user->id) {
        $avatar = "<a class='img text-center' href='{$this->routeAccountSettings}'>{$avatar}<span>{$i18n->t("Change Avatar")}</span></a>";
      }

      // Display additional info about this user after the name and the avatar to the right of it.
      $this->headingAfter = "{$personalData}<small>{$i18n->t("Joined {0} and was last seen {1}.", [
        (new Date($this->user->created))->intlFormat(),
        (new Time($this->user->access))->formatRelative(),
      ])}</small></div><div class='span span--2'>{$avatar}</div></div>";
    }
    catch (UserException $e) {
      throw new ErrorNotFoundException("No user with this name.");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  protected function getPageContent(){
    global $i18n, $session;

    // -------------------- About Me

    $aboutMe = $edit = null;
    if (empty($this->user->aboutMe) && $session->userId === $this->user->id) {
      $aboutMe = "<p>{$i18n->t("Your profile is currently empty, {0}click here to edit{1}.", [
        "<a href='{$this->routeAccountSettings}?autofocus=about_me'>", "</a>"
      ])}</p>";
    }
    else {
      $aboutMe = $this->htmlRaw($this->user->aboutMe);
      if ($session->userId === $this->user->id) {
        $edit = "<a class='small edit' href='{$this->routeAccountSettings}?autofocus=about_me'>{$i18n->t("edit")}</a>";
      }
    }
    if ($aboutMe) {
      $aboutMe = "<h2>{$i18n->t("About Me")}{$edit}</h2><div itemprop='description'>{$aboutMe}</div>";
    }

    return "{$aboutMe}";
  }

}
