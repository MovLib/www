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

use \MovLib\View\HTML\AbstractView;

/**
 * The <b>Home</b> view contains the HTML layout for the MovLib home page.
 *
 * The home page should be generated without a single database call. This should simply increase the performance of the
 * home page, as it is most certainly the most viewed page.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class HomeView extends AbstractView {

  /**
   * {@inheritdoc}
   */
  public function __construct($language) {
    parent::__construct($language, SITENAME);
  }

  /**
   * Overwrite the default header title method.
   *
   * The home page is the only page that is using a different header title pattern. Therefor we overwrite the default
   * method of the base class for each view.
   *
   * @return string
   */
  public function getHeadTitle() {
    return
      $this->title .
      //# The comma is used as separator character in the header title of the home page. The header title of the home
      //# page is built like a setence with the pattern "[sitename][separator][description]". The content of this
      //# sentence in the first version of the software was "MovLib, the free movie library.". The trailing dot was part
      //# of the slogan but the comma is translated as separate string (to ensure that the slogan stays re-usable).
      //# Translate the comma to the equivalent character in your language. More information on this specific character
      //# can be found at Wikipedia: https://en.wikipedia.org/wiki/Comma
      __(', ') .
      __('the free movie library.')
    ;
  }

  /**
   * Overwrite the default header logo method.
   *
   * The home page has to feature our own brand and should not link to itself. Therefor we overwrite the default method
   * where the logo is a link to the home page. The <code>&lt;h1&gt;</code>-element has to be unique on the home page.
   *
   * @return string
   */
  public function getHeaderLogo() {
    return
      '<h1 id="logo" class="inline">' .
        SITENAME . ' <small>' . sprintf(__('the %sfree%s movie library'), '<em class="serif">', '</em>') . '</small>' .
      '</h1>'
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedContent() {
    return
      '<pre>01[2]34[5]67[8]9a[b]cd[e]f

--- dark ---
#222
  #333
  #3b3b3b
  #484848
#555
  #626262
  #6e6e6e
  #7b7b7b
#888
  #959595
  #a1a1a1
  #aeaeae
#bbb
  #c8c8c8
  #d4d4d4
#eee
  #e1e1e1
  #fbfbfb
-- bright --</pre>' .
      '<p>Test <code>1234</code> Test</p>' .
      '<div id="homepage-banner">' . __('Do you like movies?') . '<br>' . __('Great!') . '<br>' . __('So do we!') . '</div>' .
      '<div class="row">' .
        '<div class="span3">' .
          '<h3>' . __('Movies') . '</h3>' .
        '</div>' .
        '<div class="span3">' .
          '<h3>' . __('People') . '</h3>' .
        '</div>' .
        '<div class="span3">' .
          '<h3>' . __('Marketplace') . '</h3>' .
        '</div>' .
      '</div>' .
      '<div class="row">' .
        '<div class="span3">' .
          '<h3>' . __('Releases') . '</h3>' .
        '</div>' .
        '<div class="span3">' .
          '<h3>' . __('My MovLib') . '</h3>' .
        '</div>' .
        '<div class="span3">' .
          '<h3>' . __('API') . '</h3>' .
        '</div>' .
      '</div>'
    ;
  }

}
