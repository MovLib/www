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
use \MovLib\Data\Movie\Movie;
use \MovLib\Data\Person\Full as FullPerson;
use \MovLib\Presentation\Error\Gone;
use \MovLib\Presentation\Partial\Place;
use \MovLib\Presentation\Partial\Date;
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
class Show extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person to present.
   *
   * @var \MovLib\Data\Person\Full
   */
  protected $person;

  /**
   * The translated route to the person's edit page.
   *
   * @var string
   */
  protected $routeEdit;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Presentation\Error\NotFound
   * @throws \LogicException
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->person = new FullPerson($_SERVER["PERSON_ID"]);
    $this->initPage($this->person->name);
    $this->initBreadcrumb([[ $i18n->rp("/persons"), $i18n->t("Persons") ]]);
    $routeArgs = [$this->person->id ];
    $this->initLanguageLinks("/person/{0}", $routeArgs);
    $this->routeEdit = $i18n->r("/person/{0}/edit", $routeArgs);
    $this->initSidebar([
      [ $this->person->route, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
      [ $i18n->r("/person/{0}/discussion", $routeArgs), $i18n->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
      [ $this->routeEdit, $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $i18n->r("/person/{0}/history", $routeArgs), $i18n->t("History"), [ "class" => "ico ico-history separator" ] ],
    ]);
    $this->schemaType = "Person";

    // Enhance the page title with microdata.
    $this->pageTitle = "<span itemprop='name'>{$this->person->name}</span>";

    // Display Gone page if this person was deleted.
    if ($this->person->deleted === true) {
      // @todo Implement Gone presentation for persons instead of this generic one.
      throw new Gone;
    }
    $kernel->stylesheets[] = "person";
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
      $deathDateFormatted = $deathDate->format([ "itemprop" => "birthDate" ]);
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
      $info .= "<span class='ico ico-wikipedia'></span><a href='{$this->person->wikipedia}' target='_blank'>{$i18n->t("Wikipedia Article")}</a>";
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
        : $kernel->htmlDecode($this->person->biography)
      ,
    ];

    // Filmography section.
    $filmography = null;
    $director = $this->formatFilmographyList($this->person->getMovieDirectorIdsResult());
    if ($director) {
      $filmography["director"] = [
        $i18n->t("Director"),
        $director
      ];
    }

    $cast = $this->formatFilmographyList($this->person->getMovieCastIdsResult());
    if ($cast) {
      if ($this->person->sex === 1) {
        $jobTitle = $i18n->t("Actor");
      }
      elseif ($this->person->sex === 2) {
        $jobTitle = $i18n->t("Actress");
      }
      else {
        $jobTitle = $i18n->t("Actor/Actress");
      }
      $filmography["cast"] = [
        $jobTitle,
        $cast
      ];
    }

    $crew = $this->formatFilmographyList($this->person->getMovieCrewIdsResult());
    if ($crew) {
      $filmography["crew"] = [
        $i18n->t("Crew"),
        $crew,
      ];
    }

    if (!$filmography) {
      $filmography = $i18n->t("No jobs available. Please go to a movie or serial page and add them there.");
    }
    $sections["filmography"] = [
      $i18n->t("Filmography"),
      $filmography,
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
        $hostname = parse_url($this->person->links[$i], PHP_URL_HOST);
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

  /**
   * Format a mysql result for display in the filmography section.
   *
   * @param \mysqli_result $result
   *   The result set to format.
   * @return string
   *   The fomatted list.
   */
  protected function formatFilmographyList($result) {
    $list = null;
    while ($row = $result->fetch_assoc()) {
      // Retrieve the correct entity.
      if ($row["movie_id"]) {
        $entity = new Movie($row["movie_id"]);
      }
      else {
        $entity = new Serial($row["serial_id"]);
      }

      // Mark up roles or jobs, depending on being cast or crew.
      $roleOrJob = null;
      if (!empty($row["roles"])) {
        // @todo: Link to role page and provide microdata.
        $roleOrJob = "<br><span class='o1 s s9'>{$row["roles"]}</span>";
      }
      elseif (!empty($row["job_title"])) {
        $roleOrJob = "<br><span class='o1 s s9'>{$row["job_title"]}</span>";
      }

      $list .=
        "<li class='li r'>" .
          "<span itemscope itemtype='http://schema.org/Movie'>" .
            "<span class='s s1' itemprop='datePublished'>{$entity->year}</span>" .
            "<span class='s s9'><a href='{$entity->route}' itemprop='url name'{$this->lang($entity->displayTitleLanguageCode)}>{$entity->displayTitle}</a></span>" .
          "</span>" .
          $roleOrJob .
        "</li>"
      ;
    }
    if ($list) {
      return "<ol class='hover-list no-list'>{$list}</ol>";
    }
  }

}
