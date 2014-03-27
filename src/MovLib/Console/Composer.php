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
namespace MovLib\Core;

use \Composer\Script\Event;

/**
 * React on composer execution.
 *
 * Note that any exceptions are catched by composer and the program is automatically aborted.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Composer {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The fired composer event.
   *
   * @var \Composer\Script\Event
   */
  protected $event;

  /**
   * Document root stream wrapper instance.
   *
   * @var \MovLib\Data\StreamWrapper\AbstractLocalStreamWrapper
   */
  protected $dr;

  /**
   * The name of the vendor directory.
   *
   * @var string
   */
  protected $vendor;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new Composer object.
   *
   * @param \Composer\Script\Event $event
   *   The fired composer event.
   */
  public function __construct(Event $event) {
    if (!$kernel) {
      $kernel = new \MovLib\Tool\Kernel(true);
    }
    $this->event = $event;
    $this->dr    = StreamWrapperFactory::create("dr://");
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Create symbolic link for apigen executable.
   *
   * @param string $fullName
   *   The package's full name including name and slash.
   * @return this
   */
  public function apigen($fullName) {
    $bin = "/usr/local/bin/apigen";
    if ($kernel->isWindows === false && !is_link($bin)) {
      symlink($this->dr->realpath("dr://vendor/{$fullName}/apigen.php", $bin));
    }

    // @see https://github.com/apigen/apigen/issues/252
    $path    = "dr://vendor/{$fullName}/ApiGen/Template.php";
    $content = file_get_contents($path);
    if (strpos($content, "return \TexyHtml::el('code', \$fshl->highlight(\$matches[1]));") !== false) {
      file_put_contents($path, str_replace(
        "return \TexyHtml::el('code', \$fshl->highlight(\$matches[1]));",
        "\$content = \$parser->getTexy()->protect(\$fshl->highlight(\$matches[1]), \Texy::CONTENT_MARKUP);\n         return \TexyHtml::el('code', \$content);",
        $content
      ));
    }

    return $this;
  }

  /**
   * Install phpMyAdmin.
   *
   * @param string $fullName
   *   The packages full name including the name and slash.
   * @return this
   */
  public function phpmyadmin($fullName) {
    // Create symbolic link to our phpMyAdmin configuration.
    $target = "dr://vendor/{$fullName}/config.inc.php";
    if ($kernel->isWindows === false && !is_link($target)) {
      symlink($this->dr->realpath("dr://etc/phpmyadmin/config.inc.php"), $this->dr->realpath($target));
    }

    // Create all tables for the advanced phpMyAdmin features.
    $db->queries(file_get_contents("dr://vendor/{$fullName}/examples/create_tables.sql"));

    return $this;
  }

  /**
   * Create symbolic link for phpunit executable.
   *
   * @param string $fullName
   *   The packages full name including the name and slash.
   * @return this
   */
  public function phpunit($fullName) {
    $bin = "/usr/local/bin/phpunit";
    if ($kernel->isWindows === false && !is_link($bin)) {
      symlink($this->dr->realpath("dr://vendor/{$fullName}/composer/bin/phpunit"), $bin);
    }
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Static Methods


  /**
   * Automatically called after `composer install` execution.
   *
   * @param \Composer\Script\Event $event
   *   The event fired by composer.
   */
  public static function postInstall(Event $event) {}

  /**
   * Automatically called after composer installed a package.
   *
   * @param \Composer\Script\Event $event
   *   The event fired by composer.
   */
  public static function postPackageInstall(Event $event) {
    $operation = $event->getOperation();
    if (method_exists($operation, "getPackage")) {
      $composer         = new Composer($event);
      $fullPackageName  = $operation->getPackage()->getName();
      $packageName      = substr($fullPackageName, strrpos($fullPackageName, "/") + 1);
      if (method_exists($composer, $packageName)) {
        $composer->{$packageName}($fullPackageName);
      }
    }
  }

  /**
   * Automatically called after composer updated a package.
   *
   * @param \Composer\Script\Event $event
   *   The event fired by composer.
   */
  public static function postPackageUpdate(Event $event) {
    self::postPackageInstall($event);
  }

  /**
   * Automatically called after `composer update` execution.
   *
   * @param \Composer\Script\Event $event
   *   The event fired by composer.
   */
  public static function postUpdate(Event $event) {}

}
