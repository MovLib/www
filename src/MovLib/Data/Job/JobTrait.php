<?php

/* !
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
namespace MovLib\Data\Job;

/**
 * Provides properties and methods that are needed by several job objects.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait JobTrait {

  /**
   * {@inheritdoc}
   */
  final public function getPluralKey() {
    return "jobs";
  }

  /**
   * {@inheritdoc}
   */
  final public function getPluralName() {
    static $plural;
    if (!$plural) {
      $plural = $this->intl->t("Jobs");
    }
    return $plural;
  }

  /**
   * {@inheritdoc}
   */
  final public function getSingularKey() {
    return "job";
  }

  /**
   * {@inheritdoc}
   */
  final public function getSingularName() {
    static $singular;
    if (!$singular) {
      $singular = $this->intl->t("Job");
    }
    return $singular;
  }

}
