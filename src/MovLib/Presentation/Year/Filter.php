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
namespace MovLib\Presentation\Year;

/**
 * Present all entities of this year requested via the route.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Filter extends \MovLib\Presentation\Page {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Numeric array containing all available filters.
   *
   * @var array
   */
  public static $filters = [ "movies", "series", "releases", "persons", "companies" ];

  /**
   * The year we are currently presenting.
   *
   * @var integer
   */
  protected $year;

  /**
   * The currently active filter.
   *
   * @var string
   */
  protected $filter;

  /**
   * The translated filter's name.
   *
   * @var string
   */
  protected $filterName;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new country filter view.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n;
    $this->filter  = self::$filters[$_SERVER["FILTER"]];

    // IMPORTANT!
    //
    // Right now it's not a problem to make ucfirst() and pass it to i18n for translation, as all filters are single
    // words that are already translated. We normally never pass parameters to i18n because we can't parse them, but
    // all of the filters are within our translation database from other places, that's why it works here. We have to
    // use the switch solution if we have any special words (e.g. containing a space) in the fitler.
    $this->filterName = $this->breadcrumbTitle = $i18n->t(ucfirst($this->filter));
//    switch ($this->filter) {
//      case "movies":
//        $this->filterName = $i18n->t("Movies");
//        break;
//
//      case "series":
//        $this->filterName = $i18n->t("Series");
//        break;
//
//      case "releases":
//        $this->filterName = $i18n->t("Releases");
//        break;
//
//      case "persons":
//        $this->filterName = $i18n->t("Persons");
//        break;
//
//      case "companies":
//        $this->filterName = $i18n->t("Companies");
//        break;
//    }

    $this->year = (integer) $_SERVER["ID"];
    $this->initPage($i18n->t("{entity_name} from {year}", [
      "entity_name" => $this->filterName,
      "year"        => $this->year,
    ]));
    $this->initBreadcrumb([
      [ $i18n->rp("/years"), $i18n->t("Years") ],
      [ $i18n->r("/year/{0}", [ $this->year ]), $this->year ],
    ]);
    $this->initLanguageLinks("/year/{0}/{$this->filter}", [ $this->year ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getContent() {
    return "<div class='c'><pre>Year: {$_SERVER["ID"]}
Filter Key: {$_SERVER["FILTER"]}
Filter Value: {$this->filter}
Filter Name: {$this->filterName}</pre></div>";
  }

}
