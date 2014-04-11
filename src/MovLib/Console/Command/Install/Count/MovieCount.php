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
namespace MovLib\Console\Command\Install\Count;

/**
 * Count verification for movies.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieCount extends \MovLib\Console\Command\Install\Count\AbstractEntityCountCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->entityName = "Movie";
    $this->tableName  = "movies";
    $this->addCountColumn(
      "awards",
      [ $this, "getCounts" ],
      [ "movie_id", null, [ "award_id", "award_category_id", "event_id" ], "movies_awards", "`won` > 0"  ]
    );
    $this->addCountColumn("releases", [ $this, "getReleaseCounts" ], [ "movie_id", "media_movies" ]);
    return parent::configure();
  }

}
