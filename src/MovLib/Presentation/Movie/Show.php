<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright Â© 2013-present {@link https://movlib.org/ MovLib}.
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
use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\Duration;

/**
 * Single movie presentation page.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright Â© 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitForm;
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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instatiate new single movie presentation page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n, $kernel;

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
    $this->sidebarInit([
      [ $this->movie->route, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
      [ $i18n->r("/movie/{0}/discussion", $routeArgs), $i18n->t("Discuss"), [
        "class" => "ico ico-discussion",
        "property" => "discussionUrl"
      ] ],
      [ $this->routeEdit, $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $i18n->r("/movie/{0}/history", $routeArgs), $i18n->t("History"), [ "class" => "ico ico-history separator" ] ],
    ]);
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
      $countries .= new Country($row[0], [ "property" => "contentLocation"]);
    }
    if (!$countries) {
      $countries = $i18n->t(
        "No countries assigned yet, {0}add countries{1}?",
        [ "<a href='{$this->routeEdit}'>", "</a>" ]
      );
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
      $genres      .= "<a href='{$row["route"]}' property='genre'>{$row["name"]}</a>";
    }
    if (!$genres) {
      return $i18n->t("No genres assigned yet, {0}add genres{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]);
    }
    return $genres;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  protected function getPageContent() {
    global $i18n, $session;
    $this->schemaType = "Movie";


    //------------------------------------------------------------------------------------------------------------------ Header


    // Enhance the page's title with microdata.
    if ($this->movie->displayTitle == $this->movie->originalTitle) {
      $this->pageTitle = "<span property='name'{$this->lang(
        $this->movie->displayTitleLanguageCode
      )}>{$this->movie->displayTitle}</span>";
      $this->movie->originalTitle = null;
    }
    else {
      $this->pageTitle = "<span property='alternateName'>{$this->movie->displayTitle}</span>";
    }
    if ($this->movie->year) {
      $this->pageTitle = $i18n->t("{0} ({1})", [
        $this->pageTitle,
        // @todo Add full publishing date to content attribute.
        (new Date("{$this->movie->year}-00-00"))->format(
          [ "property" => "datePublished"],
          $i18n->rp("/year/{0}/movies", [ $this->movie->year ]))
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
    $this->formInit();

    // The five available ratings.
    $ratings = [
      1 => $i18n->t("Awful"),
      2 => $i18n->t("Bad"),
      3 => $i18n->t("Okay"),
      4 => $i18n->t("Fine"),
      5 => $i18n->t("Awesome"),
    ];

    // Build the stars that show the currently signed in user's rating and allow her or him to rate this movie.
    $userRating = $this->movie->getUserRating($session->userId);
    $stars      = null;
    for ($i = 1; $i < 6; ++$i) {
      $rated  = $i <= $userRating ? " class='rated'" : null;
      $stars .=
        "<button{$rated} name='rating' type='submit' value='{$i}' title='{$ratings[$i]}'>" .
          "<span class='vh'>{$i18n->t("with {0, plural, one {one star} other {# stars}}", [ $i ])} </span>" .
        "</button>"
      ;
    }

    // Build an explanation based on available rating data. We can't use Intl plural forms here because we have to
    // enclose the various integer values in microdata.
    if ($this->movie->votes === 0) {
      $ratingSummary = $i18n->t("No one has rated this movie so far, be the first.");
    }
    elseif ($this->movie->votes === 1 && $userRating) {
      $ratingSummary = $i18n->t("You’re the only one who rated this movie (yet).");
    }
    else {
      $rating = "<span property='ratingValue'>{$i18n->format("{0,number}", [ $this->movie->ratingMean ])}</span>";
      $votes  = "<span property='ratingCount'>{$this->movie->votes}</span>";
      if ($this->movie->votes === 1) {
        $ratingSummary = $i18n->t("Rated by {votes} user with {rating}.", [ "rating" => $rating, "votes" => $votes ]);
      }
      else {
        $ratingSummary = $i18n->t("Rated by {votes} users with a {0}mean rating{1} of {rating}.", [
          "<a href='{$i18n->r(
            "/movie/{0}/rating-demographics",
            [ $this->movie->id ]
          )}' title='{$i18n->t("View the rating demographics.")}'>",
          "</a>",
          "rating" => $rating,
          "votes"  => $votes,
        ]);
      }
    }

    // Format the directors.
    $directors       = null;
    $directorsResult = $this->movie->getDirectorsLimited();
    $c               = count($directorsResult);
    for ($i = 0; $i < $c; ++$i) {
      if ($directors) {
        $directors .= ", ";
      }
      $directors .= "<span property='director' typeof='Person'><a href='{$i18n->r(
        "/person/{0}",
        [ $directorsResult[$i]["id"]]
      )}' property='name'>{$directorsResult[$i]["name"]}</a></span>";
    }
    if ($directors) {
      if ($c > 1) {
        $directorLabel = $i18n->t("{0}:", [ $i18n->t("Directors") ]);
      }
      else {
        $directorLabel = $i18n->t("{0}:", [ $directorsResult[0]["job_name"] ]);
      }
      $directors =
        "<small class='dtr'><span class='dtc'>{$directorLabel}</span><span class='dtc'>{$directors}</span></small>"
      ;
    }

    // Format first five cast members.
    $cast       = null;
    $castResult = $this->movie->getCastLimited();
    $c          = count($castResult);
    for ($i = 0; $i < $c; ++$i) {
      if ($cast) {
        $cast .= ", ";
      }
      $cast .= "<span property='actor' typeof='Person'><a href='{$i18n->r(
        "/person/{0}",
        [ $castResult[$i]["id"]]
      )}' property='url'><span property='name'>{$castResult[$i]["name"]}</span></a></span>";
    }
    if ($cast) {
      $cast = "<small class='dtr'><span class='dtc'>{$i18n->t(
        "{0}:",
        [ $i18n->t("Cast") ]
      )}</span><span class='dtc'>{$cast}</span></small>";
    }

    // Format the movie's duration and enhance it with microdata.
    $runtime = new Duration($this->movie->runtime, [ "property" => "duration" ], Duration::MINUTES);

    // But it all together after the closing title.
    if ($this->movie->originalTitle) {
      $this->headingAfter .= "<p>{$i18n->t("{0} ({1})", [
        "<span property='name'{$this->lang($this->movie->originalTitleLanguageCode)}>{$this->movie->originalTitle}</span>",
        "<i>{$i18n->t("original title")}</i>",
      ])}</p>";
    }

    $ratingMean = \NumberFormatter::create($i18n->locale, \NumberFormatter::DECIMAL)->format($this->movie->ratingMean);
    $this->headingAfter .=
      "{$this->formOpen()}<fieldset id='movie-rating'>" .
        "<legend class='vh'>{$i18n->t("Rate this movie")}</legend> " .
        "<div aria-hidden='true' class='back'><span></span><span></span><span></span><span></span><span></span></div>" .
        "<div class='front'>{$stars}</div>" .
      "</fieldset>{$this->formClose()}" .
      "<small property='aggregateRating' typeof='AggregateRating'>{$ratingSummary}</small>" .
      "<div class='dt'>" .
        "{$directors}{$cast}" .
        "<small class='dtr'>" .
          "<span class='dtc'>{$i18n->t("{0}:", [ $i18n->t("Runtime") ])}</span>" .
          "<span class='dtc'>{$runtime}</span>" .
        "</small>" .
        "<small class='dtr'>" .
          "<span class='dtc'>{$i18n->t("{0}:", [ $i18n->t("Countries") ])}</span>" .
          "<span class='dtc'>{$this->getCountries()}</span>" .
        "</small>" .
        "<small class='dtr'>" .
          "<span class='dtc'>{$i18n->t("{0}:", [ $i18n->t("Genres") ])}</span>" .
          "<span class='dtc'>{$this->getGenres()}</span>" .
        "</small>" .
      "</div></div>" . // close .span (@see headingBefore)
      "<div id='movie-poster' class='s s3 tac'>" .
        $this->getImage($this->movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_03), true, [
          "property" => "image"
        ]) .
        "<div id='movie-rating-mean'>{$ratingMean}</div>" .
      "</div></div>" // close .row (@see headingBefore)
    ;


    //------------------------------------------------------------------------------------------------------------------ Content sections


    $sections["synopsis"] = [
      $i18n->t("Synopsis"),
      empty($this->movie->synopsis)
        ? $i18n->t("No synopsis available, {0}write synopsis{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ])
        : $this->htmlDecode($this->movie->synopsis)
      ,
    ];

    $sections["releases"] = [
      $i18n->t("Releases"),
      new Alert("Not implemented yet!"),
    ];

    $trailersResult = $this->movie->getTrailers();
    $trailers       = null;
    if ($trailersResult) {
      foreach ($trailersResult as $text => $url) {
        $trailers .=
          "<li property='trailer' typeof='VideoObject'><a href='{$url}' rel='nofollow' target='_blank'>{$text}</a></li>"
        ;
      }
      $trailers = "<ul>{$trailers}</ul>";
    }
    else {
      $trailers = new Alert(
        $i18n->t("No trailers available, {0}add trailers{1}?",[ "<a href='{$this->routeEdit}'>", "</a>" ]),
        null,
        Alert::SEVERITY_INFO
      );
    }
    $sections["trailers"] = [
      $i18n->t("Trailers"),
      $trailers,
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
  protected function formValid() {
    global $i18n, $kernel, $session;

    if ($session->isAuthenticated === false) {
      $this->alerts .= new Alert(
        $i18n->t("Please {sign_in} or {join} to rate this movie.", [
          "sign_in" => "<a href='{$i18n->r("/profile/sign-in")}'>{$i18n->t("Sign In")}</a>",
          "join"    => "<a href='{$i18n->r("/profile/join")}'>{$i18n->t(
            "Join {sitename}",
            [ "sitename" => $kernel->siteName ]
          )}</a>",
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
        $this->checkErrors($i18n->t(
          "The submitted rating isn’t valid. Valid ratings range from: {min} to {max}",
          [ "min" => 1, "max" => 5 ]
        ));
      }
    }

    return $this;
  }

}
