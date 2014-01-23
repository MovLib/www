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
namespace MovLib\Tool\Console\Command\Production;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Perform various deployment related tasks.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Deploy extends \MovLib\Tool\Console\Command\AbstractCommand {

  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The directory containing CSS files to minify.
   *
   * @see Database::__construct()
   * @var string
   */
  protected $pathCss = "/public/assetss/css";

  /**
   * The directory containing Javascript files to minify.
   *
   * @see Database::__construct()
   * @var string
   */
  protected $pathJs = "/public/assets/js";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function __construct() {
    global $kernel;
    parent::__construct("deploy");
    $this->setAliases([ "dp" ]);
    $this->pathCss = "{$kernel->documentRoot}{$this->pathCss}";
    $this->pathJs  = "{$kernel->documentRoot}{$this->pathJs}";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function configure() {
    $this->setDescription("Perform various deployment related tasks.");
    $this->addInputOption("js_minify", InputOption::VALUE_NONE, "Minifies all Javascript files");
    $this->addInputOption("css_minify", InputOption::VALUE_NONE, "Minifies all CSS files.");
  }

  /**
   * @inheritdoc
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   {@inheritdoc}
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   {@inheritdoc}
   * @return array
   *   The passed options.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = parent::execute($input, $output);
    throw new \RuntimeException("Not implemented yet!");
    return $options;
  }

}
