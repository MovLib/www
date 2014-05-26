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
namespace MovLib\Presentation\User;

use \MovLib\Partial\DateTime;

/**
 * Defines the user contribution presentation object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Contributions extends \MovLib\Presentation\User\AbstractUserPresenter {
  use \MovLib\Partial\PaginationTrait;

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Contributions";
  // @codingStandardsIgnoreEnd

  public function formatArticle(\MovLib\Data\AbstractEntity $article) {
    return
      "<td class='tac'><span class='ico ico-help' title='{$this->intl->t("Help Article")}'></span></td>" .
      "<td>{$this->a($article->route, $this->htmlDecode($article->title))}</td>"
    ;
  }

  public function formatAward(\MovLib\Data\AbstractEntity $award) {
    return
      "<td class='tac'><span class='ico ico-award' title='{$this->intl->t("Award")}'></span></td>" .
      "<td>{$this->a($award->route, $this->htmlDecode($award->name))}</td>"
    ;
  }

  public function formatCategory(\MovLib\Data\AbstractEntity $category) {
    return
      "<td class='tac'><span class='ico ico-category' title='{$this->intl->t("Category")}'></span></td>" .
      "<td>{$this->a($category->route, $this->htmlDecode($category->name))}</td>"
    ;
  }

  public function formatCompany(\MovLib\Data\AbstractEntity $company) {
    return
      "<td class='tac'><span class='ico ico-company' title='{$this->intl->t("Company")}'></span></td>" .
      "<td>{$this->a($company->route, $this->htmlDecode($company->name))}</td>"
    ;
  }

  public function formatEvent(\MovLib\Data\AbstractEntity $event) {
    return
      "<td class='tac'><span class='ico ico-event' title='{$this->intl->t("Event")}'></span></td>" .
      "<td>{$this->a($event->route, $this->htmlDecode($event->name))}</td>"
    ;
  }

  public function formatGenre(\MovLib\Data\AbstractEntity $genre) {
    return
      "<td class='tac'><span class='ico ico-genre' title='{$this->intl->t("Genre")}'></span></td>" .
      "<td>{$this->a($genre->route, $this->htmlDecode($genre->name))}</td>"
    ;
  }

  public function formatJob(\MovLib\Data\AbstractEntity $job) {
    return
      "<td class='tac'><span class='ico ico-job' title='{$this->intl->t("Job")}'></span></td>" .
      "<td>{$this->a($job->route, $this->htmlDecode($job->title))}</td>"
    ;
  }

  public function formatMovie(\MovLib\Data\AbstractEntity $movie) {
    return
      "<td class='tac'><span class='ico ico-movie' title='{$this->intl->t("Movie")}'></span></td>" .
      "<td>{$this->a($movie->route, $this->htmlDecode($movie->displayTitle))}</td>"
    ;
  }

  public function formatPerson(\MovLib\Data\AbstractEntity $person) {
    return
      "<td class='tac'><span class='ico ico-person' title='{$this->intl->t("Person")}'></span></td>" .
      "<td>{$this->a($person->route, $this->htmlDecode($person->name))}</td>"
    ;
  }

  public function formatRelease(\MovLib\Data\AbstractEntity $release) {
    return
      "<td class='tac'><span class='ico ico-release' title='{$this->intl->t("Release")}'></span></td>" .
      "<td>{$this->a($release->route, $this->htmlDecode($release->title))}</td>"
    ;
  }

  public function formatSeries(\MovLib\Data\AbstractEntity $series) {
    return
      "<td class='tac'><span class='ico ico-series' title='{$this->intl->t("Series")}'></span></td>" .
      "<td>{$this->a($series->route, $this->htmlDecode($series->displayTitle))}</td>"
    ;
  }

  public function formatSystemPage(\MovLib\Data\AbstractEntity $systemPage) {
    return
      "<td class='tac'><span class='ico ico-view' title='{$this->intl->t("Movlib")}'></span></td>" .
      "<td>{$this->a($systemPage->route, $this->htmlDecode($systemPage->title))}</td>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $contributions = null;
    $dateTime      = new DateTime($this->intl, $this, $this->session->userTimezone);

    $orderBy = "`revisionId` DESC";
    $this->request->query["field"] = "date";
    $this->request->query["sort"] = "asc";
    if ($this->paginationCurrentPage === 1) {
      unset($this->request->query["page"]);
    }
    else {
      $this->request->query["page"] = $this->paginationCurrentPage;
    }
    $queryString = http_build_query($this->request->query);
    $orderCaret = "<a class='btn btn-mini ico ico-chevron-down' href='{$this->request->path}?{$queryString}'>▾</a>";
    if (isset($_GET["sort"]) && isset($_GET["field"])) {
      if ($this->request->filterInputString(INPUT_GET, "field") == "date" && $this->request->filterInputString(INPUT_GET, "sort") == "asc") {
        $orderBy = "`revisionId` ASC";
        unset($this->request->query["field"]);
        unset($this->request->query["sort"]);
        $queryString = null;
        if (!empty($this->request->query)) {
          $queryString = "?" . http_build_query($this->request->query);
        }
        $orderCaret = "<a class='btn btn-mini ico ico-chevron-up' href='{$this->request->path}{$queryString}'>▴</a>";
      }
    }

    /* @var $contribution \MovLib\Stub\Data\User\Contribution */
    foreach ($this->entity->getContributions($this->paginationOffset, $this->paginationLimit, $orderBy) as $contribution) {
      $contributions .=
        "<tr>" .
          $this->{"format{$contribution->entity}"}($contribution->entity) .
          "<td class='tac'>{$dateTime->format($contribution->dateTime)}</td>" .
          "<td><a class='btn btn-info btn-small' href='{$contribution->entity->r("/history")}/{$contribution->revisionId}'>{$this->intl->t("diff")}</a></td>" .
          "<td><a class='btn btn-success btn-small' href='{$contribution->entity->r("/history")}'>{$this->intl->t("history")}</a></td>" .
        "</tr>"
      ;
    }

    return
      "<table>" .
        "<colgroup>" .
          "<col class='s1'>" .
          "<col class='s4'>" .
          "<col class='s3'>" .
          "<col class='s1'>" .
          "<col class='s1'>" .
        "</colgroup>" .
        "<thead>" .
          "<tr>" .
            "<th colspan='2'>{$this->intl->t("Contribution")}</th>".
            "<th>{$this->intl->t("Date")} &nbsp; {$orderCaret}</th>".
            "<th colspan='2'>{$this->intl->t("Actions")}</th>" .
          "</tr>" .
        "</thead>" .
        "<tbody>{$contributions}</tbody>" .
      "</table>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return $this->calloutInfo(
      "<p>{$this->intl->t("We couldn’t find any contributions by this user")}</p>",
       $this->intl->t("No Contributions")
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(){
    return $this
      ->initPage($this->intl->t("{username}’s Contributions"), null, $this->intl->t("Contributions"))
      ->paginationInit($this->entity->getTotalContributionCount())
    ;
  }

}
