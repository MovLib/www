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
   */
  protected $title = '';

  /**
   * String containing the complete rendered view as it will be sent to the browser.
   *
   * @var string
   */
  protected $renderedView = '';

  public function __construct($title) {
    $this->setTitle($title);
  }

  /**
   * Set the title of the page.
   *
   * @param string $title
   *   The title of the page.
   * @return \MovLib\View\HTML\AbstractView
   */
  private function setTitle($title) {
    $this->title = $title;
    return $this;
  }

  protected final function getTitle() {
    return $this->title;
  }

  /**
   * Get the title for the HTML <code>&lt;title&gt;</code>-element.
   *
   * @return string
   *   The fully styled title for the HTML <code>&lt;title&gt;</code>-element.
   */
  protected final function getHeadTitle() {
    return $this->title . ' — MovLib';
  }

  /**
   *
   *
   * @return string
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
   */
  public function getHeaderLogo() {
    return '<a id="logo" href="/" title="' . _('Go back to the MovLib home page.') . '">MovLib <small>the <em class="serif">free</em> movie library</small></a>';
  }

  /**
   *
   *
   * @return string
   */
  public final function getHeader() {
    return
      '<header id="header">' .
        $this->getHeaderLogo() .
        // @todo main menu
        // @todo search
        // @todo user menu
      '</header>'
    ;
  }

  /**
   *
   *
   * @return string
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

  public final function getRenderedView() {
    return
      $this->getHead() .
      $this->getHeader() .
      $this->renderedView .
      $this->getFooter()
    ;
  }

}
