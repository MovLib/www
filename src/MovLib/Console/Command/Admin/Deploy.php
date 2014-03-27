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
namespace MovLib\Console\Command\Admin;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Perform various deployment related tasks.
 *
 * The various commands that are involved during deployment aren't available individually because they highly depend
 * on each other. For instance, each and every command depends on the initial cloning of the repository at a new and
 * unique location on the server. Of course it would be possible to work within the current document root, but it simply
 * doesn't make much sense (at least we had the impression while writing this class).
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Deploy extends \MovLib\Console\Command\AbstractCommand {
  use \MovLib\Data\Image\TraitOptimizeImage;


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Google closure compiler identifier.
   *
   * @see Deploy::googleClosureCompiler()
   * @var integer
   */
  const GOOGLE_CLOSURE_COMPILER = 0;

  /**
   * UglifyJS compiler identifier.
   *
   * @see Deploy::uglifyJS()
   * @var itneger
   */
  const UGLIFYJS = 1;


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
  protected $pathPublic = "/public";

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

  /**
   * Absolute path to the persistent uploads directory.
   *
   * Please note that this directory must reside outside of the deployed repository. Otherwise all uploaded files would
   * be deleted on upgrade.
   *
   * @var string
   */
  protected $pathUploads = "/var/lib/uploads";


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Generate cache buster strings for all assets.
   *
   * @staticvar array $extensions
   *   Numeric array containing extensions to be hashed.
   * @return this
   * @throws \RuntimeException
   */
  protected function calculateCacheBusters() {
    $this->write("Calculating cache buster hashes...");

    // Purge the Kernel's cache buster array.
    $kernel->cacheBusters = [];
    foreach ([ "css", "js", "jpg", "png", "svg" ] as $ext) {
      $kernel->cacheBusters[$ext] = [];
    }

    // Create cache busters for all CSS and JS files.
    $this->globRecursive("{$this->pathPublic}/asset", function ($splFileInfo) {
      $realPath  = $splFileInfo->getRealPath();
      $extension = $splFileInfo->getExtension();
      $basename  = substr($splFileInfo->getBasename($extension), 0, -1);
      $kernel->cacheBusters[$extension][$basename] = md5_file($realPath);
      $this->write("{$realPath} --> {$kernel->cacheBusters[$extension][$basename]}");
    }, [ "css", "js" ]);

    // Create cache busters for all images.
    $this->globRecursive("{$this->pathPublic}/asset/img", function ($splFileInfo) {
      $realPath  = $splFileInfo->getRealPath();
      $extension = $splFileInfo->getExtension();
      $basename  = str_replace("{$this->pathPublic}/asset/img/", "", substr($realPath, 0, -4));
      $kernel->cacheBusters[$extension][$basename] = md5_file($realPath);
      $this->write("{$realPath} --> {$kernel->cacheBusters[$extension][$basename]}");
    }, [ "jpg", "png", "svg" ]);

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
   * Change repository.
   *
   * @return this
   * @throws \RuntimeException
   */
  protected function changeRepository() {
    $this->write("Attempting to find symbolic link...");
    $this->shellExecute("find / -not -path '/proc*' -type l -xtype d -lname '{$kernel->documentRoot}'", $output);
    if (empty($output)) {
      throw new \RuntimeException("Couldn't find any existing symbolic link to the current document root '{$kernel->documentRoot}'");
      // @todo Might be a new installation, what should we do?
    }
    elseif (count($output) > 1) {
      throw new \RuntimeException("Multiple symbolic links found\n\n" . implode("\n", $output));
    }
    $sym = reset($output);

    // We can't create a new symbolic link as long as the old one exists.
    if (unlink($sym) === false) {
      throw new \RuntimeException("Couldn't delete existing symbolic link '{$sym}'");
    }

    // Try to create the new symbolic link.
    $this->fsSymlink($this->pathRepository, $sym);

    // Create symbolic links to upload folders.
    foreach ([ "private", "public" ] as $directory) {
      $this->fsSymlink("{$this->pathUploads}/{$directory}", "{$this->pathRepository}/{$directory}/upload");
    }

    // Looks good :)
    $this->write("Successfully changed the repository.", self::MESSAGE_TYPE_INFO);
    $this->write([
      "Please note that the old repository wasn't deleted and won't be deleted, in case you need to switch back very quickly.",
      "",
      "If you have to, use the following command: `rm -f {$sym} && ln -s {$kernel->documentRoot} {$sym}`",
    ], self::MESSAGE_TYPE_QUESTION);

    return $this;
  }

  /**
   * Compress all assets.
   *
   * Note that this method is silent because it's also used during production.
   *
   * @return this
   * @throws \RuntimeException
   */
  protected function compressAssets() {
    $this->write("Compressing all assets...");
    $this->globRecursive($this->pathPublic, function ($splFileInfo) {
      $realPath = $splFileInfo->getRealPath();
      $this->write("Compressing {$realPath}");
      FileSystem::compress($realPath);
    }, [ "css", "eot", "ico", "js", "ttf", "txt", "xml" ]);
    return $this->write("Successfully compressed all assets.");
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setName("deploy");
    $this->setDescription("Deploy GitHub master branch on production server.");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // Only root (sudo) can deploy!
    $this->checkPrivileges();

    $this->pathRepository = "{$this->pathRepository}/{$_SERVER["REQUEST_TIME"]}";
    foreach ([ "Public", "Kernel" ] as $path) {
      $path = "path{$path}";
      $this->$path = "{$this->pathRepository}{$this->$path}";
    }

    // Just making sure...
    $this->write(""); // Space things out or it might look off in some edge cases.
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

    // Let's start deployment...
    $this->write("");
    $this->write([ "Starting MovLib deployment..." ], self::MESSAGE_TYPE_INFO);
    $this->write("");
    try {
      foreach ([
        "prepareRepository",
        "optimizeCSS",
        "optimizeImages",
        "optimizeJS",
        "optimizePHP",
        "optimizeXML",
        "compressAssets",
        "calculateCacheBusters",
        // @todo maintenance mode start
        // @todo migrations (if any)
        "changeRepository",
        // @todo maintenance mode end
      ] as $task) {
        $this->$task();
      }
    }
    catch (\Exception $e) {
      $this->write(""); // Space things out or it might look off in some edge cases.
      $this->write([
        "Something went terribly wrong, see the exception message that follows.",
        "Attempting to remove cloned repository...",
        "",
        "Run `movlib deploy` again (if you want to give it another shot)!",
      ], self::MESSAGE_TYPE_ERROR);
      $this->shellExecuteDisplayOutput("rm -rf {$this->pathRepository}");
      throw $e;
    }
    $this->write("Successfully deployed MovLib.", self::MESSAGE_TYPE_INFO);

    return 0;
  }

  /**
   * Optimize JavaScript with Google's closure compiler.
   *
   * Google's closure compiler has great results with the <code>ADVANCED_OPTIMIZATIONS</code> mode, but it tends to
   * break a lot of code and ensuring that it doesn't delete almost all methods of a prototype is a pain in the ass.
   * There are more known issues with it, don't say you haven't been warned.
   *
   * PS: This will also take much more time to compile compared to uglifyjs.
   *
   * @see Deploy::uglifyJS()
   * @return this
   * @throws \RuntimeException
   */
  protected function googleClosureCompiler() {
    $movlib  = "{$this->pathPublic}/asset/js/MovLib.js";
    $closure = "java -jar /var/www/bin/closure-compiler.jar --";

    // The Google closure default compiler arguments.
    $args = [
      "charset UTF-8",
      "compilation_level ADVANCED_OPTIMIZATIONS",
      "language_in ECMASCRIPT5_STRICT",
    ];

    // Optimize all vendor supplied JavaScript files.
    $bowerClosure = $closure . implode(" --", $args);
    $this->globRecursive("{$this->pathPublic}/bower", function ($splFileInfo) use ($bowerClosure) {
      $realPath = $splFileInfo->getRealPath();

      // Remove already minified JavaScript files, we have no need for them.
      if (strpos($realPath, ".min.js") !== false) {
        unlink($realPath);
        return;
      }

      $this->shellExecuteDisplayOutput("{$bowerClosure} --js {$realPath} --js_output_file {$realPath}");
      $this->removeComments($realPath);
    }, "js");

    // Now we add the MovLib specific modules together.
    $args[] = "module_output_path_prefix {$this->pathPublic}/asset/js/build/";
    $args[] = "module MovLib:1:";
    $args[] = "js {$movlib}";

    // Add all modules with a dependency towards our MovLib main file to the arguments.
    $modules = glob("{$this->pathPublic}/asset/js/module/*.js") + glob("{$this->pathPublic}/asset/js/poly/*.js");
    foreach ($modules as $module) {
      $args[] = "module " . basename($module, ".js") . ":1:MovLib:";
      $args[] = "js {$module}";
    }

    // Put the arguments together and let closure compile them.
    $this->shellExecuteDisplayOutput($closure . implode(" --", $args));

    // Move all files from the build folder back to their initial position and remove absolutely all comments.
    $this->removeComments($movlib, $movlib);
    foreach ($modules as $module) {
      $this->removeComments(str_replace("/module/", "/build/", $module), $module);
    }

    // Remove the build folder, otherwise we'd generate cache busters for these files later on.
    $this->shellExecute("rm -rf {$this->pathPublic}/asset/js/build");

    return $this;
  }

  /**
   * Optimize CSS files.
   *
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizeCSS() {
    $this->write("Optimizing CSS...");

    // Create absolute path to the main CSS file.
    $movlib = "{$this->pathPublic}/asset/css/MovLib.css";

    // Extract which layout files we should include in the final mimized version.
    preg_match_all('/@import "(.+)";/i', file_get_contents($movlib), $matches);

    // We should have at least one.
    if (empty($matches) || empty($matches[1])) {
      throw new \RuntimeException("Couldn't extract CSS file for minification from MovLib.css");
    }

    $fh = fopen($movlib, "w");
    foreach ($matches[1] as $layout) {
      $realPath = "{$this->pathPublic}/asset/css/{$layout}";
      $this->write("Optimizing {$realPath}");
      $this->shellExecute("csso --input {$realPath} --output {$realPath}", $output);
      fwrite($fh, $this->removeComments($realPath));

      // Delete the original layout CSS file, otherwise we'd generate cache busters for them later on.
      unlink($realPath);
    }
    fclose($fh);

    // Optimize all modules.
    $modules = glob("{$this->pathPublic}/asset/css/module/*.css");
    foreach ($modules as $module) {
      $this->write("Optimizing {$module}");
      $this->shellExecute("csso --input {$module} --output {$module}", $output);
      $this->removeComments($module, $module);
    }

    // Update the autoprefixer definitions.
    $this->write("Updating autoprefixer definitions...");
    $this->shellExecuteDisplayOutput("autoprefixer --update");

    // Autoprefix all CSS files.
    $browsers = "'last 2 versions','Firefox ESR','Explorer 9','BlackBerry 10','Android 4'";
    $this->write("CSS autoprefixing for browser definition: {$browsers}");
    $modules  = implode(" ", $modules);
    $this->shellExecuteDisplayOutput("autoprefixer --browsers {$browsers} {$movlib} {$modules}");

    return $this->write("Successfully minified all CSS files.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Optimize all images.
   *
   * @return this
   */
  protected function optimizeImages() {
    $optimize = function ($extension) {
      $this->write("Optimizing {$extension} images...");
      $this->globRecursive($this->pathPublic, function ($splFileInfo) use ($extension) {
        $realPath = $splFileInfo->getRealPath();
        $this->write("Optimzing {$realPath}");
        $this->{"optimize{$extension}"}($realPath);
      }, $extension);
      $this->write("Successfully optimized {$extension} images.", self::MESSAGE_TYPE_INFO);
    };

    // @todo Optimize JPG images
    //$optimize("jpg");
    $optimize("png");
    $optimize("svg");
    return $this;
  }

  /**
   * Optimize JavaScript files.
   *
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizeJS($compiler = self::UGLIFYJS) {
    $this->write("Optimizing JavaScript...");
    if ($compiler === self::GOOGLE_CLOSURE_COMPILER) {
      $this->write("Using Google's closure compiler for JavaScript optimization...");
      $this->write("While generating great results, it has bad performane and tends to break code. You have been warned!");
      $this->googleClosureCompiler();
    }
    elseif ($compiler === self::UGLIFYJS) {
      $this->write("Using uglifyjs compiler for JavaScript optimization...");
      $this->uglifyJS();
    }
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
    $this->write("Optimizing PHP files...");

    $this->globRecursive("{$this->pathRepository}/src/MovLib", function ($splFileInfo) {
      $realPath = $splFileInfo->getRealPath();

      // No need to optimize any file within the Tool namespace!
      if (strpos($realPath, "/src/MovLib/Tool") !== false) {
        $this->write("Skipping {$realPath}", self::MESSAGE_TYPE_COMMENT);
        return;
      }

      $this->write("Optimizing {$realPath}");
      $inDevBlock = false;
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
            throw new \RuntimeException("Found unclosed @dev block in '{$realPath}' (@devStart was found at {$inDevBlock})");
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
          if (strpos($line, " function ") !== false && strpos($line, " array ") !== false) {
            $line = preg_replace("/( )?array /", "$1", $line);
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

    return $this->write("Successfully optimized PHP files.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Optimize XML files.
   *
   * @return this
   */
  protected function optimizeXML() {
    $this->write("Optimizing XML files...");
    $this->globRecursive("{$this->pathPublic}", function ($splFileInfo) {
      $realPath = $splFileInfo->getRealPath();
      // Remove all left over comments, see http://regex101.com/r/oO9nX3 for explanation of regular expression.
      $stripped = preg_replace("/\<![ \r\n\t]*(--([^\-]|[\r\n]|-[^\-])*--[ \r\n\t]*)\>/", "", file_get_contents($realPath));
      file_put_contents($realPath, trim(preg_replace('/>\s{0,}</', '><', $stripped)));
    }, "xml");

    return $this->write("Successfully optimized XML files.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Prepare the new Repository.
   *
   * Clone the repository, delete git and test files, run composer and bower and fix permissions.
   *
   * @return this
   * @throws \RuntimeException
   */
  protected function prepareRepository() {
    $commands = [
      "git clone {$this->origin} {$this->pathRepository}",
      "chown {$kernel->phpUser}:{$kernel->phpGroup} {$this->pathRepository}",
      "cd {$this->pathRepository}",
      "rm -rf .git* test",
      "composer update --no-dev",
      "composer dumpautoload -o",
      "bower update --allow-root",
      "php {$this->pathRepository}/bin/movlib.php fix-permissions {$this->pathRepository}",
    ];

    $this->shellExecuteDisplayOutput(implode(" && ", $commands));
    $this->write("Removing makefile from root directory (too dangerous).", self::MESSAGE_TYPE_COMMENT);
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
    exec("mkdir -p {$directory}");
    if (!is_dir($directory)) {
      throw new \RuntimeException("Couldn't create directory for \$target");
    }

    file_put_contents($target, $stripped);
    return $this;
  }

  /**
   * Optimize JavaScript with uglifyjs.
   *
   * @see Deploy::googleClosureCompiler()
   * @return this
   * @throws \RuntimeException
   */
  protected function uglifyJS() {
    return $this->globRecursive($this->pathPublic, function ($splFileInfo) {
      $realPath = $splFileInfo->getRealPath();

      // Remove already minified JavaScript files, we have no need for them.
      if (strpos($realPath, ".min.js") !== false) {
        unlink($realPath);
        return;
      }

      $this->write("Optimizing {$realPath}...");
      $this->shellExecute("uglifyjs --compress --mangle --output {$realPath} --screw-ie8 {$realPath}");
    }, "js");
  }

}
