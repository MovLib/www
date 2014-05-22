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
use \MovLib\Exception\ClientException\ClientExceptionInterface;
use \MovLib\Presentation\Partial\Alert;

/**
 * Present search results to the user.
 *
 * @route /search
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
   * The indexes to search in (e.g. persons).
   *
   * @var null|string
   */
  protected $indexes;

  /**
   * The user's submitted search query.
   *
   * @var null|string
   */
  protected $query;

  /**
   * The types to search for (e.g. person).
   *
   * @var null|string
   */
  protected $types;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initPage($this->intl->t("Search"));
    $this->initBreadcrumb();
    $this->sidebarInit([]);
    $this->breadcrumb->ignoreQuery = true;

    $queries       = null;
    $this->query   = $this->request->filterInputString(INPUT_GET, "q");
    if ($this->query) {
      $queries["q"] = rawurlencode($this->query);
    }

    $this->indexes = $this->request->filterInputString(INPUT_GET, "i");
    if ($this->indexes) {
      $queries["q"] = rawurlencode($this->indexes);
    }

    $this->types   = $this->request->filterInputString(INPUT_GET, "t");
    if ($this->types) {
      $queries["q"] = rawurlencode($this->types);
    }
    else {
      $this->types = "";
    }

    $this->initLanguageLinks("/search", null, false, $queries);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getContent() {
    // We're done if we have no search query.
    if (empty($this->query)) {
      $this->alertError($this->intl->t("No search query submitted."), $this->intl->t("Nothing to Search for…"));
      return;
    }

    if (empty($this->indexes)) {
      http_response_code(ClientExceptionInterface::HTTP_BAD_REQUEST);
      $this->alertError($this->intl->t("No search range submitted."), $this->intl->t("We don’t know what to search for…"));
      return;
    }

    // If we have one, ask Elastic.
    $elasticClient = new ElasticClient();
    $result = $elasticClient->search([
      "index" => $this->indexes,
      "type"  => $this->types,
      "body"  => [
        "query" => [
          "fuzzy_like_this" => [
            "fields"          => [ "titles", "name", "suggest.input" ],
            "like_text"       => $this->query,
            "max_query_terms" => 25,
            "min_similarity"  => 0.5,
          ],
        ],
      ],
    ]);

    // Elastic didn't return anything, we're done.
    if (!isset($result["hits"]) || !isset($result["hits"]["total"]) || $result["hits"]["total"] === 0) {
      $this->alertError(
        $this->intl->t("Your search {query} did not match any document.", [ "query" => $this->placeholder($this->query) ]),
        $this->intl->t("No Results")
      );
      return;
    }

    $movies  = null;
    $persons = null;
    foreach ($result["hits"]["hits"] as $delta => $entity) {
      switch ($entity["_type"]) {
        case "movie":
          $movie   = new Movie($this->diContainerHTTP, $entity["_id"]);
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
              $this->img($movie->imageGetStyle("s1"), [ "class" => "s s1", "itemprop" => "image" ]) .
              "<span class='s s9'>{$movie->displayTitle}{$movie->originalTitle}</span>" .
            "</a></li>"
          ;
          break;

        case "person":
          $person   = new Person($this->diContainerHTTP, $entity["_id"]);
          $persons .=
            "<li itemscope itemtype='http://schema.org/Person'><a class='hover-item no-link r' href='{$person->route}' itemprop='url'>" .
              $this->img($person->imageGetStyle("s1"), [ "class" => "s s1", "itemprop" => "image" ]) .
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
