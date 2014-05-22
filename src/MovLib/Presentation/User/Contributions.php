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

  /**
   * {@inheritdoc}
   */
  public function init(){
    return $this
      ->initPage($this->intl->t("{username}’s Contributions"), null, $this->intl->t("Contributions"))
      ->paginationInit($this->entity->getTotalContributionCount())
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $contributions = null;
    $dateTime      = new DateTime($this->intl, $this, $this->session->userTimezone);

    /* @var $contribution \MovLib\Stub\Data\User\Contribution */
    foreach ($this->entity->getContributions($this->paginationOffset, $this->paginationLimit) as $contribution) {
      $contributions .=
        "<li class='hover-item r'>" .
          $this->{"format{$contribution->entity}"}($contribution->entity) .
          "<div class='s s2 tar'>" .
            "<p>" .
              $dateTime->formatRelative($contribution->dateTime) .
              "<a href='{$contribution->entity->r("/history")}/{$contribution->revisionId}'>{$this->intl->t("diff")}</a> | " .
              "<a href='{$contribution->entity->r("/history")}'>{$this->intl->t("history")}</a>" .
            "</p>" .
          "</div>" .
        "</li>"
      ;
    }

    return "<ol class='hover-list no-list'>{$contributions}</ol>";
//    $result = $this->entity->getContributions($this->paginationOffset, $this->paginationLimit);
//    if ($result->num_rows > 0) {
//      $contributions = "<ol class='hover-list no-list'>";
//      while ($row = $result->fetch_assoc()) {
//        $entity = (new $row["entityClass"]($this->diContainerHTTP, $row["entityId"]));
//        $this->log->debug("row:", $row);
//        $this->log->debug($entity);
//        $contributions .=
//          "<li class='hover-item r'>" .
//            $this->{"format{$entity}"}($entity) .
//            "<div class='s s2 tar'>" .
//              "<p>" .
//                (new DateTimePartial($this->intl, $this))->format(new DateTime($row["created"])) .
//                "<a href='{$this->intl->r("{$entity->routeKey}/history", $entity->routeArgs)}/{$row["revision"]}'>{$this->intl->t("diff")}</a> " .
//                "<a href='{$this->intl->r("{$entity->routeKey}/history", $entity->routeArgs)}'>{$this->intl->t("history")}</a>" .
//              "</p>" .
//            "</div>" .
//          "</li>"
//        ;
//      }
//      $contributions .= "</ol>";
//      return $contributions;
//    }
//    else {
//      return $this->getNoItemsContent();
//    }
  }

  /**
   * Format a company.
   *
   * @param \MovLib\Data\AbstractEntity $company
   * @return string
   */
  public function formatCompany(\MovLib\Data\AbstractEntity $company) {
    return
      "<div class='s s8 tar'>" .
        "<h2 class='para'>{$this->intl->t("Company")}: {$this->a($company->route, $this->htmlDecode($company->name))}</h2>" .
      "</div>"
    ;
  }

  public function formatGenre(\MovLib\Data\AbstractEntity $genre) {
    return "Genre";
  }

  public function formatMovie(\MovLib\Data\AbstractEntity $movie) {
    return "Movie";
  }

  public function formatPerson(\MovLib\Data\AbstractEntity $person) {
    return "Person";
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

}
