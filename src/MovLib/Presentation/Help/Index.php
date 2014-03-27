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
namespace MovLib\Presentation\Help;

use \MovLib\Presentation\Partial\Alert;

/**
 * @todo Description of Show
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitSidebar;

  public function __construct() {
    $this->initPage($this->intl->t("Help"));
    $this->initLanguageLinks("/help", null, true);
    $this->initBreadcrumb();
    $this->sidebarInit([
      [ $this->languageLinks[$this->intl->languageCode], $this->intl->t("Help"), [ "class" => "ico ico-help" ] ],
    ]);
  }

  protected function getPageContent() {
    return new Alert(
      $this->intl->t("The help isn’t implemented yet."),
      $this->intl->t("Check back later"),
      Alert::SEVERITY_INFO
    );
  }

}
