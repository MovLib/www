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
namespace MovLib\Stub\Data\Revision;

/**
 * Defines the stub object diff patch.
 *
 * @see \MovLib\Data\Revision\Revision::restore()
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class DiffPatch {

  /**
   * The diff patch itself.
   *
   * This string contains the instructions that are needed by {@see \MovLib\Data\Revision\Revision::patch()} to patch
   * a newer revision to an older one.
   *
   * @var string
   */
  public $data;

  /**
   * The diff patch's unique revision identifier.
   *
   * Note that the revision identifier is only unique for the entity it was loaded for. The field for itself isn't
   * unique at all. The value is an integer but actually represent a date and time and is only casted to an integer for
   * performance and easy comparison.
   *
   * @var integer
   */
  public $revisionId;

  /**
   * The unique identifier of the user who created this diff patch.
   *
   * @var integer
   */
  public $userId;

}
