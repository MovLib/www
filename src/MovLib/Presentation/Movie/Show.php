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
use \MovLib\Data\Movie\FullMovie;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Country;
use \MovLib\Presentation\Partial\Duration;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\Lists\Persons;
use \MovLib\Presentation\Error\NotFound;

/**
 * Single movie presentation page.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitFormPage;
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie we are currently working with.
   *
   * @var \MovLib\Data\Movie\FullMovie
   */
  protected $movie;

  /**
   * The translated route to the movie's edit page.
   *
   * @var string
   */
  protected $routeEdit;

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
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n, $kernel, $session;

    // Try to load the full movie.
    $this->movie = new FullMovie((integer) $_SERVER["MOVIE_ID"]);

    // Only add CSS and JavaScript if the movie really exists.
    $kernel->stylesheets[] = "movie";
    $kernel->javascripts[] = "Movie";

    // Initialize all presentation parts.
    $this->initPage($this->movie->displayTitleWithYear);
    $this->initBreadcrumb([[ $i18n->rp("/movies"), $i18n->t("Movies") ]]);
    $this->initLanguageLinks("/movie/{0}", [ $this->movie->id ]);
    $routeArgs = [ $this->movie->id ];
    $this->routeEdit = $i18n->r("/movie/{0}/edit", $routeArgs);
    $this->initSidebar([
      [ $this->movie->route, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
      [ $i18n->r("/movie/{0}/discussion", $routeArgs), $i18n->t("Discuss"), [ "class" => "ico ico-discussion", "itemprop" => "discussionUrl" ] ],
      [ $this->routeEdit, $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $i18n->r("/movie/{0}/history", $routeArgs), $i18n->t("History"), [ "class" => "ico ico-history separator" ] ],
    ]);

    // Try to load the authenticated user's rating for this movie.
    if ($session->isAuthenticated === true) {
      $this->userRating = $this->movie->getUserRating();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the movie's formatted countries.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The movie's formatted countries.
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function getCountries() {
    global $i18n;
    $countries = null;
    $result    = $this->movie->getCountries();
    while ($row = $result->fetch_row()) {
      if ($countries) {
        $countries .= ", ";
      }
      $countries .= new Country($row[0], [ "itemprop" => "contentLocation"]);
    }
    if (!$countries) {
      $countries = $i18n->t("No countries assigned yet, {0}add countries{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]);
    }
    return $countries;
  }

  /**
   * Get the movie's formatted genres.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The movie's formatted genres.
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function getGenres() {
    global $i18n;
    $genres = null;
    $result = $this->movie->getGenres();
    $route  = $i18n->r("/genre/{0}");
    while ($row = $result->fetch_assoc()) {
      if ($genres) {
        $genres .= ", ";
      }
      $row["route"] = str_replace("{0}", $row["id"], $route);
      $genres      .= "<a href='{$row["route"]}' itemprop='genre'>{$row["name"]}</a>";
    }
    if (!$genres) {
      return $i18n->t("No genres assigned yet, {0}add genres{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]);
    }
    return $genres;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;
    $this->schemaType = "Movie";

    // Enhance the page's title with microdata.
    $this->pageTitle = "<span itemprop='name'>{$this->movie->displayTitle}</span>";
    if ($this->movie->year) {
      $this->pageTitle = $i18n->t("{0} ({1})", [
        $this->pageTitle,
        "<a itemprop='datePublished' href='{$i18n->rp("/year/{0}/movies", [ $this->movie->year ])}'>{$this->movie->year}</a>",
      ]);
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
      $rated  = $i <= $this->userRating ? " class='rated'" : null;
      $stars .=
        "<button{$rated} name='rating' type='submit' value='{$i}' title='{$ratings[$i]}'>" .
          "<span class='vh'>{$i18n->t("with {0, plural, one {one star} other {# stars}}", [ $i ])} </span>" .
        "</button>"
      ;
    }

    // Build an explanation based on available rating data.
    if ($this->movie->votes === 1 && $this->userRating) {
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
    }

    // Format the movie's duration and enhance it with microdata.
    $runtime = new Duration($this->movie->runtime, [ "itemprop" => "duration" ], Duration::MINUTES);

    // But it all together after the closing title.
    $this->headingAfter  =
        "<p>{$i18n->t("“{original_title}” ({0}original title{1})", [ "original_title" => $this->movie->originalTitle, "<em>", "</em>" ])}</p>" .
        "{$this->form->open()}<fieldset id='movie-rating'>" .
          "<legend class='vh'>{$i18n->t("Rate this movie")}</legend> " .
          "<div aria-hidden='true' class='back'><span></span><span></span><span></span><span></span><span></span></div>" .
          "<div class='front'>{$stars}</div>" .
        "</fieldset>{$this->form->close()}" .
        "<small>{$ratingSummary}</small>" .
        "<small><span class='vh'>{$i18n->t("Runtime:")} </span>{$runtime} | <span class='vh'>{$i18n->t("Countries:")} </span>{$this->getCountries()}</small>" .
        "<small><span class='vh'>{$i18n->t("Genres:")} </span>{$this->getGenres()}</small>" .
      "</div>" . // close .span
      "<div id='movie-poster' class='s s3 tac'>{$this->getImage(
        $this->movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_03),
        $i18n->rp("/movie/{0}/posters", [ $this->movie->id ]),
        [ "itemprop" => "image" ]
      )}<div id='movie-rating-mean'>" .
        \NumberFormatter::create($i18n->locale, \NumberFormatter::DECIMAL)->format($this->movie->ratingMean) .
      "</div></div>" .
    "</div>"; // close .row

    $sections["synopsis"] = [
      $i18n->t("Synopsis"),
      empty($this->movie->synopsis)
        ? $i18n->t("No synopsis available, {0}write one{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ])
        : $kernel->htmlDecode($this->movie->synopsis)
      ,
    ];

    $sections["directors"] = [
      $i18n->t("Directors"),
      new Persons(
        $this->movie->getDirectors(),
        $i18n->t("No directors assigned yet, {0}add directors{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]),
        [ "itemprop" => "director" ]
      ),
    ];

    $sections["cast"] = [
      $i18n->t("Cast"),
      new Persons(
        $this->movie->getCast(),
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
   * Validate the user's rating and update the database.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @return this
   */
  protected function valid() {
    global $i18n, $kernel, $session;

    if ($session->isAuthenticated === false) {
      $this->alerts .= new Alert(
        $i18n->t("Please {sign_in} or {join} to rate this movie.", [
          "sign_in" => "<a href='{$i18n->r("/profile/sign-in")}'>{$i18n->t("Sign In")}</a>",
          "join"    => "<a href='{$i18n->r("/profile/join")}'>{$i18n->t("Join {sitename}", [ "sitename" => $kernel->siteName ])}</a>",
        ]),
        null,
        Alert::SEVERITY_INFO
      );
    }
    else {
      $rating = filter_input(INPUT_POST, "rating", FILTER_VALIDATE_INT, [
        "flags"   => FILTER_NULL_ON_FAILURE,
        "options" => [ "min_range" => 1, "max_range" => 5 ],
      ]);
      if ($rating) {
        $this->movie->rate($rating);
      }
      else {
        $this->checkErrors($i18n->t("The submitted rating isn’t valid. Valid ratings range from: 1 to 5"));
      }
    }

    return $this;
  }

}
