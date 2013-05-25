<?php

/* !
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
 * Description of ForbiddenView
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ForbiddenView extends AlertView {

  /**
   * Create a <em>403 Forbidden</em> error page.
   *
   * @param \MovLib\Presenter\AbstractPresenter $presenter
   *   The presenter object controlling this view.
   */
  public function __construct($presenter) {
    parent::__construct($presenter, __("Access forbidden"));
    http_response_code(403);
    $this->setAlert(
      "<p>" . __("We are sorry, but you can not access this page.") . "</p>" .
      "<p>" . sprintf(
        __("There can be various reasons why you might see this error message. If you feel that receiving this error is a mistake please %s."),
        $this->a(__("contact", "route"), __("contact us"))
      ) . "</p>",
      __("Access forbidden"),
      "error",
      true
    );
  }

}
