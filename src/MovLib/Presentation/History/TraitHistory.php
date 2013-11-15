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
namespace MovLib\Presentation\History;

use \Locale;
use \MovLib\Presentation\Partial\Lists\Unordered;

/**
 * @todo Description of TraitHistory
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitHistory {


  // ------------------------------------------------------------------------------------------------------------------- Methods
  

  /**
   * Method to generate Liste of changed items.
   *
   * Use this method if the diff consists of asociative arrays! Each of these arrays have to store at least an ID!
   *
   * @global \MovLib\Data\I18n $i18n
   * @param array $diff
   *   Associative array with added, removed and edited items.
   * @param string $className
   *   The name of the class (with namespace) to instantiate to get item information.
   * @param type $methodName [optional]
   *   The name of the method to call for item information.
   * @return \MovLib\Presentation\Partial\Lists\Unordered
   *   A HTML List of changed items.
   */
  protected function diffArray($diff, $className, $methodName = "orderById") {
    global $i18n;
    $itemIds = [];
    $allItems = array_merge($diff["added"], $diff["removed"], $diff["edited"]);
    $c = count($allItems);
    for ($i = 0; $i < $c; ++$i) {
      $itemIds[] = $allItems[$i]["id"];
    }

    $removed = $this->diffArrayItems($diff, "removed", $className, $methodName, $itemIds);
    $added = $this->diffArrayItems($diff, "added", $className, $methodName, $itemIds);
    $edited = $this->diffArrayItems($diff, "edited", $className, $methodName, $itemIds);

    return new Unordered(array_merge($removed, $added, $edited), "");
  }

  /**
   * Helper Methode used only in getDiff() to get list of changed Items.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param array $diff
   *   Associative array with added, removed and edited items.
   * @param string $case
   *   String containing "added", "removed" or "edited".
   * @param string $className
   *   The name of the class (with namespace) to instantiate to get item information.
   * @param type $methodName
   *   The name of the method to call for item information.
   * @param array $itemIds
   *   Numeric array with IDs of the items we need more information.
   * @return \MovLib\Presentation\Partial\Lists\Unordered
   *   A HTML List of changed items.
   */
  protected function diffArrayItems($diff, $case, $className, $methodName, $itemIds) {
    global $i18n;
    $itemInformation = (new $className())->{$methodName}($itemIds);
    $cssClass = ($case == "added") ? "green" : (($case == "removed") ? "red" : null);

    $listItems = [];
    $c = count($diff[$case]);
    for ($i = 0; $i < $c; ++$i) {
      if (!isset($itemInformation[$diff[$case][$i]["id"]])) {
        continue;
      }

      $itemName = $itemInformation[$diff[$case][$i]["id"]]->name;
      $propertyList = [];

      foreach ($diff[$case][0] as $key => $value) {
        if ($key == "id" || $key == "old" || $case != "edited") {
          continue;
        }

        $value = $this->textDiffOfStrings($value, $diff[$case][0]['old'][$key], false);
        if ($value != $diff[$case][0]['old'][$key]) {
          $propertyList[] = "<span class='property-name'>{$i18n->t($key)}:</span> {$value}";
        }
      }

      $route         = array_pop((explode('\\', strtolower($className))));
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

  /**
   * Method to generate Liste of changed items representet by their name.
   *
   * Use this method if the diff consists of item IDs only!
   *
   * @global \MovLib\Data\I18n $i18n
   * @param array $diff
   *   Associative array with added and removed items (only item IDs!).
   * @param string $className
   *   The name of the class (with namespace) to instantiate to get item information.
   * @return \MovLib\Presentation\Partial\Lists\Unordered
   *   A HTML List of changed items representet by their name.
   */
  protected function diffIds($diff, $className) {
    global $i18n;
    $classNameWithoutNamespace = array_pop((explode('\\', strtolower($className))));
    $listItems = [];
    foreach ($diff as $key => $itemIds) {
      if (!empty($itemIds)) {
        $items = (new $className())->orderById($itemIds);
        foreach ($itemIds as $id) {
          if (isset($items[$id]->name)) {
            $listItems[] = $this->a($i18n->r("/{0}/{1}", [ $classNameWithoutNamespace, $id ]), $i18n->t("{0}", [ $items[$id]->name ]), [
              "class" => ($key == "added") ? "green" : (($key == "removed") ? "red" : null),
              "title" => $i18n->t("More about {0}", [ $items[$id]->name ])
            ]);
          }
        }
      }
    }
    return new Unordered($listItems, "");
  }

  /**
   * Formats filenames to be userd in page.
   *
   * @global \MovLib\Data\I18n
   * @param array $fileNames
   *   Numeric array with filenames.
   * @return array
   *  Numeric array with formated file names.
   */
  protected function formatFileNames(array $fileNames) {
    global $i18n;
    $c = count($fileNames);
    for ($i = 0; $i < $c; ++$i) {
      if ($fileNames[$i][2] === "_") {
        $fileNames[$i] = ucwords(str_replace("_", " ", $fileNames[$i]));
        $language = Locale::getDisplayName(substr($fileNames[$i], 0, 2), $i18n->languageCode);
        $fileNames[$i] = $i18n->t("{0} ($language)", [ substr($fileNames[$i], 3) ]);
      }
      $fileNames[$i] = $i18n->t("{0}", [ ucwords(str_replace("_", " ", $fileNames[$i]))]);
    }
    return $fileNames;
  }

  /**
   * Calls the right diff methode.
   *
   * @global \MovLib\Data\I18n
   * @param string $head
   *   Hash of git commit (newer one).
   * @param sting $ref
   *   Hash of git commit (older one).
   * @param string $filename
   *   Name of file in repository.
   * @return string
   *   Diff between revisions as HTML.
   */
  protected function getDiff($head, $ref, $filename) {
    global $i18n;   
    $methodName = ucfirst($filename);
    if (in_array($filename, $this->historyModel->files)) {
      return $this->textDiffOfRevisions($head, $ref, $filename, true);
    }
    else if (in_array($filename, $this->historyModel->serializedArrays)) {
      $diff = $this->historyModel->getArrayDiff($head, $ref, $filename);
      return $this->{"get{$methodName}"}($diff);
    }
    else if (in_array($filename, $this->historyModel->serializedIds)) {
      $diff = $this->historyModel->getIdDiff($head, $ref, $filename);
      return $this->{"get{$methodName}"}($diff);
    }
    else {
      return $i18n->t("Nothing changed");
    }
  }

  /**
   * Returns diff between two commits of one file as styled HTML.
   *
   * @param string $head
   *   Hash of git commit (newer one).
   * @param sting $ref
   *   Hash of git commit (older one).
   * @param string $filename
   *   Name of file in repository.
   * @param bool $exact [optional]
   *   Compare every letter instead every word.
   * @return string
   *   Returns diff of one file as styled HTML.
   */
  protected function textDiffOfRevisions($head, $ref, $filename, $exact) {
    $from = $this->historyModel->getFileAtRevision($filename, $ref);
    $to   = $this->historyModel->getFileAtRevision($filename, $head);

    return $this->textDiffOfStrings($from, $to, $exact);
  }

  /**
   * Returns a diff of two strings as styles HTML.
   *
   * @param string $from
   *   String (the older one).
   * @param sting $ref
   *   String (the newer one).
   * @param bool $exact [optional]
   *   Compare every letter instead every word.
   * @return string
   *   Returns a diff of two strings as styles HTML.
   */
  protected function textDiffOfStrings($from, $to, $exact = false) {
    if ($exact) {
      $from = preg_replace("/(.{1})/", "$1\n", $from);
      $to = preg_replace("/(.{1})/", "$1\n", $to);
    }
    else {
      $from = str_replace(" ", "\n", $from);
      $to = str_replace(" ", "\n", $to);
    }

    $diff = xdiff_string_diff($from, $to, strlen($to));
    $diff = explode("\n", $diff);

    $html = "";
    $added = "";
    $removed = "";
    $c = count($diff);
    for ($i = 0; $i < $c; ++$i) {
      if (isset($diff[$i][0])) {
        if ($diff[$i][0] == " ") {
          $html .= substr($diff[$i], 1);
        }
        elseif ($diff[$i][0] == "+") {
          $added .= substr($diff[$i], 1);
          if (isset($diff[ $i+1 ][0]) && $diff[ $i+1 ][0] == "+") {
            continue;
          }
          $html .= "<span class='green'>{$added}</span>";
          $added = "";
        }
        elseif ($diff[$i][0] == "-") {
          $removed .= substr($diff[$i], 1);
          if (isset($diff[ $i+1 ][0]) && $diff[ $i+1 ][0] == "-") {
            continue;
          }
          $html .= "<span class='red'>{$removed}</span>";
          $removed = "";
        }
        if ($exact == false) {
          $html .= " ";
        }
      }
    }
    return $html;
  }

}