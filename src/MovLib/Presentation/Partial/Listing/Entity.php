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
namespace MovLib\Presentation\Partial\Listing;

/**
 * List to display Awards, Genres or Jobs.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Entity extends \MovLib\Presentation\Partial\Listing\AbstractListing {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Absolute class name of the entitiy to fetch.
   *
   * @var string
   */
  protected $entity;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new listing.
   *
   * @param \mysqli_result $result
   *   The MySQLi result object containing the entities.
   * @param string $noItemsText
   *   {@inheritdoc}
   * @param string $entityName
   *   The name of the Data object to fetch (e.g. <code>"Genre"</code> which will lead to instantiation of
   *   <code>"\\MovLib\\Data\\Genre"</code>).
   */
  public function __construct($result, $noItemsText, $entityName) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($entityName)) {
      throw new \InvalidArgumentException("{$entityName} cannot be empty");
    }
    if (class_exists("\\MovLib\\Data\\{$entityName}") === false) {
      throw new \InvalidArgumentException("\\MovLib\\Data\\{$entityName} must match an existing class");
    }
    if (method_exists("\\MovLib\\Data\\{$entityName}", "getMovieCount") === false) {
      throw new \InvalidArgumentException("\\MovLib\\Data\\{$entityName} must implement method 'getMovieCount()'.");
    }
    if (method_exists("\\MovLib\\Data\\{$entityName}", "getSeriesCount") === false) {
      throw new \InvalidArgumentException("\\MovLib\\Data\\{$entityName} must implement method 'getSeriesCount()'.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    parent::__construct($result, $noItemsText);
    $this->entity = "\\MovLib\\Data\\{$entityName}";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the rendered listing.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The rendered listing.
   */
  public function __toString() {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $list = null;
      $moviesTitle = $i18n->t("Movies");
      $seriesTitle = $i18n->t("Series");
      $routeKey = $routeMovies = $routeSeries = $moviesRoute = $seriesRoute = null;
      while ($entity = $this->listItems->fetch_object($this->entity)) {
        if (!$routeKey) {
          $routeKey = preg_replace("#/[0-9]+$#", "/{0}", $entity->route);
          $routeMovies = $i18n->rp("{$routeKey}/movies");
          $routeSeries = $i18n->rp("{$routeKey}/series");
        }
        $moviesRoute = str_replace("{0}", $entity->id, $routeMovies);
        $seriesRoute = str_replace("{0}", $entity->id, $routeSeries);
        $list .=
          "<li class='hover-item r'>" .
            "<div class='s s10'>" .
              "<span class='fr'>" .
                "<a class='ico ico-movie label' href='{$moviesRoute}' title='{$moviesTitle}'> &nbsp; {$entity->getMovieCount()}</a>" .
                "<a class='ico ico-series label' href='{$seriesRoute}' title='{$seriesTitle}'> &nbsp; {$entity->getSeriesCount()}</a>" .
              "</span>" .
              "<a href='{$entity->route}' property='itemListElement'>{$entity->name}</a>" .
            "</div>" .
          "</li>"
        ;
      }
      if ($list) {
        return "<ol class='hover-list' typeof='ItemList'>{$list}</ol>";
      }
      return (string) $this->noItemsText;
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return (string) new \MovLib\Presentation\Partial\Alert(
        "<pre>{$e}</pre>",
        $i18n->t("Error Rendering List"),
        \MovLib\Presentation\Partial\Alert::SEVERITY_ERROR
      );
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }

}
