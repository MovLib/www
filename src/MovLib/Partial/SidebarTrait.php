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
namespace MovLib\Partial;

use \MovLib\Partial\Navigation;

/**
 * Add sidebar navigation to presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait SidebarTrait {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The sidebar's navigation.
   *
   * @var \MovLib\Partial\Navigation
   */
  protected $sidebarNavigation;

  /**
   * Whether to create a small sidebar or not, default is a normal sidebar.
   *
   * @var boolean
   */
  protected $sidebarSmall;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the content wrapped in the mark-up from the default presentation class plus the sidebar.
   *
   * @param string $content
   *   The presentation's content.
   * @return string
   *   The presentation's content wrapped with the main tag, header, and sidebar.
   */
  final public function getMainContent($content) {
    // We have to apply different HTML/CSS depending on the desired size of the sidebar.
    if ($this->sidebarSmall) {
      $containerClass = " sidebar-s";
      $sidebarClass   = null;
      $contentClass   = "s12";
    }
    else {
      $containerClass = null;
      $sidebarClass   = "s s2";
      $contentClass   = "s10";
    }

    // Call the parent method to get the default mark-up for the main content and add the sidebar mark-up.
    return parent::getMainContent(
      "<div class='c sidebar-c{$containerClass}'><div class='r sidebar-r'>" .
        "<aside id='sidebar' class='{$sidebarClass}' role='complementary'>" .
          "<h2 class='vh'>{$this->intl->t("Sidebar")}</h2>" .
          $this->sidebarNavigation .
        "</aside>" .
        "<div class='page-content s {$contentClass}'>{$content}</div>" .
      "</div></div>"
    );
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
  final protected function sidebarInit(array $menuitems, $small = false) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert($this instanceof \MovLib\Presentation\AbstractPresenter);
    assert(!empty($this->title), "You have to initialize the page before you initialize the sidebar trait.");
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

  /**
   * Initialize the sidebar, including the default toolbox (view, edit, discussion, ...) items.
   *
   * @param \MovLib\Data\AbstractEntity $entity
   *   The entity for which the toolbox items will be generated.
   * @param array $menuitems [optional]
   *   Additional items for the sidebar.
   * @param boolean $small [optional]
   *   Whether to create a small sidebar or not, defaults to <code>FALSE</code>.
   * @return this
   */
  final protected function sidebarInitToolbox(\MovLib\Data\AbstractEntity $entity, array $menuitems = null, $small = false) {
    $viewAttributes = [ "class" => "ico ico-view" ];
    if ($this->schemaType && $this->request->path != $this->entity->route) {
      $viewAttributes["property"] = "url";
    }
    if ($entity->deleted) {
      $toolboxItems = [
        [ $this->entity->route, $this->intl->t("View"), $viewAttributes ],
        [ $this->intl->r("{$this->entity->routeKey}/discussion", $this->entity->routeArgs), $this->intl->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
        [ $this->intl->r("{$this->entity->routeKey}/history", $this->entity->routeArgs), $this->intl->t("History"), [ "class" => "ico ico-history" ] ]
      ];
    }
    else {
      $toolboxItems = [
        [ $this->intl->r($this->entity->routeKey, $this->entity->routeArgs), $this->intl->t("View"), $viewAttributes ],
        [ $this->intl->r("{$this->entity->routeKey}/edit", $this->entity->routeArgs), $this->intl->t("Edit"), [ "class" => "ico ico-edit" ] ],
        [ $this->intl->r("{$this->entity->routeKey}/discussion", $this->entity->routeArgs), $this->intl->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
        [ $this->intl->r("{$this->entity->routeKey}/history", $this->entity->routeArgs), $this->intl->t("History"), [ "class" => "ico ico-history" ] ],
        [ $this->intl->r("{$this->entity->routeKey}/delete", $this->entity->routeArgs), $this->intl->t("Delete"), [ "class" => "ico ico-delete separator" ] ],
      ];
    }
    if ($menuitems) {
      $toolboxItems = array_merge($toolboxItems, $menuitems);
    }
    return $this->sidebarInit($toolboxItems, $small);
  }

}
