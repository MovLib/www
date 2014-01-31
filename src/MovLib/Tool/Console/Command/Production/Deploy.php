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

use \MovLib\Data\UnixShell as sh;
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


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Command option to minify css.
   *
   * @var string
   */
  const OPTION_CSS = "minifyCss";

  /**
   * Command option to minify javascript.
   *
   * @var string
   */
  const OPTION_JS = "minifyJs";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The directory containing CSS files to minify.
   *
   * @see Database::__construct()
   * @var string
   */
  protected $pathCss = "/public/asset/css";

  /**
   * The directory containing Javascript files to minify.
   *
   * @see Database::__construct()
   * @var string
   */
  protected $pathJs = "/public/asset/js";


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
    $this->setDescription("Perform all deployment related tasks or only specific tasks via options.");
    $this->addOption(self::OPTION_JS, "j", InputOption::VALUE_NONE, "Minifies all Javascript files");
    $this->addOption(self::OPTION_CSS, "c", InputOption::VALUE_NONE, "Minifies all CSS files.");
  }
  /**
   * Run all deployment related tasks.
   *
   * @return this
   */
  private function deploy() {
    // Array containing the names of all tasks that should be executed.
    $tasks = [
      self::OPTION_CSS,
      self::OPTION_JS,
    ];

    $this->write("Start deploying of MovLib", self::MESSAGE_TYPE_INFO)->progressStart(count($tasks));

    foreach ($tasks as $task) {
      $this->$task()->progressAdvance();
    }
    return $this->progressFinish()->write("Successfully deployed MovLib.", self::MESSAGE_TYPE_INFO);
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
    $all = true;
    foreach ([ self::OPTION_CSS, self::OPTION_JS ] as $option) {
      if ($options[$option]) {
        $this->$option();
        $all = false;
      }
    }
    if ($all === true) {
      $this->deploy();
    }
    return $options;
  }

  /**
   * Creates a minified version of all our css.
   *
   * @return this
   * @throws \RuntimeException
   */
  private function minifyCss() {
    $minifiedCss = "";

    if (($movLibCss = file_get_contents("{$this->pathCss}/MovLib.css")) === false) {
      throw new \RuntimeException("Couldn't read '{$this->pathCss}/MovLib.css'!");
    }

    $movLibCss = explode("\n", $movLibCss);
    $c = count($movLibCss);
    for ($i = 0; $i < $c; ++$i) {
      if (substr($movLibCss[$i], 0, 7) == "@import") {
        $file = rtrim(trim($movLibCss[$i], "@import \""), "\";");
        if (sh::execute("csso {$this->pathCss}/{$file}", $output) === true) {
          $minifiedCss .= substr(array_pop($output), 3);
        }
        else {
          throw new \RuntimeException("Couldn't minify '{$this->pathCss}/{$file}'!");
        }
      }
    }

    if ((file_put_contents("{$this->pathCss}/MovLib.min.css", $minifiedCss)) === false) {
      throw new \RuntimeException("Couldn't write '{$this->pathCss}/MovLib.min.css'!");
    }

    return $this->write("Successfully minified css.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Creates a minified version of our javascript.
   *
   * @return this
   * @throws \RuntimeException
   */
  private function minifyJs() {
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->pathJs)) as $file) {
      if (substr($file, -3) == ".js" && substr($file, -7) != ".min.js") {
        $outputFile = rtrim("$file", ".js") . ".min.js";
        if (sh::execute("uglifyjs {$file} -o {$outputFile}") === false) {
          throw new \RuntimeException("Couldn't minify '{$file}'!");
        }
      }
    }

    return $this->write("Successfully minified javascript.", self::MESSAGE_TYPE_INFO);
  }

}
