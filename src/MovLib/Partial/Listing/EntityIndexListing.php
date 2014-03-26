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
namespace MovLib\Partial\Listing;

/**
 * Images list for entity instances with series and movie counts.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class EntityIndexListing extends \MovLib\Partial\Listing\EntityListing {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The translated entity movies route.
   *
   * Contains <code>{{ id }}</code> insteat of entity id.
   *
   * @var string
   */
  protected $moviesRoute;

  /**
   * The translated entity series route.
   *
   * Contains <code>{{ id }}</code> insteat of entity id.
   *
   * @var string
   */
  protected $seriesRoute;

  /**
   * Translated movies title.
   *
   * @var string
   */
  protected $moviesTitle;

  /**
   * Translated series title.
   *
   * @var string
   */
  protected $seriesTitle;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @param string $moviesRoute
   *   The translated entity movies route containing <code>{{ id }}</code> as placeholder for
   *   the entity id (e.g. "/award/1/category/{{ id }}/movies".
   * @param string $seriesRoute
   *   The translated entity series route containing <code>{{ id }}</code> as placeholder for
   *   the entity id (e.g. "/award/1/category/{{ id }}/series".
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct($listItems, $noItemsText, $entityName, $moviesRoute, $seriesRoute) {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (method_exists("\\MovLib\\Data\\{$entityName}", "getMoviesCount") === false) {
      throw new \InvalidArgumentException("\\MovLib\\Data\\{$entityName} must implement method 'getMoviesCount()'.");
    }
    if (method_exists("\\MovLib\\Data\\{$entityName}", "getSeriesCount") === false) {
      throw new \InvalidArgumentException("\\MovLib\\Data\\{$entityName} must implement method 'getSeriesCount()'.");
    }
    foreach ([ "movies", "series" ] as $routeKey) {
      if (strpos(${"{$routeKey}Route"}, "{{ id }}") === false) {
        throw new \InvalidArgumentException("\${$routeKey}Route doesn't contain entity id placeholder token {{ id }}");
      }
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->moviesTitle = $i18n->t("Movies");
    $this->moviesRoute = $moviesRoute;
    $this->seriesTitle = $i18n->t("Series");
    $this->seriesRoute = $seriesRoute;
    parent::__construct($listItems, $noItemsText, $entityName);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getAdditionalContent($entity, $listItem) {
    // @devStart
    // @codeCoverageIgnoreStart
    if(!($entity instanceof $this->entity)) {
      throw new \InvalidArgumentException("\$entity must be of type {$this->entity}");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $currentMovieRoute  = str_replace("{{ id }}", $entity->id, $this->moviesRoute);
    $currentSeriesRoute = str_replace("{{ id }}", $entity->id, $this->seriesRoute);

    return
      "<span class='fr'>" .
        "<a class='ico ico-movie label' href='{$currentMovieRoute}' title='{$this->moviesTitle}'>" .
          " &nbsp; {$entity->getMoviesCount()}" .
        "</a>" .
        "<a class='ico ico-series label' href='{$currentSeriesRoute}' title='{$this->seriesTitle}'>" .
          " &nbsp; {$entity->getSeriesCount()}" .
        "</a>" .
      "</span>"
    ;
  }

}
