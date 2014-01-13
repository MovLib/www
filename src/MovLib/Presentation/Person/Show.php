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
use \MovLib\Presentation\Partial\Country;
use \MovLib\Presentation\Partial\Date;

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
    $kernel->stylesheets[] = "person";
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
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;
    $this->schemaType = "Person";

    // Enhance the page title with microdata.
    $this->pageTitle = "<span itemprop='name'>{$this->person->name}</span>";

    // Display Gone page if this person was deleted.
    if ($this->person->deleted === true) {
      // @todo Implement Gone presentation for persons instead of this generic one.
      throw new Gone;
    }

    // Enhance the header, insert row and span before the title.
    $this->headingBefore = "<div class='r'><div class='s s10'>";

    // Put the personal information together.
    $info = [];
    if ($this->person->bornName) {
      $info[] = "<span itemprop='additionalName'>{$this->person->bornName}</span>";
    }
    if (!$this->person->deathDate) {
      $date = new Date($this->person->birthDate);
      $info[] = "<time datetime='{$date->format()}'>{$date->getAge()}</time>";
    }
    if ($this->person->sex > 0) {
      $gender     = $this->person->sex === 1 ? $i18n->t("Male") : $i18n->t("Female");
      $info[] = "<span itemprop='gender'>{$gender}</span>";
    }
    if ($this->person->birthplace) {
      $country = new Country($this->person->birthplace->countryCode);
      $info[] = "<span itemprop='nationality'>{$country}</span>";
    }

    $info = implode(", ", $info);
    $info = "<p>{$info}</p>";

    // Put all header information together after the closing title.
    $this->headingAfter =
      "{$info}</div>" . // close .s
      "<div id='person-photo' class='s s2'>{$this->getImage(
        $this->person->displayPhoto->getStyle(PersonImage::STYLE_SPAN_02),
        $i18n->rp("/person/{0}/photos", [ $this->person->id ]),
        [ "itemprop" => "image" ]
      )}</div>" .
    "</div>"; // close .r

    $sections["biography"] = [
      $i18n->t("Biography"),
      empty($this->person->biography)
        ? $i18n->t("No biography available, {0}write one{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ])
        : $kernel->htmlDecode($this->person->biography)
      ,
    ];

    $filmography = null;
    $result = $this->person->getMovieDirectorIdsResult();
    $director = null;
    while ($row = $result->fetch_assoc()) {
      if ($row["movie_id"]) {
        $entity = new Movie($row["movie_id"]);
      }
      else {
        $entity = new Serial($row["serial_id"]);
      }
      $director .= "<li><a href='{$entity->route}'>{$entity->displayTitle}<span class='fr'>{$entity->year}</span></a></li>";
    }
    if ($director) {
      $filmography["director"] = [
        $i18n->t("Director"),
        "<ol class='hover-list no-list'>{$director}</ol>",
      ];
    }

    $result = $this->person->getMovieCastIdsResult();
    $cast = null;
    while ($row = $result->fetch_assoc()) {
      if ($row["movie_id"]) {
        $entity = new Movie($row["movie_id"]);
      }
      else {
        $entity = new Serial($row["serial_id"]);
      }
      $cast .= "<li><a href='{$entity->route}'>{$entity->displayTitle}<span class='fr'>{$entity->year}</span></a></li>";
    }
    if ($cast) {
      $filmography["cast"] = [
        $i18n->t("Cast"),
        "<ol class='hover-list no-list'>{$cast}</ol>",
      ];
    }

    $result = $this->person->getMovieCrewIdsResult();
    $crew = null;
    while ($row = $result->fetch_assoc()) {
      if ($row["movie_id"]) {
        $entity = new Movie($row["movie_id"]);
      }
      else {
        $entity = new Serial($row["serial_id"]);
      }
      $crew .= "<li><a href='{$entity->route}'>{$i18n->t("{0} as {1}{2}", [ $entity->displayTitle, "<em>{$row["job_title"]}</em>", "<span class='fr'>{$entity->year}</span>" ])}</a></li>";
    }
    if ($crew) {
      $filmography["cast"] = [
        $i18n->t("Cast"),
        "<ol class='hover-list no-list'>{$crew}</ol>",
      ];
    }

    if (!$filmography) {
      $filmography = $i18n->t("No jobs available, {0}add some{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]);
    }
    $sections["filmography"] = [
      $i18n->t("Filmography"),
      $filmography,
    ];

    $links = null;
    if (empty($this->person->links)) {
      $links = $i18n->t("No links available, {0}add some{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ]);
    }
    else {
      $links .= "<ul class='no-list'>";
      foreach ($this->person->links as $website => $url) {
        $links .= "<li><a href='{$url}' itemprop='url' target='_blank'>{$website}</a></li>";
      }
      $links .= "</ul>";
    }
    $sections["links"] = [
      $i18n->t("External links"),
      $links,
    ];

    $content = null;
    foreach ($sections as $id => $section) {
      $this->sidebarNavigation->menuitems[] = [ "#{$id}", $section[0] ];
      $content .= "<div id='{$id}'><h2>{$section[0]}</h2>";
      if (is_array($section[1])) {
        foreach ($section[1] as $subId => $subSection) {
          $this->sidebarNavigation->menuitems[] = [ "#{$id}-{$subId}", $subSection[0] ];
          $content .= "<div id='{$id}-{$subId}'><h3>{$subSection[0]}</h3>{$subSection[1]}</div>";
        }
      }
      else {
        $content .= $section[1];
      }
      $content .= "</div>";
    }
    return $content;
  }

}
