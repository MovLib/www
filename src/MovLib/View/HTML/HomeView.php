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
 * The home page should be generated without a single database call.
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

//    foreach ([ 'info', 'success', 'warning', 'error' ] as $delta => $alert) {
//      $this->setAlert('This would be my message body.', "I am the $alert alert!", $alert);
//    }
//
//    foreach ([ 'info', 'success', 'warning', 'error' ] as $delta => $alert) {
//      $this->setAlert('Perfect if you want to tell the user a lot (or something very important).', "I am the $alert block alert!", $alert, true);
//    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBodyClass() {
    return 'home';
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function getRenderedContent() {
    $testdrive = '<p>Flatstrap may be our base, but there is a lot of room to improve their styles. Especially in terms of contrast and CSS performance (because they try to be compatible with just about anything).</p><h2>Buttons</h2>';
    foreach ([ 'large', 'default', 'small', 'mini' ] as $delta => $size) {
      $testdrive .= '<p>';
      foreach ([ 'default', 'primary', 'info', 'success', 'warning', 'danger', 'inverse' ] as $delta => $state) {
        $testdrive .= '<a class="btn btn-' . $size . ' btn-' . $state . '" href="#">' . $state . '-' . $size . '</a> ';
      }
      $testdrive .= '</p>';
    }

    return
      '<div id="home-banner"><div class="row"><div class="span0 lead hero">' . __('Do you like movies?<br>Great, so do we!') . '</div></div></div>' .
      '<div class="centered">' .
        $this->getRow(
          '<h3>' . __('Movies') . '</h3><p>' . __('Discover new and old movies, find out about all related details like who was the director, when and where was it released, what releases are available, find poster and lobby card art, plus many, many more …') . '</p>',
          '<h3>' . __('Persons') . '</h3><p>' . __('You always wanted to collect all movies of a specific director, actor or any other movie related person? This is the place for you to go. Find out all details about the person you admire, or simply add them yourself if you are an expert.') . '</p>',
          '<h3>' . __('Marketplace') . '</h3><p>' . __('Searching for a specific release? Our marketplace is free, open, and built upon the exact release database. This makes it easy for sellers to list their inventory and buyers are able to specify the exact version they want.') . '</p>'
        ) .
        $this->getRow(
          '<h3>' . __('Releases') . '</h3><p>' . __('') . '</p>',
          '<h3>' . sprintf(__('My %s'), SITENAME) . '</h3><p></p>' . $this->getAnchor(__('sign-up', 'route'), __('Sign up for a new account'), [ 'class' => [ 'btn', 'btn-success', 'btn-large' ]]),
          '<h3>' . __('API') . '</h3><p>' . sprintf(__('The %s API is a REST interface to access the free movie library. Specifically designed for all developers out there. We want to keep the barrier as low as possible and ensure that everybody can use the data we all collect here at %s.'), SITENAME, SITENAME) . '</p>' . $this->getAnchor('//api.movlib.org', 'Read the API documentation', [ 'class' => [ 'btn', 'btn-primary', 'btn-large' ]])
        ) .
      '</div>' .
      '<div class="row"><div class="span-0">' . $testdrive . '</div></div>'
    ;
  }

}
