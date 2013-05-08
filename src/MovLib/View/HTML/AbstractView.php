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


  // ------------------------------------------------------------------------------------------------------------------- Properties


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
   * Array containing all alert messages that might be set during execution.
   *
   * @var array
   */
  private $alerts = [];

  /**
   * The language object that was passed to the view by the controlling presenter.
   *
   * @var \MovLib\Entity\Language
   */
  protected $language;

  /**
   * The title of the page.
   *
   * @var string
   */
  protected $title = '';


  // ------------------------------------------------------------------------------------------------------------------- Constructor


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


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the class name of the <code>body</code>-element.
   *
   * @return string
   *   The class which should be applied to the <code>body</code>-element.
   */
  abstract public function getBodyClass();

  /**
   * Get the rendered content, without HTML head, header or footer.
   *
   * @return string
   */
  abstract public function getRenderedContent();


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Helper function to add a class to a tag where you don’t know if the class array is present or not.
   *
   * @param string|array $class
   *   The class or classes to add to the attributes.
   * @param array $attributes
   *   The attributes array.
   * @return \MovLib\View\HTML\AbstractView
   */
  protected final function addClass($class, &$attributes = []) {
    if (array_key_exists('class', $attributes) === false) {
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
   * Create empty HTML element, usage inspired by jQuery.
   *
   * @param string $tag
   *   The tag of the HTML element.
   * @param array $attributes
   *   [optional] The attributes that should be applied to the HTML element.
   * @return string
   *   The fully rendered empty HTML element.
   */
  protected final function emptyTag($tag, $attributes = []) {
    return '<' . $tag . $this->expandTagAttributes($attributes) . '>';
  }

  /**
   * Expand the given HTML element attributes for usage on an HTML element.
   *
   * @param array $attributes
   *   [optional] The attributes that should be expanded, if array is empty, empty stirng is returned.
   * @return string
   *   Expanded attributes or empty string.
   */
  protected final function expandTagAttributes($attributes = []) {
    if (empty($attributes)) {
      return '';
    }
    foreach ($attributes as $attribute => $value) {
      // Drop the title if it's empty.
      if ($attribute === 'title' && empty($value)) {
        unset($attributes[$attribute]);
      }
      else {
        if (is_array($value)) {
          $value = implode(' ', $value);
        }
        if ($attribute === 'href' || $attribute === 'src' || $attribute === 'action') {
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


  // ------------------------------------------------------------------------------------------------------------------- Public Final Methods


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
   *   [optional] The attributes that should be applied to the HTML element, defaults to no attributes.
   * @return string
   *   The rendered anchor element ready for print.
   */
  public final function getAnchor($href, $text, $attributes = []) {
    // Simple and fast check if the given route is internal and needs a slash at the beginning.
    if (empty($href) || strpos($href, '//') !== false || $href[0] !== '#' || $href[0] !== '/') {
      $href = "/$href";
    }
    $attributes['href'] = $href;
    return $this->getTag('a', $text, $attributes);
  }

  /**
   * Get all alerts that were previously set.
   *
   * The surrounding HTML is always included, no matter if there are any alerts or not. This is important to ensure
   * that the ID is present within the DOM for later alert insertion via JavaScript.
   *
   * @return string
   *   All alerts ready for print.
   */
  public final function getAlerts() {
    // By default we assume that there are no alerts at all. The additional empty CSS class makes sure tha the default
    // minimum height and margin is not applied to the spanning div element. It is important that any JavaScript that
    // might add content to the div removes the class to reapply the minimum height and margin.
    $content = ' empty">';
    if (empty($this->alerts) === false) {
      $content = '">' . implode('', $this->alerts);
    }
    return '<div class="row"><div id="alerts" class="span span-0' . $content . '</div></div>';
  }

  /**
   * Get the HTML footer including all script tags.
   *
   * @return string
   *   The footer ready for print.
   */
  public final function getFooter() {
    return
        '<footer id="footer">' .
          '<div class="row footer-rows">' .
            $this->getRow(
              '<h3>' . SITENAME . '</h3>' .
              $this->getLinklist([
                [ 'href' => __('about', 'route'), 'text' => __('About'), 'title' => sprintf(__('Find out more about %s.'), SITENAME) ],
                [ 'href' => __('blog', 'route'), 'text' => __('Blog'), 'title' => sprintf(__('Stay up to date about the latest developments around %s.'), SITENAME) ],
                [ 'href' => __('contact', 'route'), 'text' => __('Contact'), 'title' => __('Feedback is always welcome, no matter if positive or negative.') ],
                [ 'href' => __('resources', 'route'), 'text' => __('Logos &amp; Badges'), 'title' => __('If you want to create something awesome.') ],
                [ 'href' => __('legal', 'route'), 'text' => __('Legal'), 'title' => sprintf(__('Collection of the various legal terms and conditions used around %s.'), SITENAME) ]
              ], [ 'class' => 'no-list' ]),
              '<h3>' . __('Join in') . '</h3>' .
              $this->getLinklist([
                [ 'href' => __('sign-up', 'route'), 'text' => __('Sign up'), 'title' => __('') ]
              ], [ 'class' => 'no-list' ]),
              '<h3>' . __('Get help') . '</h3>' .
              $this->getLinklist([
                [ 'href' => __('help', 'route'), 'text' => __('Help'), 'title' => __('') ]
              ], [ 'class' => 'no-list' ])
            ) .
          '</div>' .
          '<div class="row footer-copyright">' .
            '<div class="span span-0"><p>' .
              $this->getIcon('cc') . ' ' . $this->getIcon('cc-zero') . ' ' . sprintf(__('Database data is available under the %s license.'), $this->getAnchor('//creativecommons.org/publicdomain/zero/1.0/deed.' . $this->language->getCode(), __('Creative Commons — CC0 1.0 Universal'), [ 'rel' => 'license' ])) . '<br>' .
              __('Additional terms may apply for third-party content, please refer to any license or copyright information that is additionaly stated.') . '<br>' .
              sprintf(__('By using this site, you agree to the %s and %s.'), $this->getAnchor(__('terms-of-use', 'route'), __('Terms of Use')), $this->getAnchor(__('privacy-policy', 'route'), __('Privacy Policy'))) .
            '</p></div>' .
          '</div>' .
        '</footer>' .
        '<div id="footer-logo"></div>'
      // @todo Add aggregated scripts
    ;
    // Please note that a closing body or html tag is not necessary!
  }

  /**
   * Helper method to generate HTML form elements.
   *
   * @param string $action
   *   The target URL of the form.
   * @param string $content
   *   The HTML content of the form.
   * @param array $attributes
   *   [optional] The attributes that should be applied to the HTML element, defaults to no attributes.
   * @param string $method
   *   [optional] Change submit method of the form, defaults to HTTP POST.
   * @return string
   *   The form ready for print.
   */
  public final function getForm($action, $content, $attributes = [], $method = 'post') {
    // Only internal routes are allowed!
    $attributes['action'] = "/$action";
    $attributes['method'] = $method;
    return $this->addClass('form', $attributes)->getTag('form', $content, $attributes);
  }

  /**
   * Get the HTML head element, this includes doctype and the html root element.
   *
   * @link http://www.netmagazine.com/features/create-perfect-favicon
   * @return string
   *   The head ready for print.
   */
  public final function getHead() {
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
        // @todo Cheapo cache buster (only for development)
        // @todo Deliver font from our server for full cache control
        '<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:400,400italic,700,700italic&subset=latin,cyrillic-ext,greek-ext,greek,vietnamese,latin-ext,cyrillic">' .
        // @todo Optimize icon css
        '<link rel="stylesheet" href="/assets/font/css/entypo.css?' . rand() . '">' .
        // @todo Aggregate all css files
        '<link rel="stylesheet" href="/assets/css/global.css?' . rand() . '">' .
        '<link rel="logo" href="/assets/img/logo/vector.svg">' .
        '<link rel="icon" href="/assets/img/logo/vector.svg">' .
        $icons .
        // @todo Add Windows 8 Tile icon / color
        '<link rel="copyright" href="//creativecommons.org/licenses/by-sa/3.0/">' .
        // @todo META tags
        // @todo Facebook tags
        '<meta name="viewport" content="width=device-width,initial-scale=1.0">' .
        '<meta http-equiv="X-UA-Compatible" content="IE=edge">' .
      '</head>' .
      '<body class="' . $this->getBodyClass() . '">'
    ;
  }

  /**
   * Get the HTML header, this includes the logo, navigations and search box.
   *
   * @return string
   *   The header ready for print.
   */
  public final function getHeader() {
    return
      '<header id="header">' .
        $this->getRow(
          $this->getHeaderLogo(),
          $this->getHeaderNavigation(),
          $this->getHeaderUserNavigation() . $this->getHeaderSearch()
        ) .
      '</header>'
    ;
  }

  /**
   * Get the HTML header main navigation.
   *
   * @see \MovLib\View\HTML\AbstractView::getNavigation
   * @return string
   *   The main navigation ready for print.
   */
  public final function getHeaderNavigation() {
    return $this->getNavigation('main', [
      /* 0 => */[
        'route' => __('movies', 'route'),
        'text' => __('Movies'),
        'title' => __('Browse all movies of this world, check out the latest additions or create a new entry yourself.'),
      ],
      /* 1 => */[
        'route' => __('persons', 'route'),
        'text' => __('Persons'),
        'title' => __('Browse all movie related persons of this world, check out the latest additions or create a new entry yourself.'),
      ],
      /* 2 => */[
        'route' => __('marketplace', 'route'),
        'text' => __('Marketplace'),
        'title' => __('Searching for a specific release of a movie or soundtrack, this is the place to go, for free of course.'),
      ],
    ], $this->activeHeaderNavigationPoint, null, ' <span>/</span> ');
  }

  /**
   * Get the HTML header search.
   *
   * @return string
   *   The header search ready for print.
   */
  public final function getHeaderSearch() {
    return $this->getForm(
      __('search', 'route'),
      $this->getInput('search', [
        'accesskey' => 'f',
        'placeholder' => __('Search…', 'input[type="search"]'),
        'title' => __('Enter the search term you wish to search for and hit enter. [alt-shift-f]'),
        'role' => 'textbox',
      ], true) .
      $this->getTag('button', $this->getIcon('search', [ 'class' => [ 'inline' ] ]), [
        'class' => [ 'input', 'input-submit', 'transition' ],
        'type' => 'submit',
        'title' => __('Start searching for the entered keyword.')
      ]),
      [ 'class' => [ 'search', 'header-search', 'clear-right', 'pull-right' ], 'role' => [ 'search' ] ]
    );
  }

  /**
   * Get the HTML header user navigation.
   *
   * @todo Menu has to change upon user state (signed in / out).
   * @see \MovLib\View\HTML\AbstractView::getNavigation
   * @return string
   *   The user navigation ready for print.
   */
  public final function getHeaderUserNavigation() {
    return $this->getNavigation('user', [
      /* 0 => */[
        'route' => __('sign_up', 'route'),
        'text' => __('Sign up'),
        'title' => __('Click here to sign up for a new and free account.'),
      ],
      /* 1 => */[
        'route' => __('sign_in', 'route'),
        'text' => __('Sign in'),
        'title' => __('Already have an account? Click here to sign in.'),
      ],
      /* 2 => */[
        'route' => __('help', 'route'),
        'text' => __('Help'),
        'title' => __('If you have questions click here to find our help articles.'),
      ]
    ], $this->activeHeaderUserNavigationPoint, [ 'class' => [ 'pull-right' ] ]);
  }

  /**
   * Helper method to generate HTML icon element.
   *
   * @param string $name
   *   The name of the icon, please refer to our icon table: @todo Were do we put this? Github Wiki?
   * @param array $attributes
   *   [optional] The attributes that should be applied to the icon element, defaults to no attributes.
   * @return string
   *   The icon ready for print.
   */
  public final function getIcon($name, $attributes = []) {
    $iconClasses = [ 'icon', "icon-$name" ];
    if (array_key_exists('class', $attributes)) {
      $attributes['class'] = array_merge($attributes['class'], $iconClasses);
    }
    else {
      $attributes['class'] = $iconClasses;
    }
    return $this->getTag('i', '', $attributes);
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
  public final function getInput($type, $attributes = [], $tabindex = false) {
    $attributes['type'] = $type;
    if ($tabindex === true) {
      $attributes['tabindex'] = $this->getTabindex();
    }
    return $this->addClass([ 'input', "input-$type" ], $attributes)->emptyTag('input', $attributes);
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
   *   [optional] The attributes that should be applied to the HTML element, defaults to no attributes.
   * @return string
   *   The image ready for print.
   */
  public final function getImage($src, $alt, $width, $height, $attributes = []) {
    foreach ([ 'src', 'alt', 'width', 'height' ] as $delta => $attribute) {
      $attributes[$attribute] = ${$attribute};
    }
    return $this->emptyTag('img', $attributes);
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
   *       'text' => __('Example', 'context'),
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
  public final function getNavigation($role, array $points, $activePointIndex = -1, $attributes = [], $glue = ' ') {
    foreach ($points as $delta => $point) {
      $point['attributes'] = [ 'class' => [ 'menuitem', "item-$delta" ], 'role' => 'menuitem' ];
      if ($delta === $activePointIndex) {
        $points[$delta] = $this->getTag('span', $point['text'], $point['attributes']);
      }
      else {
        $point['attributes']['title'] = $point['title'];
        $points[$delta] = $this->getAnchor($point['route'], $point['text'], $point['attributes']);
      }
    }
    $attributes['role'] = 'menu';
    return $this->addClass([ 'nav', "$role-nav" ], $attributes)->getTag('nav', implode($glue, $points), $attributes);
  }

  /**
   * Create an unordered list from the given array where each list item is a single anchor element.
   *
   * @param array $link
   *   The list items, associative array in the form:
   *   <ul>
   *     <li><b>delta</b>
   *       <ul>
   *         <li><b>href:</b> The target of the anchor.</li>
   *         <li><b>text:</b> The text that should be linked.</li>
   *         <li><b>[optional] title:</b> The title of the anchor.</li>
   *         <li><b>[optional] attributes:</b> Attributes that should be applied to the anchor.</li>
   *       </ul>
   *     </ul>
   *   </ul>
   * @param array $attributes
   *   [optional] Attributes that should be applied to the unordered list element, defaults to empty array.
   * @param string $type
   *   [optional] The list type, defaults to <code>&lt;ul&gt;</code>.
   * @return string
   *   The fully rendered unordered list.
   */
  public final function getLinklist(array $links, $attributes = [], $type = 'ul') {
    foreach ($links as $delta => $link) {
      if (array_key_exists('attributes', $link) === false) {
        $link['attributes'] = [];
      }
      if (array_key_exists('title', $link)) {
        $link['attributes']['title'] = $link['title'];
      }
      $links[$delta] = $this->getAnchor($link['href'], $link['text'], $link['attributes']);
    }
    return $this->getList($links, $attributes, $type);
  }

  /**
   * Get HTML list with the given array elements as list items.
   *
   * @param array $points
   *   The list items, array containing the string representation of each point.
   * @param array $attributes
   *   [optional] Attributes that should be applied to the list element, defaults to no attributes.
   * @param string $type
   *   [optional] The list type, defaults to unordered list.
   * @return string
   *   The list ready for print.
   */
  public final function getList(array $points, $attributes = [], $type = 'ul') {
    foreach ($points as $delta => $point) {
      $points[$delta] = '<li class="leaf-' . $delta . '">' . $point . '</li>';
    }
    return $this->getTag($type, implode('', $points), $attributes);
  }

  /**
   * Get the full rendered view, with HTML head, header and footer.
   *
   * @return string
   *   The rendered view ready for print.
   */
  public final function getRenderedView() {
    return
      $this->getHead() .
      $this->getHeader() .
      $this->getAlerts() .
      '<div id="content" class="' . $this->getBodyClass() . '-content">' . $this->getRenderedContent() . '</div>' .
      $this->getFooter()
    ;
  }

  /**
   * Get a row with equally distributed spans. The amount of passed strings defines the row count.
   *
   * @return string
   *   The rendered row ready for print.
   */
  public final function getRow() {
    $spans = func_get_args();
    $cols = count($spans);
    foreach ($spans as $delta => $span) {
      $spans[$delta] = '<div class="item-' . $delta . ' span span-' . $cols . '">' . $span . '</div>';
    }
    return '<div class="row">' . implode('', $spans) . '</div>';
  }

  /**
   * Create HTML element, usage inspired by jQuery.
   *
   * @param string $tag
   *   The tag of the HTML element.
   * @param string $content
   *   The content of the HTML element.
   * @param array $attributes
   *   [optional] The attributes that should be applied to the HTML element, defaults to no attributes.
   * @return string
   *   The element ready for print.
   */
  public final function getTag($tag, $content = '', $attributes = []) {
    return '<' . $tag . $this->expandTagAttributes($attributes) . '>' . $content . '</' . $tag . '>';
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Get the logo for the <code>&lt;header&gt;</code>-element.
   *
   * This method must stay public and not final. We have to overwrite this in the special homepage view!
   *
   * @see \MovLib\View\HTML\HomeView
   * @return string
   *   The logo ready for print.
   */
  public function getHeaderLogo() {
    return
      '<a id="logo" class="inline" href="/" title="' . String::checkPlain(sprintf(__('Go back to the %s home page.'), SITENAME)) . '">' .
        SITENAME . ' <small>' . sprintf(__('the %sfree%s movie library'), '<em class="serif">', '</em>') . '</small>' .
      '</a>'
    ;
  }

  /**
   * Get the title for the HTML <code>&lt;title&gt;</code>-element.
   *
   * This method must stay public and not final. We have to overwrite this in the special homepage view!
   *
   * @see \MovLib\View\HTML\HomeView
   * @return string
   *   The title ready for print.
   */
  public function getHeadTitle() {
    //# The em dash is used as separator character in the header title to denoate the source of the document (like in a
    //# quote the author), this should be translated to the equivalent character in your language. More information on
    //# this specific character can be found at Wikipedia: https://en.wikipedia.org/wiki/Dash#Em_dash
    return String::checkPlain($this->title . __(' — ', 'html head title')) . SITENAME;
  }

  /**
   * Add new alert message to the output of the view.
   *
   * @param string $message
   *   The message that should be displayed to the user.
   * @param string $title
   *   [optional] [recommended] Short descriptive title that summarizes the alert, defaults to no title at all.
   * @param string $severity
   *   [optional] The severity level of this alert, defaults to warning. Available severity levels are:
   *   <ul>
   *     <li>info</li>
   *     <li>warning (default)</li>
   *     <li>success</li>
   *     <li>error</li>
   *   </ul>
   * @param boolean $block
   *   [optional] If your message is very long, or your alert is very important, increase the padding around the message
   *   and enclose the title in a level-4 heading instead of the bold tag.
   * @return \MovLib\View\HTML\AbstractView
   */
  public final function setAlert($message, $title = '', $severity = 'warning', $block = false) {
    if (empty($title) === false) {
      if ($block === true) {
        $title = "<h4>$title</h4>";
      }
      else {
        $title = "<b>$title</b> ";
      }
    }
    $class = "alert alert-$severity";
    if ($block === true) {
      $class .= ' alert-block';
    }
    $this->alerts[] = '<div class="' . $class . '">' . $title . $message . '</div>';
    return $this;
  }

}
