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
namespace MovLib\Presentation\Help;

use \MovLib\Data\Help\HelpCategory;

/**
 * The main help page.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new help presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;

    $this->initPage($i18n->t("Help"));
    $this->initLanguageLinks("/help");
    $this->initBreadcrumb();

    $kernel->stylesheets[] = "help";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n;

    $content = "";
    $categoryResult = HelpCategory::getHelpCategoryIds();
    while ($row = $categoryResult->fetch_object()) {
      /* @var $category \MovLib\Data\Help\HelpCategory */
      $category = new HelpCategory($row->id);

      $content .=
        "<div class='s s4'>" .
          "<h2 class='ico {$category->icon} tac'> {$category->title}</h2>" .
          "<p>{$category->description}</p>" .
          "<p class='tac'>" .
            "<a class='btn btn-success btn-large' href='{$category->route}'>" .
              $i18n->t("{0} Help", [ $category->title ]) .
            "</a>" .
          "</p>" .
        "</div>"
      ;
    }

    return "<div class='c'><div class='r'>{$content}</div></div>";
  }

}
