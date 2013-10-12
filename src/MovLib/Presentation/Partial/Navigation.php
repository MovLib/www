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
namespace MovLib\Presentation\Partial;

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
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Navigation extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The navigation's ID.
   *
   * Please note that the actual ID used within the output will have <code>"-nav"</code> appended to it. This is to
   * ensure that there won't be any name collisions with the ID of the <code><body></code> elment if you use your
   * page's ID as ID for your navigation. The same is true for the title element of the navigation, the ID of that one
   * will be <code>"{$this->id}-nav-title"</code>.
   *
   * @var string
   */
  public $id;

  /**
   * The navigation's title.
   *
   * @var string
   */
  public $title;

  /**
   * The navigation's menuitems.
   *
   * @var array
   */
  public $menuitems;

  /**
   * The navigation's menuitem glue.
   *
   * The glue is used to implode the <var>Navigation::$menuitems</var>.
   *
   * @var string
   */
  public $glue = " ";

  /**
   * The navigation's attributes.
   *
   * Associative array with additional attributes for the <code><nav></code> element itself.
   *
   * @var array
   */
  public $attributes;

  /**
   * Flag indicating if the navigation's title should be hidden via CSS.
   *
   * If set to <code>TRUE</code> (default) the CSS class <i>visuallyhidden</i> will be added to the title of the
   * navigation. Set it to <code>FALSE</code> if you want the title displayed.
   *
   * @var boolean
   */
  public $hideTitle = true;

  /**
   * Callable that will be called with each menuitem before sending it to <code>$this->a()</code>.
   *
   * @see \MovLib\Test\Presentation\Partial\Navigation::testToStringClosure()
   * @var null|\Closure
   */
  public $closure;

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
   * @param string $id
   *   The globally unique identifier of this navigation.
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
   */
  public function __construct($id, $title, array $menuitems) {
    $this->id        = $id;
    $this->title     = $title;
    $this->menuitems = $menuitems;
  }

  /**
   * Get the navigation as string.
   *
   * @return string
   *   The navigation as string.
   */
  public function __toString() {
    $menuitems = "";
    if ($this->unorderedList === true) {
      $menuitems = "<ul class='no-list'>";
    }
    $c = count($this->menuitems);
    for ($i = 0; $i < $c; ++$i) {
      if ($i !== 0) {
        $menuitems .= $this->glue;
      }
      $this->menuitems[$i][2]["role"] = "menuitem";
      if (is_callable($this->closure)) {
        call_user_func_array($this->closure, [ &$this->menuitems[$i], $i, $c ]);
      }
      $menuitem   = $this->a($this->menuitems[$i][0], $this->menuitems[$i][1], $this->menuitems[$i][2]);
      $menuitems .= ($this->unorderedList === true) ? "<li>{$menuitem}</li>" : $menuitem;
    }
    if ($this->unorderedList === true) {
      $menuitems .= "</ul>";
    }
    $this->attributes["id"]   = "{$this->id}-nav";
    $this->attributes["role"] = "navigation";
    $this->hideTitle          = ($this->hideTitle === true) ? " class='visuallyhidden'" : "";
    return "<nav{$this->expandTagAttributes($this->attributes)}><h2{$this->hideTitle} id='{$this->id}-nav-title'>{$this->title}</h2><div role='menu'>{$menuitems}</div></nav>";
  }

}
