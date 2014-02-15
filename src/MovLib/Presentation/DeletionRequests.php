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
namespace MovLib\Presentation;

use \MovLib\Data\DeletionRequest;
use \MovLib\Presentation\Partial\Alert;

/**
 * Show all deletion requests for the current language.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class DeletionRequests extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitPagination;
  use \MovLib\Presentation\TraitSidebar;

  /**
   * Get the deletion requests page content.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @return string
   *   The deletion requests page content.
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function getPageContent() {
    global $i18n, $session;

    // @todo Check user reputation, for now limited to admins.
    $session->checkAuthorizationAdmin($i18n->t("Only administrators can handle deletion requests."));

    // Initialize presentation basics.
    $this->initPage($i18n->t("Deletion Requests"));
    $this->initLanguageLinks("/deletion-requests", null, true);
    $this->initBreadcrumb();

    // Create a filter for each predefined deletion request reason and of course one to list them all.
    $spam      = DeletionRequest::REASON_SPAM;
    $duplicate = DeletionRequest::REASON_DUPLICATE;
    $other     = DeletionRequest::REASON_OTHER;
    $menuitems = [
      0          => [ $i18n->rp("/deletion-requests"), $i18n->t("All") ],
      $spam      => [ "{$i18n->rp("/deletion-requests")}?{$i18n->r("reason")}={$spam}", $i18n->t("Spam") ],
      $duplicate => [ "{$i18n->rp("/deletion-requests")}?{$i18n->r("reason")}={$duplicate}", $i18n->t("Duplicate") ],
      $other     => [ "{$i18n->rp("/deletion-requests")}?{$i18n->r("reason")}={$other}", $i18n->t("Other") ],
    ];

    // Extract possible filters from the requested URL.
    $reasonId     = filter_input(INPUT_GET, $i18n->r("reason"), FILTER_VALIDATE_INT, [ "options" => [ "min_range" => 1 ] ]);
    $languageCode = filter_input(INPUT_GET, $i18n->r("language_code"), FILTER_VALIDATE_REGEXP, [ "options" => [ "default" => null, "regexp" => "/[a-z][a-z]/" ] ]);

    // Initialize the sidebar with the sorted menuitems and ignore the query string within
    // the requested URI for determining which tab is active.
    $this->sidebarInit($menuitems);
    $this->sidebarNavigation->ignoreQuery = false;

    // Nothing to do if we have no deletion requests at all.
    $this->paginationInit(DeletionRequest::getCount($reasonId, $languageCode));
    if ($this->paginationTotalResults === 0) {
      return new Alert(
        $i18n->t("Great, not a single deletion request is waiting for approval."),
        $i18n->t("No Deletion Requests"),
        Alert::SEVERITY_SUCCESS
      );
    }

    // Build listing of all deletion requests.
    $requests = DeletionRequest::getResult($this->paginationOffset, $this->paginationLimit, $reasonId, $languageCode);
    $list     = null;

    /* @var $deletionRequest \MovLib\Data\DeletionRequest */
    while ($deletionRequest = $requests->fetch_object("\\MovLib\\Data\\DeletionRequest")) {
      $dateTime    = $i18n->formatDate($deletionRequest->created, null, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
      $contentLink = "<a href='{$deletionRequest->routes[$i18n->languageCode]}'>";
      $user        = "<a href='{$deletionRequest->user->route}'>{$deletionRequest->user->name}</a>";

      // The content is already filtered, no need to state the reason with each entry.
      if ($reasonId) {
        $list .= $i18n->t("{date}: {user} has requested that {0}this content{1} should be deleted.", [
          "date" => $dateTime, "user" => $user, $contentLink, "</a>",
        ]);
      }
      else {
        $list .= $i18n->t("{date}: {user} has requested that {0}this content{1} should be deleted for the reason: “{reason}”", [
          "date" => $dateTime, "user" => $user, $contentLink, "</a>", "reason" => $deletionRequest->reason,
        ]);
      }

      $list = "<li>{$list}</li>";
    }

    $requests->free();

    return "<div id='filter' class='tar'>{$i18n->t("You can filter the deletion requests via the sidebar menu.")}</div><ol class='no-list'>{$list}</ol>";
  }

}
