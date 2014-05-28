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

use \MovLib\Presentation\Error\Forbidden;

/**
 * 403 Forbidden
 *
 * The server understood the request, but is refusing to fulfill it. Authorization will not help and the request SHOULD
 * NOT be repeated. If the request method was not HEAD and the server wishes to make public why the request has not been
 * fulfilled, it SHOULD describe the reason for the refusal in the entity.  If the server does not wish to make this
 * information available to the client, the status code 404 (Not Found) can be used instead.
 *
 * @link https://tools.ietf.org/html/rfc2616#section-10.4.4
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ForbiddenException extends \RuntimeException implements \MovLib\Exception\ClientException\ClientExceptionInterface {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "ForbiddenException";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function getPresentation(\MovLib\Core\HTTP\Container $container) {
    $container->presenter = (new Forbidden($container))->init($this->message);
    return $container->presenter->getPresentation($container->presenter->getContent());
  }

}
