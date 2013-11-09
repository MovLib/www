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

    // We need to fix the permissions after generating the skeletons, therefor we need elevated privileges.
    $this->checkPrivileges();

    // If we have them, generate the skeletons.
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

    // Every test file has the same path as the source file, the only reside in a different directory. All source files
    // are in "src" and all test files in "test", therefore we have to replace that portion of the part. Every test
    // file ends on Test, this is per convention from PHPUnit, therefor we simply replace the ".php" file extension,
    // prefix it with "Test" and we have the absolute path to the test file.
    $testFile = str_replace([ "/src/", ".php" ], [ "/test/", "Test.php" ], $file);

    // We have to remove the document root and "src" portion and the file extension to get the fully qualified class
    // name and of course we have to replace the directory separator with the PHP namespace separator.
    $class    = strtr(str_replace([ "{$kernel->documentRoot}/src", ".php" ], "", $file), DIRECTORY_SEPARATOR, "\\");

    // Check if we are really dealing with a (abstract) class or a trait, otherwise there's no need for a skeleton.
    if (!class_exists($class) && !trait_exists($class)) {
      return $this;
    }

    // Create a reflection of this particular class, in order to easily gather information about it. We also need the
    // real source code to make absolutely sure that the method is really declared in this class (traits are a real
    // problem because they are reported as declared in the using class by the reflector).
    $reflector = new \ReflectionClass($class);
    $source    = file_get_contents($file);

    // We might have to extend the existing test if we already have a test file.
    if (is_file($testFile)) {
      $this->skeletonExtend($reflector, $class, $source, $testFile);
    }
    // If not, generate a totally new skeleton for this source file.
    else {
      $this->skeletonNew($reflector, $class, $source, $testFile);
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

    // Let's find out if we did anything.
    $doneSomething = false;
    foreach ([ "Deleted", "New", "Extended" ] as $action) {
      if (!empty($this->{"skeletons{$action}"})) {
        $this->write(strtoupper($action), self::MESSAGE_TYPE_INFO);
        $this->write($this->{"skeletons{$action}"});
        $doneSomething = true;
      }
    }

    // If we haven't done anything, tell the client.
    if ($doneSomething === false) {
      $this->write("All tests are up-to-date, nothing was deleted, generated or extended!", self::MESSAGE_TYPE_COMMENT);
    }

    return $this;
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
   * Whether this method should be ignored or not during skeleton generation.
   *
   * @param \ReflectionMethod $method
   *   The method to check.
   * @return boolean
   *   <code>TRUE</code> if this method should be ignored, otherwise <code>FALSE</code>.
   */
  protected function ignoreMethod(\ReflectionMethod $method) {
    return (($docComment = $method->getDocComment()) && strpos($docComment, "@skeletonGeneratorIgnore") !== false);
  }

  /**
   * Extend an existing test.
   *
   * @param \ReflectionClass $reflector
   *   Reflector of the class for which we should extend the test.
   * @param string $class
   *   Full class name, e.g. <code>"\Foo\Bar"</code>.
   * @param string $source
   *   The source code of the class for which we should extend the test.
   * @param string $testFile
   *   Absolute path to the test file.
   * @return this
   */
  protected function skeletonExtend($reflector, $class, $source, $testFile) {
    // Create a reflector for the existing test class.
    $testReflector = new \ReflectionClass("{$class}Test");

    // Go through all existing test methods and store them in an associative array for later comparison.
    $testMethods   = [];
    /* @var $testMethod \ReflectionMethod */
    foreach ($testReflector->getMethods() as $testMethod) {
      if ($testMethod->getDeclaringClass() == $testReflector) {
        $testMethods[] = $testMethod;
      }
    }

    $tests = [];
    /* @var $method \ReflectionMethod */
    foreach ($reflector->getMethods() as $method) {
      if ($this->ignoreMethod($method) === true) {
        continue;
      }
      $methodName = $method->getName();

      // Make absolutely sure that this method is declared in this class. First we simply ask the reflector which covers
      // almost all inheritance scenarios, but we also have to check the source code itself in case this class makes use
      // of a trait, because the reflector will report that the class is declaring the method in this case.
      if ($method->getDeclaringClass() == $reflector && strpos($source, "function {$methodName}") !== false) {
        // We asume that this test isn't implemented yet.
        $testExists = false;

        // Build the test method name, we have to remove all underlines and make the first characters uppercased. Any
        // test method has the format "testMethodName" (this includes tests for magic stuff like "__construct" becomes
        // "testConstruct").
        $methodTestName = ucfirst(ltrim($methodName, "_"));

        // We can't utilize a simple array search because the test method might have additional information appended to
        // its name (e.g. "testMyMethodInvalidInput"). This is why we utilize the strpos() function to check if any
        // test method is declared containing the name of this method.
        foreach ($testMethods as $testMethod) {
          if (strpos($testMethod, $methodTestName) !== false) {
            $testExists = true;
            break;
          }
        }

        // We create a test method skeleton for this method if the above code doesn't find any matching test.
        if ($testExists === false) {
          $tests[] = str_replace([ "{methodName}", "{methodTestName}" ], [ $methodName, $methodTestName ], $this->methodTemplate);
        }
      }
    }

    // Check if we have any new tests that we should extend this test case with.
    if (!empty($tests)) {
      // Snatch the source code of the existing test.
      $existingTestCase = file_get_contents($testFile);

      // Put some space between each test method for readability, according to our coding standards this is a single
      // blank line (plus one line feed for the closing "}" of the test method before).
      $tests = implode("\n\n", $tests);

      // Search for the last occurence of "}", which always marks the end of the class. It's very important to use the
      // multibyte function at this point because ALL our source files are in UTF-8.
      $insertPosition = mb_strrpos($existingTestCase, "}") - 1;

      // Now we insert the new test method exactly before the end of the existing test case and create the new extended
      // test case.
      $extendedTestCase = mb_substr($existingTestCase, 0, $insertPosition) . "\n{$tests}\n\n}\n";

      // Straight forward, override the existing test file with the extended test case code.
      file_put_contents($testFile, $extendedTestCase);

      // Stack this test file for the final report.
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
   * @param string $source
   *   The source code of the class for which we should generate a skeleton.
   * @param string $testFile
   *   Absolute path to the test file.
   * @return this
   */
  protected function skeletonNew($reflector, $class, $source, $testFile) {
    $tests = [];
    /* @var $method \ReflectionMethod */
    foreach ($reflector->getMethods() as $method) {
      if ($this->ignoreMethod($method) === true) {
        continue;
      }
      $methodName = $method->getName();

      if ($method->getDeclaringClass() == $reflector && strpos($source, "function {$methodName}") !== false) {
        $methodTestName = ucfirst(ltrim($methodName, "_"));
        $tests[] = str_replace([ "{methodName}", "{methodTestName}" ], [ $methodName, $methodTestName ], $this->methodTemplate);
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
