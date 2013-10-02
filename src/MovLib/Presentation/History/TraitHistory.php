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

use \IntlDateFormatter;
use \Locale;
use \MovLib\Data\Users;
use \MovLib\Presentation\Partial\Lists;

/**
 * Description of AbstractHistory
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitHistory {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The history model to display.
   *
   * @var \MovLib\Data\History\AbstractHistory
   */
  protected $historyModel;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Returns diff between two commits of one file as styled HTML.
   *
   * @param string $head
   *   Hash of git commit (newer one).
   * @param sting $ref
   *   Hash of git commit (older one).
   * @param string $filename
   *   Name of file in repository.
   * @return string
   *   Returns diff of one file as styled HTML.
   */
  private function diffToHtml($head, $ref, $filename) {
    $diff = $this->historyModel->getDiff($head, $ref, $filename);

    $html = "";
    $c = count($diff);
    // the first 5 lines are the header, nothing to do with it.
    for ($i = 5; $i < $c; ++$i) {
      if ($diff[$i][0] == " ") {
        $html .= substr($diff[$i], 1);
      }
      elseif ($diff[$i][0] == "+") {
        $tmp = substr($diff[$i], 1);
        $html .= "<span class='green'>{$tmp}</span>";
      }
      elseif ($diff[$i][0] == "-") {
        $tmp = substr($diff[$i], 1);
        $html .= "<span class='red'>{$tmp}</span>";
      }
    }
    return $html;
  }

  /**
   * Formats filenames to be userd in page.
   *
   * @param array $fileNames
   *   Numeric array with filenames.
   * @global \MovLib\Data\I18n
   * @return array
   *  Numeric array with formated file names.
   */
  private function formatFileNames($fileNames) {
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
   * @inheritdoc
   */
  protected function getBreadcrumbs() {
    global $i18n;
    return [
      [ $i18n->r("/movies"), $i18n->t("Movies"), [
        "title" => $i18n->t("Have a look at the latest {0} entries at MovLib.", [ $i18n->t("movie") ])
      ]]
    ];
  }

  /**
   * Helper function to build diff page.
   *
   * @global \MovLib\Data\I18n
   * @return string
   *   Returns HTML of diff page.
   */
  private function getDiffContent() {
    global $i18n;
    $html =
      "<div id='revision-diff'>" .
        $this->a($i18n->r("/{0}/{1}/history", [ $this->historyModel->type, $_SERVER["MOVIE_ID"] ]),
          $i18n->t("go back"), [
            "class" => "pull-right"
          ]
        ) .
        "<h2>{$i18n->t("Difference between revisions")}</h2>";

    $changedFiles = $this->historyModel->getChangedFiles($_SERVER["REVISION_HASH"], "{$_SERVER["REVISION_HASH"]}^1");
    $formatedFileNames = $this->formatFileNames($changedFiles);

    $c = count($changedFiles);
    for ($i = 0; $i < $c; ++$i) {
       $changedFiles[$i] = $formatedFileNames[$i] .
       "<div class='well well--small'>" .
         $this->getDiffOfFile($_SERVER["REVISION_HASH"], "{$_SERVER["REVISION_HASH"]}^", $changedFiles[$i]) .
       "</div>";
    }

    $html .=
        (new Lists($changedFiles, ""))->toHtmlList() .
      "</div>";

    return $html;
  }

  /**
   *
   * @param string $head
   *   Hash of git commit (newer one).
   * @param sting $ref
   *   Hash of git commit (older one).
   * @param string $filename
   *   Name of file in repository.
   * @return string
   *   Diff between revisions as HTML.
   */
  private function getDiffOfFile($head, $ref, $filename) {
    if (in_array($filename, $this->historyModel->files)) {
      return $this->diffToHtml($head, $ref, $filename);
    }

    $diff = $this->historyModel->getArrayDiff($head, $ref, $filename);
    $methodName = ucfirst($filename);
    return $this->{"get{$methodName}"}($diff);
  }

  /**
   * Helper function to build revision history.
   *
   * @global \MovLib\Data\I18n
   * @return string
   *   Returns HTML of revision history.
   */
  private function getRevisionHistoryContent() {
    global $i18n;
    $commits = $this->historyModel->getLastCommits();
    $userIds = [];

    $c = count($commits);
    for ($i = 0; $i < $c; ++$i) {
      $userIds[] = $commits[$i]["author_id"];
    }

    $users = (new Users())->getUsers($userIds);

    $revisions = [];
    for ($i = 0; $i < $c; ++$i) {
      $revisions[$i] = $i18n->formatDate(
        $commits[$i]["timestamp"],
        null,
        IntlDateFormatter::MEDIUM,
        IntlDateFormatter::MEDIUM
      );

      if (isset($users[ $userIds[$i] ])) {
        $authorName = $users[ $userIds[$i] ]["name"];
        $revisions[$i] .=
          $i18n->t(" by ") .
          $this->a($i18n->r("/user/{0}", [ $authorName ]), $i18n->t("{0}", [ $authorName ]), [
            "title" => $i18n->t("Profile of {0}", [ $authorName ])
          ]);
      }

      $revisions[$i] .=
        ": {$commits[$i]["subject"]} " .
        $this->a($i18n->r("/{0}/{1}/diff/{2}", [ $this->historyModel->type, $_SERVER["MOVIE_ID"], $commits[$i]["hash"] ]),
          $i18n->t("show diff"), [
            "class" => "pull-right"
          ]
        );

      $changedFiles = $this->historyModel->getChangedFiles($commits[$i]["hash"], "{$commits[$i]["hash"]}^1");
      $revisions[$i] .= (new Lists($this->formatFileNames($changedFiles), $i18n->t("Nothing changed"), [
        "class" => "well well--small no-list"
      ]))->toHtmlList();
    }

    return
      "<div id='revision-history'>" .
        "<h2>{$i18n->t("Revision history")}</h2>" .
        (new Lists($revisions, $i18n->t("No revisions found")))->toHtmlList() .
      "</div>";
  }

}