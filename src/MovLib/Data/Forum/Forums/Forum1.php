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
 * Defines the Announcements forum object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Forum1 implements ForumInterface {

  /**
   * {@inheritdoc}
   */
  public function getCategoryId() {
    return 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(\MovLib\Core\Intl $intl, $languageCode = null) {
    return $intl->t(
      "Important news around {0}our{1} website will be announced by the administrators in the {2}announcement " .
      "forum{3}. Active users should have a look at this forum on a regular basis, because all important facts are " .
      "posted here first.",
      [ "<em>", "</em>", "<strong>", "</strong>" ],
      $languageCode
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(\MovLib\Core\Intl $intl, $languageCode = null) {
    return $intl->t("Announcements", null, $languageCode);
  }

}
