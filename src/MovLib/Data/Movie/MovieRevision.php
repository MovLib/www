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
namespace MovLib\Data\Movie;

/**
 * Defines the revision entity object for movies.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class MovieRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "MovieRevision";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * {@inheritdoc}
   */
  public static $originatorClassId = 1;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie revision.
   */
  public function __construct($id = null) {
    if ($id) {
      $connection = Database::getConnection();
    }
    if ($this->id) {
      $this->year && ($this->year = (integer) $this->year);
    }
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    static $properties = null;
    if (!$properties) {
      $properties = array_merge(parent::__sleep(), [ "year", "titles" ]);
    }
    return $properties;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function addCommitFields(\MovLib\Core\Database\Query\Update $update, \MovLib\Core\Revision\RevisionInterface $oldRevision, $languageCode) {
    return $update;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert) {
    return $insert;
  }

}
