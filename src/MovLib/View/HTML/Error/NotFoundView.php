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
namespace MovLib\View\HTML\Error;

use \MovLib\View\HTML\AlertView;

/**
 * Display a <em>404 Not Found</em> error page (with correct HTTP headers) to the user.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class NotFoundView extends AlertView {

  /**
   * Create a <em>404 Not Found</em> error page.
   *
   * @param \MovLib\Presenter\AbstractPresenter $presenter
   *   The presenter object controlling this view.
   */
  public function __construct($presenter) {
    parent::__construct($presenter, __("Not Found"));
    http_response_code(404);
    $this->setAlert(
      "<p>" . __("We are sorry, but we could not find the page you are looking for.") . "</p>" .
      "<p>" . __("There can be various reasons why you might see this error message. If you feel that receiving this error is a mistake please !contactLink.", [ "!contactLink" => $this->a(route("contact"), __("contact us")) ]) . "</p>",
      __("Not Found"),
      self::ALERT_SEVERITY_ERROR,
      true
    );
  }

}
