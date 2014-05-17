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

use \MovLib\Partial\Time;

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
    return $this->initPage($this->intl->t("{username}’s Contributions"), null, $this->intl->t("Contributions"));
  }

  /**
   * {@inheritdoc}
   */
  public function getContent(){
    $result = $this->entity->getContributions($this->paginationOffset, $this->paginationLimit);
    if ($result->num_rows > 0) {
      $contributions = "<ol class='hover-list no-list'>";
      while ($row = $result->fetch_assoc()) {
        $entity = new $row["entityClass"]($this->diContainerHTTP, $row["entityId"]);
        $revisionInfo = $entity->getRevisionInfo();
        $created = new Time($this->intl, $row["created"]);
        $contributions .=
          "<li class='hover-item r'>" .
            "<div class='s s8'>" .
              "<h2 class='para'>{$revisionInfo->type}: {$this->a($revisionInfo->route, $this->htmlDecode($revisionInfo->name))}</h2>" .
              "<p>{$this->htmlDecode($row["commitMessage"])}</p>" .
            "</div>" .
            "<div class='s s2 tar'>" .
              "<p>{$created->formatRelative()}</p>" .
              "<p><a href='{$revisionInfo->route}/{$this->intl->r("history")}/{$row["revisionHash"]}'>{$this->intl->t("show diff")}</a></p>" .
            "</div>" .
          "</li>"
        ;
      }
      $contributions .= "</ol>";
      return $contributions;
    }
    else {
      return $this->getNoItemsContent();
    }
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
