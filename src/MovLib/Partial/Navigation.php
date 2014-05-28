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

/**
 * HTML navigation including all ARIA roles.
 *
 * Everything in this class is kept public, we want to ensure highest flexibility while working with this.
 *
 * The navigation has all accessability (ARIA) roles correctly applied. The <code><nav></code> element will be used
 * as the most outter wrapper element around the navigation. Unlike most other software we aren't using an unordered
 * list to create the navigation, instead we use <i>normal</i> anchor elements. The reason for this is simple. The ARIA
 * attributes help us to define the real semantic of the mark-up. This is ensured by applying <code>role='menuitem'</code>
 * to each anchor element. We save a lot of document objects this way (consider <code><ul><li><a></li></ul></code> vs.
 * <code><a></code> with many menuitems). But there is an optional parameter available that allows developers to wrap
 * the menuitems in an unordered list, if it really makes sense (e.g. for styling via CSS).
 *
 * @link http://stackoverflow.com/questions/12279113/recommended-wai-aria-implementation-for-navigation-bar-menu
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Navigation {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Navigation";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The navigation's attributes.
   *
   * Associative array with additional attributes for the <code><nav></code> element itself.
   *
   * @var array
   */
  protected $attributes;

  /**
   * The navigation's menuitem glue.
   *
   * The glue is used to implode the <var>Navigation::$menuitems</var>.
   *
   * @var string
   */
  public $glue = " ";

  /**
   * The level of the heading.
   *
   * @var string
   */
  public $headingLevel = "2";

  /**
   * Flag indicating if the navigation's title should be hidden via CSS.
   *
   * If set to <code>TRUE</code> (default) the CSS class <i>vh</i> will be added to the title of the
   * navigation. Set it to <code>FALSE</code> if you want the title displayed.
   *
   * @var boolean
   */
  public $hideTitle = true;

  /**
   * Whether to ignore the query string while checking if the link should be marked active or not. Default is to ignore
   * the query string.
   *
   * @see \MovLib\Presentation\AbstractBase::a()
   * @var boolean
   */
  public $ignoreQuery = false;

  /**
   * The navigation's menuitems.
   *
   * @var array
   */
  public $menuitems;

  /**
   * The presenting presenter.
   *
   * @var \MovLib\Presentation\AbstractPresenter
   */
  protected $presenter;

  /**
   * The navigation's title.
   *
   * @var string
   */
  protected $title;

  /**
   * Flag indicating if all menuitems should be wrapped in an unordered list.
   *
   * Please be sure to read and understand the class description before chaning this flag to <code>TRUE</code>.
   *
   * @var boolean
   */
  public $unorderedList = false;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new navigation partial.
   *
   * @param \MovLib\Presentation\AbstractPresenter $presenter
   *   The presenting presenter.
   * @param string $title
   *   Descriptive title for the complete navigation.
   * @param array $menuitems
   *   The menuitems of this navigation. Numeric array with numeric arrays where each sub-array has to have the
   *   following form:
   *   <ul>
   *     <li><code>0</code> contains the already translated and expanded route</li>
   *     <li><code>1</code> contains the already translated linktext</li>
   *     <li><code>2</code> [optional] can contain an associative array with additional attributes that should be
   *     applied to the menuitem</li>
   *   </ul>
   *   For a more in-depth explanation have a look at {@see AbstractPage::a()}.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the <code><nav></code> element.
   */
  public function __construct(\MovLib\Presentation\AbstractPresenter $presenter, $title, array $menuitems, array $attributes = null) {
    $this->presenter  = $presenter;
    $this->title      = $title;
    $this->menuitems  = $menuitems;
    $this->attributes = $attributes;
  }

  /**
   * Get the navigation as string.
   *
   * @return string
   *   The navigation as string.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $menuitems = null;
      foreach ($this->menuitems as $menuitem) {
        if ($menuitems && $this->unorderedList === false) {
          $menuitems .= $this->glue;
        }
        if (!empty($menuitem)) {
          if (is_array($menuitem)) {
            $menuitem[2]["role"] = "menuitem";
            $menuitem            = $this->presenter->a($menuitem[0], $menuitem[1], $menuitem[2], $this->ignoreQuery);
          }
          $menuitems .= $this->unorderedList === true ? "<li>{$menuitem}</li>" : $menuitem;
        }
      }
      if ($this->unorderedList === true) {
        $menuitems = "<ul class='no-list'>{$menuitems}</ul>";
      }
      $this->attributes["role"] = "navigation";
      $hideTitle                = $this->hideTitle ? " class='vh'" : null;
      return "<nav{$this->presenter->expandTagAttributes($this->attributes)}><h{$this->headingLevel}{$hideTitle}>{$this->title}</h{$this->headingLevel}><div role='menu'>{$menuitems}</div></nav>";
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return (string) new \MovLib\Partial\Alert("<pre>{$e}</pre>", "Error Rendering Element", \MovLib\Partial\Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Set a menuitem active.
   *
   * @param integer $index
   *   The index within the array to set active.
   * @return this
   */
  public function setActive($index) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!isset($this->menuitems[$index])) {
      throw new \InvalidArgumentException("No menuitem with index '{$index}'");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return $this->presenter->addClass("active", $this->menuitems[$index][2]);
  }

}
