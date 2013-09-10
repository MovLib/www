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
namespace MovLib\View\HTML\User;

use \MovLib\View\HTML\AbstractFormView;
use \MovLib\View\HTML\Alert;

/**
 * User notification settings form template.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserNotificationSettingsView extends AbstractFormView {
  use \MovLib\View\HTML\TraitSecondaryNavigationView;

  /**
   * Instantiate new user notification settings view.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param \MovLib\Presenter\User\UserNotificationSettingsPresenter $presenter
   *   The presenting presenter.
   */
  public function __construct($presenter) {
    global $i18n;
    $this->init($presenter, $i18n->t("Notification Settings"));
    $this->stylesheets[] = "modules/user.css";
  }

  /**
   * @inheritdoc
   */
  public function getSecondaryContent() {
    global $i18n;
    return new Alert($i18n->t("The notification system isn’t implemented yet."), [
      "block"    => true,
      "title"    => $i18n->t("Check back later"),
      "severity" => Alert::SEVERITY_INFO,
    ]);
  }

}
