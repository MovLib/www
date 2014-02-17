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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Uri of the Repository to clone from.
   *
   * @var string
   */
  protected $origin = "https://github.com/MovLib/www.git";

  /**
   * The directory containing CSS files to minify.
   *
   * @see Deploy::__construct()
   * @var string
   */
  protected $pathCss = "public/asset/css";

  /**
   * The directory containing images.
   *
   * @see Deploy::__construct()
   * @var string
   */
  protected $pathImg = "public/asset/img";

  /**
   * The directory containing Javascript files to minify.
   *
   * @see Deploy::__construct()
   * @var string
   */
  protected $pathJs = "public/asset/js";

  /**
   * The kernel path.
   *
   * @see Deploy::__construct()
   * @var string
   */
  protected $pathKernel = "src/MovLib/Kernel.php";

   /**
   * The directory containing the source code.
   *
   * @see Deploy::__construct()
   * @var string
   */
  protected $pathClone = "/usr/local/src/movlib";

  /**
   * The directory containing the source code.
   *
   * @see Deploy::__construct()
   * @var string
   */
  protected $pathSrc = "src/MovLib";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("deploy");
    $this->setAliases([ "dp" ]);

    $this->pathClone  = "{$this->pathClone}/" . time();
    $this->pathCss    = "{$this->pathClone}/{$this->pathCss}";
    $this->pathImg    = "{$this->pathClone}/{$this->pathImg}";
    $this->pathJs     = "{$this->pathClone}/{$this->pathJs}";
    $this->pathKernel = "{$this->pathClone}/{$this->pathKernel}";
    $this->pathSrc    = "{$this->pathClone}/{$this->pathSrc}";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods

 /**
   * Creates cache buster strings for the various assets.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   * @throws \RuntimeException
   */
  protected function cacheBuster() {
    global $kernel;
    $kernel->cacheBusters = [ "css" => [], "js" => [], "jpg" => [], "png" => [], "svg" => [] ];

    foreach (["css", "js"] as $extension) {
      $this->globRecursive("{$this->pathClone}/public/asset/{$extension}/module", function($realpath, $splFileInfo, $extension) {
        global $kernel;
        $kernel->cacheBusters[$extension][$splFileInfo->getFilename()] = md5_file($realpath);
      }, $extension);
    }
    $kernel->cacheBusters["css"]["MovLib.css"] = md5_file("{$this->pathCss}/MovLib.css");
    $kernel->cacheBusters["js"]["MovLib.js"] = md5_file("{$this->pathJs}/MovLib.js");

    foreach (["jpg", "png", "svg"] as $extension) {
      $this->globRecursive("{$this->pathClone}/public/asset/img", function($realpath, $splFileInfo, $extension) {
        global $kernel;
        if (substr($realpath, -7) != ".svg.gz") {
          $kernel->cacheBusters[$extension][$splFileInfo->getFilename()] = md5_file($realpath);
        }
      }, $extension);
    }

    if (($kernelContent = file_get_contents($this->pathKernel)) === false) {
      throw new \RuntimeException("Couldn't read '{$this->pathKernel}'!");
    }

    foreach ($kernel->cacheBusters as $extension => $cacheBusters) {
      $kernelContent = str_replace("[ /*####{$extension}-cache-buster####*/ ]", var_export($cacheBusters, true), $kernelContent);
    }

    if (file_put_contents($this->pathKernel, $kernelContent) === false) {
      throw new \RuntimeException("Couldn't write '{$this->pathKernel}'!");
    }

    return $this->write("Successfully initialized cache busters.", self::MESSAGE_TYPE_INFO);
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
        if (sh::execute("gzip -9 -c {$realpath} > {$outputFile}") === false) {
          throw new \RuntimeException("Couldn't compress '{$realpath}'!");
        }
      }
    }, "svg");

    return $this->write("Successfully compressed SVG images.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * @inheritdoc
   */
  public function configure() {
    $this->setDescription("Perform all deployment related tasks or only specific tasks via options.");
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
    $this->checkPrivileges();
    // Array containing the names of all tasks in right order that should be executed.
    $tasks = [
      "prepareRepository",
      "optimizeCode",
      "minifyCss",
      "minifyJs",
      "compressImages",
      "cacheBuster",
    ];

    $this->write("\nStart deploying of MovLib\n", self::MESSAGE_TYPE_INFO);

    foreach ($tasks as $task) {
      $this->$task();
    }
    return $this->write("\nSuccessfully deployed MovLib.\n", self::MESSAGE_TYPE_INFO);
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

    if ((file_put_contents("{$this->pathCss}/MovLib.css", $minifiedCss)) === false) {
      throw new \RuntimeException("Couldn't write '{$this->pathCss}/MovLib.css'!");
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
        rename($outputFile, $realpath);
      }
    }, "js");

    return $this->write("Successfully minified javascript.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Optimize PHP code.
   *
   * Remove PHP code only used in development.
   *
   * @return this
   * @throws \RuntimeException
   */
  private function optimizeCode() {
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
        rename("{$realpath}.tmp", $realpath);
      }
      else {
        unlink("{$realpath}.tmp");
      }
    }, "php");

    return $this->write("Successfully removed PHP code only used in development.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Prepare the new Repository.
   *
   * Clone the repository, delete git and test files, run composer and bower and set right permissions.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   * @throws \RuntimeException
   */
  private function prepareRepository() {
    global $kernel;

    if (sh::executeDisplayOutput("git clone {$this->origin} {$this->pathClone}") === false) {
      throw new \RuntimeException("Couldn't clone into '{$this->pathClone}'!");
    }
    else {
      $this->write("Successfully cloned repository into '{$this->pathClone}'.", self::MESSAGE_TYPE_INFO);
    }

    if (sh::executeDisplayOutput("cd {$this->pathClone} && rm -rf .git* test") === false) {
      throw new \RuntimeException("Couldn't delete git and test files!");
    }

    if (sh::executeDisplayOutput("cd {$this->pathClone} && composer update --no-dev") === false) {
      throw new \RuntimeException("Couldn't run composer update!");
    }
    else {
      $this->write("Successfully run composer update.", self::MESSAGE_TYPE_INFO);
    }

    if (sh::executeDisplayOutput("cd {$this->pathClone} && bower update --allow-root") === false) {
      throw new \RuntimeException("Couldn't run bower update!");
    }
    else {
      $this->write("Successfully run bower update.", self::MESSAGE_TYPE_INFO);
    }

    if (sh::executeDisplayOutput("chown -R {$kernel->phpUser}:{$kernel->phpGroup} {$this->pathClone}") === false) {
      throw new \RuntimeException("Couldn't change permissions of '{$this->pathClone}'!");
    }

    return $this->write("Successfully prepared repository.", self::MESSAGE_TYPE_INFO);
  }

}
