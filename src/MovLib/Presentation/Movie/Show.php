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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Movie\Full as MovieFull;
use \MovLib\Presentation\Partial\Lists\GlueSeparated;
use \MovLib\Presentation\Partial\Lists\Unordered;

/**
 * Single movie presentation page.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Movie\AbstractMoviePage {


  // ------------------------------------------------------------------------------------------------------------------- Magic methods


  /**
   * Instatiate new single movie presentation page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $i18n, $session;
    try {
      $this->movie = new MovieFull($_SERVER["MOVIE_ID"]);
      $this->init($this->movie->displayTitleWithYear);
      if ($this->movie->year) {
        $this->pageTitle = $i18n->t("{0} {1}({2}){3}", [ $this->movie->displayTitle, "<small>", $this->movie->year, "</small>" ]);
      }
      if ($this->movie->deleted === true) {
        return;
      }
      $this->headingBefore = "<div class='row'><div class='span span--9'>";

      // Construct the "Your rating" section for the rating explanation.
      if ($session->isAuthenticated === false) {
        $userRating = $i18n->t(
          "please {0}log in{1} to rate this movie",
          [ "<a href='{$i18n->r("/users/login")}' title='{$i18n->t("Click here to log in to your account.")}'>", "</a>" ]
        );
      }
      elseif (($userRating = $this->ratingModel->getMovieRating($session->userId, $this->movie->id))) {
        $userRating = $i18n->t("your rating: {0}", [ $userRating ]);
      }
      else {
        $userRating = $i18n->t("you haven’t rated this movie yet");
      }

      $countries = new GlueSeparated($this->movie->countries, $i18n->t("No countries assigned yet, {0}add countries{1}?", [ "<a href='{$i18n->r("/movie/{0}/edit", [ $this->movie->id ])}'>", "</a>" ]));
      $genres    = new GlueSeparated($this->movie->genres, $i18n->t("No genres assigned yet, {0}add genres{1}?", [ "<a href='{$i18n->r("/movie/{0}/edit", [ $this->movie->id ])}'>", "</a>" ]));
      $styles    = new GlueSeparated($this->movie->styles, $i18n->t("No styles assigned yet, {0}add styles{1}?", [ "<a href='{$i18n->r("/movie/{0}/edit")}'>", "</a>" ]));

      $this->headingAfter  =
          "<p>{$i18n->t("“{0}” ({1}original title{2})", [ $this->movie->originalTitle, "<em>", "</em>" ])}</p>" .
          "<form action='{$i18n->r("/movie/{0}", [ $this->movie->id ])}' id='movie__rating' method='post'>" .
            "<span class='visuallyhidden'>{$i18n->t("Rating")}: </span>" .
            "<div id='movie__rating__back'>" .
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
                "<a href='{$i18n->r("/movie/{0}/rating-demographics", [ $this->movie->id ])}' title='{$i18n->t("View Rating demographics")}'>",
                $this->movie->votes,
                "</a>",
                "<a href='{$i18n->r("/help/average-rating")}' title='{$i18n->t("Go to help page: {0}", [ "Average Rating"] )}'>",
                "</a>",
                $this->movie->rating,
                $userRating,
              ]) .
          "</p>" .
          "<small><span class='visuallyhidden'>{$i18n->t("Countries")}: </span>{$countries}</small>" .
          "<small>" .
            "<span class='visuallyhidden'>{$i18n->t("Length")}: </span> {$this->movie->runtime} {$i18n->t("min.")}" .
            " | <span class='visuallyhidden'>{$i18n->t("Genres")}: </span> {$genres}" .
            " | <span class='visuallyhidden'>{$i18n->t("Styles")}: </span> {$styles}" .
          "</small>" .
        "</div>{$this->getImage(
        $this->movie->displayPoster->getImageStyle(),
        $i18n->t("/movie/{0}/posters", [ $this->movie->id ]),
        null,
        [ "class" => "span span--3" ]
      )}</div>";
    }
    catch (\DomainException $e) {

    }
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    if ($this->movie->deleted === true) {
      return $this->getGoneContent();
    }

    // Numeric Array holding all the page's content points in a uniform way.
    // Format:
    //   [0] => [ "id" => "first section id",   "title" => "translated title", "content" => "section content" ]
    //   [1] => [ "id" => "second section id",  "title" => "translated title", "content" => "section content" ]
    $contents = [];
    $editRoute = $i18n->r("/movie/{0}/edit", [ $this->movie->id ]);

    // ----------------------------------------------------------------------------------------------------------------- Synopsis

    $contents[] = [
      "id"      => "synopsis",
      "title"   => $i18n->t("Synopsis"),
      "content" => $this->movie->synopsis
    ];

    // ----------------------------------------------------------------------------------------------------------------- Directors

    $list = new Unordered($this->movie->getDirectors(), $i18n->t(
      "No directors assigned yet, {0}add directors{1}?", [ "<a href='{$editRoute}'>", "</a>" ]
    ));
    $list->closure = function (&$director) {
      global $i18n;
      $director = $this->a($i18n->r("/person/{0}", [ $director["id"] ]), $director["name"]);
    };
    $contents[] = [ "id" => "directors", "title" => $i18n->t("Directors"), "content" => $list ];

    // ----------------------------------------------------------------------------------------------------------------- Titles

    $contents[] = [
      "id"      => "titles",
      "title"   => $i18n->t("Titles"),
      "content" => new Unordered(array_column($this->movie->getTitles(), "title"), $i18n->t(
        "No titles assigned yet, {0}add titles{1}?", [ "<a href='{$editRoute}'>", "</a>" ]
      )),
    ];

    // ----------------------------------------------------------------------------------------------------------------- Taglines

    $contents[] = [
      "id"      => "taglines",
      "title"   => $i18n->t("Taglines"),
      "content" => new Unordered(array_column($this->movie->getTagLines(), "tagline"), $i18n->t(
        "No taglines assigned yet, {0}add taglines{1}?", [ "<a href='{$editRoute}'>", "</a>" ]
      )),
    ];

    // Construct the content from the content array.
    $content = null;
    $c       = count($contents);
    for ($i = 0; $i < $c; ++$i) {
      $this->secondaryNavigation->menuitems[] = [ "#{$contents[$i]["id"]}", $contents[$i]["title"], [ "title" => $i18n->t("Go to section") ] ];
      $content .= "<div id='{$contents[$i]["id"]}' class='movie-section'><h2>{$contents[$i]["title"]} <small><a href='{$editRoute}'>{$i18n->t("Edit")}</a></small></h2>{$contents[$i]["content"]}</div>";
    }
    return $content;
  }

}
