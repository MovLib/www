<?php

/* !
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
namespace MovLib\Presentation;

use \MovLib\Presentation\Partial\Alert;

/**
 * @todo Description of TraitGone
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitGone {

  /**
   * The translated gone alert message.
   *
   * @var string
   */
  protected $goneAlertMessage;

  /**
   * Get the content for gone pages.
   *
   * @return \MovLib\Presentation\Partial\Alert
   *   The gone alert message.
   */
  protected function goneGetContent() {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($this->goneAlertMessage)) {
      throw new \LogicException($this->intl->t("You have to provide a message for the gone page!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    return new Alert(
      "<p>{$this->intl->t("The deletion message is provided below for reference.")}</p><p>{$this->goneAlertMessage}</p>",
      $this->intl->t("This page has been deleted."),
      Alert::SEVERITY_ERROR
    );
  }

}
