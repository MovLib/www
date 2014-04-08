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
namespace MovLib\Data;

/**
 * Defines the default implementation for the rating interface.
 *
 * @see \MovLib\Data\RatingInterface
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait RatingTrait {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity's Bayes rating.
   *
   * @var null|float
   */
  public $bayesRating;

  /**
   * The entity's mean rating.
   *
   * @var float
   */
  public $meanRating;

  /**
   * The entity's global rank.
   *
   * @var null|integer
   */
  public $rank;

  /**
   * The entity's votes.
   *
   * @var null|integer
   */
  public $votes;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Rate this entity.
   *
   * @param integer $rating
   *   A valid rating between 1 and 5.
   * @param integer $userId
   *   The user to rate for.
   * @param integer|null $userRating
   *   The user's current rating for this entity.
   * @return this
   */
  public function rate($rating, $userId, $userRating) {
    $mysqli = $this->getMySQLi();
    // This user hasn't voted for this entity yet.
    if ($userRating === null) {
      $mysqli->query("INSERT INTO `{$this->tableName}_ratings` SET `{$this->singularKey}_id` = {$this->id}, `user_id` = {$userId}, `rating` = {$rating}");
      ++$this->votes;
    }
    // This user already voted for this entity.
    else {
      $mysqli->query("UPDATE `{$this->tableName}_ratings` SET `rating` = {$rating} WHERE `{$this->singularKey}_id` = {$this->id} AND `user_id` = {$userId}");
    }

    // Calculate the new mean rating for this entity.
    $this->meanRating = round(($this->meanRating + $rating) / $this->votes, 2);

    // Update the entity's rating statistics.
    $mysqli->query("UPDATE `{$this->tableName}` SET `mean_rating` = {$this->meanRating}, `votes` = {$this->votes} WHERE `id` = {$this->id}");

    // @todo Update Bayes rating and global rank!

    return $this;
  }

}
