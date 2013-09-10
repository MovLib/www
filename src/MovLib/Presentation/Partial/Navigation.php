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
   * Callable that will be called with each menuitem before sending it to
   * <code>\MovLib\Presentation\AbstractPage::a()</code>.
   *
   * Useful if you want to iterate over all menuitems after a navigational object was passed around in the application.
   * It's important that you actually bind the closure to this object. Calling the closure's magic <code>__invoke</code>
   * method would make it slow, therefor binding is the preferred way (although requires more writing and the
   * introduction of an additional variable, in order to call <code>\Closure::bindTo()</code>. The reason for this is
   * simple, <code>__invoke()</code> is a special magic method to call an object as function and involves a lot of
   * checks and computation. Whilst binding the closure to this object actually copies the created anonymous function
   * into this instance as a real regular method.
   *
   * <b>Example:</b>
   * We could solve the <code>\MovLib\Presentation\Page::getBreadcrumb()</code> with a closure as well.
   * <pre>
   *  $breadcrumb = new Navigation("foo", $i18n->t("Bar"), []);
   *  $closure = function (&$menuitem, $index, $count) {
   *    global $i18n;
   *    if ($index !== 0 && $index !== ($count - 1) && mb_strlen($menuitem[1]) > 25) {
   *      $menuitem[2]["title"] = $menuitem[1];
   *      $menuitem[1] = mb_strimwidth($menuitem[1], 0, 25, $i18n->t("…"));
   *    }
   *  };
   *  $closure->bindTo($breadcrumb);
   * </pre>
   * But you might have guessed that the closure makes no sense at this point, because it involves lots of math to
   * exclude the first and the last element. Instead it's much more intelligent to simply loop over the additional
   * breadcrumb trails (as it is really implemented). But I hope you get the idea.
   *
   * @var null
   */
  public $closure = null;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new navigation partial.
   *
   * Create a new navigation with all accessability (ARIA) roles correctly applied. The <code><nav></code>-element will
   * be used to create the navigation. Unlike most developers we aren't using an unordered list to create the
   * navigation, instead we use <i>normal</i> anchor elements. The reason for this is simple. ARIA helps us to define
   * the real meaning of each anchor element, by applying <code><a role='menuitem'></code> to each anchor. Additionally
   * we save a lot of document objects (consider <code><ul><li><a></code> vs. <code><a></code> with many navigational
   * points).
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
    $this->id = $id;
    $this->title = $title;
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
    $c = count($this->menuitems);
    for ($i = 0; $i < $c; ++$i) {
      if ($i !== 0) {
        $menuitems .= $this->glue;
      }
      $this->menuitems[$i][2]["role"] = "menuitem";
      if ($this->closure) {
        $this->closure($this->menuitems[$i], $i, $c);
      }
      $menuitems .= $this->a($this->menuitems[$i][0], $this->menuitems[$i][1], $this->menuitems[$i][2]);
    }
    $this->attributes["id"] = "{$this->id}-nav";
    $this->attributes["role"] = "menu";
    $this->attributes["aria-labelledby"] = "{$this->id}-title";
    $this->hideTitle = ($this->hideTitle === true) ? " class='visuallyhidden'" : "";
    return "<nav{$this->expandTagAttributes($this->attributes)}><h2{$this->hideTitle} id='{$this->id}-nav-title' role='presentation'>{$this->title}</h2>{$menuitems}</nav>";
  }

}
