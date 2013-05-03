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

use \MovLib\Entity\Language;
use \MovLib\Utility\String;

/**
 * The <b>AbstractView</b> is the base class for all other HTML views.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractView {

  /**
   * The title of the page.
   *
   * @var string
   */
  protected $title = '';

  /**
   * The language object that was passed to the view by the controlling presenter.
   *
   * @var \MovLib\Entity\Language
   */
  protected $language;

  /**
   * Array index of the active header navigation point.
   *
   * This is kept complicated to ensure that it's not easily overwritten. The active state of a header navigation point
   * is not determined by the presenter, because it only affects the view itself, but not the presentation of the pages
   * content. The amount of header navigation points is very limited and if a new view class is created the developer is
   * forced to have a look at the methods body to find out which index might be the right one.
   *
   * @see \MovLib\View\HTML\AbstractView::$activeHeaderUserNavigationPoint
   * @var int
   */
  protected $activeHeaderNavigationPoint;

  /**
   * Array index of the active header user navigation point.
   *
   * @see \MovLib\View\HTML\AbstractView::$activeHeaderNavigationPoint
   * @var int
   */
  protected $activeHeaderUserNavigationPoint;

  /**
   * Initialize new view.
   *
   * @param \MovLib\Entity\Language $language
   *   The language object from the presenter that controls this view.
   * @param string $title
   *   The unique title of this view.
   */
  public function __construct(Language $language, $title) {
    $this->language = $language;
    $this->title = $title;
  }


  /**
   * Get current counter of the global <code>tabindex</code>-attribute for HTML elements and increment the static
   * variable associated with it once.
   *
   * Many browsers have a very strange <kbd>tab</kbd>-policy. This counter variable is to make sure that users who love
   * or have to use the keyboard can easily navigate through our pages. You should only use the tabindex for
   * <strong>important</strong> page elements. For instance, the main navigation isn't that important for a user if he
   * already reached the page he wants. On the other hand the header search field is a very important field, in contrast
   * to that the associated search submit button is not. If a user is using the <kbd>tab</kbd>-key to navigate through
   * the page, she or he most certainly also knows that he can easily submit the form by hitting enter within the search
   * field itself.
   *
   * Use all your knowledge as web user to decide whetever an element is important enough to make use of this index or
   * not.
   *
   * @link http://www.w3.org/TR/2010/WD-wai-aria-practices-20100916/#focus_tabindex
   * @link http://www.w3.org/TR/wai-aria/usage#managingfocus
   * @staticvar int $tabindex
   *   Static counter to keep track of the page's tabindex accros a single request.
   * @return int
   *   The current counter of the index.
   */
  protected final function getTabindex() {
    static $tabindex = 1;
    return $tabindex++;
  }

  /**
   * Get the title for the HTML <code>&lt;title&gt;</code>-element.
   *
   * @return string
   *   The fully styled title for the HTML <code>&lt;title&gt;</code>-element.
   */
  public function getHeadTitle() {
    //# The em dash is used as separator character in the header title to denoate the source of the document (like in a
    //# quote the author), this should be translated to the equivalent character in your language. More information on
    //# this specific character can be found at Wikipedia: https://en.wikipedia.org/wiki/Dash#Em_dash
    return String::checkPlain($this->title . __(' — ', 'html head title')) . SITENAME;
  }

  /**
   * Expand the given HTML element attributes for usage on an HTML element.
   *
   * @param array $attributes
   *   [optional] The attributes that should be expanded, if array is empty, empty stirng is returned.
   * @return string
   *   Expanded attributes or empty string.
   */
  protected final function expandTagAttributes(array $attributes = []) {
    if (empty($attributes)) {
      return '';
    }
    foreach ($attributes as $attribute => $value) {
      // Drop the title if it's empty.
      if (empty($value) && 'title' === $attribute) {
        unset($attributes[$attribute]);
      }
      else {
        if (is_array($value)) {
          $value = implode(' ', $value);
        }
        if ('href' === $attribute || 'src' === $attribute || 'action' === $attribute) {
          $value = htmlentities($value);
        }
        else {
          $value = String::checkPlain($value);
        }
        $attributes[$attribute] = $attribute . '="' . $value . '"';
      }
    }
    return ' ' . implode(' ', $attributes);
  }

  /**
   * Helper function to add a class to a tag where you don’t know if the class array is present or not.
   *
   * @param string|array $class
   *   The class or classes to add to the attributes.
   * @param array $attributes
   *   The attributes array.
   * @return \MovLib\View\HTML\AbstractView
   */
  protected final function addClass($class, array &$attributes = []) {
    if (!isset($attributes['class'])) {
      $attributes['class'] = [];
    }
    if (is_array($class)) {
      $attributes['class'] = array_merge($attributes['class'], $class);
    }
    else {
      $attributes['class'][] = $class;
    }
    return $this;
  }

  /**
   * Create HTML element, usage inspired by jQuery.
   *
   * @param string $tag
   *   The tag of the HTML element.
   * @param string $content
   *   The content of the HTML element.
   * @param array $attributes
   *   [optional] The attributes that should be applied to the HTML element.
   * @return string
   *   The fully rendered HTML element.
   */
  protected final function tag($tag, $content = '', array $attributes = []) {
    return '<' . $tag . $this->expandTagAttributes($attributes) . '>' . $content . '</' . $tag . '>';
  }

  /**
   * Create empty HTML element, usage inspired by jQuery.
   *
   * @param string $tag
   *   The tag of the HTML element.
   * @param array $attributes
   *   [optional] The attributes that should be applied to the HTML element.
   * @return string
   *   The fully rendered empty HTML element.
   */
  protected final function emptyTag($tag, array $attributes = []) {
    return '<' . $tag . $this->expandTagAttributes($attributes) . '>';
  }

  /**
   * Helper function to generate HTML anchor element.
   *
   * Usage example with an internal route:
   * <pre>
   * $this->link(__('example', 'route'), __('Linktext'), [
   *   'id' => 'example-anchor-id',
   *   'class' => [ 'example-anchor-class-1', 'example-anchor-class-2' ],
   *   'tabindex' => $this->getTabindex(),
   *   'data-example' => 'example data',
   * ]);
   * </pre>
   *
   * Please note that you have to translate texts that you pass to this method before passing them. This is important
   * because <em>xgettext</em> extracts the translations via a static code analysis and can not handle
   *
   * @param string $href
   *   The hyper reference of this anchor tag, can be a hash, external URL or internal route (leading slash is added
   *   automatically, see above example on how to call this method with a route).
   * @param string $text
   *   The text that should appear within the anchor element: <code>&lt;a&gt;$text&lt;/a&gt;</code>
   * @param array $attributes
   *   [optional] The attributes that should be applied to the HTML element.
   * @return string
   *   The rendered HTML anchor element.
   */
  protected final function anchor($href, $text, array $attributes = []) {
    // Simple and fast check if the given route is internal and needs a slash at the beginning.
    if (empty($href) || strpos($href, '//') !== false || $href[0] !== '#' || $href[0] !== '/') {
      $href = "/$href";
    }
    $attributes['href'] = $href;
    return $this->tag('a', $text, $attributes);
  }

  /**
   * Helper method to generate HTML image elements.
   *
   * @param string $src
   *   Absolute path to the image.
   * @param string $alt
   *   Alternative text describing the images content.
   * @param int $width
   *   The width of the image.
   * @param int $height
   *   The height of the image.
   * @param array $attributes
   *   [optional] The attributes that should be applied to the HTML element.
   * @return string
   *   The rendered HTML image element.
   */
  protected final function img($src, $alt, $width, $height, array $attributes = []) {
    $attributes['src'] = $src;
    $attributes['alt'] = $alt;
    $attributes['width'] = $width;
    $attributes['height'] = $height;
    return $this->emptyTag('img', $attributes);
  }

  /**
   * Helper method to generate HTML form elements.
   *
   * @param string $action
   *   The target URL of the form.
   * @param string $content
   *   The HTML content of the form.
   * @param array $attributes
   *   [optional] The attributes that should be applied to the HTML element.
   * @param string $method
   *   [optional] Change submit method of the form, by default all forms are submitted using HTTP POST.
   * @return string
   *   The rendered HTML form element.
   */
  protected final function form($action, $content, array $attributes = [], $method = 'post') {
    // Only internal routes are allowed!
    $attributes['action'] = "/$action";
    $attributes['method'] = $method;
    return $this->addClass('form', $attributes)->tag('form', $content, $attributes);
  }

  /**
   * Helper method to generate HTML input elements.
   *
   * @param string $type
   *   The input elements type.
   * @param array $attributes
   *   [optional] The attributes that should be applied to the HTML element.
   * @param boolean $tabindex
   *   [optional] Set to true to add tabindex attribute to the element.
   * @return string
   *   The rendered HTML input element.
   */
  protected final function input($type, array $attributes = [], $tabindex = false) {
    $attributes['type'] = $type;
    if ($tabindex) {
      $attributes['tabindex'] = $this->getTabindex();
    }
    return $this->addClass([ 'input', "input-$type" ], $attributes)->emptyTag('input', $attributes);
  }

  /**
   * Get the HTML head element, this includes doctype and the html root element.
   *
   * @todo Add link to icon blog article (can not remember the name, but there must be a bookmark somewhere).
   * @return string
   */
  public final function getHead() {
    /* @var $icons string */
    $icons = '';

    // IMPORTANT: Go from big to small, many browsers simply use the last one, even if the need another one.
    foreach ([ '256', '128', '64', '32', '24', '16' ] as $delta => $size) {
      $icons .= '<link rel="icon" sizes="' . $size . 'x' . $size . '" href="/assets/img/logo/' . $size . '.png">';
    }

    // @todo Add Apple touch icons.

    return
      '<!doctype html>' .
      '<html id="nojs" lang="' . $this->language->getCode() . '" dir="' . $this->language->getDirection() . '">' .
      '<head>' .
        // If any DNS record should be pre-fetched:
        //'<link rel="dns-prefetch" href="">' .
        '<title>' . $this->getHeadTitle() . '</title>' .
        // @todo Optimize icon css
        // @todo Cheapo cache buster (only for development)
        '<link rel="stylesheet" href="/assets/font/css/entypo.css?' . rand() . '">' .
        '<link rel="stylesheet" href="/assets/css/global.css?' . rand() . '">' .
        '<link rel="logo" href="/assets/img/logo/vector.svg">' .
        '<link rel="icon" href="/assets/img/logo/vector.svg">' .
        $icons .
        // @todo Add Windows 8 Tile icon / color
        '<link rel="copyright" href="//creativecommons.org/licenses/by-sa/3.0/">' .
        // @todo PNG favicons
        // @todo META tags
        // @todo Facebook tags
      '</head>' .
      '<body>' .
        '<div id="container">'
    ;
  }

  /**
   * Get the logo for the <code>&lt;header&gt;</code>-element.
   *
   * @return string
   *   HTML mark-up for the logo.
   */
  public function getHeaderLogo() {
    return
      '<a id="logo" href="/" title="' . String::checkPlain(sprintf(__('Go back to the %s home page.', SITENAME))) . '">' .
        SITENAME . ' <small>' . sprintf(__('the %sfree%s movie library', '<em class="serif">', '</em>')) . '</small>' .
      '</a>'
    ;
  }

  /**
   * Helper method to generate a navigation.
   *
   * @param string $role
   *   The logic role of this navigation menu (e.g. <em>main</em>, <em>footer</em>, ...).
   * @param array $points
   *   Keyed array containing the navigation points in the form:
   *   <pre>
   *   array(
   *     0 => array(
   *       'route' => __('example', 'route'),
   *       'linktext' => __('Example', 'context'),
   *       'title' => __('This is an example for a correct navigation point link title.'),
   *     )
   *   )
   *   </pre>
   * @param int $activePointIndex
   *   [optional] Index of the element within the array that should be marked active.
   * @param array $attributes
   *   [optional] The attributes that should be applied to the HTML nav element.
   * @param string $glue
   *   [optional] The string that is used to combine the various navigation points.
   * @return string
   *   Fully rendered navigation.
   */
  protected final function getNavigation($role, array $points, $activePointIndex = -1, array $attributes = [], $glue = ' ') {
    foreach ($points as $delta => $point) {
      $point['attr'] = [ 'class' => [ 'menuitem', "item-$delta" ], 'role' => 'menuitem' ];
      if ($delta === $activePointIndex) {
        $points[$delta] = $this->tag('span', $point['linktext'], $point['attr']);
      }
      else {
        $point['attr']['title'] = $point['title'];
        $points[$delta] = $this->anchor($point['route'], $point['linktext'], $point['attr']);
      }
    }
    $attributes['role'] = 'menu';
    return $this->addClass([ 'nav', "$role-nav" ], $attributes)->tag('nav', implode($glue, $points), $attributes);
  }

  /**
   * Get the header search.
   *
   * @return string
   */
  public final function getHeaderSearch() {
    return $this->form(
      __('search', 'route'),
      $this->input('search', [
        'accesskey' => 'f',
        'placeholder' => __('Search…', 'input[type="search"]'),
        'title' => __('Enter the search term you wish to search for and hit enter. [alt-shift-f]'),
        'role' => 'textbox',
      ], true) .
      $this->tag('button', '<i class="icon-search inline"></i>', [
        'class' => [ 'input', 'input-submit' ],
        'type' => 'submit',
        'title' => __('Start searching for the entered keyword.')
      ]),
      [ 'class' => [ 'search', 'header-search', 'inline' ], 'role' => [ 'search' ] ]
    );
  }

  /**
   * Get the header navigation.
   *
   * @todo Every header nav point has to have a descriptive title.
   * @see \MovLib\View\HTML\AbstractView::getNavigation
   * @return string
   */
  public final function getHeaderNavigation() {
    return $this->getNavigation('main', [
      /* 0 => */[
        'route' => __('movies', 'route'),
        'linktext' => __('Movies', 'header .main-nav'),
        'title' => '',
      ],
      /* 1 => */[
        'route' => __('persons', 'route'),
        'linktext' => __('Persons', 'header .main-nav'),
        'title' => '',
      ],
      /* 2 => */[
        'route' => __('marketplace', 'route'),
        'linktext' => __('Marketplace', 'header .main-nav'),
        'title' => '',
      ],
    ], $this->activeHeaderNavigationPoint, [ 'class' => [ 'inline' ] ], ' / ');
  }

  /**
   * Get the header user navigation.
   *
   * @todo Every header user nav point has to have a descriptive title.
   * @todo Menu has to change upon user state (signed in / out).
   * @see \MovLib\View\HTML\AbstractView::getNavigation
   * @return string
   */
  public final function getHeaderUserNavigation() {
    return $this->getNavigation('user', [
      /* 0 => */[
        'route' => __('sign_up', 'route'),
        'linktext' => __('Sign up', 'header .user-nav'),
        'title' => '',
      ],
      /* 1 => */[
        'route' => __('sign_in', 'route'),
        'linktext' => __('Sign in', 'header .user-nav'),
        'title' => '',
      ],
      /* 2 => */[
        'route' => __('help', 'route'),
        'linktext' => __('Help', 'header .user-nav'),
        'title' => '',
      ]
    ], $this->activeHeaderUserNavigationPoint, [ 'class' => [ 'inline' ] ]);
  }

  /**
   * Get the header.
   *
   * @return string
   */
  public final function getHeader() {
    return
      '<header id="header" class="clearfix">' .
        $this->getHeaderLogo() .
        $this->getHeaderNavigation() .
        $this->getHeaderSearch() .
        $this->getHeaderUserNavigation() .
      '</header>'
    ;
  }

  /**
   * Get the footer.
   *
   * @return string
   */
  public final function getFooter() {
    // Please note that a closing body or html tag is not necessary, let us save the bytes.
    return
          '<footer id="footer">' .
            '<div class="row">' .

            '</div>' .
            '<div class="copyright">' .
              sprintf(
                __('Text is available under the %s; additional terms may apply. By using this site, you agree to the %s and %s.'),
                '<a rel="license" href="//creativecommons.org/licenses/by-sa/3.0/">' . __('Creative Commons Attribution-ShareAlike License') . '</a>',
                '<a href="' . __('terms-of-use', 'route') . '">' . __('Terms of Use') . '</a>',
                '<a href="' . __('privacy-policy', 'route') . '">' . __('Privacy Policy') . '</a>'
              ) .
            '</div>' .
          '</footer>' .
        '</div>' // end #container
      // @todo Add aggregated scripts
    ;
  }

  /**
   * Get the rendered content, without HTML head, header and footer.
   *
   * @return string
   */
  abstract public function getRenderedContent();

  /**
   * Get the full rendered view, with HTML head, header and footer.
   *
   * @return string
   */
  public final function getRenderedView() {
    return
      $this->getHead() .
      $this->getHeader() .
      $this->getRenderedContent() .
      $this->getFooter()
    ;
  }

}
