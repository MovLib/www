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
use \MovLib\Data\Movie\MovieSet;
use \MovLib\Data\Person\PersonSet;
use \MovLib\Data\Release\ReleaseSet;
use \MovLib\Data\Series\SeriesSet;
use \MovLib\Exception\ClientException\ClientExceptionInterface;

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
  use \MovLib\Partial\SectionTrait;

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Show";
  // @codingStandardsIgnoreEnd


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
    $queries       = null;
    $this->query   = $this->request->filterInputString(INPUT_GET, "q");
    if ($this->query) {
      $queries["q"] = rawurlencode($this->query);
    }

    $this->indexes = $this->request->filterInputString(INPUT_GET, "i");
    if ($this->indexes) {
      $queries["i"] = rawurlencode($this->indexes);
    }

    $this->types   = $this->request->filterInputString(INPUT_GET, "t");
    if ($this->types) {
      $queries["t"] = rawurlencode($this->types);
    }
    else {
      $this->types = "";
    }

    $this->initPage($this->intl->t("Search"), $this->intl->t("Search: {query}", [ "query" => $this->placeholder($this->query) ]));
    $this->initBreadcrumb();
    $this->sidebarInit([]);
    $this->breadcrumb->ignoreQuery = true;

    $this->initLanguageLinks("/search", null, false, $queries);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getContent() {
    // We're done if we have no search query.
    if (empty($this->query)) {
      http_response_code(ClientExceptionInterface::HTTP_BAD_REQUEST);
      $this->alertError($this->intl->t("No search query submitted."), $this->intl->t("Nothing to Search for…"));
      return;
    }

    if (empty($this->indexes)) {
      http_response_code(ClientExceptionInterface::HTTP_BAD_REQUEST);
      $this->alertError($this->intl->t("No search range submitted."), $this->intl->t("We don’t know what kind of items you are looking for…"));
      return;
    }

    // If we have a query and indexes, ask our Search object.
    $search = new \MovLib\Core\Search\Search();
    try {
      $result = $search->fuzzySearch($this->query, $this->indexes);
    }
    // Missing index or type, assume the user typed invalid parameters.
    catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
      http_response_code(ClientExceptionInterface::HTTP_BAD_REQUEST);
      $this->alertError($this->intl->t("Wrong search parameters."), $this->intl->t("Malformed search options specified. We don’t know how to fulfill your search request."));
      return;
    }

    // Add fulltext search links to an alert.
    $queryString = str_replace(" ", "+", $this->query) . "+site:{$this->config->hostnameStatic}";
    $ddg = "<li class='s s3'><a href='https://duckduckgo.com/?q={$queryString}' rel='nofollow', target='_blank'><img class='inline-middle' src='https://duckduckgo.com/favicon.ico' alt='Duck Duck Go icon' height='16' width='16'>Duck Duck Go</a></li>";
    $fulltextItems = [
      "<li class='s s3'><a href='https://google.com/search?q={$queryString}' rel='nofollow', target='_blank'><img class='inline-middle' src='https://www.google.com/images/google_favicon_128.png' alt='Google icon' height='16' width='16'>Google</a></li>",
      "<li class='s s3'><a href='https://www.bing.com/search?q={$queryString}' rel='nofollow', target='_blank'><img class='inline-middle' src='https://www.bing.com/s/a/bing_p.ico' alt='Google icon' height='16' width='16'>Bing</a></li>",
      "<li class='s s3'><a href='https://search.yahoo.com/search?p={$queryString}' rel='nofollow', target='_blank'><img class='inline-middle' src='http://img2.wikia.nocookie.net/__cb20130905153346/logopedia/images/2/2f/Yahoo_Favicon_2013.png' alt='Yahoo! icon' height='16' width='16'>Yahoo!</a></li>",
    ];

    // Randomize display order of Google, Bing and Yahoo! links.
    shuffle($fulltextItems);

    // Always display Duck Duck Go first.
    $fulltextAlert = "{$this->intl->t("Try a full text search with the sites linked below.")}<ul class='no-list r'>{$ddg}";

    // Add the others.
    foreach ($fulltextItems as $item) {
      $fulltextAlert .= $item;
    }
    $this->alert($this->intl->t("Haven’t found what you were looking for?"), "{$fulltextAlert}</ul>");

    // No results returned, we are done.
    if (count($result) === 0) {
      $this->alertError(
        $this->intl->t("Your search {query} did not match any document.", [ "query" => $this->placeholder($this->query) ]),
        $this->intl->t("No Results")
      );
      return;
    }

    $ids = null;

    foreach ($result as $indexName => $types) {
      foreach ($types as $typeName => $documents) {
        if (empty($ids[$typeName])) {
          $ids[$typeName] = [];
        }
        $ids[$typeName] = array_merge($ids[$typeName], array_keys($documents));
      }
    }

    // Instantiate entity sets and formatting helpers according to the types received.
    // Render them to sections straight away.
    $notImplementedMessage = null;
    foreach ($ids as $typeName => $idsToLoad) {
      $typeName    = ucfirst($typeName);
      $setClass    = "\\MovLib\\Data\\{$typeName}\\{$typeName}Set";
      $helperClass = "\\MovLib\\Partial\\Helper\\{$typeName}Helper";
      // Check for incomplete implementations, add alert message and continue.
      if (!method_exists($setClass, "loadIdentifiers") || !method_exists($helperClass, "getListing")) {
        $notImplementedMessage .= "<p>{$this->intl->t("Search for {type} is not implemented yet.", [ "type" => $typeName ])}</p>";
        continue;
      }
      $set = (new $setClass($this->container))->loadIdentifiers($idsToLoad);
      $this->sectionAdd($set->bundleTitle, (new $helperClass($this->container))->getListing($set), false);
    }

    // Display not implemented message if there were entities which don't have the necessary implementations
    // for rendering them.
    if ($notImplementedMessage) {
      $this->alertInfo($this->intl->t("Not implemented yet."), $notImplementedMessage);
    }

    // Put it all together and we're done.
    return "<div id='filter' class='tar'>Filter</div>{$this->sections}";
  }

}
