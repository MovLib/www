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
namespace MovLib\Partial;

use \MovLib\Partial\FormElement\Select;

/**
 * Defines formatting methods to represent currencies.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Currency extends \MovLib\Core\Presentation\Base {

  /**
   * Get select form element to select a currency.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   The HTTP dependency injection container.
   * @param string $value
   *   The form element's value.
   * @param array $attributes [optional]
   *   The form element's additional attributes.
   * @param string $id [optional]
   *   The form element's unique identifier, defaults to <code>"currency"</code>.
   * @param string $label [optional]
   *   The form element's translated label, default to <code>$this->intl->t("Currency")</code>.
   * @return \MovLib\Presentation\Partial\FormElement\Select
   *   The select form element to select a currency.
   */
  public function getSelectFormElement(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, &$value, array $attributes = null, $id = "currency", $label = null) {
    $options = [];
    /* @var $currency \MovLib\Stub\Data\Currency */
    foreach ($diContainerHTTP->intl->getTranslations("currencies") as $currency) {
      $options[$currency->code] = $diContainerHTTP->intl->t("{0} ({1})", [ $currency->symbol, $currency->name ]);
    }
    return new Select($diContainerHTTP, $id, $label ?: $diContainerHTTP->intl->t("Currency"), $options, $value, $attributes);
  }

}
