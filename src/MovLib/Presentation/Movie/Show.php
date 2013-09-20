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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\MovieImage;
use \MovLib\Data\Rating;
use \MovLib\Exception\Client\NotFoundException;
use \MovLib\Exception\MovieException;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Lists;

/**
 * The movie display page.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Movie\AbstractMoviePage {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The rating of the user.
   *
   * @var \MovLib\Data\Rating
   */
  private $ratingModel;


  // ------------------------------------------------------------------------------------------------------------------- Magic methods


  /**
   * Instatiate new movie presentation.
   */
  public function __construct() {
    global $i18n;
    try {
      $this->initMovie();
      $this->init($this->title);
      if (isset($this->model->year)) {
        $this->pageTitle = $this->model->getDisplayTitle() . " <small>({$this->model->year})</small>";
      }
      if ($this->model->deleted === true) {
        // Status code for "Gone".
        http_response_code(410);
        return;
      }
      $this->ratingModel = new Rating();
      // Configure headingBefore and headingAfter for the custom movie header.
      $displayPoster = $this->model->getDisplayPoster();
      $this->headingBefore = "<div class='row'><div class='span span--9' id='movie-show__header'>";
      $this->headingAfter = "{$this->getHeaderAdditions()}</div>{$this->getImage(
        $displayPoster,
        MovieImage::IMAGESTYLE_LARGE_FIXED_WIDTH,
        [ "alt" => "{$this->title} {$displayPoster->imageAlt}" ],
        $i18n->r("/movie/{0}/posters", [ $this->model->id ]),
        [ "class" => "span span--3", "id" => "movie-show__header__poster" ]
      )}</div>";
    }
    catch (MovieException $e) {
      throw new NotFoundException($e);
    }
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    if ($this->model->deleted === true) {
        $gone = new Alert(
          "<p>{$i18n->t("The deletion message is provided below for reference.")}</p>" .
          /** @todo Provide commit message with history implementation. */
          "<p>" .
            $i18n->t(
              "The movie has been deleted. A look at the edit {0}history{2} or {1}discussion{2} will explain why that " .
              "is the case. Please discuss with the person responsible for this deletion before you restore this entry " .
              "from its {0}history{2}.",
              [
                "<a href='{$i18n->r("/movie/{0}/history", [ $this->model->id ])}'>",
                "<a href='{$i18n->r("/movie/{0}/discussion", [ $this->model->id ])}'>",
                "</a>",
              ]
            ) .
          "</p>"
        );
        $gone->title = $i18n->t("This Movie has been deleted.");
        $gone->severity = Alert::SEVERITY_ERROR;
        return $gone;
    }
    // Numeric Array holding all the page's content points in a uniform way.
    // Format:
    //   [0] => [ "id" => "first section id",   "title" => "translated title", "content" => "section content" ]
    //   [1] => [ "id" => "second section id",  "title" => "translated title", "content" => "section content" ]
    $contents = [];

    // ----------------------------------------------------------------------------------------------------------------- Synopsis

    $contents[] = [
      "id"      => "synopsis",
      "title"   => $i18n->t("Synopsis"),
      "content" => $this->model->synopsis
    ];

    // ----------------------------------------------------------------------------------------------------------------- Directors

    $directors = $this->model->getDirectors();
    $c = count($directors);
    for ($i = 0; $i < $c; ++$i) {
      $directors[$i] = $this->a($i18n->r("/person/{0}", [ $directors[$i]["id"] ]), $directors[$i]["name"]);
    }
    $contents[] = [
      "id"      => "directors",
      "title"   => $i18n->t("Directors"),
      "content" => (new Lists($directors, ""))->toHtmlList(),
    ];

    // ----------------------------------------------------------------------------------------------------------------- Titles

    $contents[] = [
      "id"      => "titles",
      "title"   => $i18n->t("Titles"),
      "content" => (new Lists(array_column($this->model->getTitles(), "title"), ""))->toHtmlList(),
    ];

    // ----------------------------------------------------------------------------------------------------------------- Taglines

    $contents[] = [
      "id"      => "taglines",
      "title"   => $i18n->t("Taglines"),
      "content" => (new Lists(array_column($this->model->getTagLines(), "tagline"), ""))->toHtmlList(),
    ];

    // Construct the content from the content array.

    $content = "";
    $c = count($contents);
    for ($i = 0; $i < $c; ++$i) {
      $this->navigation->menuitems[] = [ "#{$contents[$i]["id"]}", $contents[$i]["title"], [ "title" => $i18n->t("Go to section") ] ];
      if (empty($contents[$i]["content"])) {
        $contents[$i]["content"] = $i18n->t("No {0} assigned yet, {1}add {0}{2}?", [
          $contents[$i]["title"],
          "<a href='{$i18n->r("/movie/{0}/edit-{1}", [ $contents[$i]["id"] ])}'>",
          "</a>",
        ]);
      }
      $content .=
        "<div id='{$contents[$i]["id"]}' class='movie-section'>" .
          "<h2>{$contents[$i]["title"]} <small>{$this->a($i18n->r("/movie/{0}/edit-{1}", [ $this->model->id, $contents[$i]["id"] ]), $i18n->t("Edit"))}</small></h2>" .
          $contents[$i]["content"] .
        "</div>"
      ;
    }
    return $content;
  }


  // ------------------------------------------------------------------------------------------------------------------- Helper methods


  /**
   * Get the additional HTML content after the title for the custom movie header.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return string
   *   The addition movie header HTML content.
   */
  private function getHeaderAdditions() {
    global $i18n, $session;
    $additions = "";

    // ----------------------------------------------------------------------------------------------------------------- Original title

    $additions .= "<p>{$i18n->t("“{0}” ({1}original title{2})", [ $this->model->originalTitle, "<em>", "</em>" ])}</p>";

    // ----------------------------------------------------------------------------------------------------------------- Rating form + explanation

    // Calculate the width for the rating stars in px.
    $ratingWidth = ($this->model->rating * 19 + (ceil($this->model->rating) - 1) * 5);
    $ratingWidth = $ratingWidth < 0 ? 0 : "{$ratingWidth}px";

    // Construct the "Your rating" section for the rating explanation.
    if ($session->isAuthenticated === false) {
      $userRating = $i18n->t(
        "please {0}log in{1} to rate this movie",
        [ "<a href='{$i18n->r("/user/login")}' title='{$i18n->t("Click here to log in to your account.")}'>", "</a>" ]
      );
    }
    elseif (($userRating = $this->ratingModel->getMovieRating($session->userId, $this->model->id))) {
      $userRating = $i18n->t("your rating: ", [ $userRating ]);
    }
    else {
      $userRating = $i18n->t("you haven't rated this movie yet");
    }
    $additions .=
      "<form action='{$i18n->r("/movie/{0}", [ $this->model->id ])}' id='movie__rating' method='post'>" .
        "<span class='visuallyhidden'>{$i18n->t("Rating")}: </span>" .
        "<div id='movie__rating__back' style='width:{$ratingWidth}'>" .
          "<span>&#xe000;</span>" .
          "<span>&#xe000;</span>" .
          "<span>&#xe000;</span>" .
          "<span>&#xe000;</span>" .
          "<span>&#xe000;</span>" .
        "</div>" .
        "<div id='movie__rating__front'>" .
          "<label class='popup-container'>" .
            "<small class='popup popup--inverse'>{$i18n->t("Awful")}</small>" .
            "<button name='rating' title='{$i18n->t("Rate this movie with {0} stars.", [ 1 ])}' type='submit' value='1'>&#xe000;</button>" .
          "</label>" .
          "<label class='popup-container'>" .
            "<small class='popup popup--inverse'>{$i18n->t("Not that bad")}</small>" .
            "<button name='rating' title='{$i18n->t("Rate this movie with {0} stars.", [ 2 ])}' type='submit' value='2'>&#xe000;</button>" .
          "</label>" .
          "<label class='popup-container'>" .
            "<small class='popup popup--inverse'>{$i18n->t("Fair")}</small>" .
            "<button name='rating' title='{$i18n->t("Rate this movie with {0} stars.", [ 3 ])}' type='submit' value='3'>&#xe000;</button>" .
          "</label>" .
          "<label class='popup-container'>" .
            "<small class='popup popup--inverse'>{$i18n->t("Pretty Good")}</small>" .
            "<button name='rating' title='{$i18n->t("Rate this movie with {0} stars.", [ 4 ])}' type='submit' value='4'>&#xe000;</button>" .
          "</label>" .
          "<label class='popup-container'>" .
            "<small class='popup popup--inverse'>{$i18n->t("Great")}</small>" .
            "<button name='rating' title='{$i18n->t("Rate this movie with {0} stars.", [ 5 ])}' type='submit' value='5'>&#xe000;</button>" .
          "</label>" .
        "</div>" .
      "</form>" .
      "<p id='movie__rating__description' class='small'>" .
        $i18n->t(
          "Rated by {0}{1,number,integer} users{2} with an {3}average rating{4} of {5,number} ({6}).",
          [
            "<a href='{$i18n->r("/movie/{0}/rating-demographics", [ $this->model->id ])}' title='{$i18n->t("View Rating demographics")}'>",
            $this->model->votes,
            "</a>",
            "<a href='{$i18n->r("/help/average-rating")}' title='{$i18n->t("Go to help page: {0}", [ "Average Rating"] )}'>",
            "</a>",
            $this->model->rating,
            $userRating,
          ]) .
      "</p>"
    ;

    // ----------------------------------------------------------------------------------------------------------------- Countries

    $countries = $this->model->getCountries();
    $c = count($countries);
    for ($i = 0; $i < $c; ++$i) {
      $countries[$i] = $this->a($i18n->r("/country/{0}", [ $countries[$i]["code"] ]), $countries[$i]["name"]);
    }
    $countries = (new Lists($countries, $i18n->t("No {0} assigned yet, {1}add {0}{2}?", [ $i18n->t("countries"), "<a href='{$i18n->r("/movie/{0}/edit", [ $this->model->id ])}'>", "</a>" ])))->toCommaSeparatedList();
    $additions .= "<p class='small'><span class='visuallyhidden'>{$i18n->t("Countries")}: </span>{$countries}</p>";

    // ----------------------------------------------------------------------------------------------------------------- Runtime, Genres & Styles

    $genres = $this->model->getGenres();
    $c = count($genres);
    for ($i = 0; $i < $c; ++$i) {
      $genres[$i] = $this->a($i18n->r("/genre/{0}", [ $genres[$i]["id"] ]), $genres[$i]["name"]);
    }
    $genres = (new Lists($genres, $i18n->t("No {0} assigned yet, {1}add {0}{2}?", [ $i18n->t("genres"), "<a href='{$i18n->r("/movie/{0}/edit-genres", [ $this->model->id ])}'>", "</a>" ])))->toCommaSeparatedList();
    $styles = $this->model->getStyles();
    $c = count($styles);
    for ($i = 0; $i < $c; ++$i) {
      $styles[$i] = $this->a($i18n->r("style/{0}", [ $styles[$i]["id"] ]), $styles[$i]["name"]);
    }
    $styles = (new Lists($styles, $i18n->t("No {0} assigned yet, {1}add {0}{2}?", [ $i18n->t("styles"), "<a href='{$i18n->r("/movie/{0}/edit-styles", [ $this->model->id ])}'>", "</a>"])))->toCommaSeparatedList();

    $additions .=
      "<p class='small'>" .
        "<span class='visuallyhidden'>{$i18n->t("Length")}: </span> {$this->model->runtime} {$i18n->t("min.")}" .
        " | <span class='visuallyhidden'>{$i18n->t("Genres")}: </span> {$genres}" .
        " | <span class='visuallyhidden'>{$i18n->t("Styles")}: </span> {$styles}" .
      "</p>"
    ;

    return $additions;
  }

}
