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
namespace MovLib\Tool\Console\Command\Provision;

/**
 * Install nginx.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Nginx extends \MovLib\Tool\Console\Command\Provision\AbstractProvision {

  /**
   * Array used to collect clean-up paths.
   *
   * @var array
   */
  private $src = [];

  /**
   * Download and prepare OpenSSL.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @return $this
   * @throws \Exception
   * @throws \RuntimeException
   */
  private final function downloadOpenSSL() {
    global $kernel;

    $this->src["openssl"] = $this->getSourcePath("openssl", $kernel->configuration->nginx->opensslVersion);
    $this->extract(
      $this->download("https://www.openssl.org/source/openssl-{$kernel->configuration->nginx->opensslVersion}.tar.gz"),
      $this->src["openssl"]
    );

    // We need two additional patches for OpenSSL.
    chdir($this->src["openssl"]);
    foreach ([ "openssl-1.0.1f-fix_parallel_build-1.patch", "openssl-1.0.1f-fix_pod_syntax-1.patch" ] as $patch) {
      $this->download("http://www.linuxfromscratch.org/patches/blfs/svn/{$patch}");
      $this->shExecute("patch -Np1 -i {$patch}");
      $this->fsDelete($patch);
    }

    // Otherwise checkinstall will complain about differing filemtimes.
    $this->shExecute("touch {$this->src["openssl"]}/Makefile*");

    return $this;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function provision() {
    global $kernel;

    // Download and extract nginx source.
    $this->src["nginx"] = $this->getSourcePath("nginx", $kernel->configuration->nginx->version);
    $this->extract(
      $this->download("http://nginx.org/download/nginx-{$kernel->configuration->nginx->version}.tar.gz"),
      $this->src["nginx"]
    );

    // Download and extract OpenSSL source.
    $this->downloadOpenSSL($this->src);

    // Download and extract PCRE source.
    $this->src["pcre"] = $this->getSourcePath("pcre", $kernel->configuration->nginx->pcreVersion);
    $this->extract(
      $this->download("ftp://ftp.csx.cam.ac.uk/pub/software/programming/pcre/pcre-{$kernel->configuration->nginx->pcreVersion}.tar.gz"),
      $this->src["pcre"]
    );

    // Downlad zlib
    $this->src["zlib"] = "{$this->src["nginx"]}/zlib";
    $this->download("git://github.com/madler/zlib.git", $this->src["zlib"]);

    // Expand placeholder tokens in configure options.
    $tokens = implode("|", array_keys($this->src));
    foreach ($kernel->configuration->nginx->configure as $delta => $argument) {
      $kernel->configuration->nginx->configure[$delta] = preg_replace_callback("/\{\{ ({$tokens}) \}\}/", function ($matches) {
        if (isset($matches[1]) && isset($this->src[$matches[1]])) {
          return "'{$this->src[$matches[1]]}'";
        }
        throw new \RuntimeException("Unknown placeholder token '{$matches[0]}' in nginx configure arguments");
      }, $argument);
    }

    // Downlad nginx_accept_language_module
    foreach ($kernel->configuration->nginx->modules as $name => $url) {
      $modulePath = "{$this->src["nginx"]}/{$name}";
      $this->download($url, $this->src[$name]);
      $kernel->configuration->nginx->configure[] = "add-module='{$modulePath}'";
    }

    // Configure the installation.
    $this->configure($this->src["nginx"], $kernel->configuration->nginx->configure);

    // Purge possibly installed old nginx installation.
    $this->aptPurge("movlib-nginx");

    // Install new nginx.
    $this->checkinstall($this->src["nginx"], "nginx", $kernel->configuration->nginx->version, [
      "pkgrelease=1",
      "provides='nginx'"
    ]);

    // Remove default configuration files.
    $this->fsDelete("{$kernel->configuration->directory->etc}/nginx/*");

    // Create custom MovLib configuration files.
    // @todo Call nginx configuration command.

    // Clean-up
    foreach ($this->src as $path) {
      $this->fsDelete($path, "rf");
    }

    return $this;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function validate() {
    global $kernel;

    foreach ([ "version", "opensslVersion", "pcreVersion" ] as $version) {
      if (empty($kernel->configuration->nginx->{$version})) {
        throw new \LogicException("The nginx '{$version}' cannot be empty");
      }
    }

    if (preg_match("/[0-9]+\.[0-9]+\.[0-9]+/", $kernel->configuration->nginx->version) !== 1) {
      throw new \LogicException("The nginx 'version' must be given in the format: <major>.<minor>.<patch>");
    }

    if (preg_match("/[0-9]+\.[0-9]+\.[0-9]+[a-z]+/", $kernel->configuration->nginx->opensslVersion) !== 1) {
      throw new \LogicException("The nginx 'opensslVersion' must be given in the format: <major>.<minor>.<patch><release>");
    }

    if (preg_match("/[0-9]+\.[0-9]+/", $kernel->configuration->nginx->pcreVersion) !== 1) {
      throw new \LogicException("The nginx 'pcreVersion' must be given in the format: <major>.<minor>");
    }

    if (empty($kernel->configuration->nginx->configure) || !is_array($kernel->configuration->nginx->configure)) {
      throw new \LogicException("The nginx 'configure' options must be given as array and cannot be empty");
    }

    $etcPath = "{$kernel->documentRoot}{$kernel->configuration->directory->etc}/nginx";
    if (empty(glob("{$etcPath}/*"))) {
      throw new \LogicException("The nginx configuration files are missing from '{$etcPath}'");
    }

    return $this;
  }

}
