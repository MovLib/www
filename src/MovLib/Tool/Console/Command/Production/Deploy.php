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
   * Command option to remove PHP code only used in development.
   *
   * @var string
   */
  const OPTION_DEV = "removeDevCode";

  /**
   * Command option to compress svg image files.
   *
   * @var string
   */
  const OPTION_IMG = "compressImages";

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
  protected $pathCss = "public/asset/css";

  /**
   * The directory containing images.
   *
   * @see Database::__construct()
   * @var string
   */
  protected $pathImg = "public/asset/img";

  /**
   * The directory containing Javascript files to minify.
   *
   * @see Database::__construct()
   * @var string
   */
  protected $pathJs = "public/asset/js";

  /**
   * The directory containing the source code.
   *
   * @see Database::__construct()
   * @var string
   */
  protected $pathSrc = "src/MovLib";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function __construct() {
    global $kernel;
    parent::__construct("deploy");
    $this->setAliases([ "dp" ]);
    $this->pathCss = "{$kernel->documentRoot}/{$this->pathCss}";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function configure() {
    $this->setDescription("Perform all deployment related tasks or only specific tasks via options.");
    $this->addOption(self::OPTION_JS, "j", InputOption::VALUE_NONE, "Minifies all Javascript files");
    $this->addOption(self::OPTION_CSS, "c", InputOption::VALUE_NONE, "Minifies all CSS files.");
    $this->addOption(self::OPTION_IMG, "i", InputOption::VALUE_NONE, "Compress all SVG images.");
    $this->addOption(self::OPTION_DEV, "d", InputOption::VALUE_NONE, "Removes PHP code only used in development");
  }

  /**
   * Compresses all SVG images for NginX.
   *
   * @return this
   * @throws \RuntimeException
   */
  private function compressImages() {
    $this->globRecursive($this->pathImg, function($realpath) {
      if (substr($realpath, -7) != ".svg.gz") {
        $outputFile = $realpath . ".gz";
        if (sh::execute("gzip -c {$realpath} > {$outputFile}") === false) {
          throw new \RuntimeException("Couldn't compress '{$realpath}'!");
        }
      }
    }, "svg");

    return $this->write("Successfully compressed SVG images.", self::MESSAGE_TYPE_INFO);
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
      self::OPTION_DEV,
      self::OPTION_IMG,
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
    foreach ([ self::OPTION_CSS, self::OPTION_JS, self::OPTION_DEV, self::OPTION_IMG ] as $option) {
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
    $fhr = fopen("{$this->pathCss}/MovLib.css", "r");
    while ($line = fgets($fhr)) {
      if (substr($line, 0, 7) == "@import") {
        $file = rtrim(trim($line, "@import \""), "\";\n");
        if (sh::execute("csso {$this->pathCss}/{$file}", $output) === true) {
          $minifiedCss .= substr(array_pop($output), 3);
        }
        else {
          throw new \RuntimeException("Couldn't minify '{$this->pathCss}/{$file}'!");
        }
      }
    }
    fclose($fhr);

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
    $this->globRecursive($this->pathJs, function($realpath) {
      if (substr($realpath, -7) != ".min.js") {
        $outputFile = rtrim($realpath, ".js") . ".min.js";
        if (sh::execute("uglifyjs {$realpath} -o {$outputFile}") === false) {
          throw new \RuntimeException("Couldn't minify '{$realpath}'!");
        }
      }
    }, "js");

    return $this->write("Successfully minified javascript.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Remove PHP code only used in development.
   *
   * @return this
   * @throws \RuntimeException
   */
  private function removeDevCode() {
    $this->globRecursive($this->pathSrc, function($realpath) {
      $foundDev   = false;
      $inDevBlock = false;

      $fho = fopen($realpath, "r");
      $fhc = fopen("{$realpath}.tmp", "wb");
      while ($line = fgets($fho)) {
        if (strpos($line, "// @devStart") !== false) {
          $foundDev   = true;
          $inDevBlock = true;
          continue;
        }
        if (strpos($line, "// @devEnd") !== false) {
          $inDevBlock = false;
          continue;
        }
        if ($inDevBlock === false) {
          fwrite($fhc, $line);
        }
      }
      fclose($fho);
      fclose($fhc);

      if ($foundDev === true) {
        //rename("{$realpath}.tmp", $realpath);
      }
      else {
        unlink("{$realpath}.tmp");
      }
    }, "php");

    return $this->write("Successfully removed PHP code only used in development.", self::MESSAGE_TYPE_INFO);
  }

}
