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
namespace MovLib\Presentation\Person;

use \MovLib\Data\Image\PersonImage;
use \MovLib\Data\Image\MoviePoster;
use \MovLib\Data\Movie\Cast;
use \MovLib\Data\Movie\Crew;
use \MovLib\Presentation\Partial\Place;
use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\Lists\Unordered;
use \MovLib\Presentation\Partial\Lists\Ordered;

/**
 * Presentation of a single person.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Person\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person presentation.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   * @throws \LogicException
   */
  public function __construct() {
    parent::__construct();
    $routeArgs = [ $this->person->id ];
    $this->initLanguageLinks("/person/{0}", $routeArgs);
    array_pop($this->breadcrumb->menuitems);

    // Enhance the page title with microdata.
    $this->pageTitle = "<span itemprop='name'>{$this->person->name}</span>";


  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Construct the header with formatted personal information.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   */
  protected function buildHeader() {
    global $i18n;
    // Enhance the header, insert row and span before the title.
    $this->headingBefore = "<div class='r'><div class='s s10'>";

    // Append sex information to name.
    if ($this->person->sex === 1 || $this->person->sex === 2) {
      if ($this->person->sex === 1) {
        $title = $i18n->t("Male");
      }
      elseif ($this->person->sex === 2) {
        $title = $i18n->t("Female");
      }
      $this->pageTitle .= " <sup class='ico ico-sex{$this->person->sex} sex sex-{$this->person->sex}' title='{$title}'></sup>";
    }

    // Put the personal information together.
    $info = null;
    if ($this->person->bornName) {
      $info = $i18n->t("{0} ({1})", [
        "<span itemprop='additionalName'>{$this->person->bornName}</span>",
        "<i>{$i18n->t("born name")}</i>",
      ]);
    }

    // Construct birth info in a translatable way.
    $birth = $birthDate = $birthDateFormatted = $birthAge = $birthPlace = null;
    if ($this->person->birthDate) {
      $birthDate          = new Date($this->person->birthDate);
      $birthDateFormatted = "<a href='{$i18n->rp("/year/{0}/persons", [ $birthDate->dateInfo["year"] ])}'>{$birthDate->format([ "itemprop" => "birthDate" ])}</a>";
      $birthAge           = $birthDate->getAge();
    }
    if ($this->person->birthplace) {
      $birthPlace = new Place($this->person->birthplace);
    }

    if ($birthDate && $birthPlace) {
      if ($this->person->deathDate) {
        $birth = $i18n->t("Born on {date} in {place} and would be {age} years old.");
      }
      else {
        $birth = $i18n->t("Born on {date} in {place} and is {age} years old.");
      }
      $birth = str_replace([ "{date}", "{place}", "{age}" ], [ $birthDateFormatted, $birthPlace, $birthAge ], $birth);
    }
    elseif ($birthDate) {
      if ($this->person->deathDate) {
        $birth = $i18n->t("Born on {date} and would be {age} years old.");
      }
      else {
        $birth = $i18n->t("Born on {date} and is {age} years old.");
      }
      $birth = str_replace([ "{date}", "{age}" ], [ $birthDateFormatted, $birthAge ], $birth);
    }
    elseif ($birthPlace) {
      $birth = $i18n->t("Born in {place}.", [ "place" => $birthPlace ]);
    }
    if ($birth) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= $birth;
    }

    // Construct death info in a translatable way.
    $death = $deathDate = $deathDateFormatted = $deathAge = $deathPlace = null;
    if ($this->person->deathDate) {
      $deathDate          = new Date($this->person->deathDate);
      $deathDateFormatted = $deathDate->format([ "itemprop" => "deathDate" ]);
      if ($this->person->birthDate) {
        $deathAge         = $birthDate->getAge($this->person->deathDate);
      }
    }
    if ($this->person->deathplace) {
      $deathPlace = new Place($this->person->deathplace);
    }

    if ($deathDate && $deathPlace) {
      if ($this->person->birthDate) {
        $death = $i18n->t("Died on {date} in {place} at the age of {age} years.", [ "date" => $deathDateFormatted, "place" => $deathPlace, "age" => $deathAge ]);
      }
      else {
        $death = $i18n->t("Died on {date} in {place}.", [ "date" => $deathDateFormatted, "place" => $deathPlace ]);
      }
    }
    elseif ($deathDate) {
      if ($this->person->birthDate) {
        $death = $i18n->t("Died on {date} at the age of {age} years.", [ "date" => $deathDateFormatted, "age" => $deathAge ]);
      }
      else {
        $death = $i18n->t("Died on {date}.", [ "date" => $deathDateFormatted ]);
      }
    }
    elseif ($deathPlace) {
      $death = $i18n->t("Died in {place}.", [ "place" => $deathPlace ]);
    }
    if ($death) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= $death;
    }

    // Construct the wikipedia link.
    if ($this->person->wikipedia) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= "<span class='ico ico-wikipedia'></span><a href='{$this->person->wikipedia}' itemprop='sameAs' target='_blank'>{$i18n->t("Wikipedia Article")}</a>";
    }

    // Put all header information together after the closing title.
    $this->headingAfter =
          "<p>{$info}</p>" .
        "</div>" . // close .s
        "<div id='person-photo' class='s s2'>{$this->getImage($this->person->displayPhoto->getStyle(PersonImage::STYLE_SPAN_02), true, [ "itemprop" => "image" ])}</div>" .
      "</div>" // close .r
    ;

    return $this;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;

    $editLinkOpen = "<a href='{$this->routeEdit}'>";

    // Construct personal information and put them into the header.
    $this->buildHeader();

    // Biography section.
    $sections["biography"] = [
      $i18n->t("Biography"),
      empty($this->person->biography)
        ? $i18n->t("No biography available, {0}write one{1}?", [ $editLinkOpen, "</a>" ])
        : $this->htmlDecode($this->person->biography)
      ,
    ];

    // Movies section.
    $sections["movies"] = [
      $i18n->t("Movies"),
      $this->getMoviesSection()
    ];

    // Additional names section.
    $sections["aliases"] = [
      $i18n->t("Also Known As"),
      new Ordered($this->person->aliases, $i18n->t("No additional names available, {0}add some{1}?", [ $editLinkOpen, "</a>" ]), [ "class" => "grid-list no-list r" ], [ "class" => "mb10 s s3", "itemprop" => "additionalName" ]),
    ];

    // External links section.
    $links = null;
    if (empty($this->person->links)) {
      $links = $i18n->t("No links available, {0}add some{1}?", [ $editLinkOpen, "</a>" ]);
    }
    else {
      $links .= "<ul class='grid-list no-list r'>";
      $c = count($this->person->links);
      for ($i = 0; $i < $c; ++$i) {
        $hostname = str_replace("www.", "", parse_url($this->person->links[$i], PHP_URL_HOST));
        $links .= "<li class='mb10 s s3'><a href='{$this->person->links[$i]}' itemprop='url' rel='nofollow' target='_blank'>{$hostname}</a></li>";
      }
      $links .= "</ul>";
    }
    $sections["links"] = [
      $i18n->t("External Links"),
      $links,
    ];

    // Construct content and sidebar.
    $content = null;
    foreach ($sections as $id => $section) {
      $this->sidebarNavigation->menuitems[] = [ "#{$id}", $section[0] ];
      $content .= "<div id='{$id}'><h2>{$section[0]}</h2>";
      if (is_array($section[1])) {
        foreach ($section[1] as $subId => $subSection) {
          $this->sidebarNavigation->menuitems[] = [ "#{$id}-{$subId}", $subSection[0] ];
          $attributes = isset($subSection[2]) ? $this->expandTagAttributes($subSection[2]) : null;
          $content .= "<div id='{$id}-{$subId}'><h3{$attributes}>{$subSection[0]}</h3>{$subSection[1]}</div>";
        }
      }
      else {
        $content .= $section[1];
      }
      $content .= "</div>";
    }
    return $content;
  }

  protected function getMoviesSection() {
    global $i18n;
    $moviesResult = $this->person->getMovies();
    $movieInfos   = [];
    $movieJobs    = [];


    // ----------------------------------------------------------------------------------------------------------------- Fetch movie information


    /* @var $movie \MovLib\Data\Movie\FullMovie */
    while ($movie = $moviesResult->fetch_object("\\MovLib\\Data\\Movie\\FullMovie")) {
      // Get the movie's genres.
      $genres = null;
      $result = $movie->getGenres();
      while ($row = $result->fetch_assoc()) {
        if ($genres) {
          $genres .= "&nbsp;";
        }
        $genres      .= "<span class='label'>{$row["name"]}</span>";
      }
      if ($genres) {
        $genres = "<p class='small'>{$genres}</p>";
      }

      // Construct basic movie information.
      $movieInfos[$movie->id] =
        "<a class='img fl' href='{$movie->route}' itemprop='url'>" .
          "<div class='s s1 tac'>" .
            $this->getImage($movie->displayPoster->getStyle(MoviePoster::STYLE_SPAN_01), false, [ "itemprop" => "image" ]) .
          "</div>" .
          "<div class='s s5'>{$this->getTitleInfo($movie)}{$genres}</div>" .
        "</a>";

      // Add the director job if our person directed the current movie.
      if ($movie->director) {
        $movieJobs[$movie->id] = [ "<a href='{$i18n->r("/job/{0}", [ $movie->director ])}'>{$i18n->t("Director")}</a>" ];
      }
      else {
        $movieJobs[$movie->id] = [];
      }
    }

    // If there were no movies consumed, return a descriptive text.
    if (empty($movieInfos)) {
      return $i18n->t("No movie jobs available. Please go to movie pages and add some.");
    }


    // ----------------------------------------------------------------------------------------------------------------- Fetch cast information


    $castResult       = Cast::getPersonCast($this->person->id);
    $jobActor         = $i18n->t("Actor");
    $jobActress       = $i18n->t("Actress");
    $jobActorActress  = $i18n->t("Actor/Actress");
    $roleHimself      = $i18n->t("Himself");
    $roleHerself      = $i18n->t("Herself");
    $roleSelf         = $i18n->t("Self");
    /* @var $cast \MovLib\Data\Movie\Cast */
    while ($cast = $castResult->fetch_object("\\MovLib\\Data\\Movie\\Cast")) {
      if ($this->person->sex === 1) {
        $job = $jobActor;
        $role = $roleHimself;
      }
      elseif ($this->person->sex === 2) {
        $job = $jobActress;
        $role = $roleHerself;
      }
      else {
        $job = $jobActorActress;
        $role = $roleSelf;
      }
      $job = $this->a($i18n->r("/job/{0}", [ $cast->jobId ]), $job);

      if ($cast->roleName) {
        $role = $cast->roleName;
      }
      elseif ($cast->role) {
        if ($cast->role === true) {
          $role = $this->a($this->person->route, $role);
        }
        else {
          $role = $this->a($cast->role->route, $cast->role->name);
        }
      }

      if (!$role) {
        $movieJobs[$cast->movieId][] = $i18n->t("{job}", [ "job" => $job ]);
      }
      else {
        $movieJobs[$cast->movieId][] = $i18n->t("{job} ({role})", [ "job" => $job, "role" => $role ]);
      }
    }


    // ----------------------------------------------------------------------------------------------------------------- Fetch crew information


    $crewResult = Crew::getPersonCrew($this->person->id);
    /* @var $crew \MovLib\Data\Movie\Crew */
    while ($crew = $crewResult->fetch_object("\\MovLib\\Data\\Movie\\Crew")) {
      $movieJobs[$crew->movieId][] = $this->a($i18n->r("/job/{0}", [ $crew->jobId ]), $crew->jobTitle);
    }


    // ----------------------------------------------------------------------------------------------------------------- Build the listing


    $list = null;
    foreach ($movieInfos as $id => $info) {
      $jobs = new Unordered($movieJobs[$id], "", [ "class" => "no-list jobs s s4 tar" ]);
      $list .= "<li class='li s r'>{$info}{$jobs}</li>";
    }
    return "<ol class='hover-list no-list'>{$list}</ol>";
  }

  /**
   * Construct movie title information for display.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Movie\Movie $movie
   *   The movie to display the title information for. Can also be <code>\\MovLib\\Data\\Movie\\FullMovie</code>.
   * @param array $attributes [optional]
   *   Additional attributes to apply to the wrapper.
   * @param string $wrap
   *   The enclosing tag.
   * @return string
   *   The formatted title information.
   * @throws \LogicException
   */
  protected function getTitleInfo($movie) {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!isset($movie) || !isset($movie->displayTitle)) {
      throw new \LogicException("You have to pass a valid movie object to get title information!");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // We have to use different micro-data if display and original title differ.
    if ($movie->displayTitle != $movie->originalTitle) {
      $displayTitleItemprop = "alternateName";
      $originalTitle = "<br><span class='small'>{$i18n->t("{0} ({1})", [
        "<span itemprop='name'{$this->lang($movie->originalTitleLanguageCode)}>{$movie->originalTitle}</span>",
        "<i>{$i18n->t("original title")}</i>",
      ])}</span>";
    }
    // Simply clear the original title if it's the same as the display title.
    else {
      $displayTitleItemprop = "name";
      $originalTitle = null;
    }
    $displayTitle = "<span class='link-color' itemprop='{$displayTitleItemprop}'{$this->lang($movie->displayTitleLanguageCode)}>{$movie->displayTitle}</span>";

    // Append year enclosed in micro-data to display title if available.
    if (isset($movie->year)) {
      $displayTitle = $i18n->t("{title} ({year})", [ "title" => $displayTitle, "year" => "<span itemprop='datePublished'>{$movie->year}</span>" ]);
    }

    return "<p>{$displayTitle}{$originalTitle}</p>";
  }

}
