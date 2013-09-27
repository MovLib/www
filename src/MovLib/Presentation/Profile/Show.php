<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Profile;

use \MovLib\Data\User;

/**
 * User account summary for logged in user's.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\Profile\TraitProfile;

  /**
   * Instantiate new user show presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @throws \MovLib\Exception\UnauthorizedException
   */
  public function __construct() {
    global $i18n, $session;
    $session->checkAuthorization($i18n->t("You must be signed in to view your profile."));
    $this->init(null)->user = new User(User::FROM_ID, $session->userId);
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n, $session;
    ob_start();
    var_dump($this->user);
    $var[] = ob_get_clean();
    ob_start();
    var_dump($session);
    $var[] = ob_get_clean();
    ob_start();
    var_dump($GLOBALS);
    $var[] = ob_get_clean();
    for ($i = 0; $i < 3; ++$i) {
      $var[$i] = highlight_string("<?php\n\n{$var[$i]}\n?>", true);
      $var[$i] = str_replace(
        [ "<code>", "</code>" ],
        [ "<pre style='whitespace:normal'>", "</pre>" ],
        $var[$i]
      );
    }
    return
      "<h2>{$i18n->t("Your Account Summary")}</h2>" .
      "<div class='row'>" .
        "<dl class='dl--horizontal span span--7'>" .
          "<dt>{$i18n->t("Username")}</dt><dd>{$this->user->name}</dd>" .
          "<dt>{$i18n->t("User ID")}</dt><dd>{$this->user->id}</dd>" .
          "<dt>{$i18n->t("Edits")}</dt><dd>{$this->user->edits}</dd>" .
          "<dt>{$i18n->t("Reputation")}</dt><dd><em>@todo</em> reputation counter</dd>" .
          "<dt>{$i18n->t("Email Address")}</dt><dd>{$this->user->email}</dd>" .
          "<dt>{$i18n->t("Registration")}</dt><dd>{$i18n->formatDate($this->user->created, $this->user->timeZoneId)}</dd>" .
          "<dt>{$i18n->t("Last visit")}</dt><dd>{$i18n->formatDate($this->user->access, $this->user->timeZoneId)}</dd>" .
        "</dl>" .
        "<div class='span span--2'>" .
          $this->a($i18n->r("/user/account-settings"), $this->getImage($this->user, User::IMAGESTYLE_BIG), [
            "class" => "change-avatar no-border",
            "title" => "Change your avatar image.",
          ]) .
        "</div>" .
      "</div>" .
      "<h2>User</h2>{$var[0]}" .
      "<h2>Session</h2>{$var[1]}"
    ;
  }

}
