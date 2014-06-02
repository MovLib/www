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
namespace MovLib\Presentation\Help\Api;

/**
 * Defines the API index presentation.
 *
 * @route /help/api
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Index";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initPage($this->intl->t("MovLib API"));
    $this->initBreadcrumb([ [ $this->intl->r("/help"), $this->intl->t("Help") ] ]);
    $this->initLanguageLinks("/help/api");
    $this->stylesheets[] = "help";
    $this->sidebarInit([ [ $this->request->path, $this->title, [ "class" => "ico ico-api" ] ] ]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->callout($this->intl->t(
      "The MovLib API is a REST interface to access the free movie library. Specifically designed for all " .
      "developers out there. We want to keep the barrier as low as possible and ensure that everybody can use the " .
      "data we all collect here at MovLib."
    ));
  }

}
