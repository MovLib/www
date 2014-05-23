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
namespace MovLib\Stub\Data\User;

/**
 * Defines the contribution object.
 *
 * @see \MovLib\Data\User\User::getContributions()
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class Contribution {

  /**
   * The entity the user contributed to.
   *
   * @var \MovLib\Data\AbstractEntity
   */
  public $entity;

  /**
   * The entity's unique identifier the user contributed to.
   *
   * @var integer
   */
  public $entityId;

  /**
   * The entity's class name the user contributed to.
   *
   * @var string
   */
  public $entityClassName;

  /**
   * The contribution's date and time.
   *
   * @var \MovLib\Component\DateTime
   */
  public $dateTime;

  /**
   * The contributin's revision identifier.
   *
   * @var integer
   */
  public $revisionId;

}
