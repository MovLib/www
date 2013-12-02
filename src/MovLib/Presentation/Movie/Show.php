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
use \MovLib\Data\Movie\Full as FullMovie;
use \MovLib\Exception\Client\ErrorNotFoundException;
use \MovLib\Presentation\Partial\Country;
use \MovLib\Presentation\Partial\Duration;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\Lists\GlueSeparated;
use \MovLib\Presentation\Partial\Lists\Persons;

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
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie's translated and formatted synopsis.
   *
   * @var string
   */
  protected $synopsis;
  protected $directors;
  protected $cast;



  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instatiate new single movie presentation page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $i18n, $kernel, $session;
    try {
      // Instantiate movie, initialize page and set the microdata schema.
      $this->movie = new FullMovie($_SERVER["MOVIE_ID"]);
      $this->init($this->movie->displayTitleWithYear);
      $this->schemaType = "Movie";

      // Enhance the page's title with microdata.
      if ($this->movie->year) {
        $this->pageTitle = $i18n->t("{0} ({1})", [
          "<span itemprop='name'>{$this->movie->displayTitle}</span>",
          "<a itemprop='datePublished' href='{$i18n->r("/year/{0}", [ $this->movie->year ])}'>{$this->movie->year}</a>",
        ]);
      }
      else {
        $this->pageTitle = "<span itemprop='name'>{$this->movie->displayTitle}</span>";
      }

      // Display gone page if this movie was deleted.
      if ($this->movie->deleted === true) {
        // @todo Implement gone presentation for movies.
        throw new \LogicException("Not implemented yet!");
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
        $rating = $i18n->t("please {0}sign in{1} to rate this movie", [ "<a href='{$i18n->r("/profile/sign-in")}'>", "</a>" ]);
      }

      // Instantiate the rating form.
      $this->form             = new Form($this);
      $this->form->attributes = [ "id" => "movie-rating" ];

      // The five available ratings.
      $ratings = [
        1 => $i18n->t("Awful"),
        2 => $i18n->t("Bad"),
        3 => $i18n->t("Okay"),
        4 => $i18n->t("Fine"),
        5 => $i18n->t("Terrific"),
      ];

      // Build the stars that show the currently signed in user's rating and allow her or him to rate this movie.
      $stars = null;
      for ($i = 1; $i <= 5; ++$i) {
        $rated  = $i <= $userRating ? " rated" : null;
        $stars .=
          "<label class='popup-container{$rated}'>" .
            "<small class='popup popup--inverse'>{$ratings[$i]}</small>" .
            "<button name='rating' type='submit' value='{$i}'><i class='ico-star'></i></button>" .
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
      $countries->closure = [ $this, "formatCountry" ];

      // Format the movie's duration and enhance it with microdata.
      $runtime = new Duration($this->movie->runtime, [ "itemprop" => "duration" ], Duration::MINUTES);

      // Format the movie's genres and styles and enhance them with microdata. We mark the styles as genres as well
      // because schema.org doesn't have any special mark-up for sub-genres.
      $genres          = new GlueSeparated($this->movie->genres, $i18n->t("No genres assigned yet, {0}add genres{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]));
      $genres->closure = [ $this, "formatGenre" ];
      $styles          = new GlueSeparated($this->movie->styles, $i18n->t("No styles assigned yet, {0}add styles{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]));
      $styles->closure = [ $this, "formatStyle" ];

      // But it all together after the closing title.
      $this->headingAfter  =
          "<p>{$i18n->t("“{0}” ({1}original title{2})", [ $this->movie->originalTitle, "<em>", "</em>" ])}</p>" .
          "{$this->form->open()}<fieldset>" .
            "<input type='hidden' value='movie_rating'>" .
            "<legend class='visuallyhidden'>{$i18n->t("Your Rating:")}</legend>" .
            "<div class='back'>" . str_repeat("<i class='ico-star'></i>", 5) . "</div>" .
            "<div class='front'>{$stars}</div>" .
          "</fieldset>{$this->form->close()}" .
          "<small>{$ratingExplanation}</small>" .
          "<small><span class='visuallyhidden'>{$i18n->t("Runtime:")} </span>{$runtime} | <span class='visuallyhidden'>{$i18n->t("Countries:")} </span>{$countries}</small>" .
          "<small><span class='visuallyhidden'>{$i18n->t("Genres:")} </span>{$genres} | <span class='visuallyhidden'>{$i18n->t("Styles:")} </span>{$styles}</small>" .
        "</div>" . // close .span
        $this->getImage(
          $this->movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_03),
          $i18n->r("/movie/{0}/posters", [ $this->movie->id ]),
          [ "itemprop" => "image" ],
          [ "class" => "img span span--3" ]
        ) .
      "</div>"; // close .row

      // Translate the titles of each section.
      $titleSynopsis  = $i18n->t("Synopsis");
      $titleDirectors = $i18n->t("{0, plural, one {Director} other {Directors}}", [ count($this->movie->directors) ]);
      $titleCast      = $i18n->t("Cast");

      // Add a jump link for each section to the secondary navigation.
      $this->sidebarNavigation->menuitems[] = [ "#synopsis", $titleSynopsis ];
      $this->sidebarNavigation->menuitems[] = [ "#directors", $titleDirectors ];
      $this->sidebarNavigation->menuitems[] = [ "#cast", $titleCast ];

      $synopsis = empty($this->movie->synopsis)
        ? $i18n->t("No synopsis available, {0}write one{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ])
        : $kernel->htmlDecode($this->movie->synopsis)
      ;
      $this->synopsis = "<h2>{$titleSynopsis}</h2><div itemprop='description'>{$synopsis}</div>";

      $directors = new Persons(
        $this->movie->getDirectorsResult(),
        $i18n->t("No directors assigned yet, {0}add directors{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]),
        [ "itemprop" => "director" ]
      );
      $this->directors = "<h2>{$titleDirectors}</h2>{$directors}";

      $cast = new Persons(
        $this->movie->getCastResult(),
        $i18n->t("No cast assigned yet, {0}add cast{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]),
        [ "itemprop" => "actor" ]
      );
      $this->cast = "<h2>{$titleCast}</h2>{$cast}";
    }
    // We don't have any movie with the given identifier.
    catch (\OutOfBoundsException $e) {
      throw new ErrorNotFoundException("Couldn't find movie for identifier '{$_SERVER["MOVIE_ID"]}'");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    return
      "<div id='synopsis'>{$this->synopsis}</div>" .
      "<div id='directors'>{$this->directors}</div>" .
      "<div id='cast'>{$this->cast}</div>"
    ;
  }

  /**
   * Format a single country.
   *
   * @param string $name
   *   <b>Unused!</b>
   * @param string $code
   *   The country's ISO 3166-1 alpha-2 code.
   * @return \MovLib\Presentation\Partial\Country
   *   The country partial that represents this country.
   */
  public function formatCountry($name, $code) {
    return new Country($code, [ "itemprop" => "contentLocation" ]);
  }

  /**
   * Format a single genre.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $name
   *   The genre's translated name.
   * @param integer $id
   *   The genre's unique identifier.
   * @return string
   *   The formatted genre.
   */
  public function formatGenre($name, $id) {
    global $i18n;
    return "<a href='{$i18n->r("/genre/{0}", [ $id ])}' itemprop='genre'>{$name}</a>";
  }

  /**
   * Format a single style.
   *
   * @internal
   *   We have to use genre for the itemprop attribute because Schema.org has no styles.
   * @global \MovLib\Data\I18n $i18n
   * @param string $name
   *   The style's translated name.
   * @param integer $id
   *   The style's unique identifier.
   * @return string
   *   The formatted style.
   */
  public function formatStyle($name, $id) {
    global $i18n;
    return "<a href='{$i18n->r("/style/{0}", [ $id ])}' itemprop='genre'>{$name}</a>";
  }

}
