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
 * @todo Description of EventCount
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class EventCount extends \MovLib\Console\Command\Install\Count\AbstractEntityCountCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->entityName = "Event";
    $this->tableName  = "events";
    $this->addCountColumn("companies", [ $this, "getAwardeeCounts" ], [ "event_id", $this->tableName, "company_id", "companies" ]);
    $this->addCountColumn("movies", [ $this, "getCounts" ], [ "event_id", null, "movie_id", "movies_awards", "`won` > 0" ]);
    $this->addCountColumn("persons", [ $this, "getAwardeeCounts" ], [ "event_id", $this->tableName, "person_id", "persons" ]);
    $this->addCountColumn("series", [ $this, "getCounts" ], [ "event_id", null, "series_id", "series_awards", "`won` > 0" ]);
    return parent::configure();
  }

}
