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
namespace MovLib\Partial\Helper;

/**
 * Release helper methods.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ReleaseHelper {

  /**
   * Get a release listing.
   *
   * @param \MovLib\Data\Release\ReleaseSet $releaseSet
   *
   * @return string
   *   The release listing.
   */
  public function getListing(\MovLib\Data\Release\ReleaseSet $releaseSet) {
    $items = null;
    foreach ($releaseSet as $releaseId => $release) {
      $items .= $this->formatListingItem($release, $releaseId);
    }
    return "<ol class='hover-list no-list'>{$items}</ol>";
  }

  /**
   * Format a single listing item.
   *
   * @todo Replace with concrete formatting implementation once releases are ready for production.
   *
   * @param \MovLib\Data\Release\Release $release
   *   The release to format.
   * @param integer $releaseId
   *   The current loop's delta.
   * @return string
   *   The formatted listing item.
   */
  protected function formatListingItem(\MovLib\Data\Release\Release $release, $releaseId) {
    return "<li class='hover-item r'><div class='s s10'><h2 class='para'><a href='{$release->route}'>{$release->title}</a></h2></div></li>";
  }

}
