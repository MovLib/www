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
namespace MovLib\Presentation\Error;

use \MovLib\Exception\ClientException\ClientExceptionInterface;

/**
 * Defines the forbidden error page.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Forbidden extends \MovLib\Presentation\AbstractPresenter {

  /**
   * {@inheritdoc}
   */
  public function getContent() {}

  /**
   * {@inheritdoc}
   */
  public function init($alertTitle = null) {
    http_response_code(ClientExceptionInterface::HTTP_FORBIDDEN);
    $this->response->cacheable = false;
    $this->alertError(
      $alertTitle,
      $this->intl->t(
        "There can be various reasons why you might see this error message. If you feel that receiving this error is a mistake please {0}contact us{1}.",
        [ "<a href='{$this->intl->r("/contact")}'>", "</a>" ]
    ));
    return $this->initPage($this->intl->t("Forbidden"));
  }

}
