<?php

/* !
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
namespace MovLib\Presentation\Persons;

use \MovLib\Data\Person\Person;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Date;

/**
 * The listing for the latest person additions.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;
  use \MovLib\Presentation\TraitPagination;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new latest persons presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->initPage($i18n->t("Persons"));
    $this->initBreadcrumb();
    $this->initLanguageLinks("/persons", null, true);
    $this->initSidebar([
      [ $this->languageLinks[$i18n->languageCode], $i18n->t("Persons"), [ "class" => "ico ico-person" ] ],
      [ $i18n->rp("/movies"), $i18n->t("Movies"), [ "class" => "ico ico-movie" ] ],
      [ $i18n->rp("/releases"), $i18n->t("Releases"), [ "class" => "ico ico-release" ] ],
      [ $i18n->rp("/series"), $i18n->t("Series"), [ "class" => "ico ico-series" ] ],
      [ $i18n->rp("/help"), $i18n->t("Help"), [ "class" => "ico ico-help" ] ],
    ]);
    $this->initPagination(Person::getTotalCount());
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n;

    $list = null;
    $personsResult = Person::getPersons($this->resultsOffset, $this->resultsPerPage);
    /* @var $person \MovLib\Data\Person\Person */
    while ($person = $personsResult->fetch_object("MovLib\\Data\\Person\\Person")) {
      $additionalNames = null;
      if ($person->bornName) {
        $additionalNames .= $i18n->t("born as “{0}”", [ "<span itemprop='additionalName'>{$person->bornName}</span>" ]);
      }
      if ($person->nickname) {
        if ($additionalNames) {
          $additionalNames .= " ";
        }
        $additionalNames .= $i18n->t("aka “{0}”", [ "<span itemprop='additionalName'>{$person->nickname}</span>" ]);
      }
      if ($additionalNames) {
        $additionalNames = "<br>{$additionalNames}";
      }

      $lifeDates = null;
      if ($person->birthDate || $person->deathDate) {
        if ($person->birthDate) {
          $lifeDates .= (new Date($person->birthDate))->formatSchemaProperty("birthDate", [ "title" => $i18n->t("Date of Birth") ]);
        }
        else {
          $lifeDates .= "?";
        }

        if ($person->deathDate) {
          $lifeDates .= " – " . (new Date($person->deathDate))->formatSchemaProperty("deathDate", [ "title" => $i18n->t("Date of Death") ]);
        }

        $lifeDates = "<br>{$lifeDates}";
      }

      $list .=
        "<li itemscope itemtype='http://schema.org/Person'>" .
          "<a class='img r' href='{$i18n->r("/person/{0}", [ $person->id ])}' itemprop='url'>" .
            $this->getImage($person->displayPhoto->getStyle(\MovLib\Data\Image\PersonPhoto::STYLE_SPAN_01), false, [ "class" => "s s1", "itemprop" => "image" ]) .
            "<span class='s s9'><span itemprop='name'>{$person->name}</span>" .
              "<span class='small'>{$additionalNames}{$lifeDates}</span>" .
            "</span>" .
          "</a>" .
        "</li>"
      ;
    }
    if ($list) {
      return "<div id='filter'>filter filter filter</div><ol class='img-list no-list'>{$list}</ol>";
    }
    return new Alert(
      $i18n->t(
        "We couldn’t find any persons matching your filter criteria, or there simply aren’t any persons available. Would you like to {0}create a new entry{1}?",
        [
          "<a href='{$i18n->r("/person/create")}'>",
          "</a>"
        ]
      ),
      $i18n->t("No persons"),
      Alert::SEVERITY_INFO
    );
  }

}
