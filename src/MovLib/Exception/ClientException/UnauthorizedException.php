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
namespace MovLib\Exception\ClientException;

use \MovLib\Partial\Alert;
use \MovLib\Presentation\Profile\SignIn;

/**
 * 401 Unauthorized
 *
 * The request requires user authentication. The response MUST include a WWW-Authenticate header field (
 * {@link https://tools.ietf.org/html/rfc2616#section-14.47 section 14.47}) containing a challenge applicable to the
 * requested resource. The client MAY repeat the request with a suitable Authorization header field (
 * {@link https://tools.ietf.org/html/rfc2616#section-14.8 section 14.8). If the request already included Authorization
 * credentials, then the 401 response indicates that authorization has been refused for those credentials. If the 401
 * response contains the same challenge as the prior response, and the user agent has already attempted authentication
 * at least once, then the user SHOULD be presented the entity that was given in the response, since that entity might
 * include relevant diagnostic information. HTTP access authentication is explained in "HTTP Authentication: Basic and
 * Digest Access Authentication" [{@link https://tools.ietf.org/html/rfc2616#ref-43 43}].
 *
 * @link https://tools.ietf.org/html/rfc2616#section-10.4.2
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class UnauthorizedException extends \RuntimeException implements \MovLib\Exception\ClientException\ClientExceptionInterface {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "UnauthorizedException";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The alert message.
   *
   * @var mixed
   */
  protected $alert;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new unauthorized exception.
   *
   * @internal
   *   We have to overwrite the constructor at this point because our parent class automatically transforms the message
   *   into a string.
   * @param mixed $message [optional]
   *
   */
  public function __construct($message = null, $code = 0, \Exception $previous = null) {
    $this->alert = $message;
    parent::__construct("You must be signed in to access this content.", $code, $previous);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getPresentation(\MovLib\Core\HTTP\Container $container) {
    header("WWW-Authenticate: {$container->config->sitename} location='{$container->intl->r("/profile/sign-in")}'", true, 401);

    // Never cache an unauthorized response.
    $container->response->cacheable = false;

    // Trick the sign in presentation.
    $container->request->method    = "GET";
    $container->request->methodGET = true;

    // Use default message if no message was passed.
    if (empty($this->alert)) {
      $this->alert = $container->intl->t(
        "You must be signed in to access this content. Please use the form below to sign in or {0}join {sitename}{1}.",
        [ "<a href='{$container->intl->r("/profile/join")}'>", "</a>", "sitename" => $container->config->sitename ]
      );
    }

    // Allow classes to define custom alert messages.
    if (!($this->alert instanceof Alert)) {
      $this->alert = new Alert($this->alert, $container->intl->t("Unauthorized"), Alert::SEVERITY_ERROR);
    }

    $languageLinks                             = $container->presenter->languageLinks;
    $container->presenter                = (new SignIn($container))->init();
    $container->presenter->alerts       .= $this->alert;
    $container->presenter->languageLinks = $languageLinks;
      return $container->presenter->getPresentation($container->presenter->getContent());
    }

}
