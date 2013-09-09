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
namespace MovLib\View\HTML;

/**
 * Trait for reusing a secondary navigation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitSecondaryNavigationView {

  /**
   * @inheritdoc
   */
  public function getContent() {
    global $i18n;
    $navigationPoints = $this->presenter->getSecondaryNavigationPoints();
    $navigation = "";
    $k = count($navigationPoints);
    for ($i = 0; $i < $k; ++$i) {
      $this->addClass("menuitem", $navigationPoints[$i][2]);
      $navigationPoints[$i][2]["role"] = "menuitem";
      $navigation .= "<li>{$this->a($navigationPoints[$i][0], $navigationPoints[$i][1], $navigationPoints[$i][2])}</li>";
    }
    return
      "<div class='container'><div class='row'>" .
        "<aside class='span span--3'><nav aria-labelledby='nav-secondary__title' class='nav--secondary' id='nav-secondary' role='menu'>" .
          "<h2 class='visuallyhidden' id='nav-secondary__title' role='presentation'>{$i18n->t("Secondary Navigation")}</h2>" .
          "<ul class='no-list'>{$navigation}</ul>" .
        "</nav></aside>" .
        "<div class='span span--9'>{$this->getSecondaryContent()}</div>" .
      "</div></div>"
    ;
  }

  /**
   * The view only has to provide the content beside the secondary navigation.
   *
   * @return string
   *   The content beside the secondary navigation.
   */
  abstract public function getSecondaryContent();

}
