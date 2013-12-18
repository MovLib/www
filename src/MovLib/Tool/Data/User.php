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
namespace MovLib\Tool\Data;

/**
 * Extended user class for internal usage.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class User extends \MovLib\Data\User\Full {

  /**
   * Regenerate all image styles of the user's avatar.
   *
   * @return this
   * @throws \ErrorException
   */
  public function regenerateImageStyles() {
    // Only regenerate if we have an avatar.
    if ($this->imageExists === false) {
      return $this;
    }

    // Remove all styles that were previously generated.
    foreach ($this->styles as $style => $styleData) {
      if ($style !== self::STYLE_SPAN_02) {
        unlink($this->getPath($style));
      }
    }

    // Upload our own avatar again.
    $this->upload($this->getPath(self::STYLE_SPAN_02), $this->extension, -1, -1);

    return $this;
  }

}
