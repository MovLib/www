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
   * Instantiate new show all deletion requests.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $i18n, $session;

    // @todo Check user reputation, for now limited to admins.
    $session->checkAuthorizationAdmin($i18n->t("Only administrators can handle deletion requests."));

    $this->initPage($i18n->t("Deletion Requests"));
    $this->initLanguageLinks("/deletion-requests", null, true);
    $this->initBreadcrumb();
    $this->initPagination(DeletionRequest::getCount());

    $menuitems = [
      [ $i18n->rp("/deletion-requests"), $i18n->t("All") ],
      DeletionRequest::REASON_SPAM      => [ "{$i18n->rp("/deletion-requests")}?{$i18n->r("reason")}=" . DeletionRequest::REASON_SPAM, $i18n->t("Spam") ],
      DeletionRequest::REASON_DUPLICATE => [ "{$i18n->rp("/deletion-requests")}?{$i18n->r("reason")}=" . DeletionRequest::REASON_DUPLICATE, $i18n->t("Duplicate") ],
      DeletionRequest::REASON_OTHER     => [ "{$i18n->rp("/deletion-requests")}?{$i18n->r("reason")}=" . DeletionRequest::REASON_OTHER, $i18n->t("Other") ],
    ];

    if (isset($_GET[$i18n->r("reason")]) && isset($menuitems[$_GET[$i18n->r("reason")]])) {
      $first = $menuitems[$_GET[$i18n->r("reason")]];
      unset($menuitems[$_GET[$i18n->r("reason")]]);
      array_unshift($menuitems, $first);
    }

    $menuitems[0][2] = [ "class" => "separator" ];

    $this->initSidebar($menuitems);
    $this->sidebarNavigation->ignoreQuery = false;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n;

    // Nothing to do if we have no requests at all.
    if ($this->resultsTotalCount === 0) {
      return new Alert($i18n->t("Great, not a single deletion request is waiting for approval."), $i18n->t("No Deletion Requests"), Alert::SEVERITY_SUCCESS);
    }

    $requests = DeletionRequest::getResult($this->resultsOffset, $this->resultsPerPage);
    $list     = null;
    /* @var $deletionRequest \MovLib\Data\DeletionRequest */
    while ($deletionRequest = $requests->fetch_object("\\MovLib\\Data\\DeletionRequest")) {
      $dateTime    = $i18n->formatDate($deletionRequest->created, null, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
      $contentLink = "<a href='{$deletionRequest->routes[$i18n->languageCode]}'>";
      $user        = "<a href='{$deletionRequest->user->route}'>{$deletionRequest->user->name}</a>";
      if (!isset($_GET[$i18n->t("reason")])) {
        $list .= $i18n->t("{date}: {user} has requested that {0}this content{1} should be deleted for the reason: “{reason}”", [
          "date" => $dateTime, "user" => $user, $contentLink, "</a>", "reason" => $deletionRequest->reason,
        ]);
      }
      else {
        $list .= $i18n->t("{date}: {user} has requested that {0}this content{1} should be deleted.", [
          "date" => $dateTime, "user" => $user, $contentLink, "</a>",
        ]);
      }
      $list = "<li>{$list}</li>";
    }
    $requests->free();
    $list = "{$list}{$list}{$list}{$list}{$list}{$list}{$list}{$list}{$list}";
    return "<div id='filter' class='tar'>{$i18n->t("You can filter the deletion requests via the sidebar menu.")}</div><ol class='no-list'>{$list}</ol>";
  }

}
