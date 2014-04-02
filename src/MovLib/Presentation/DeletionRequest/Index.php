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
namespace MovLib\Presentation\DeletionRequest;

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
class Index extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitPagination;
  use \MovLib\Presentation\TraitSidebar;

  /**
   * Get the deletion requests page content.
   *
   * @return string
   *   The deletion requests page content.
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function getPageContent() {
    // @todo Check user reputation, for now limited to admins.
    $session->checkAuthorizationAdmin($this->intl->t("Only administrators can handle deletion requests."));

    // Initialize presentation basics.
    $this->initPage($this->intl->t("Deletion Requests"));
    $this->initLanguageLinks("/deletion-requests", null, true);
    $this->initBreadcrumb();

    // Create a filter for each predefined deletion request reason and of course one to list them all.
    $spam      = DeletionRequest::REASON_SPAM;
    $duplicate = DeletionRequest::REASON_DUPLICATE;
    $other     = DeletionRequest::REASON_OTHER;
    $menuitems = [
      0          => [ $this->intl->rp("/deletion-requests"), $this->intl->t("All") ],
      $spam      => [ "{$this->intl->rp("/deletion-requests")}?{$this->intl->r("reason")}={$spam}", $this->intl->t("Spam") ],
      $duplicate => [ "{$this->intl->rp("/deletion-requests")}?{$this->intl->r("reason")}={$duplicate}", $this->intl->t("Duplicate") ],
      $other     => [ "{$this->intl->rp("/deletion-requests")}?{$this->intl->r("reason")}={$other}", $this->intl->t("Other") ],
    ];

    // Extract possible filters from the requested URL.
    $reasonId     = filter_input(INPUT_GET, $this->intl->r("reason"), FILTER_VALIDATE_INT, [ "options" => [ "min_range" => 1 ] ]);
    $languageCode = filter_input(INPUT_GET, $this->intl->r("language_code"), FILTER_VALIDATE_REGEXP, [ "options" => [ "default" => null, "regexp" => "/[a-z][a-z]/" ] ]);

    // Initialize the sidebar with the sorted menuitems and ignore the query string within
    // the requested URI for determining which tab is active.
    $this->sidebarInit($menuitems);
    $this->sidebarNavigation->ignoreQuery = false;

    // Nothing to do if we have no deletion requests at all.
    $this->paginationInit(DeletionRequest::getCount($reasonId, $languageCode));
    if ($this->paginationTotalResults === 0) {
      return new Alert(
        $this->intl->t("Great, not a single deletion request is waiting for approval."),
        $this->intl->t("No Deletion Requests"),
        Alert::SEVERITY_SUCCESS
      );
    }

    // Build listing of all deletion requests.
    $requests = DeletionRequest::getResult($this->paginationOffset, $this->paginationLimit, $reasonId, $languageCode);
    $list     = null;

    /* @var $deletionRequest \MovLib\Data\DeletionRequest */
    while ($deletionRequest = $requests->fetch_object("\\MovLib\\Data\\DeletionRequest")) {
      $dateTime    = $this->intl->formatDate($deletionRequest->created, null, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
      $contentLink = "<a href='{$deletionRequest->routes[$this->intl->languageCode]}'>";
      $user        = "<a href='{$deletionRequest->user->route}'>{$deletionRequest->user->name}</a>";

      // The content is already filtered, no need to state the reason with each entry.
      if ($reasonId) {
        $list .= $this->intl->t("{date}: {user} has requested that {0}this content{1} should be deleted.", [
          "date" => $dateTime, "user" => $user, $contentLink, "</a>",
        ]);
      }
      else {
        $list .= $this->intl->t("{date}: {user} has requested that {0}this content{1} should be deleted for the reason: “{reason}”", [
          "date" => $dateTime, "user" => $user, $contentLink, "</a>", "reason" => $deletionRequest->reason,
        ]);
      }

      $list = "<li>{$list}</li>";
    }

    $requests->free();

    return "<div id='filter' class='tar'>{$this->intl->t("You can filter the deletion requests via the sidebar menu.")}</div><ol class='no-list'>{$list}</ol>";
  }

}
