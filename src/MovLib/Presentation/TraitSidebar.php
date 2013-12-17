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
 * Sidebar navigation for presentations.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitSidebar {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The sidebar's navigation.
   *
   * @var \MovLib\Presentation\Partial\Navigation
   */
  protected $sidebarNavigation;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the presentation's page content.
   *
   * @return string
   *   The presentation's page content.
   */
  protected abstract function getPageContent();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Implement the content getter and insert the sidebar.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The presentation's content wrapped in a container and including the sidebar.
   */
  protected function getContent() {
    global $i18n;
    // Allow implementing class to alter the sidebar within the getPageContent method.
    $content = $this->getPageContent();
    return
      "<div class='c sidebar-c'><div class='r sidebar-r'>" .
        "<aside id='sidebar' class='s s2' role='complementary'><h2 class='vh'>{$i18n->t("Sidebar")}</h2>{$this->sidebarNavigation}</aside>" .
        "<div class='page-content s s10'>{$content}</div>" .
      "</div></div>"
    ;
  }

  /**
   * Initialize the sidebar.
   *
   * The sidebar navigation keeps its menuitems always active and doesn't honor the query string of the current page.
   * This ensures that sections are highlighted to be active when the user is visiting them. They'll still be clickable
   * though.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param array $menuitems
   *   The sidebar navigation's menuitems.
   * @return this
   */
  protected function initSidebar($menuitems) {
    global $i18n;
    $this->addBodyClass("sidebar");
    $this->sidebarNavigation                = new Navigation($i18n->t("Secondary Navigation"), $menuitems, [ "id" => "sidebar-nav" ]);
    $this->sidebarNavigation->ignoreQuery   = true;
    $this->sidebarNavigation->unorderedList = true; // For CSS styling.
    return $this;
  }

}
