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

use \MovLib\Partial\Date;

/**
 * Public user profile presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\User\AbstractUserPresenter {

  /**
   * Instantiate new user presentation.
   */
  public function init() {
    return $this->initPage(null);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getContent(){
    // http://schema.org/Person
    $this->schemaType = "Person";

    // Mark the username as additional name.
    $this->headingSchemaProperty = "additionalName";

    // Wrap the complete header content in a row and the heading itself in a span.
    $this->headingBefore = "<div class='r'><div class='s s10'>";

    // Create user info.
    $personalData = null;

    // Format the user's birthday if available.
    if ($this->user->birthdate) {
      $age = (new Date($this->intl, $this))->getAge($this->user->birthdate);
      $personalData[] = "<time datetime='{$this->user->birthdate}' property='birthDate'>{$age}</time>";
    }
    if ($this->user->sex > 0) {
      $gender     = $this->user->sex === 1 ? $this->intl->t("Male") : $this->intl->t("Female");
      $personalData[] = "<span itemprop='gender'>{$gender}</span>";
    }
    if ($this->user->countryCode) {
//      $country        = new Country($this->user->countryCode);
//      $personalData[] = "<span itemprop='nationality'>{$country}</span>";
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

    // Display additional info about this user after the name and the avatar to the right of it.
    $this->headingAfter =
        $personalData .
        "<small>{$this->intl->t("Joined {date} and was last seen {time}.", [
          "date" => ""/*(new Date($this->user->created))->intlFormat()*/,
          "time" => ""/*(new Time($this->user->access))->formatRelative()*/,
        ])}</small>" .
      "</div>" .
      $this->img($this->user->imageGetStyle(), [ "class" => "s s2", "property" => "image" ], false) .
    "</div>";

    $publicProfile = $edit = null;

    // ----------------------------------------------------------------------------------------------------------------- About Me

    $aboutMe = null;
    if (empty($this->user->aboutMe) && $this->session->userId === $this->user->id) {
      $aboutMe = "<p>{$this->intl->t("Your profile is currently empty, {0}click here to edit{1}.", [
        "<a href='{$this->intl->r("/profile/account-settings")}'>", "</a>"
      ])}</p>";
    }
    else {
      $aboutMe = $this->htmlDecode($this->user->aboutMe);
      if ($this->session->userId === $this->user->id) {
        $edit = "<a class='small edit' href='{$this->intl->r("/profile/account-settings")}'>{$this->intl->t("edit")}</a>";
      }
    }
    if ($aboutMe) {
      $publicProfile .= "<h2>{$this->intl->t("About Me")}{$edit}</h2><div itemprop='description'>{$aboutMe}</div>";
    }

    // ----------------------------------------------------------------------------------------------------------------- Rating Stream

    $publicProfile .= "<h2>{$this->intl->t("Recently Rated Movies")}</h2>";
    if ($this->session->userId === $this->user->id) {
      $noRatingsText = $this->intl->t(
        "You haven’t rated a single movie yet, use the {0}search{1} to explore movies you already know.",
        [ "<a href='{$this->intl->r("/search")}'>", "</a>" ]
      );
    }
    else {
      $noRatingsText = $this->intl->t(
        "{username} hasn’t rated a single movie yet, that makes us a sad panda.",
        [ "username" => $this->user->name ]
      );
    }

//    $ratings = Movie::getUserRatings($this->user->id);
    $ratingStream = null;
//    /* @var $movie \MovLib\Data\Movie\FullMovie */
//    while ($movie = $ratings->fetch_object("\\MovLib\\Data\\Movie\\FullMovie")) {
//      // We have to use different micro-data if display and original title differ.
//      if ($movie->displayTitle != $movie->originalTitle) {
//        $displayTitleItemprop = "alternateName";
//        $movie->originalTitle = "<br><span class='small'>{$this->intl->t("{0} ({1})", [
//          "<span itemprop='name'{$this->lang($movie->originalTitleLanguageCode)}>{$movie->originalTitle}</span>",
//          "<i>{$this->intl->t("original title")}</i>",
//        ])}</span>";
//      }
//      // Simplay clear the original title if it's the same as the display title.
//      else {
//        $displayTitleItemprop = "name";
//        $movie->originalTitle = null;
//      }
//      $movie->displayTitle = "<span class='link-color' itemprop='{$displayTitleItemprop}'{$this->lang($movie->displayTitleLanguageCode)}>{$movie->displayTitle}</span>";
//
//      // Append year enclosed in micro-data to display title if available.
//      if (isset($movie->year)) {
//        $movie->displayTitle = $this->intl->t("{0} ({1})", [ $movie->displayTitle, "<span itemprop='datePublished'>{$movie->year}</span>" ]);
//      }
//
//      $ratingInfo = null;
//      $ratingData = $movie->getUserRating($this->user->id);
//      if ($ratingData !== null) {
//        $rating = str_repeat("<img alt='' height='20' src='{$this->getURL("asset://star.svg")}' width='24'>", $ratingData["rating"]);
//        $ratingTime = (new Time($ratingData["created"]))->formatRelative();
//        $ratingInfo = "<div class ='rating-user tar' title='{$this->intl->t("{user}’s rating", [ "user" => $this->user->name])}'>{$rating}<br><small>{$ratingTime}</small></div>";
//      }
//
//      // Construct the genre listing.
//      $genres = null;
//      $result = $movie->getGenres();
//      $route  = $this->intl->r("/genre/{0}");
//      while ($row = $result->fetch_assoc()) {
//        if ($genres) {
//          $genres .= "&nbsp;";
//        }
//        $row["route"] = str_replace("{0}", $row["id"], $route);
//        $genres      .= "<a class='label' href='{$row["route"]}' itemprop='genre'>{$row["name"]}</a>";
//      }
//      if ($genres) {
//        $genres = "<p class='small'>{$genres}</p>";
//      }
//
//      // Put the movie list entry together.
//      $ratingStream .=
//        "<li class='s10' itemtype='http://schema.org/Movie' itemscope>" .
//          "<div class='hover-item no-link r'>" .
//            "<div class='s s1 tac'>" .
//              $this->getImage($movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_01), false, [ "itemprop" => "image" ]) .
//            "</div>" .
//            $ratingInfo .
//            "<span class='s s7'><p><a href='{$movie->route}' itemprop='url'>{$movie->displayTitle}</a>{$movie->originalTitle}</p>{$genres}</span>" .
//          "</a>" .
//        "</li>"
//      ;
//    }

    if ($ratingStream) {
      $publicProfile .= "<ol class='hover-list no-list'>{$ratingStream}</ol>";
    }
    else {
      $publicProfile .= $this->callout($noRatingsText, $this->intl->t("Nothing Rated"), "info");
    }

    return $publicProfile;
  }

}
