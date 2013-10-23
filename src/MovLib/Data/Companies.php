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
namespace MovLib\Data;

/**
 * Default companies implementation.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Companies extends \MovLib\Data\DatabaseArrayObject {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new companies object.
   */
  public function __construct() {
    $this->query = "SELECT `company_id` AS `id`, `name` FROM `companies`";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Order selected companies by ID.
   *
   * @param array $filter [optional]
   *   Array containing all company IDs to fetch.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderById(array $filter = null) {
    $this->objectsArray = [];
    if ($filter) {
      $c      = count($filter);
      $in     = rtrim(str_repeat("?,", $c), ",");
      $result = $this->query("{$this->query} WHERE `company_id` IN({$in})", str_repeat("i", $c), $filter)->get_result();
    }
    else {
      $result = $this->query($this->query)->get_result();
    }
    /* @var $company \MovLib\Data\Company */
    while ($company = $result->fetch_object("\\MovLib\\Data\\Company")) {
      $this->objectsArray[$company->id] = $company;
    }
    return $this;
  }

  /**
   * Order selected companies by name.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderByName() {
    global $i18n;
    $this->objectsArray = [];
    $result = $this->query($this->query)->get_result();
    /* @var $company \MovLib\Data\Genre */
    while ($company = $result->fetch_object("\\MovLib\\Data\\Company")) {
      $this->objectsArray[$company->name] = $company;
    }
    $i18n->getCollator()->ksort($this->objectsArray);
    return $this;
  }

}
