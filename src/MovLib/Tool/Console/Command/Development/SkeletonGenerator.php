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
namespace MovLib\Tool\Console\Command\Development;

use \MovLib\Tool\Console\Command\Production\FixPermissions;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate unit test skeletons for one or more classes from the source.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SkeletonGenerator extends \MovLib\Tool\Console\Command\Development\AbstractDevelopmentCommand {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Skeleton template for abstract test case's.
   *
   * @var string
   */
  protected $abstractTemplate;

  /**
   * Skeleton template for class test case's.
   *
   * @var string
   */
  protected $classTemplate;

  /**
   * Skeleton template for test case's methods.
   *
   * @var string
   */
  protected $methodTemplate;

  /**
   * Skeleton template for trait test case's.
   *
   * @var string
   */
  protected $traitTemplate;

  /**
   * Numeric array containing all deleted tests.
   *
   * @var array
   */
  protected $skeletonsDeleted = [];

  /**
   * Numeric array containing extended skeletons.
   *
   * @var array
   */
  protected $skeletonsExtended = [];

  /**
   * Numeric array containing newly generated skeletons.
   *
   * @var array
   */
  protected $skeletonsNew = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("skeleton-generator");
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setDescription("Generate unit test skeletons for one or more classes from the source.");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = parent::execute($input, $output);
    $this->checkPrivileges();
    $this->generateSkeletons();
    return $options;
  }

  /**
   * Generate a new skeleton or extend an existing one.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $file
   *   Absolute path to the source file.
   * @return this
   */
  protected function generateSkeleton($file) {
    global $kernel;
    $testFile = str_replace([ "/src/", ".php" ], [ "/test/", "Test.php" ], $file);
    $class    = strtr(str_replace([ "{$kernel->documentRoot}/src", ".php" ], "", $file), DIRECTORY_SEPARATOR, "\\");
    if (!class_exists($class) && !trait_exists($class)) {
      return $this;
    }
    $reflector = new \ReflectionClass($class);
    if (is_file($testFile)) {
      $this->skeletonExtend($reflector, $class, $testFile);
    }
    else {
      $this->skeletonNew($reflector, $class, $testFile);
    }
    return $this;
  }

  /**
   * Generate all skeletons.
   *
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function generateSkeletons() {
    global $kernel;
    $this->write("Generating unit test skeletons for all files in <info>'{$kernel->documentRoot}/src/'</info> ...");

    // Load template files.
    foreach ([ "abstract", "class", "method", "trait" ] as $tpl) {
      $this->{"{$tpl}Template"} = file_get_contents("{$kernel->documentRoot}/conf/skeleton/{$tpl}.tpl.php");
    }

    // Collect all source files.
    $files = [];
    $this->globRecursive("src/MovLib", function ($realpath) use (&$files) {
      $files[] = $realpath;
    });

    // Generate skeletons for all files.
    if (($c = count($files))) {
      $this->progressStart($c);
      for ($i = 0; $i < $c; ++$i) {
        $this->generateSkeleton($files[$i]);
        $this->progressAdvance();
      }
      $this->progressFinish();

      // Fix the permissions on all generated and extended files.
      (new FixPermissions())->fixPermissions("test");
    }

    // Remove all tests that aren't needed anymore.
    $this->globRecursive("test/MovLib", function ($realpath) {
      if (strpos($realpath, "Test.php") !== false && !is_file(str_replace([ "/test/", "Test.php" ], [ "/src/", ".php" ], $realpath))) {
        unlink($realpath);
        $this->skeletonsDeleted[] = $realpath;
      }
    });

    $this->write("Sekeleton Generator Report:");
    $doneSomething = false;
    foreach ([ "Deleted", "New", "Extended" ] as $action) {
      if (!empty($this->{"skeletons{$action}"})) {
        $this->write(strtoupper($action), self::MESSAGE_TYPE_INFO);
        $this->write($this->{"skeletons{$action}"});
        $doneSomething = true;
      }
    }
    if ($doneSomething === false) {
      $this->write("All tests are up-to-date, nothing was deleted, generated or extended!", self::MESSAGE_TYPE_COMMENT);
    }
  }

  /**
   * Recursive glob that finds all php files in the given directory.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $path
   *   Relative path to glob within the document root without leading slash.
   * @param callable $callback
   *   Callable to call on each iteration.
   */
  protected function globRecursive($path, $callback) {
    global $kernel;
    /* @var $splFileInfo \SplFileInfo */
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator("{$kernel->documentRoot}/{$path}"), \RecursiveIteratorIterator::SELF_FIRST) as $splFileInfo) {
      $realpath = $splFileInfo->getRealPath();
      if ($splFileInfo->isFile() && strpos($splFileInfo->getBasename(), ".php") !== false) {
        call_user_func($callback, $realpath, $splFileInfo);
      }
    }
  }

  /**
   * Extend an existing test.
   *
   * @param \ReflectionClass $reflector
   *   Reflector of the class for which we should generate a skeleton.
   * @param string $class
   *   Full class name, e.g. <code>"\Foo\Bar"</code>.
   * @param string $testFile
   *   Absolute path to the test file.
   * @return this
   */
  protected function skeletonExtend($reflector, $class, $testFile) {
    $testReflector = new \ReflectionClass("{$class}Test");
    $testMethods   = [];
    /* @var $testMethod \Reflectionmethod */
    foreach ($testReflector->getMethods() as $testMethod) {
      if ($testMethod->getDeclaringClass() === $testReflector) {
        $testMethods[] = $testMethod;
      }
    }

    $classContent = \ReflectionClass::export($class, true);
    $tests = [];
    /* @var $method \ReflectionMethod */
    foreach ($reflector->getMethods() as $method) {
      if ($method->getDeclaringClass() === $reflector && strpos($classContent, $method->getName()) !== false) {
        $testExists     = false;
        $methodName     = $method->getName();
        $methodTestName = ucfirst(ltrim($methodName, "_"));
        foreach ($testMethods as $testMethod) {
          if (strpos($testMethod, $methodTestName) !== false) {
            $testExists = true;
          }
        }
        if ($testExists === false) {
          $tests[] = str_replace(
            [ "{methodName}", "{methodTestName}" ],
            [ $methodName, $methodTestName ],
            $this->methodTemplate
          );
        }
      }
    }

    if (!empty($tests)) {
      $existingTest = file_get_contents($testFile);
      $insertPosition = mb_strrpos($existingTest, "}") - 1;
      file_put_contents($testFile, mb_substr($existingTest, 0, $insertPosition) . "\n" . implode("\n\n", $tests) . "\n\n}\n");
      $this->skeletonsExtended[] = $testFile;
    }

    return $this;
  }

  /**
   * Generates a new skeleton.
   *
   * @param \ReflectionClass $reflector
   *   Reflector of the class for which we should generate a skeleton.
   * @param string $class
   *   Full class name, e.g. <code>"\Foo\Bar"</code>.
   * @param string $testFile
   *   Absolute path to the test file.
   * @return this
   */
  protected function skeletonNew($reflector, $class, $testFile) {
    $classContent = \ReflectionClass::export($class, true);
    $tests = [];
    /* @var $method \ReflectionMethod */
    foreach ($reflector->getMethods() as $method) {
      if ($method->getDeclaringClass() === $reflector && strpos($classContent, $method->getName()) !== false) {
        $methodName     = $method->getName();
        $methodTestName = ucfirst(ltrim($methodName, "_"));
        $tests[]        = str_replace(
          [ "{methodName}", "{methodTestName}" ],
          [ $methodName, $methodTestName ],
          $this->methodTemplate
        );
      }
    }

    if (empty($tests)) {
      return $this;
    }

    $directory = dirname($testFile);
    if (!is_dir($directory)) {
      mkdir($directory, 0770, true);
    }

    if ($reflector->isAbstract()) {
      $template = $this->abstractTemplate;
    }
    elseif ($reflector->isTrait()) {
      $template = $this->traitTemplate;
    }
    else {
      $template = $this->classTemplate;
    }

    $className = $reflector->getShortName();
    file_put_contents($testFile, str_replace(
      [ "{class}", "{classEscaped}", "{className}", "{classPropertyName}", "{namespace}", "{tests}" ],
      [ $class, str_replace("\\", "\\\\", $class), $className, lcfirst($className), $reflector->getNamespaceName(), implode("\n\n", $tests) ],
      $template
    ));

    $this->skeletonsNew[] = $testFile;
    return $this;
  }

}
