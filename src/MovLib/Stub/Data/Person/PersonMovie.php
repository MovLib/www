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
namespace MovLib\Stub\Data\Person;

/**
 * Person movie stub.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class PersonMovie {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "PersonMovie";
  // @codingStandardsIgnoreEnd

  /**
   * The person's movie.
   *
   * @var \MovLib\Data\Movie\FullMovie
   */
  public $movie;

  /**
   * The director job.
   *
   * @var \MovLib\Stub\Data\Person\Job
   */
  public $director;

  /**
   * The cast job.
   *
   * @var \MovLib\Stub\Data\Person\Job
   */
  public $cast;

  /**
   * Associative array containing the person's roles in this movie.
   *
   * The key is either a person identifier if the person plays another person or a string if it is just a minor role.
   * The value is a numeric array: <code>0</code> contains the role identifier and <code>1</code> the role name.
   *
   * @var array
   */
  public $roles;

  /**
   * Associative array containing the person's crew jobs in this movie.
   *
   * The key is the jobs identifier and the value is the translated and gendered job name.
   *
   * @var array
   */
  public $jobs;

}
