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

use \MovLib\Presentation\Partial\Alert;
use \MovLib\Data\Image\PersonImage;
use \MovLib\Data\Movie\Cast;
use \MovLib\Data\Movie\Crew;
use \MovLib\Presentation\Partial\Place;
use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\Lists\Ordered;
use \MovLib\Presentation\Partial\FormElement\InputSex;
use \MovLib\Presentation\Partial\Listing\Movie\MoviePersonListing;

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
   */
  public function __construct() {
    parent::__construct();
    $routeArgs = [ $this->person->id ];
    $this->initLanguageLinks("/person/{0}", $routeArgs);

    // Enhance the page title with microdata.
    $this->pageTitle = "<span property='name'>{$this->person->name}</span>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n;

    $editLinkArgs = [ "<a href='{$i18n->r("/person/{0}/edit", [ $this->person->id ])}'>", "</a>" ];

    // ----------------------------------------------------------------------------------------------------------------- Build page header.
    // Append sex information to name.
    if ($this->person->sex === InputSex::MALE || $this->person->sex === InputSex::FEMALE) {
      if ($this->person->sex === InputSex::MALE) {
        $title = $i18n->t("Male");
      }
      elseif ($this->person->sex === InputSex::FEMALE) {
        $title = $i18n->t("Female");
      }
      $this->pageTitle .= " <sup class='ico ico-sex{$this->person->sex} sex sex-{$this->person->sex}' title='{$title}'></sup>";
    }

    // Put the personal information together.
    $info = null;
    if ($this->person->bornName) {
      $info = $i18n->t("{0} ({1})", [
        "<span property='additionalName'>{$this->person->bornName}</span>",
        "<i>{$i18n->t("born name")}</i>",
      ]);
    }

    // Construct birth info in a translatable way.
    $birth = $birthDate = $birthDateFormatted = $birthAge = $birthPlace = null;
    if ($this->person->birthDate) {
      $birthDate          = new Date($this->person->birthDate);
      $birthDateFormatted = "<a href='{$i18n->rp("/year/{0}/persons", [ $birthDate->dateInfo["year"] ])}'>{$birthDate->format([ "property" => "birthDate" ])}</a>";
      $birthAge           = $birthDate->getAge();
    }
    $birthPlace = $this->person->getBirthPlace();
    if ($birthPlace) {
      $birthPlace = new Place($birthPlace);
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
      $deathDateFormatted = $deathDate->format([ "property" => "deathDate" ]);
      if ($birthDate) {
        $deathAge         = $birthDate->getAge($this->person->deathDate);
      }
    }
    $deathPlace = $this->person->getDeathPlace();
    if ($deathPlace) {
      $deathPlace = new Place($deathPlace);
    }

    if ($deathDate && $deathPlace) {
      if ($birthDate) {
        $death = $i18n->t(
          "Died on {date} in {place} at the age of {age} years.",
          [ "date" => $deathDateFormatted, "place" => $deathPlace, "age" => $deathAge ]
        );
      }
      else {
        $death = $i18n->t("Died on {date} in {place}.", [ "date" => $deathDateFormatted, "place" => $deathPlace ]);
      }
    }
    elseif ($deathDate) {
      if ($birthDate) {
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
      $info .= "<span class='ico ico-wikipedia'></span><a href='{$this->person->wikipedia}' property='sameAs' target='_blank'>{$i18n->t("Wikipedia Article")}</a>";
    }

    // Put all header information together after the closing title.
    $personPhoto         = $this->getImage(
      $this->person->displayPhoto->getStyle(PersonImage::STYLE_SPAN_02),
      true,
      [ "property" => "image" ]
    );
    // Enhance the header, insert row and span before the title.
    $this->headingBefore = "<div class='r'><div class='s s10'>";
    $this->headingAfter  =
          "<p>{$info}</p>" .
        "</div>" . // close .s
        "<div id='person-photo' class='s s2'>{$personPhoto}</div>" .
      "</div>" // close .r
    ;


    // ----------------------------------------------------------------------------------------------------------------- Build page sections.


    // Biography section.
    if ($this->person->biography) {
      $biography = $this->htmlDecode($this->person->biography);
    }
    else {
      $biography = new Alert(
        $i18n->t("No biography available, {0}write one{1}?", $editLinkArgs),
        null,
        Alert::SEVERITY_INFO
      );
    }
    $sections["biography"] = [
      $i18n->t("Biography"),
      $biography,
    ];

    // Movies section.
    $sections["movies"] = [
      $i18n->t("Movies"),
      $this->getMoviesSection()
    ];

    // Additional names section.
    $sections["aliases"] = [
      $i18n->t("Also Known As"),
      new Ordered($this->person->getAliases(), $i18n->t("No additional names available, {0}add some{1}?", $editLinkArgs), [ "class" => "grid-list no-list r" ], [ "class" => "mb10 s s3", "property" => "additionalName" ]),
    ];

    // External links section.
    $links = null;
    $personLinks = $this->person->getLinks();
    if ($personLinks) {
      $links .= "<ul class='grid-list no-list r'>";
      $c = count($personLinks);
      for ($i = 0; $i < $c; ++$i) {
        $hostname = str_replace("www.", "", parse_url($personLinks[$i], PHP_URL_HOST));
        $links .= "<li class='mb10 s s3'><a href='{$personLinks[$i]}' property='url' rel='nofollow' target='_blank'>{$hostname}</a></li>";
      }
      $links .= "</ul>";
    }
    else {
      $links = $i18n->t("No links available, {0}add some{1}?", $editLinkArgs);
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
   * Construct the movies section with all appearances of this person.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The movies section.
   */
  protected function getMoviesSection() {
    global $i18n;

    $listing = new MoviePersonListing($this->person->getMovies());
    if (!($movies = $listing->getListing())) {
      return new Alert($i18n->t("Seems like {person_name} hasn’t worked on any movies."), null, Alert::SEVERITY_INFO);
    }


    // ----------------------------------------------------------------------------------------------------------------- Fetch cast information


    $castResult = $this->person->getMovieCast();
    switch ($this->person->sex) {
      case InputSex::MALE:
        $job  = $i18n->t("Actor");
        $role = $i18n->t("Himself");
        break;

      case InputSex::FEMALE:
        $job  = $i18n->t("Actress");
        $role = $i18n->t("Herself");
        break;

      default:
        $job  = $i18n->t("Actor/Actress");
        $role = $i18n->t("Self");
        break;
    }

    /* @var $cast \MovLib\Data\Movie\Cast */
    while ($cast = $castResult->fetch_object("\\MovLib\\Data\\Movie\\Cast")) {
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

      if ($role) {
        $job = $i18n->t("{0} ({1})", [ $job, $role ]);
      }

      $movies[$cast->movieId]["#jobs"] .= "<li property='actor' resource='#'>{$job}</li>";
    }


    // ----------------------------------------------------------------------------------------------------------------- Fetch crew information


    $crewResult = $this->person->getMovieCrew();
    /* @var $crew \MovLib\Data\Movie\Crew */
    while ($crew = $crewResult->fetch_object("\\MovLib\\Data\\Movie\\Crew")) {
      $movies[$crew->movieId]["#jobs"] .= "<li>{$this->a($i18n->r("/job/{0}", [ $crew->jobId ]), $crew->jobTitle)}</li>";
    }


    // ----------------------------------------------------------------------------------------------------------------- Build the listing


    $list = null;
    foreach ($movies as $id => $html) {
      $list .= "<li class='li s r' typeof='Movie'>{$html["#movie"]}<ul class='no-list jobs s s4 tar'>{$html["#jobs"]}</ul></li>";
    }
    return "<ol class='hover-list no-list'>{$list}</ol>";
  }

}
