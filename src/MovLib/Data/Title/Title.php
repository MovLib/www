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
namespace MovLib\Data\Title;

/**
 * Defines a title entity object.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Title extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The title's translated comment.
   *
   * @var string
   */
  public $comment;

  /**
   * The entity's singular key the title belongs to.
   *
   * @var string
   */
  protected $entitySingularKey;

  /**
   * The entity's plural key the title belongs to.
   *
   * @var string
   */
  protected $entityPluralKey;

  /**
   * The title's ISO alpha-2 language code.
   *
   * @var string
   */
  public $languageCode;

  /**
   * The title's movie identifier.
   *
   * @var integer
   */
  public $movieId;

  /**
   * The title's series identifier.
   *
   * @var integer
   */
  public $seriesId;

  /**
   * The title.
   *
   * @var string
   */
  public $title;

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->tableName = "{$this->entityPluralKey}_titles";
    return parent::init();
  }

}
