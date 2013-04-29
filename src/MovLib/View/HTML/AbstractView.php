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
   * @since 0.0.1-dev
   */
  protected $title = '';

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
   * @since 0.0.1-dev
   */
  protected $activeHeaderNavigationPoint;

  /**
   * Array index of the active header user navigation point.
   *
   * @see \MovLib\View\HTML\AbstractView::$activeHeaderNavigationPoint
   * @var int
   * @since 0.0.1-dev
   */
  protected $activeHeaderUserNavigationPoint;

  /**
   * Initialize new view.
   *
   * @param string $title
   *   The unique title of this view.
   * @since 0.0.1-dev
   */
  public function __construct($title) {
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
   * @since 0.0.1-dev
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
   * @since 0.0.1-dev
   */
  public function getHeadTitle() {
    //# The em dash is used as separator character in the header title to denoate the source of the document (like in a
    //# quote the author), this should be translated to the equivalent character in your language. More information on
    //# this specific character can be found at Wikipedia: https://en.wikipedia.org/wiki/Dash#Em_dash
    return $this->title . p_('html head title', ' — ') . SITENAME;
  }

  /**
   * Get the HTML head element, this includes doctype and the html root element.
   *
   * @return string
   * @since 0.0.1-dev
   */
  public final function getHead() {
    return
      '<!doctype html>' .
      '<html id="nojs">' . // @todo Add lang and dir attributes
      '<head>' .
        // If any DNS record should be pre-fetched:
        //'<link rel="dns-prefetch" href="">' .
        '<title>' . $this->getHeadTitle() . '</title>' .
        '<link rel="stylesheet" href="/assets/css/global.css">' .
        // @todo Aggregates CSS \w cache buster.
        '<link rel="logo" type="image/svg" href="/assets/img/logo.svg">' .
        '<link rel="icon" type="image/svg" href="/assets/img/logo.svg">' .
        '<link rel="copyright" href="//creativecommons.org/licenses/by-sa/3.0">' .
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
   * @since 0.0.1-dev
   */
  public function getHeaderLogo() {
    return '<a id="logo" href="/" title="' . _('Go back to the MovLib home page.') . '">MovLib <small>the <em class="serif">free</em> movie library</small></a>';
  }

  /**
   * Get the header search.
   *
   * @return string
   * @since 0.0.1-dev
   */
  public final function getHeaderSearch() {
    return
      '<form class="search" action="/' . p_('route', 'search') . '" method="post">' .
        '<input class="input-search" type="search" tabindex="' . $this->getTabindex() . '" placeholder="' . p_('input[type="search"]', 'Search…') . '">' .
        '<input class="input-submit" type="submit" value="' . p_('input[type="submit"]', 'Search') . '">' .
      '</form>'
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
   *       'route' => p_('route', 'example'),
   *       'linktext' => p_('context', 'Example'),
   *       'title' => _('This is an example for a correct navigation point link title.'),
   *     )
   *   )
   *   </pre>
   * @param int $activePointIndex
   *   [optional] Index of the element within the array that should be marked active.
   * @return string
   *   Fully rendered navigation.
   * @since 0.0.1-dev
   */
  protected final function getNavigation($role, array $points, $activePointIndex = -1) {
    /* @var $return string */
    $return = '<nav class="nav ' . $role . '-nav"><ul class="menu" role="menu">';

    /*
     * @var $delta int
     * @var $point array
     */
    foreach ($points as $delta => $point) {
      $return .= '<li role="menuitem" class="menuitem item-' . $delta;
      if ($delta === $activePointIndex) {
        $return .= ' active">' . $point['linktext'];
      }
      else {
        $point['title'] = empty($point['title']) ? '' : ' title="' . $point['title'] . '"';
        $return .= '"><a href="/' . $point['route'] . '"' . $point['title'] . '>' . $point['linktext'] . '</a>';
      }
      $return .= '</li>';
    }

    return $return . '</ul></nav>';
  }

  /**
   * Get the header navigation.
   *
   * @todo Every header nav point has to have a descriptive title.
   * @see \MovLib\View\HTML\AbstractView::getNavigation
   * @return string
   * @since 0.0.1-dev
   */
  public final function getHeaderNavigation() {
    return $this->getNavigation('main', [
      /* 0 => */[
        'route' => p_('route', 'movies'),
        'linktext' => p_('header .main-nav', 'Movies'),
      ],
      /* 1 => */[
        'route' => p_('route', 'persons'),
        'linktext' => p_('header .main-nav', 'Persons'),
      ],
      /* 2 => */[
        'route' => p_('route', 'marketplace'),
        'linktext' => p_('header .main-nav', 'Marketplace'),
      ],
    ], $this->activeHeaderNavigationPoint);
  }

  /**
   * Get the header user navigation.
   *
   * @todo Every header user nav point has to have a descriptive title.
   * @todo Menu has to change upon user state (signed in / out).
   * @see \MovLib\View\HTML\AbstractView::getNavigation
   * @return string
   * @since 0.0.1-dev
   */
  public final function getHeaderUserNavigation() {
    return $this->getNavigation('user', [
      /* 0 => */[
        'route' => p_('route', 'sign_up'),
        'linktext' => p_('header .user-nav', 'Sign up'),
      ],
      /* 1 => */[
        'route' => p_('route', 'sign_in'),
        'linktext' => p_('header .user-nav', 'Sign in'),
      ],
      /* 2 => */[
        'route' => p_('route', 'help'),
        'linktext' => p_('header .user-nav', 'Help'),
      ]
    ], $this->activeHeaderUserNavigationPoint);
  }

  /**
   * Get the header.
   *
   * @return string
   * @since 0.0.1-dev
   */
  public final function getHeader() {
    return
      '<header id="header">' .
        $this->getHeaderLogo() .
        $this->getHeaderSearch() .
        $this->getHeaderNavigation() .
        $this->getHeaderUserNavigation() .
      '</header>'
    ;
  }

  /**
   * Get the footer.
   *
   * @return string
   * @since 0.0.1-dev
   */
  public final function getFooter() {
    // Please note that a closing body or html tag is not necessary, let us save the bytes.
    return
          '<footer id="footer">' .
            // @todo Add footer content
          '</footer>' .
        '</div>' // end #container
      // @todo Add aggregated scripts
    ;
  }

  /**
   * Get the rendered content, without HTML head, header and footer.
   *
   * @return string
   * @since 0.0.1-dev
   */
  abstract public function getRenderedContent();

  /**
   * Get the full rendered view, with HTML head, header and footer.
   *
   * @return string
   * @since 0.0.1-dev
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
