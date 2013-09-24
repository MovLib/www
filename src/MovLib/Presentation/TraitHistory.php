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
namespace MovLib\Presentation;

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
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    return
      "<h2>{$i18n->t("Revision history")}<h2>" .
      "{$this->getRevisionHistory()}";
  }

  /**
   * @inheritdoc
   */
  protected function init($title) {
    $this->stylesheets[] = "modules/history.css";
    return parent::init($title);
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
   * @return string
   *   Returns diff of one file as styled HTML.
   */
  private function getDiffAsHTML($head, $ref, $filename) {
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

  private function getRevisionHistory() {
    $commits = $this->historyModel->getLastCommits();

    $html = "";
    foreach ($commits as $commit) {
      $html .= "<p>{$commit["subject"]}</p>";
    }
    return $html;
  }

}