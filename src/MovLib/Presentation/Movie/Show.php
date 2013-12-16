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
use \MovLib\Exception\Client\ErrorNotFoundException;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Country;
use \MovLib\Presentation\Partial\Duration;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\Help;
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
   * The user's movie rating.
   *
   * @var integer
   */
  protected $userRating;


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
    $kernel->javascripts[] = "Movie";

    try {
      // Instantiate movie, initialize page and set the microdata schema.
      $this->init();
      $this->initPage($this->movie->displayTitleWithYear);
      $this->initBreadcrumb();
      $this->initLanguageLinks("/movie/{0}", [ $this->movie->id ]);
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
      $this->headingBefore = "<div class='r'><div class='s s9'>";

      // Instantiate the rating form.
      $this->form = new Form($this);

      // The five available ratings.
      $ratings = [
        1 => $i18n->t("Awful"),
        2 => $i18n->t("Bad"),
        3 => $i18n->t("Okay"),
        4 => $i18n->t("Fine"),
        5 => $i18n->t("Awesome"),
      ];

      // Build the stars that show the currently signed in user's rating and allow her or him to rate this movie.
      $stars = null;
      for ($i = 1; $i < 6; ++$i) {
        $rated  = $i <= $this->movie->userRating ? " rated" : null;
        $stars .=
          "<button class='popup-c{$rated}' name='rating' type='submit' value='{$i}'>" .
            "<small class='popup tac'>{$ratings[$i]}</small>" .
            "<span class='vh'>{$i18n->t("Rate with {0, plural, one {one star} other {# stars}}", [ $i ])}</span>" .
          "</button>"
        ;
      }

      $ratingHelp = null;
      // Build an explanation based on available rating data.
      if ($this->movie->votes === 1 && $this->movie->userRating) {
        $ratingSummary = $i18n->t("You’re the only one who voted for this movie (yet).");
      }
      else {
        $ratingSummary = $i18n->t(
          "Rated by {votes, plural,
  zero  {nobody}
  one   {one user with {mean_rating, plural, one {1 star} other {# stars}}}
  other {{link_rating_demographics}# users{link_close} with a {link_rating_help}mean rating{link_close} of {mean_rating, number}}
}.",
          [
            "link_rating_demographics" => "<a href='{$i18n->r("/movie/{0}/rating-demographics", [ $this->movie->id ])}' title='{$i18n->t("View the rating demographics.")}'>",
            "votes"                    => $this->movie->votes,
            "link_close"               => "</a>",
            "link_rating_help"         => "<a href='{$i18n->r("/help/movies/ratings")}' title='{$i18n->t("Go to the rating help page to find out more.")}'>",
            "mean_rating"              => $this->movie->ratingMean,
          ]
        );

        // Don't tell a user who isn't signed in that she or he has to sign in. We tell this to the user on click / submit.
        if ($session->isAuthenticated === true) {
          $ratingHelp = new Help(($this->movie->userRating === null
            ? $i18n->t("You haven’t rated this movie yet.")
            : $i18n->t("Your current rating is {0,number}, simply vote again to update it.", [ $this->movie->userRating ])
          ), "movie-rating");
        }
      }

      // Format the movie's countries and enhance them with microdata.
      $countries          = new GlueSeparated($this->movie->countries, $i18n->t("No countries assigned yet, {0}add countries{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]));
      $countries->closure = [ $this, "formatCountry" ];

      // Format the movie's duration and enhance it with microdata.
      $runtime = new Duration($this->movie->runtime, [ "itemprop" => "duration" ], Duration::MINUTES);

      // Format the movie's genres.
      $genres          = new GlueSeparated($this->movie->genres, $i18n->t("No genres assigned yet, {0}add genres{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]));
      $genres->closure = [ $this, "formatGenre" ];

      // But it all together after the closing title.
      $this->headingAfter  =
          "<p>{$i18n->t("“{original_title}” ({0}original title{1})", [ "original_title" => $this->movie->originalTitle, "<em>", "</em>" ])}</p>" .
          "{$this->form->open()}<fieldset id='movie-rating'>{$ratingHelp}" .
            "<legend class='vh'>{$i18n->t("Rate this movie:")}</legend>" .
            "<div aria-hidden='true' class='back'><span></span><span></span><span></span><span></span><span></span></div>" .
            "<div class='front'>{$stars}</div>" .
          "</fieldset>{$this->form->close()}" .
          "<small>{$ratingSummary}</small>" .
          "<small><span class='vh'>{$i18n->t("Runtime:")} </span>{$runtime} | <span class='vh'>{$i18n->t("Countries:")} </span>{$countries}</small>" .
          "<small><span class='vh'>{$i18n->t("Genres:")} </span>{$genres}</small>" .
        "</div>" . // close .span
        "<div id='movie-poster' class='s s3 tac'>{$this->getImage(
          $this->movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_03),
          $i18n->rp("/movie/{0}/posters", [ $this->movie->id ]),
          [ "itemprop" => "image" ]
        )}<div id='movie-rating-mean'>" .
          \NumberFormatter::create($i18n->locale, \NumberFormatter::DECIMAL)->format($this->movie->ratingMean) .
        "</div></div>" .
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
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n, $kernel;

    $sections["synopsis"] = [
      $i18n->t("Synopsis"),
      empty($this->movie->synopsis)
        ? $i18n->t("No synopsis available, {0}write one{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ])
        : $kernel->htmlDecode($this->movie->synopsis)
      ,
    ];

    $sections["directors"] = [
      $i18n->t("{0, plural, one {Director} other {Directors}}", [ count($this->movie->directors) ]),
      new Persons(
        $this->movie->getDirectorsResult(),
        $i18n->t("No directors assigned yet, {0}add directors{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]),
        [ "itemprop" => "director" ]
      ),
    ];

    $sections["cast"] = [
      $i18n->t("Cast"),
      new Persons(
        $this->movie->getCastResult(),
        $i18n->t("No cast assigned yet, {0}add cast{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]),
        [ "itemprop" => "actor" ]
      ),
    ];

    $sections["releases"] = [
      $i18n->t("Releases"),
      new Alert("Not implemented yet!"),
    ];

    $sections["trailers"] = [
      $i18n->t("Trailers"),
      new Alert("Not implemented yet!"),
    ];

    $sections["reviews"] = [
      $i18n->t("Reviews"),
      new Alert("Not implemented yet!"),
    ];

    $content = null;
    foreach ($sections as $id => $section) {
      $this->sidebarNavigation->menuitems[] = [ "#{$id}", $section[0] ];
      $content .= "<div id='{$id}'><h2>{$section[0]}</h2>{$section[1]}</div>";
    }
    return $content;
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
   * Validate the user's rating and update the database.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @return this
   */
  public function validate() {
    global $i18n, $kernel, $session;

    if ($session->isAuthenticated === false) {
      $this->alerts .= new Alert(
        $i18n->t("Please {0}sign in{1} or {2}join {sitename}{1} to rate this movie.", [
          "<a href='{$i18n->r("/profile/sign-in")}'>", "</a>", "<a href='{$i18n->r("/profile/join")}'>", "sitename" => $kernel->siteName,
        ]),
        null,
        Alert::SEVERITY_INFO
      );
    }
    elseif (!empty($_POST["rating"])) {
      $_POST["rating"] = (integer) $_POST["rating"];
      if ($_POST["rating"] > 0 && $_POST["rating"] < 6) {
        $this->movie->rate($_POST["rating"]);
      }
      else {
        $this->checkErrors($i18n->t("The submitted rating of {0,number,integer} isn’t valid. Valid ratings range from: 1 to 5", [ $_POST["rating"] ]));
      }
    }

    return $this;
  }

}
