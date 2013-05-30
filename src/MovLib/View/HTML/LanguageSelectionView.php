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

use \Locale;
use \MovLib\View\HTML\AbstractView;

/**
 * If the user accesses our website without any subdomain ask him to choose his favorite language.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class LanguageSelectionView extends AbstractView {

  /**
   * {@inheritdoc}
   */
  public function __construct($presenter) {
    parent::__construct($presenter, SITENAME);
    $this->addStylesheet("/assets/css/modules/language-selection.css");
  }

  /**
   * {@inheritdoc}
   */
  public function getHeadTitle() {
    return SITENAME;
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedContent() {
    $points = [];
    foreach ($this->presenter->getLanguage()->getIntlLocales() as $code => $locale) {
      $points[] = [
        "href" => "//{$code}.{$_SERVER["SERVER_NAME"]}",
        "text" => Locale::getDisplayLanguage($locale, $locale),
        "attributes" => [ "lang" => $code ],
      ];
    }
    return
      "<div id='content' class='{$this->getShortName()}-content row' role='main'><div class='span span--1 text-center'>" .
        "<h1 class='inline text-left'>" . SITENAME . " <small>the <em>free</em> movie library</small></h1>" .
        "<p>Please select your preferred language from the list below.</p>" .
        $this->getNavigation(__("Language links"), $this->getShortName(), $points, -1, " / ", [ "class" => "well well--large" ]) .
        "<p>Is your language missing from our list? Help us translate " . SITENAME . " to your language. More info can be found at <a href='//locale.movlib.lorg'>our translation portal</a>.</p>" .
      "</div></div>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedView() {
    return $this->getHead() . $this->getRenderedContent();
  }

}
