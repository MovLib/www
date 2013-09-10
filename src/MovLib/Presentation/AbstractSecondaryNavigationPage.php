<?php

/* !
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

use \MovLib\Presentation\Partial\Navigation;

/**
 * Description of SecondaryNavigationPage
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractSecondaryNavigationPage extends \MovLib\Presentation\Page {

  /**
   * @inheritdoc
   */
  public function getContent() {
    global $i18n;
    $navigation = new Navigation($this->id, $i18n->t("Secondary Navigation"), $this->getSecondaryNavigationMenuitems());
    $navigation->attributes["class"] = "secondary-navigation";
    $navigation->unorderedList = true; // We need the unordered list for styling, check the CSS.
    return "<div class='container'><div class='row'><aside class='span span--3'>{$navigation}</aside><div class='span span--9'>{$this->getPageContent()}</div></div></div>";
  }

  /**
   * Get the menuitems for the secondary navigation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   The menuitems for the secondary navigation.
   */
  abstract protected function getSecondaryNavigationMenuitems();

  /**
   * Get the page's content.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The page's content.
   */
  abstract protected function getPageContent();

}
