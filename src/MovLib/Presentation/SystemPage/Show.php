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
namespace MovLib\Presentation\SystemPage;

use \MovLib\Data\SystemPage;

/**
 * Single system page presentation.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Page {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The system page to present.
   *
   * @var \MovLib\Data\SystemPage
   */
  protected $systemPage;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new system page presentation.
   */
  public function __construct() {
    $this->systemPage = new SystemPage($_SERVER["ID"]);
    $this->initPage($this->systemPage->title);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Kernel $kernel
   */
  protected function getContent() {
    global $kernel;
    return "<div class='c'><div class='r'><div class='s s12'>{$kernel->htmlDecode($this->systemPage->text)}</div></div></div>";
  }

}
