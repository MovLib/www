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

use \MovLib\Data\Movie\Movie;
use \MovLib\Data\Movie\MovieRatings;
use \MovLib\Data\User\FullUser;
use \MovLib\Presentation\Partial\Country;
use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\Time;

/**
 * Public user profile presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user we are currently displaying.
   *
   * @var \MovLib\Data\User\FullUser
   */
  protected $user;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   * @throws \MovLib\Presentation\Redirect\Permanent
   */
  public function __construct() {
    global $i18n, $kernel;
    $kernel->stylesheets[] = "user";
    $this->user = new FullUser(FullUser::FROM_NAME, $_SERVER["USER_NAME"]);
    $this->initBreadcrumb([[ $i18n->rp("/users"), $i18n->t("Users") ]]);
    $routeArgs = [ $this->user->filename ];
    $this->sidebarInit([
      [ $i18n->r("/user/{0}/uploads", $routeArgs), "{$i18n->t("Uploads")} <span class='fr'>{$i18n->format("{0,number}", [ $this->user->getTotalUploadsCount() ])}</span>" ],
      [ $i18n->r("/user/{0}/collection", $routeArgs), "{$i18n->t("Collection")} <span class='fr'>{$i18n->format("{0,number}", [ $this->user->getTotalCollectionCount() ])}</span>" ],
      [ $i18n->r("/user/{0}/contact", $routeArgs), $i18n->t("Contact") ],
    ]);
    $this->initPage($this->user->name);
    $this->initLanguageLinks("/user/{0}", $routeArgs);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   */
  protected function getPageContent(){
    global $i18n, $kernel, $session;

    // http://schema.org/Person
    $this->schemaType = "Person";

    // Mark the username as additional name.
    $this->headingSchemaProperty = "additionalName";

    // Wrap the complete header content in a row and the heading itself in a span.
    $this->headingBefore = "<div class='r'><div class='s s10'>";

    // Create user info.
    $personalData = null;

    // Format the user's birthday if available.
    if ($this->user->birthday) {
      $date = new Date($this->user->birthday);
      $personalData[] = "<time itemprop='birthDate' datetime='{$date->dateValue}'>{$date->getAge()}</time>";
    }
    if ($this->user->sex > 0) {
      $gender     = $this->user->sex === 1 ? $i18n->t("Male") : $i18n->t("Female");
      $personalData[] = "<span itemprop='gender'>{$gender}</span>";
    }
    if ($this->user->countryCode) {
      $country        = new Country($this->user->countryCode);
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

    $avatar = $this->getImage($this->user->getStyle(), false, [ "itemprop" => "image" ]);

    // Display additional info about this user after the name and the avatar to the right of it.
    $this->headingAfter = "{$personalData}<small>{$i18n->t("Joined {date} and was last seen {time}.", [
      "date" => (new Date($this->user->created))->intlFormat(),
      "time" => (new Time($this->user->access))->formatRelative(),
    ])}</small></div><div class='s s2'>{$avatar}</div></div>";

    $publicProfile = $edit = null;

    // ----------------------------------------------------------------------------------------------------------------- About Me

    $aboutMe = null;
    if (empty($this->user->aboutMe) && $session->userId === $this->user->id) {
      $aboutMe = "<p>{$i18n->t("Your profile is currently empty, {0}click here to edit{1}.", [
        "<a href='{$i18n->r("/profile/account-settings")}'>", "</a>"
      ])}</p>";
    }
    else {
      $aboutMe = $this->htmlDecode($this->user->aboutMe);
      if ($session->userId === $this->user->id) {
        $edit = "<a class='small edit' href='{$i18n->r("/profile/account-settings")}'>{$i18n->t("edit")}</a>";
      }
    }
    if ($aboutMe) {
      $publicProfile .= "<h2>{$i18n->t("About Me")}{$edit}</h2><div itemprop='description'>{$aboutMe}</div>";
    }

    // ----------------------------------------------------------------------------------------------------------------- Rating Stream

    $publicProfile .= "<h2>{$i18n->t("Recently Rated Movies")}</h2>";
    $ratings        = $this->user->getTotalRatingsCount();
    if ($ratings === 0) {
      if ($session->userId === $this->user->id) {
        $publicProfile .= "<p>{$i18n->t("You haven’t rated a single movie yet, use the {0}search{1} to explore movies you already know.", [
          "<a href='{$i18n->r("/search")}'>", "</a>"
        ])}</p>";
      }
      else {
        $publicProfile .= "<p>{$i18n->t("{username} hasn’t rated a single movie yet, that makes us a sad panda.", [
          "username" => $this->user->name
        ])}</p>";
      }
    }
    else {
      $publicProfile .= "<table id='movie-rating'>";
      $movieRatings   = (new MovieRatings())->getOrderedByCreated(MovieRatings::FROM_USER_ID, $this->user->id);
      $c = count($movieRatings);
      for ($i = 0; $i < $c; ++$i) {
        $movie = new Movie($movieRatings[$i]->movieId);

        // Format the display title for the list.
        if ($movie->year) {
          $title = $i18n->t("{0} ({1})", [ $movie->displayTitle, $movie->year ]);
        }
        else {
          $title = "<span itemprop='name'>{$movie->displayTitle}</span>";
        }

        // Append the original title to the output if it differs from the localized title.
        if ($movie->displayTitle != $movie->originalTitle) {
          $title .= "<br><span class='small'>{$i18n->t("Original title: “{original_title}”", [
            "original_title" => "<span>{$movie->originalTitle}</span>",
          ])}</span>";
        }

        $publicProfile .=
          "<tr class='rating'>" .
            "<td>{$this->getImage($movie->displayPoster->getStyle(\MovLib\Data\Image\MoviePoster::STYLE_SPAN_01), false)}<td>" .
            "<td>{$this->a($i18n->r("/movie/{0}", [ $movie->id ]), $title)}<td>" .
            "<td><span class='star'>{$movieRatings[$i]->rating}</span><td>" .
          "</tr>";
      }
      $publicProfile .= "</table>";
    }

    return $publicProfile;
  }

}
