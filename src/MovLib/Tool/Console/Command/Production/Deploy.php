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
  use \MovLib\Data\Image\TraitOptimizeImage {
    optimizeJPG as private traitOptimizeJPG;
    optimizePNG as private traitOptimizePNG;
    optimizeSVG as private traitOptimizeSVG;
  }


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * URI of the repository to clone from.
   *
   * @var string
   */
  protected $origin = "https://github.com/MovLib/www.git";

  /**
   * Absolute path to the assets.
   *
   * @see Deploy::__constructor()
   * @var string
   */
  protected $pathAssets = "/public/asset";

  /**
   * The kernel path.
   *
   * @see Deploy::__construct()
   * @var string
   */
  protected $pathKernel = "/src/MovLib/Kernel.php";

   /**
   * The directory containing the repositories with the source code.
   *
   * @see Deploy::__construct()
   * @var string
   */
  protected $pathRepository = "/usr/local/src/movlib";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("deploy");
    $this->setAliases([ "dp" ]);
    $this->pathRepository = "{$this->pathRepository}/{$_SERVER["REQUEST_TIME"]}";
    foreach ([ "Assets", "Kernel" ] as $path) {
      $path = "path{$path}";
      $this->$path = "{$this->pathRepository}{$this->$path}";
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Generate cache buster strings for all assets.
   *
   * @global \MovLib\Kernel $kernel
   * @staticvar array $extensions
   *   Numeric array containing extensions to be hashed.
   * @return this
   * @throws \RuntimeException
   */
  protected function calculateCacheBusters() {
    global $kernel;
    $this->write("Calculating cache buster hashes...");
    $extensions = [ "css", "js", "jpg", "png", "svg" ];

    // Purge the Kernel's cache buster array.
    $kernel->cacheBusters = [];
    foreach ($extensions as $ext) {
      $kernel->cacheBusters[$ext] = [];
    }

    // Create cache busters for all CSS and JS files.
    $this->globRecursive($this->pathAssets, function ($splFileInfo) {
      global $kernel;
      $kernel->cacheBusters[$splFileInfo->getExtension()][$splFileInfo->getFilename()] = md5_file($splFileInfo->getRealPath());
    }, $extensions);

    // Get the source code of the new Kernel.
    if (($kernelContent = file_get_contents($this->pathKernel)) === false) {
      throw new \RuntimeException("Couldn't read '{$this->pathKernel}'!");
    }

    // Insert new cache busters into new Kernel file.
    foreach ($kernel->cacheBusters as $extension => $cacheBusters) {
      $kernelContent = str_replace("[ /*####{$extension}-cache-buster####*/ ]", var_export($cacheBusters, true), $kernelContent);
    }

    // Write Kernel with new cache busters to disk.
    if (file_put_contents($this->pathKernel, $kernelContent) === false) {
      throw new \RuntimeException("Couldn't write '{$this->pathKernel}'!");
    }

    return $this->write("Successfully calculated cache buster hashes.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * @inheritdoc
   */
  public function configure() {
    $this->setDescription("Deploy GitHub master branch on production server.");
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
    $this->write([
      "This will pull the current master branch from GitHub and the site will be put in maintenance mode during the deployment.",
      "",
      "Be sure to run all tests BEFORE deploying!"
    ], self::MESSAGE_TYPE_ERROR);
    $this->write("");
    if ($this->askConfirmation("Are you sure you want to continue with deployment?", false) === false) {
      $this->write("Aborting deployment...", self::MESSAGE_TYPE_INFO);
      return;
    }

    $this->write("Starting MovLib deployment...");
    try {
      foreach ([
        "prepareRepository",
        "optimizeCSS",
        "optimizeJPG",
        "optimizeJS",
        "optimizePHP",
        "optimizePNG",
        "optimizeSVG",
        "calculateCacheBusters",
        // @todo maintenance mode start
        // @todo migrations (if any)
        // @todo change symbolic link of /var/www
        // @todo maintenance mode end
      ] as $task) {
        $this->$task();
      }
    }
    catch (\Exception $e) {
      //$this->write("Attempting to remove cloned repository...", self::MESSAGE_TYPE_ERROR);
      //sh::executeDisplayOutput("rm -rf {$this->pathRepository}");
      throw $e;
    }
    $this->write("Successfully deployed MovLib.", self::MESSAGE_TYPE_INFO);

    return $options;
  }

  /**
   * Optimize CSS files.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizeCSS() {
    global $kernel;
    $this->write("Optimizing CSS...");

    // Create absolute path to the main CSS file.
    $movlib = "{$this->pathAssets}/css/MovLib.css";

    // Extract which layout files we should include in the final mimized version.
    preg_match_all('/@import "(.+)";/i', file_get_contents($movlib), $matches);

    // We should have at least one.
    if (empty($matches) || empty($matches[1])) {
      throw new \RuntimeException("Couldn't extract CSS file for minification from MovLib.css");
    }

    $fh = fopen($movlib, "w");
    foreach ($matches[1] as $layout) {
      $realPath = "{$this->pathAssets}/css/{$layout}";
      // Optimize the layout CSS file.
      if (sh::execute("csso --input {$realPath} --output {$realPath}", $output) === false) {
        $output = implode("\n", $output);
        throw new \RuntimeException("Couldn't minify '{$realPath}'\n\n{$output}");
      }
      fwrite($fh, $this->removeComments($realPath));

      // Delete the original layout CSS file, otherwise we'd generate cache busters for them later on.
      unlink($realPath);
    }
    fclose($fh);
    $kernel->compress($movlib);

    // Optimize all modules.
    $modules = glob("{$this->pathAssets}/css/module/*.css");
    foreach ($modules as $module) {
      if (sh::execute("csso --input {$module} --output {$module}", $output) === false) {
        $output = implode("\n", $output);
        throw new \RuntimeException("Couldn't minify '{$module}'\n\n{$output}");
      }
      $this->removeComments($module, $module);
      $kernel->compress($module);
    }

    // Update the autoprefixer definitions.
    if (sh::executeDisplayOutput("autoprefixer --update") === false) {
      throw new \RuntimeException("Couldn't update autoprefixer definitions");
    }

    // Autoprefix all CSS files.
    $modules = implode(" ", $modules);
    if (sh::executeDisplayOutput("autoprefixer --browsers 'last 2 versions','Firefox ESR','Explorer 9','BlackBerry 10','Android 4' {$movlib} {$modules}") === false) {
      throw new \RuntimeException("Couldn't autoprefix CSS files");
    }

    return $this->write("Successfully minified all CSS files.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Optimize JPG images.
   *
   * @return this
   */
  protected function optimizeJPG() {
    return $this;
  }

  /**
   * Optimize JavaScript files.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizeJS() {
    global $kernel;

    $this->write("Optimizing JavaScript...");
    $movlib = "{$this->pathAssets}/js/MovLib.js";

    // The Google closure compiler arguments.
    $args = [
      "charset UTF-8",
      "compilation_level ADVANCED_OPTIMIZATIONS",
      "language_in ECMASCRIPT5_STRICT",
      "module_output_path_prefix {$this->pathAssets}/js/build/",
      "module MovLib:1:",
      "js {$movlib}",
    ];

    // Add all modules with a dependency towards our MovLib main file to the arguments.
    $modules = glob("{$this->pathAssets}/js/module/*.js");
    foreach ($modules as $module) {
      $args[] = "module " . basename($module, ".js") . ":1:MovLib:";
      $args[] = "js {$module}";
    }

    // Put the arguments together and let closure compile them.
    if (sh::executeDisplayOutput("java -jar /var/www/bin/closure-compiler.jar --" . implode(" --", $args)) === false) {
      throw new \RuntimeException("Couldn't minify JavaScript");
    }

    // Move all files from the build folder back to their initial position and remove absolutely all comments.
    $this->removeComments($movlib, $movlib);
    $kernel->compress($movlib);
    foreach ($modules as $module) {
      $this->removeComments(str_replace("/module/", "/build/", $module), $module);
      $kernel->compress($module);
    }

    // Remove the build folder, otherwise we'd generate cache busters for these files later on.
    sh::execute("rm -rf {$this->pathAssets}/js/build");

    return $this->write("Successfully optimized JavaScript.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Remove PHP code only used in development.
   *
   * @return this
   * @throws \LogicException
   * @throws \RuntimeException
   */
  protected function optimizePHP() {
    $this->write("Optimizing PHP...");

    $this->globRecursive("{$this->pathRepository}/src/MovLib", function ($splFileInfo) {
      $inDevBlock = false;
      $realPath   = $splFileInfo->getRealPath();
      $tmpPath    = "{$realPath}.tmp";

      if (($fhSource = fopen($realPath, "rb")) === false) {
        throw new \RuntimeException("Couldn't open '{$realPath}' for reading");
      }
      if (($fhTarget = fopen($tmpPath, "wb")) === false) {
        throw new \RuntimeException("Couldn't open '{$tmpPath}' for writing");
      }

      $lineNumber = 0;
      while ($line = fgets($fhSource)) {
        ++$lineNumber;

        if (strpos($line, "// @devStart") !== false) {
          if ($inDevBlock !== false) {
            throw new \RuntimeException("Found unclosed @dev block in '{$realPath}'");
          }
          $inDevBlock = $lineNumber;
          continue;
        }
        if (strpos($line, "// @devEnd") !== false) {
          if ($inDevBlock === false) {
            throw new \RuntimeException("Found unopened @dev block in '{$realPath}'");
          }
          $inDevBlock = false;
          continue;
        }

        // Only write this file to the target file if we aren't within a @dev block.
        if ($inDevBlock === false) {
          // Remove array type checks from method signatures.
          if (strpos($line, " function ") !== false) {
            $line = preg_replace("/function.+\((?:.+( array ).+)*\)/", "", $line);
          }
          fwrite($fhTarget, $line);
        }
      }
      fclose($fhSource);
      fclose($fhTarget);

      // Make sure that we don't end with an unclosed @dev block.
      if ($inDevBlock !== false) {
        throw new \LogicException("Found unclosed @dev block in '{$realPath}' (@devStart was found at {$inDevBlock})");
      }

      // Replace existing file with stripped file.
      rename($tmpPath, $realPath);
    }, "php");

    return $this->write("Successfully optimized PHP.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Optimize PNG images.
   *
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizePNG() {
    $this->write("Optimizing PNG images...");

    $this->globRecursive("{$this->pathAssets}/img", function ($splFileInfo) {
      $this->traitOptimizePNG($splFileInfo->getRealPath());
    }, "png");

    return $this->write("Successfully optimized PNG images.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Optimize SVG images.
   *
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizeSVG() {
    $this->write("Optimizing SVG images...");

    $this->globRecursive("{$this->pathAssets}/img", function ($splFileInfo) {
      $this->traitOptimizeSVG($splFileInfo->getRealPath());
    }, "svg");

    return $this->write("Successfully optimized SVG images.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Prepare the new Repository.
   *
   * Clone the repository, delete git and test files, run composer and bower and fix permissions.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   * @throws \RuntimeException
   */
  protected function prepareRepository() {
    global $kernel;

    $commands = [
      "git clone {$this->origin} {$this->pathRepository}",
      "chown {$kernel->phpUser}:{$kernel->phpGroup} {$this->pathRepository}",
      "cd {$this->pathRepository}",
      "rm -rf .git* test",
      "composer update --no-dev",
      "composer dumpautoload -o",
      "bower update --allow-root",
      "movlib fix-permissions",
    ];

    if (sh::executeDisplayOutput(implode(" && ", $commands)) === false) {
      throw new \RuntimeException("Preparation of repository failed");
    }

    // Remove the makefile from the root directory (too dangerous).
    unlink("{$this->pathRepository}/makefile");

    return $this->write("Successfully prepared repository.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Remove left over comments from minified file.
   *
   * @param string $source
   *   Absolute path to the minified source file.
   * @param boolean|string $target
   *   Either <code>TRUE</code> (default) which will return the <var>$source</var> with stripped comments or a string
   *   containing the absolute path to the target file with stripped comments.
   * @return string|this
   *   Either <var>$source</var> with stripped comments (if <var>$target</var> is set to <code>TRUE</code>, default) or
   *   <var>$this</var> for chaining.
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  protected function removeComments($source, $target = true) {
    // Remove all left over comments, see http://regex101.com/r/uJ2jF2 for explanation of regular expression.
    $stripped = preg_replace("/^\s*\/\*[*!]?\s*$.*^\s*\*\/\s*\n?/mis", "", file_get_contents($source));

    // Return the stripped source if requested.
    if ($target === true) {
      return $stripped;
    }

    // Otherwise make sure that the cally really passed a path.
    if (!is_string($target)) {
      throw new \InvalidArgumentException("\$target must be of type string and an absolute path to a file");
    }

    // Make sure that the target is within our current working repository.
    if (strpos($this->pathRepository, $target) !== false) {
      throw new \InvalidArgumentException("\$target must be within the current working repository: '{$target}'");
    }

    // Make sure that the target directory exists.
    $directory = dirname($target);
    sh::execute("mkdir -p {$directory}");
    if (!is_dir($directory)) {
      throw new \RuntimeException("Couldn't create directory for \$target");
    }

    file_put_contents($target, $stripped);
    return $this;
  }

}
