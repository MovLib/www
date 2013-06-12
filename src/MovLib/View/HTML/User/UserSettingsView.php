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

use \MovLib\Model\UserModel;
use \MovLib\View\HTML\AbstractFormView;

/**
 * Render the user's control center where she/he can change all account settings.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserSettingsView extends AbstractFormView {

  /**
   * The user presenter controlling this view.
   *
   * @var \MovLib\Presenter\UserPresenter
   */
  protected $presenter;

  /**
   * The name of the settings tab that should be rendered.
   *
   * @var string
   */
  public $tab = "Account";

  /**
   * {@inheritdoc}
   */
  public function __construct($presenter) {
    global $i18n;
    parent::__construct($presenter, $i18n->t("Settings"));
    $this->addStylesheet("/assets/css/modules/user.css");
  }

  /**
   * {@inheritdoc}
   */
  public function getFormContent() {
    global $i18n;
    return "";
  }

  private function getAccountTab() {
    global $i18n;
    return "";
  }

  private function getPasswordTab() {
    global $i18n;
    return "";
  }

}
