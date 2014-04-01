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
namespace MovLib\Partial;

use \MovLib\Data\Date;

/**
 * Add various person formatting functions to presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait PersonTrait {

  /**
   * Get a person's born name.
   *
   * @param \MovLib\Data\Person\Person $person
   *   The person to get the born name from.
   * @return null|string
   *   The formatted born name or <code>NULL</code> if none was present.
   */
  final protected function getPersonBornName(\MovLib\Data\Person\Person $person) {
    if ($person->bornName) {
      return $this->intl->t("{0} ({1})", [
        "<span property='additionalName'>{$person->bornName}</span>",
        "<i>{$this->intl->t("born name")}</i>",
      ]);
    }
  }

}
