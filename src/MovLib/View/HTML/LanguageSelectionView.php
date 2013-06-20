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
use \MovLib\Model\I18nModel;
use \MovLib\Utility\FileSystem;
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
  public function __construct($languageSelectionPresenter) {
    global $i18n;
    parent::__construct($languageSelectionPresenter, $i18n->t("Language Selection"));
    $this->stylesheets[] = "modules/language-selection.css";
  }

  /**
   * Get the rendered content, without HTML head, header or footer.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   */
  public function getContent() {
    global $i18n;
    $points = [];
    foreach (I18nModel::$supportedLanguageCodes as $code) {
      $points[] = [
        "https://{$code}.{$_SERVER["SERVER_NAME"]}/",
        Locale::getDisplayLanguage($code, $code),
        [ "lang" => $code, "rel" => "prefetch" ]
      ];
    }
    return
      "<div id='content' class='{$this->getShortName()}-content' role='main'>" .
        "<div class='container text-center'>" .
          "<h1 id='logo-big' class='clear-fix'>" .
            "<img class='pull-left' src='" . FileSystem::asset("img/logo/vector.svg") . "' alt='{$i18n->t("MovLib, the free movie library.")}' width='192' height='192'>" .
            "<span>{$i18n->t("MovLib <small>the <em>free</em> movie library.</small>")}</span>" .
          "</h1>" .
          "<p>{$i18n->t("Please select your preferred language from the list below.")}</p>" .
          $this->getNavigation($i18n->t("Language links"), $this->getShortName(), $points, -1, " / ", [ "class" => "well well--large" ]) .
        "</div>" .
      "</div>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getFooter() {
    global $i18n;
    return
      "<footer id='footer'>" .
        "<div class='container text-center'>" .
          "<p>{$i18n->t(
            "Is your language missing from our list? Help us translate MovLib to your language. More information can be found in {0}our translation portal{1}.",
            [ "<a href='https://localize.{$_SERVER["SERVER_NAME"]}/'>", "</a>" ]
          )}</p>" .
        "</div>" .
      "</footer>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedView() {
    return $this->getHead() . $this->getContent() . $this->getFooter();
  }

}
