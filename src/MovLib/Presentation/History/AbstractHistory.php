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
  
  
  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n
   */
  protected function getPageContent() {
    global $i18n;
    
    $commits = $this->historyModel->getLastCommits();
    $userIds = [];

    $c = count($commits);
    for ($i = 0; $i < $c; ++$i) {
      $userIds[] = $commits[$i]["author_id"];
    }

    $users = (new Users())->orderById($userIds);

    $revisions = [];
    for ($i = 0; $i < $c; ++$i) {
      $revisions[$i] = $i18n->formatDate(
        $commits[$i]["timestamp"],
        null,
        IntlDateFormatter::MEDIUM,
        IntlDateFormatter::MEDIUM
      );

      if (isset($users[ $userIds[$i] ]->name)) {
        $authorName = $users[ $userIds[$i] ]->name;
        $revisions[$i] .=  $i18n->t(" by ");
        $revisions[$i] .=
          $this->a(
            $i18n->r("/user/{0}", [ $authorName ]), 
            $authorName, 
            [ "title" => $i18n->t("Profile of {0}", [ $authorName ]) ]
          );
        echo $revisions[$i];
       
      }

      $revisions[$i] .=
        ": {$commits[$i]["subject"]} " .
        $this->a($i18n->r("/{0}/{1}/diff/{2}", [ $this->historyModel->type, $this->historyModel->id, $commits[$i]["hash"] ]),
          $i18n->t("show diff"), [
            "class" => "pull-right"
          ]
        );
        

      $changedFiles = $this->historyModel->getChangedFiles($commits[$i]["hash"], "{$commits[$i]["hash"]}^1");
      $revisions[$i] .= new Unordered($this->formatFileNames($changedFiles), $i18n->t("Nothing changed"), [
        "class" => "well well--small no-list"
      ]);
    }

    return
      "<div id='revision-history'>" .
        "<h2>{$i18n->t("Revision history")}</h2>" .
        new Unordered($revisions, $i18n->t("No revisions found")) .
      "</div>";
  }

}
