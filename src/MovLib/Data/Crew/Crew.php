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
namespace MovLib\Data\Crew;

/**
 * Defines a crew entity object.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Crew extends \MovLib\Data\Job\Job {


  //-------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Crew";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The direction job's identifier.
   *
   * @var integer
   */
  const JOB_ID_DIRECTION = 2;

  /**
   * The production job's identifier.
   *
   * @var integer
   */
  const JOB_ID_PRODUCTION = 3;

  /**
   * The screenwritin job's identifier.
   *
   * @var integer
   */
  const JOB_ID_SCREENWRITING = 4;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The crew's alias.
   *
   * @var string
   */
  public $alias;

  /**
   * The crew's company identifier.
   *
   * @var integer
   */
  public $companyId;

  /**
   * The company associated with the company identifier (for the sake of simplicity).
   *
   * @var \MovLib\Data\Company\Company
   */
  public $company;

  /**
   * The crew's job identifier.
   *
   * @var integer
   */
  public $jobId;

  /**
   * The crew's movie identifier.
   *
   * @var integer
   */
  public $movieId;

  /**
   * The crew's person identifier.
   *
   * @var integer
   */
  public $personId;

  /**
   * The person associated with the person identifier (for the sake of simplicity).
   *
   * @var \MovLib\Data\Person\Person
   */
  public $person;

  /**
   * The crew's series identifier.
   *
   * @var integer
   */
  public $seriesId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new crew object.
   *
   * @param \MovLib\Core\Container $container
   *   {{@inheritdoc}}
   */
  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container);
  }

}
