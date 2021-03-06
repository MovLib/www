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
namespace MovLib\Data\Cast;

use \MovLib\Partial\Sex;

/**
 * Defines a cast entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Cast extends \MovLib\Data\Job\Job {


  //-------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Cast";
  // @codingStandardsIgnoreEnd

  const JOB_ID = 1;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The cast's alias.
   *
   * @var string
   */
  public $alias;

  /**
   * The entity this cast member belongs to.
   *
   * @var \MovLib\Data\AbstractEntity
   */
  protected $entity;

  /**
   * The cast's job identifier.
   *
   * @var integer
   */
  public $jobId;

  /**
   * The cast's movie identifier.
   *
   * @var integer
   */
  public $movieId;

  /**
   * The cast's person identifier.
   *
   * @var integer
   */
  public $personId;

  /**
   * The cast's person name.
   *
   * @var string
   */
  public $personName;

  /**
   * The role name (for roles with no further data).
   *
   * @var string
   */
  public $role;

  /**
   * The role identifier (for persons playing other persons).
   *
   * @var integer
   */
  public $roleId;

  /**
   * The role person's name.
   *
   * @var string
   */
  public $roleName;

  /**
   * The cast's translated role title for persons playing themselves.
   *
   * @var string
   */
  public $roleTitleSelf;

  /**
   * The cast's series identifier.
   *
   * @var integer
   */
  public $seriesId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new single cast member.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   */
  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container);
    if (empty($this->changed)) {
      $this->changed = new \MovLib\Component\DateTime();
    }
    if (empty($this->created)) {
      $this->created = new \MovLib\Component\DateTime();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init(array $values = null, $sex = Sex::UNKNOWN) {
    parent::init($values);
    if ($sex === Sex::MALE) {
      $this->roleTitleSelf = $this->intl->t("Himself");
    }
    elseif ($sex === Sex::FEMALE) {
      $this->roleTitleSelf = $this->intl->t("Herself");
    }
    else {
      $this->roleTitleSelf = $this->intl->t("Self");
    }
    return $this;
  }


}
