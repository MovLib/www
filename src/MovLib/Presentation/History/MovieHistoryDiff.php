<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\History;

use \MovLib\Presentation\Partial\Lists\Unordered;

/**
 * The movie history page.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieHistoryDiff extends \MovLib\Presentation\Movie\AbstractMoviePage {
  use \MovLib\Presentation\History\TraitHistory;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instatiate new movie history diff presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct($context = "history") {
    global $i18n;
    $this->initMovie();
    $this->init($i18n->t("History of {0}", [ $this->title ]));

    $this->historyModel = new \MovLib\Data\History\Movie($this->model->id, $context);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    $this->addClass("active", $this->secondaryNavigation->menuitems[3][2]);
    return $this->getDiffContent();
  }

//  /**
//   * Helper method to generate Liste of changed items.
//   *
//   * @global \MovLib\Data\I18n $i18n
//   * @param array $diff
//   *   Associative array with added and removed items.
//   * @param string $className
//   *   The name of the class (with namespace) to instantiate to get item information.
//   * @param type $methodName
//   *   The name of the method to call for item information.
//   * @return \MovLib\Presentation\Partial\Lists
//   *   A HTML List of changed items.
//   */
//  private function getDiffItems($diff, $className, $methodName) {
//    global $i18n;
//    $items = [];
//    foreach ($diff as $key => $itemIds) {
//      if (!empty($itemIds)) {
//        $itemNames = (new $className())->{$methodName}($itemIds);
//        foreach ($itemIds as $id) {
//          if (isset($itemNames[$id])) {
//            $route = explode('\\', strtolower($className))[3];
//            $items[] = $this->a($i18n->r("/{0}/{1}", [ $route, $id ]), $i18n->t("{0}", [ $itemNames[$id] ]), [
//              "class" => ($key == "added") ? "green" : (($key == "removed") ? "red" : null),
//              "title" => $i18n->t("Description of {0}", [ $itemNames[$id] ])
//            ]);
//          }
//        }
//      }
//    }
//    return (new Lists($items, ""))->toHtmlList();
//  }

  private function getDiff($diff, $className, $methodName) {
    global $i18n;
    $itemIds = [];
    $allItems = array_merge($diff["added"], $diff["removed"], $diff["edited"]);
    $c = count($allItems);
    for ($i = 0; $i < $c; ++$i) {
      $itemIds[] = $allItems[$i]['id'];
    }

    $removed = $this->getDiffItems($diff, "removed", $className, $methodName, $itemIds);
    $added = $this->getDiffItems($diff, "added", $className, $methodName, $itemIds);
    $edited = $this->getDiffItems($diff, "edited", $className, $methodName, $itemIds);

    return new Unordered(array_merge($removed, $added, $edited), "");
  }

  private function getDiffItems($diff, $case, $className, $methodName, $itemIds) {
    global $i18n;
    $itemNames = (new $className())->{$methodName}($itemIds);
    switch ($case) {
      case "added":
        $cssClass = "green";
        break;

      case "removed":
        $cssClass = "red";
        break;

      default:
        $cssClass = null;
    }

    $listItems = [];
    $c         = count($diff[$case]);
    for ($i = 0; $i < $c; ++$i) {
      if (!isset($itemNames[$diff[$case][$i]["id"]])) {
        continue;
      }

      $itemName = $itemNames[$diff[$case][$i]["id"]];
      $propertyList = [];
      foreach ($diff[$case][0] as $key => $value) {
        if ($key == "id" || $key == "old") {
          continue;
        }

        if ($case == "edited") {
          $value = $this->diffBetweenStringsToHtml($value, $diff[$case][0]['old'][$key]);
        }
        $propertyList[] = "<span class='property-name'>{$i18n->t($key)}:</span> {$value}";
      }

      $route         = explode("\\", strtolower($className))[3];
      $unorderedList = new Unordered($propertyList, "", [ "class" => $cssClass ]);
      $listItems[]   =
        "{$this->a($i18n->r("/{0}/{1}", [ $route, $diff[$case][$i]['id'] ]), $i18n->t("{0}", [ $itemName ]), [
          "class" => $cssClass,
          "title" => $i18n->t("Information about {0}", [ $itemName ])
        ])}{$unorderedList}"
      ;
    }

    return $listItems;
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff Methods

 /**
   * Helper method to generate Liste of changed awards.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed awards.
   */
  private function getAwards($diff) {
    // @todo: implement
  }

  /**
   * Helper method to generate Liste of changed casts.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed casts.
   */
  private function getCast($diff) {
    return $this->getDiff($diff, "\MovLib\Data\Person", "getPersonNames");
  }

  /**
   * Helper method to generate Liste of changed countries.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed countries.
   */
  private function getCountries($diff) {
    return $this->getDiffItems($diff, "\MovLib\Data\Country", "getCountryNames");
  }

  /**
   * Helper method to generate Liste of changed crew members.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed crew members.
   */
  private function getCrew($diff) {
    // @todo: implement
  }

  /**
   * Helper method to generate Liste of changed directors.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed directors.
   */
  private function getDirectors($diff) {
    return $this->getDiffItems($diff, "\MovLib\Data\Person", "getPersonNames");
  }

  /**
   * Helper method to generate Liste of changed genres.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed genres.
   */
  private function getGenres($diff) {
    return $this->getDiffItems($diff, "\MovLib\Data\Genre", "getGenreNames");
  }

  /**
   * Helper method to generate Liste of changed languages.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed languages.
   */
  private function getLanguages($diff) {
    return $this->getDiffItems($diff, "\MovLib\Data\Language", "getLanguageNames");
  }

  /**
   * Helper method to generate Liste of changed links.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed links.
   */
  private function getLinks($diff) {
    // @todo: implement
  }

  /**
   * Helper method to generate Liste of changed relationships.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed relationships.
   */
  private function getRelationships($diff) {
    // @todo: implement
  }

  /**
   * Helper method to generate Liste of changed styles.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed styles.
   */
  private function getStyles($diff) {
    return $this->getDiffItems($diff, "\MovLib\Data\Style", "getStyleNames");
  }

  /**
   * Helper method to generate Liste of changed taglines.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed taglines.
   */
  private function getTaglines($diff) {
    // @todo: implement
  }

  /**
   * Helper method to generate Liste of changed titles.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed titles.
   */
  private function getTitles($diff) {
    // @todo: implement
  }

  /**
   * Helper method to generate Liste of changed trailers.
   *
   * @param array $diff
   *   Associative array with added and removed items.
   * @return \MovLib\Presentation\Partial\Lists
   *   A HTML List of changed trailers.
   */
  private function getTrailers($diff) {
    // @todo: implement
  }

}
