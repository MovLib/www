<?php

/**
 * Main PHP script serving all page requests within the MovLib application.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright (c) 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/ movlib.org
 * @since 0.0.1
 */

/**
 * Global magic autoload function.
 *
 * @param string $class
 *   The name of the class that should be loaded automatically.
 * @throws RuntimeException
 *   If the requested file does not exist a runtime exception is thrown.
 * @return void
 * @since 0.0.1
 */
function __autoload($class) {
  // Build absolute path to class file.
  $class = __DIR__ . DIRECTORY_SEPARATOR . strtr($class, '\\', DIRECTORY_SEPARATOR) . '.inc';

  if (file_exists($class)) {
    require $class;
  } else {
    throw new RuntimeException();
  }
}

/* @var $class string */
$class = 'MovLib\\Presenter\\' . $_SERVER['PRESENTER'];

// This is the most outer place to catch an exception.
try {
  new $class();
} catch (Exception $e) {
  
}
