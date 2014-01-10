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
namespace MovLib\Presentation\Deletion;

use \MovLib\Data\Deletion;
use \MovLib\Data\User\User;
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
class Show extends \MovLib\Presentation\Page {
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
    $this->initPagination(Deletion::getCount());
    $this->initSidebar([]);
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;

    // Nothing to do if we have no requests at all.
    if ($this->resultsTotalCount === 0) {
      return new Alert($i18n->t("Great, not a single deletion request is waiting for approval."), $i18n->t("No Deletion Requests"), Alert::SEVERITY_SUCCESS);
    }

    $requests = Deletion::getResult($this->resultsOffset, $this->resultsPerPage);
    $list     = null;
    while ($request = $requests->fetch_assoc()) {
      $link = "<a href='{$request["url"]}?deletion_request={$request["id"]}'>{$request["title"]}</a>";
      $date = $i18n->formatDate($request["created"], null, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
      $user = new User(User::FROM_ID, $request["user_id"]);

      $list .= "<li>{$i18n->t("{0} ({1})", [ $link, $date ])}<blockquote>{$kernel->htmlDecode($request["reason"])}<cite>{$user->name} {$i18n->t("(Request ID {0})", [ "<code>{$request["id"]}</code>" ])}</cite></blockquote></li>";
    }
    $requests->free();
    return "<div id='filter'>filter filter fitler</div><ol class='no-list'>{$list}</ol>";
  }

}
