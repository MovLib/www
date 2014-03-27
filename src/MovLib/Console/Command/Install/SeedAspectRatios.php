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
namespace MovLib\Console\Command\Install;

/**
 * Seed aspect ratios.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SeedAspectRatios extends \MovLib\Console\Command\Install\AbstractIntlCommand {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * All available aspect ratios.
   *
   * @link https://en.wikipedia.org/wiki/Aspect_ratio_%28image%29
   * @var array
   */
  protected $aspectRatios = [
     "1.19"  => null,
     "1.334" => "4:3",
     "1.375" => null,
     "1.43"  => null,
     "1.50"  => null,
     "1.556" => null,
     "1.60"  => "16:10",
     "1.667" => null,
     "1.75"  => null,
     "1.778" => "16:9",
     "1.85"  => null,
     "1.896" => null,
     "2.00"  => null,
     "2.10"  => "21:10",
     "2.20"  => null,
     "2.35"  => null,
     "2.37"  => "21:9",
     "2.39"  => null,
     "2.55"  => null,
     "2.59"  => null,
     "2.667" => null,
     "2.76"  => null,
     "2.93"  => null,
     "4.00"  => null,
    "12.00"  => null,
  ];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setName("seed-aspect-ratios");
    $this->setDescription("Seed aspect ratios");
  }

  /**
   * {@inheritdoc}
   */
  protected function translate() {
    $numberFormatter = new \NumberFormatter($i18n->locale, \NumberFormatter::DECIMAL, "#0.###");
    $translations    = null;
    foreach ($this->aspectRatios as $intl => $info) {
      $name = "{$numberFormatter->format($intl)}:1";
      if (isset($info)) {
        $name = $i18n->t("{0} ({1})", [ $name, $info ]);
      }
      $this->writeDebug("Aspect Ratio <comment>{$intl} => {$name}</comment>");
      $translations .= '"' . $intl . '"=>(object)["code"=>"' . $intl . '","name"=>"' . $name . '"],';
    }
    return $translations;
  }

}
