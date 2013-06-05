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
use \MovLib\Utility\I18n;
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
    parent::__construct($presenter, "MovLib");
    $this->addStylesheet("/assets/css/modules/language-selection.css");
  }

  /**
   * {@inheritdoc}
   */
  public function getHeadTitle() {
    return "MovLib";
  }

  /**
   * Get the rendered content, without HTML head, header or footer.
   *
   * @global \MovLib\Utility\I18n $i18n
   *   The global i18n instance.
   * @return string
   */
  public function getContent() {
    global $i18n;
    $languageCode = $i18n->getLanguageCode();
    $points = [];
    foreach (I18n::getSupportedLanguageCodes() as $code) {
      $points[] = [
        "href" => "//{$code}.{$_SERVER["SERVER_NAME"]}",
        "text" => Locale::getDisplayLanguage($code, $code),
        [ "lang" => $code ]
      ];
    }
    return
      "<div id='content' class='{$this->getShortName()}-content row' role='main'><div class='span span--1 text-center'>" .
        "<h1 lang='{$languageCode}' class='inline text-left'>{$i18n->t("MovLib <small>the <em>free</em> movie library.</small>")}</h1>" .
        "<p lang='{$languageCode}'>{$i18n->t("Please select your preferred language from the list below.")}</p>" .
        $this->getNavigation($i18n->t("Language links"), $this->getShortName(), $points, -1, " / ", [ "class" => "well well--large" ]) .
        "<p lang='{$languageCode}'>{$i18n->t("Is your language missing from our list? Help us translate MovLib to your language. More info can be found at {0}our translation portal{1}.", [ "<a href='//localize.movlib.org'>", "</a>" ])}</p>" .
      "</div></div>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedView() {
    return $this->getHead() . $this->getContent();
  }

}
