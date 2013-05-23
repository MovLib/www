<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Model;

use \MovLib\Exception\DatabaseException;
use \MovLib\Model\AbstractModel;

/**
 * The releases model is responsible for all database related functionality of releases.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ReleasesModel extends AbstractModel {

  /**
   * The language code for the movie queries in ISO 639-1:2002 format.
   *
   * @var string
   */
  private $languageCode;

  public function __construct($languageCode) {
    parent::__construct();
    $this->languageCode = $languageCode;
  }

  public function getReleasesForMovie($movie_id) {
    $query = <<<EOD
SELECT r.`release_id`, r.`release_title`, r.`release_date`,
c.`name_en` AS `country_name_en`, c.`name_{$this->languageCode}` AS `country_name_{$this->languageCode}`, c.`iso_alpha_2`, c.`iso_alpha_3`
FROM `releases` r

INNER JOIN `movies_has_releases` mhr
ON mhr.`releases_release_id` = r.`release_id`

INNER JOIN `countries` c
ON r.`countries_country_id` = c.`country_id`

WHERE mhr.`movies_movie_id` = ?
ORDER BY r.`release_date` ASC
EOD;

    $result = $this->query($query, "i", [ $movie_id ]);
    return $result;
  }

}
