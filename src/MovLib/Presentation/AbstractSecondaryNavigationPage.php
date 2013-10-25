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
namespace MovLib\Presentation;

use \MovLib\Presentation\Partial\Navigation;

/**
 * Base class for page's with secondary navigation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractSecondaryNavigationPage extends \MovLib\Presentation\Page {

  /**
   * The navigation instance with the secondary navigation.
   *
   * The property allows extending classes to access and alter the secondary navigation.
   *
   * @var \MovLib\Presentation\Partial\Navigation
   */
  protected $secondaryNavigation;

  /**
   * @inheritdoc
   */
  public function getContent() {
    global $i18n;
    $this->secondaryNavigation = new Navigation($this->id, $i18n->t("Secondary Navigation"), $this->getSecondaryNavigationMenuitems());
    $this->secondaryNavigation->attributes["class"] = "secondary-navigation";
    $this->secondaryNavigation->unorderedList = true; // We need the unordered list for styling, check the CSS.
    $pageContent = $this->getPageContent();
    return "<div class='container'><div class='row'><aside class='span span--3' role='complementary'>{$this->secondaryNavigation}</aside><div class='span span--9'>{$pageContent}</div></div></div>";
  }

  /**
   * Get the menuitems for the secondary navigation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   The menuitems for the secondary navigation.
   */
  protected abstract function getSecondaryNavigationMenuitems();

  /**
   * Get the page's content.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The page's content.
   */
  protected abstract function getPageContent();

}
