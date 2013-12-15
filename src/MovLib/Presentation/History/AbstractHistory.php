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

use \IntlDateFormatter;
use \MovLib\Data\User\Users;
use \MovLib\Presentation\Partial\Lists\Unordered;

/**
 * Description of AbstractHistory
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractHistory extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\History\TraitHistory;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The history model to display.
   *
   * @var \MovLib\Data\History\AbstractHistory
   */
  protected $historyModel;

  /**
   * Current revision item hash to be used in closure.
   *
   * @var string
   */
  protected $revisionItemHash;


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * Helper mothod to format changes file.
   *
   * @global \MovLib\Data\I18n
   * @param string $listItem
   *   Name of the changed file.
   * @return string
   *   Returns one changed file for formatRevision().
   */
  public function formatChangedFile($listItem) {
    global $i18n;
    return $this->a($i18n->r("/{0}/{1}/diff/{2}#{3}", [
        $this->historyModel->type,
        $this->historyModel->id,
        $this->revisionItemHash,
        $listItem
      ]), $listItem
    );
  }

  /**
   * Helper mothod to format diff items.
   *
   * @param string $listItem
   *   Name of the list item.
   * @return string
   *   Returns one list item for diff page.
   */
  public function formatDiff($listItem) {
    $displayItem = $this->formatFileNames([$listItem])[0];
    return "<a id='$displayItem'>{$displayItem}</a><div class='well well--small'>{$this->getDiff(
      $_SERVER["REVISION_HASH"],
      "{$_SERVER["REVISION_HASH"]}^",
      $listItem
    )}</div>";
  }

  /**
   * Helper mothod to format revision items.
   *
   * @global \MovLib\Data\I18n
   * @param string $revisionItem
   *   Name of the revision item.
   * @return string
   *   Returns one revision item for revision page.
   */
  public function formatRevision($revisionItem) {
    global $i18n;
    $this->revisionItemHash = $revisionItem["hash"];
    $changedFiles = $this->historyModel->getChangedFiles($revisionItem["hash"], "{$revisionItem["hash"]}^1");
    $list = new Unordered($this->formatFileNames($changedFiles), $i18n->t("Nothing changed"), [
      "class" => "well well--small no-list"
    ]);
    $list->closure = [ $this, "formatChangedFile" ];

    return
      "{$this->a(
        $i18n->r("/{0}/{1}/diff/{2}", [
          $this->historyModel->type,
          $this->historyModel->id,
          $revisionItem["hash"]
        ]), $i18n->t("diff")
      )} | {$i18n->formatDate(
        $revisionItem["timestamp"],
        null,
        IntlDateFormatter::MEDIUM,
        IntlDateFormatter::MEDIUM
      )} {$i18n->t("by")} {$this->a($i18n->r("/user/{0}", [  $revisionItem["author_name"] ]),
        $i18n->t("{0}", [ $revisionItem["author_name"] ]),
        [ "title" => $i18n->t("Profile of {0}", [ $revisionItem["author_name"] ]) ]
      )}: {$revisionItem["subject"]}{$list}";
  }

  /**
   * Method to build diff page.
   *
   * @global \MovLib\Data\I18n
   * @return string
   *   Returns HTML of diff page.
   */
  protected function diffPage() {
    global $i18n;
    $list = new Unordered($this->historyModel->getChangedFiles($_SERVER["REVISION_HASH"], "{$_SERVER["REVISION_HASH"]}^1"),
      $i18n->t("Nothing changed")
    );
    $list->closure = [ $this, "formatDiff" ];

    return
      "<div id='revision-diff'>{$this->a(
        $i18n->r("/{0}/{1}/history", [ $this->historyModel->type, $this->historyModel->id ]),
        $i18n->t("go back"),
        [ "class" => "fr" ]
      )}<h2>{$i18n->t("Difference between revisions")}</h2>{$list}</div>"
    ;
  }

  /**
   * Helper function to build revision history.
   *
   * @global \MovLib\Data\I18n
   * @return string
   *   Returns HTML of revision history.
   */
  protected function revisionsPage() {
    global $i18n;
    $commits = $this->historyModel->getLastCommits();

    $userIds = [];
    $c = count($commits);
    for ($i = 0; $i < $c; ++$i) {
      $userIds[] = $commits[$i]["author_id"];
    }
    $users = (new Users())->orderById($userIds);
    for ($i = 0; $i < $c; ++$i) {
      $commits[$i]["author_name"] = isset($users[ $commits[$i]["author_id"] ]) ? $users[ $commits[$i]["author_id"] ]->name : "";
    }

    $list = new Unordered($commits, $i18n->t("No revisions found"));
    $list->closure = [ $this, "formatRevision" ];

    return "<div id='revision-history'><h2>{$i18n->t("Revision history")}</h2>{$list}</div>";
  }

}
