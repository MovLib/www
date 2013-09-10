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
namespace MovLib\View\HTML;

use \MovLib\View\HTML\AbstractPageView;

/**
 * Special view without any content (by default) for displaying alert messages.
 *
 * A presenter inserts HTML mark-up in the content of such a view by utilizing the <code>AlertView::setContent()</code>
 * method.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AlertView extends AbstractPageView {

  /**
   * Additional content to display.
   *
   * @var string
   */
  public $content;

  /**
   * Instantiate new alert view.
   *
   * @param \MovLib\Presenter\AbstractPresenter $presenter
   *   The presenter controlling this view, any presenter that is compatible with the abstract presenter interface.
   * @param string $title
   *   Already translated title for this view.
   * @param string $content [optional]
   *   Content to display below the alert messages.
   */
  public function __construct($presenter, $title, $content = "") {
    $this->init($presenter, $title);
    $this->content = $content;
  }

  /**
   * @inheritdoc
   */
  public function getContent() {
    return $this->content ? "<div class='container'>{$this->content}</div>" : "";
  }

}
