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

use \MovLib\Data\Image\MoviePoster;
use \MovLib\Data\Movie\Full as MovieFull;
use \MovLib\Exception\Client\ErrorNotFoundException;
use \MovLib\Presentation\Partial\Country;
use \MovLib\Presentation\Partial\Duration;
use \MovLib\Presentation\Partial\Lists\GlueSeparated;
use \MovLib\Presentation\Partial\Lists\Ordered;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instatiate new single movie presentation page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $i18n, $session;
    try {
      // Instantiate movie, initialize page and set the microdata schema.
      $this->movie = new MovieFull($_SERVER["MOVIE_ID"]);
      $this->init($this->movie->displayTitleWithYear);
      $this->schemaType = "Movie";

      // Enhance the page's title with microdata.
      if ($this->movie->year) {
        $this->pageTitle = $i18n->t("{movie_title} {time_open}({year}){time_close}", [
          "movie_title" => "<span itemprop='name'>{$this->movie->displayTitle}</span>",
          "time_open"   => "<time itemprop='datePublished'>",
          "year"        => $this->movie->year,
          "time_close"  => "</time>",
        ]);
      }
      else {
        $this->pageTitle = "<span itemprop='name'>{$this->movie->displayTitle}</span>";
      }

      // Display gone page if this movie was deleted.
      if ($this->movie->deleted === true) {
        return;
      }

      // Enhance the header, insert row and span before the title.
      $this->headingBefore = "<div class='row'><div class='span span--9'>";

      // Format the rating for the currently signed in user, if any.
      $userRating = null;
      if ($session->isAuthenticated === true) {
        $userRating = $session->getMovieRating($this->movie->id);
        if ($userRating) {
          $rating = $i18n->t("your rating: {0,number}", [ $userRating ]);
        }
        else {
          $rating = $i18n->t("you haven’t rated this movie yet");
        }
      }
      else {
        $rating = $i18n->t("please {0}sign in{1} to rate this movie", [ "<a href='{$i18n->r("/users/login")}'>", "</a>" ]);
      }

      // The five available ratings.
      $ratings = [
        1 => $i18n->t("Awful"),
        2 => $i18n->t("Not that bad"),
        3 => $i18n->t("Fair"),
        4 => $i18n->t("Pretty good"),
        5 => $i18n->t("Awesome"),
      ];

      // Build the stars that show the currently signed in user's rating and allow her or him to rate this movie.
      $stars = null;
      for ($i = 1; $i <= 5; ++$i) {
        $rated  = $i <= $userRating ? " rated" : null;
        $stars .=
          "<label class='popup-container{$rated}'>" .
            "<small class='popup popup--inverse'>{$ratings[$i]}</small>" .
            "<button name='rating' type='submit' value='{$i}'><i class='icon icon--star'></i></button>" .
          "</label>"
        ;
      }

      // Build an explanation based on available rating data.
      $ratingExplanation = null;
      if ($this->movie->votes === 1 && $userRating) {
        $ratingExplanation = $i18n->t("You’re the only one who voted for this movie (yet).");
      }
      else {
        $ratingExplanation = $i18n->t(
          "Rated by {votes, plural,
  zero  {nobody}
  one   {one user with {mean_rating, plural, one {1 star} other {# stars}}}
  other {{link_rating_demographics}# users{link_close} with a {link_rating_help}mean rating{link_close} of {mean_rating, number}}
}.",
          [
            "link_rating_demographics" => "<a href='{$i18n->r("/movie/{0}/rating-demographics", [ $this->movie->id ])}' title='{$i18n->t("View the rating demographics.")}'>",
            "votes"                    => $this->movie->votes,
            "link_close"               => "</a>",
            "link_rating_help"         => "<a href='{$i18n->r("/help/rating")}' title='{$i18n->t("Go to the rating help page to find out more.")}'>",
            "mean_rating"              => $this->movie->ratingMean,
          ]
        );
      }

      // Format the movie's countries and enhance them with microdata.
      $countries          = new GlueSeparated($this->movie->countries, $i18n->t("No countries assigned yet, {0}add countries{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]));
      $countries->closure = function ($name, $code) {
        return new Country($code, [ "itemprop" => "contentLocation" ]);
      };

      // Format the movie's duration and enhance it with microdata.
      $runtime = new Duration($this->movie->runtime, [ "itemprop" => "duration" ], Duration::MINUTES);

      // Format the movie's genres and styles and enhance them with microdata. We mark the styles as genres as well
      // because schema.org doesn't have any special mark-up for sub-genres.
      $genres          = new GlueSeparated($this->movie->genres, $i18n->t("No genres assigned yet, {0}add genres{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]));
      $styles          = new GlueSeparated($this->movie->styles, $i18n->t("No styles assigned yet, {0}add styles{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]));
      $genres->closure = $styles->closure = function ($name) {
        return "<span itemprop='genre'>{$name}</span>";
      };

      // But it all together after the closing title.
      $this->headingAfter  =
          "<p>{$i18n->t("“{0}” ({1}original title{2})", [ $this->movie->originalTitle, "<em>", "</em>" ])}</p>" .
          "<form action='{$this->routeMovie}' id='movie-rating' method='post'><fieldset>" .
            "<input type='hidden' value='movie_rating'>" .
            "<legend class='visuallyhidden'>{$i18n->t("Your Rating:")}</legend>" .
            "<div class='back'>" . str_repeat("<i class='icon icon--star'></i>", 5) . "</div>" .
            "<div class='front'>{$stars}</div>" .
          "</fieldset></form>" .
          "<small>{$ratingExplanation}</small>" .
          "<small><span class='visuallyhidden'>{$i18n->t("Runtime:")} </span>{$runtime} | <span class='visuallyhidden'>{$i18n->t("Countries:")} </span>{$countries}</small>" .
          "<small><span class='visuallyhidden'>{$i18n->t("Genres:")} </span>{$genres} | <span class='visuallyhidden'>{$i18n->t("Styles:")} </span>{$styles}</small>" .
        "</div>" . // close .span
        $this->getImage(
          $this->movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_03),
          $i18n->t("/movie/{0}/posters", [ $this->movie->id ]),
          [ "itemprop" => "image" ],
          [ "class" => "span span--3" ]
        ) .
      "</div>"; // close .row
    }
    // We don't have any movie with the given identifier.
    catch (\OutOfBoundsException $e) {
      throw new ErrorNotFoundException("Couldn't find movie for identifier '{$_SERVER["MOVIE_ID"]}'");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;

    // Translate the titles of each section.
    $titleSynopsis  = $i18n->t("Synopsis");
    $titleDirectors = $i18n->t("{0, plural, one {Director} other {Directors}}", [ count($this->movie->directors) ]);

    // Add a jump link for each section to the secondary navigation.
    $this->secondaryNavigation->menuitems[] = [ "#synopsis", $titleSynopsis ];
    $this->secondaryNavigation->menuitems[] = [ "#directors", $titleDirectors ];

    // Prepare the content for each section.
    $synopsis = empty($this->movie->synopsis)
      ? $i18n->t("No synopsis available, {0}write one{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ])
        : $kernel->htmlDecode($this->movie->synopsis)
    ;

    $directors = new Ordered($this->movie->directors, $i18n->t("No directors assigned yet, {0}add directors{1}?", [
        "<a href='{$this->routeEdit}'>", "</a>",
      ]), null, [ "itemprop" => "director" ]);
    $directors->closure = [ $this, "formatDirector" ];

    return
      "<div id='synopsis'><h2>{$titleSynopsis}</h2><div itemprop='description'>{$synopsis}</div></div>" .
      "<div id='directors'><h2>{$titleDirectors}</h2>{$directors}</div>"
    ;
  }

  /**
   * Format a single director.
   *
   * @param \MovLib\Data\Person $person
   *   The person to format as director.
   * @return string
   *   The formatted director.
   */
  public function formatDirector($person) {
    return $person->name;
  }

}
