<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
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
namespace MovLib\Data\Forum\Forums;

/**
 * Defines the forum interface for all concrete forums.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface ForumInterface {

  /**
   * Get the unique category identifier this forum belongs to.
   *
   * @see Forum::getCategories
   * @return integer
   *   The unique category identifier this forum belongs to.
   */
  public function getCategoryId();

  /**
   * Get the forum's translated description.
   *
   * <b>NOTE</b><br>
   * The description contains unencoded raw HTML.
   *
   * @internal
   *   Right now there's no way to get the description without the HTML because there's no need for it.
   * @param \MovLib\Core\Intl $intl
   *   The internationalization instance for translation.
   * @param string $languageCode
   *   The system language's ISO 639-1 alpha-2 code to translate the description to, defaults to <code>NULL</code> and
   *   the language from <var>$intl</var> will be used.
   * @return string
   *   The forum's translated description.
   */
  public function getDescription(\MovLib\Core\Intl $intl, $languageCode = null);

  /**
   * Get the forum's translated title.
   *
   * @param \MovLib\Core\Intl $intl
   *   The internationalization instance for translation.
   * @param string $languageCode [optional]
   *   The system language's ISO 639-1 alpha-2 code to translate the description to, defaults to <code>NULL</code> and
   *   the language from <var>$intl</var> will be used.
   * @return string
   *   The forum's translated title.
   */
  public function getTitle(\MovLib\Core\Intl $intl, $languageCode = null);

}
