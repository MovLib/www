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

use \MovLib\Partial\Navigation;

/**
 * Add sidebar navigation to presentation.
 *
 * @see \MovLib\Presentation\AbstractBase
 *
 * @method string a($route, $text, array $attributes = null, $ignoreQuery = true)
 * @method this addClass($class, array &$attributes = null)
 * @method string collapseWhitespace($string)
 * @method string expandTagAttributes(array $attributes)
 * @method string getImage($style, $route = true, array $attributes = null, array $anchorAttributes = null)
 * @method string htmlDecode($text)
 * @method string htmlDecodeEntities($text)
 * @method string htmlEncode($text)
 * @method string lang($lang)
 * @method string normalizeLineFeeds($text)
 * @method string placeholder($text)
 *
 * @see \MovLib\Presentation\AbstractPresenter
 *
 * @property string $alerts
 * @property string $bodyClasses
 * @property \MovLib\Presentation\Partial\Navigation $breadcrumb
 * @property string $breadcrumbTitle
 * @property string $contentAfter
 * @property string $contentBefore
 * @property string $headingBefore
 * @property string $headingAfter
 * @property string $headingSchemaProperty
 * @property-read string $id
 * @property-read array $languageLinks
 * @property-read array $namespace
 * @property-read string $pageTitle
 * @property-read string $schemaType
 * @property-read string $title
 * @method string getContent()
 * @method string getFooter()
 * @method string getHeader()
 * @method string getHeadTitle()
 * @method string getPresentation()
 * @method string getMainContent()
 * @method this initBreadcrumb()
 * @method this initLanguageLinks($route, array $args = null, $plural = false, $query = null)
 * @method this initPage($title)
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

  /**
   * Whether to create a small sidebar or not, default is a normal sidebar.
   *
   * @var boolean
   */
  protected $sidebarSmall;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the presentation's page content.
   *
   * @return string
   *   The presentation's page content.
   */
  abstract protected function getPageContent();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Implement the content getter and insert the sidebar.
   *
   * @return string
   *   The presentation's content wrapped in a container and including the sidebar.
   */
  protected function getContent() {
    // Allow implementing class to alter the sidebar within the getPageContent method.
    $content = $this->getPageContent();

    // We have to apply different HTML/CSS depending on the desired size of the sidebar.
    if ($this->sidebarSmall) {
      $containerClass                    = " sidebar-s";
      $sidebarClass                      = null;
      $contentClass                      = "s12";
    }
    else {
      $containerClass = null;
      $sidebarClass   = "s s2";
      $contentClass   = "s10";
    }

    return
      "<div class='c sidebar-c{$containerClass}'><div class='r sidebar-r'>" .
        "<aside id='sidebar' class='{$sidebarClass}' role='complementary'><h2 class='vh'>{$this->intl->t("Sidebar")}</h2>{$this->sidebarNavigation}</aside>" .
        "<div class='page-content s {$contentClass}'>{$content}</div>" .
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
   * @param array $menuitems
   *   The sidebar navigation's menuitems.
   * @param boolean $small [optional]
   *   Whether to create a small sidebar or not, defaults to <code>FALSE</code>.
   * @return this
   */
  final protected function sidebarInit($menuitems, $small = false) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!method_exists($this, "initPage")) {
      throw new \LogicException("You can only use the sidebar trait within a presenting page class");
    }
    if (empty($this->title)) {
      throw new \LogicException("You have to initialize the page before you initialize the sidebar trait");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (($this->sidebarSmall = $small)) {
      $c = count($menuitems);
      for ($i = 0; $i < $c; ++$i) {
        $menuitems[$i][1] = "<span class='text'>{$menuitems[$i][1]}</span>";
      }
    }

    $this->bodyClasses                     .= " sidebar";
    $this->sidebarNavigation                = new Navigation($this, $this->intl->t("Secondary Navigation"), $menuitems, [ "id" => "sidebar-nav" ]);
    $this->sidebarNavigation->ignoreQuery   = true;
    $this->sidebarNavigation->unorderedList = true; // For CSS styling.

    return $this;
  }

}
