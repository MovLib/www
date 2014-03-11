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
use \MovLib\Presentation\Partial\Place;
use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\Lists\Ordered;
use \MovLib\Presentation\Partial\FormElement\InputSex;
use \MovLib\Data\Person\FullPerson;

/**
 * Presentation of a single person.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright Â© 2013 MovLib
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
    global $i18n;
    $this->person = new FullPerson((integer) $_SERVER["PERSON_ID"]);
    $this->initPage($this->person->name);
    $this->initLanguageLinks("/person/{0}", [ $this->person->id]);
    $this->initBreadcrumb([[ $i18n->rp("/persons"), $i18n->t("Persons") ]]);
    $this->sidebarInit();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;

    // Enhance the page title with microdata.
    $this->schemaType = "Person";
    $this->pageTitle  = "<span property='name'>{$this->person->name}</span>";

    if ($this->person->deleted === true) {
      return $this->goneGetContent();
    }

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
      $this->person->getStyle(FullPerson::STYLE_SPAN_02),
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


    $content = null;

    // Biography section.
    if ($this->person->biography) {
      $content .= $this->getSection("biography", $i18n->t("Biography"), $this->htmlDecode($this->person->biography));
    }

    // Additional names section.
    if (($personAliases = $this->person->getAliases())) {
      $aliases = null;
      $c       = count($personAliases);
      for ($i = 0; $i < $c; ++$i) {
        $aliases .= "<li class='mb10 s s3' property='additionalName'>{$personAliases[$i]}</li>";
      }
      $content .= $this->getSection("aliases", $i18n->t("Also Known As"), "<ol class='grid-list no-list r'>{$aliases}</ol>");
    }

    // External links section.
    $personLinks = $this->person->getLinks();
    if ($personLinks) {
      $links = null;
      $c     = count($personLinks);
      for ($i = 0; $i < $c; ++$i) {
        $hostname = str_replace("www.", "", parse_url($personLinks[$i], PHP_URL_HOST));
        $links .= "<li class='mb10 s s3'><a href='{$personLinks[$i]}' property='url' rel='nofollow' target='_blank'>{$hostname}</a></li>";
      }
      $content .= $this->getSection("links", $i18n->t("External Links"), "<ul class='grid-list no-list r'>{$links}</ul>");
    }

    if ($content) {
      return $content;
    }

    return new Alert(
      $i18n->t(
        "{sitename} has no further details about {person_name}.",
        [ "sitename"    => $kernel->siteName, "person_name" => $this->person->name ]
      ),
      $i18n->t("No Data Available"),
      Alert::SEVERITY_INFO
    );
  }

  /**
   * Construct a section in the main content and add it to the sidebar.
   *
   * @param string $id
   *   The section's unique identifier.
   * @param string $title
   *   The section's translated title.
   * @param string $content
   *   The section's content.
   * @return string
   *   The section ready for display.
   */
  protected function getSection($id, $title, $content) {
    // Add the section to the sidebar as anchor.
    $this->sidebarNavigation->menuitems[] = [ "#{$id}", $title ];

    return "<div id='{$id}'><h2>{$title}</h2>{$content}</div>";
  }

}
