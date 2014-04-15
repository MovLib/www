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
namespace MovLib\Presentation\Search;

use \Elasticsearch\Client as ElasticClient;
use \MovLib\Data\Image\AbstractImage;
use \MovLib\Data\Movie\Movie;
use \MovLib\Data\Person\Person;
use \MovLib\Presentation\Partial\Alert;

/**
 * Present search results to the user.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Show extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's submitted search query.
   *
   * @var null|string
   */
  protected $query;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initPage($this->intl->t("Search"));
    $this->initBreadcrumb();
    $this->sidebarInit([]);
    $this->breadcrumb->ignoreQuery = true;

    $query       = null;
    $this->query = filter_input(INPUT_GET, "q", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_NULL_ON_FAILURE);
    if ($this->query) {
      $query = rawurlencode($this->query);
      $query = "?q={$query}";
    }
    $this->initLanguageLinks("/search", null, false, $query);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getContent() {
    // We're done if we have no search query.
    if (empty($this->query)) {
      $this->alerts .= new Alert(
        $this->intl->t("No search query submitted."),
        $this->intl->t("Nothing to Search for…"),
        Alert::SEVERITY_ERROR
      );
      return;
    }

    // If we have one ask Elastic.
    $elasticClient = new ElasticClient();
    $result = $elasticClient->search([
      "index" => "movlib",
      "type"  => "movie,person",
      "body"  => [
        "query" => [
          "fuzzy_like_this" => [
            "fields"          => [ "titles", "names" ],
            "like_text"       => $this->query,
            "max_query_terms" => 25,
            "min_similarity"  => 0.3,
          ],
        ],
      ],
    ]);

    // Elastic didn't return anything, we're done.
    if (!isset($result["hits"]) || !isset($result["hits"]["total"]) || $result["hits"]["total"] === 0) {
      $this->alerts .= new Alert(
        $this->intl->t("Your search “{query}” did not match any document.", [ "query" => $this->placeholder($this->query) ]),
        $this->intl->t("No Results"),
        Alert::SEVERITY_INFO
      );
      return;
    }

    $movies  = null;
    $persons = null;
    foreach ($result["hits"]["hits"] as $delta => $entity) {
      switch ($entity["_type"]) {
        case "movie":
          $movie   = new Movie($entity["_id"]);
          if ($movie->displayTitle != $movie->originalTitle) {
            $displayTitleItemprop = "alternateName";
            $movie->originalTitle = "<br><span class='small'>{$this->intl->t("{0} ({1})", [
              "<span itemprop='name'{$this->lang($movie->originalTitleLanguageCode)}>{$movie->originalTitle}</span>",
              $this->intl->t("original title"),
            ])}</span>";
          }
          else {
            $displayTitleItemprop = "name";
            $movie->originalTitle = null;
          }
          $movie->displayTitle = "<span class='link-color' itemprop='{$displayTitleItemprop}'{$this->lang($movie->displayTitleLanguageCode)}>{$movie->displayTitle}</span>";
          $movies .=
            "<li itemscope itemtype='http://schema.org/Movie'><a class='hover-item no-link r' href='{$movie->route}' itemprop='url'>" .
              $this->getImage($movie->displayPoster->getStyle(AbstractImage::STYLE_SPAN_01), false, [ "class" => "s s1", "itemprop" => "image" ]) .
              "<span class='s s9'>{$movie->displayTitle}{$movie->originalTitle}</span>" .
            "</a></li>"
          ;
          break;

        case "person":
          $person   = new Person($entity["_id"]);
          $persons .=
            "<li itemscope itemtype='http://schema.org/Person'><a class='hover-item no-link r' href='{$person->route}' itemprop='url'>" .
              $this->getImage($person->displayPhoto->getStyle(AbstractImage::STYLE_SPAN_01), false, [ "class" => "s s1", "itemprop" => "image" ]) .
              "<span class='s s9'>{$person->name}</span>" .
            "</a></li>"
          ;
          break;
      }
    }

    if ($movies) {
      $title                                = $this->intl->t("Movies");
      $this->sidebarNavigation->menuitems[] = [ "#movies", $title ];
      $movies                               = "<h2 id='movies'>{$title}</h2><ol class='hover-list no-list'>{$movies}</ol>";
    }

    if ($persons) {
      $title                                = $this->intl->t("Persons");
      $this->sidebarNavigation->menuitems[] = [ "#persons", $title ];
      $persons                              = "<h2 id='persons'>{$title}</h2><ol class='hover-list no-list'>{$persons}</ol>";
    }

    // Put it all together and we're done.
    return "<div id='filter' class='tar'>Filter</div>{$movies}{$persons}";
  }

}
