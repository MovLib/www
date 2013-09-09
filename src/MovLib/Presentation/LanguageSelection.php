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
namespace MovLib\Presentation;

use \Locale;
use \MovLib\Presentation\Partial\Navigation;

/**
 * The global language selection page.
 *
 * The language selection page is displayed if a user accesses MovLib without any language code specific subdomain. We
 * don't know what language the user might prefer and we don't want to guess (like many others are doing it, e.g. Google
 * and they're doing a really awful job there). Freedom of joice is the motto. This page is different than most other
 * MovLib page's and therefor we extend the abstract page and not the reference implementation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class LanguageSelection extends \MovLib\Presentation\AbstractPage {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Numeric array containing all supported languages.
   *
   * @var \MovLib\Presentation\Partial\Navigation
   */
  private $navigation;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new language selection presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->init($i18n->t("Language Selection"));
    $this->stylesheets[] = "modules/language-selection.css";

    // Construct the languages navigation.
    $menuitems = [];
    foreach ($GLOBALS["movlib"]["locales"] as $languageCode => $locale) {
      $menuitems[] = [
        "{$_SERVER["SCHEME"]}://{$languageCode}.{$_SERVER["SERVER_NAME"]}/",
        Locale::getDisplayLanguage($locale, $languageCode),
        [ "lang" => $languageCode, "rel" => "prefetch", "tabindex" => $this->getTabindex() ],
      ];
    }
    $this->navigation = new Navigation($this->id, $i18n->t("Available Languages"), $menuitems);
    $this->navigation->attributes["class"] = "well well--large";
    $this->navigation->glue = " / ";
  }

  /**
   * @inheritdoc
   */
  public function getPresentation() {
    global $i18n;
    $html = parent::getPresentation();
    return
      "{$html}<div class='{$this->id}-content' id='content' role='main'><div class='container'>" .
        "<h1 class='clear-fix' id='logo-big'>" .
          "<img alt='{$i18n->t("MovLib, the free movie library.")}' height='192' src='{$GLOBALS["movlib"]["static_domain"]}img/logo/vector.svg' width='192'>" .
          "<span>{$i18n->t("MovLib <small>the <em>free</em> movie library.</small>")}</span>" .
        "</h1>" .
        "<p>{$i18n->t("Please select your preferred language from the following list.")}</p>{$this->navigation}" .
      "</div></div>" .
      "<footer id='footer'><div class='container'><p>{$i18n->t(
        "Is your language missing from our list? Help us translate {0} to your language. More information can be found at {1}our translation portal{2}.",
        [ "MovLib", "<a href='{$GLOBALS["movlib"]["localize_domain"]}'>", "</a>" ]
      )}</p></div></footer>"
    ;
  }

}
