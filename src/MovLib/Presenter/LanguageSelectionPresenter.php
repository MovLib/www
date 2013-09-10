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
namespace MovLib\Presenter;

use \MovLib\Presenter\AbstractPresenter;
use \MovLib\View\HTML\LanguageSelectionView;
use \Locale;

/**
 * The language selection presenter presents the language selection if our website is accessed without subdomain.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class LanguageSelectionPresenter extends AbstractPresenter {

  /**
   * Instantiate new language selection presenter.
   */
  public function __construct() {
    new LanguageSelectionView($this);
  }

  /**
   * The language selection presenter has no breadcrumb!
   */
  public function getBreadcrumb() {}

  /**
   * Get menupoints for the language selection.
   *
   * @todo Sort locales by user and/or page count.
   * @return array
   */
  public function getLanguageSelectionMenupoints() {
    $points = [];
    foreach ($GLOBALS["movlib"]["locales"] as $languageCode => $locale) {
      $points[] = [
        "{$_SERVER["SCHEME"]}://{$languageCode}.{$_SERVER["SERVER_NAME"]}/",
        Locale::getDisplayLanguage($locale, $languageCode),
        [ "lang" => $languageCode, "rel" => "prefetch" ],
      ];
    }
    return $points;
  }

}
