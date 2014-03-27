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
namespace MovLib\Exception;

use \MovLib\Presentation\Profile\SignIn;
use \MovLib\Presentation\Partial\Alert;

/**
 * The request requires user authentication.
 *
 * @link https://tools.ietf.org/html/rfc2616#section-10.4.2
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class UnauthorizedException extends \RuntimeException implements \MovLib\Exception\ClientException {

  /**
   * {@inheritdoc}
   */
  public function getPresentation() {
    header("WWW-Authenticate: {$config->siteName} location='{$i18n->r("/profile/sign-in")}'", true, 401);

    // Never cache an unauthorized response.
    $response->cacheable = false;

    // Trick the sign in presentation.
    $request->method = "GET";

    // Use default message if no message was set.
    if (empty($this->message)) {
      $this->message = $i18n->t(
        "Please use the form below to sign in or {0}join {sitename}{1}.",
        [ "<a href='{$i18n->r("/profile/join")}'>", "</a>", "sitename" => $config->siteName ]
      );
    }

    // Allow classes to define their own alert partial.
    if (!is_object($this->message)) {
      $this->message = new Alert(
        $this->message,
        $i18n->t("You must be signed in to access this content."),
        Alert::SEVERITY_ERROR
      );
    }

    // Put the unauthorized exception together.
    $presenter = new SignIn();
    $presenter->alerts .= $this->message;
    $content = $presenter->getContent();
    $header  = $presenter->getHeader();
    $main    = $presenter->getMainContent($content);
    $footer  = $presenter->getFooter();
    return $presenter->getPresentation($header, $main, $footer);
  }

}
