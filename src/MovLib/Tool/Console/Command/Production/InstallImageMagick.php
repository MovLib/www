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

use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Install ImageMagick.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InstallImageMagick extends \MovLib\Tool\Console\Command\AbstractInstall {

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("install-imagemagick", "ImageMagick");
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setDescription("Remove installed ImageMagick and install new version.");
    $this->addArgument("version", InputArgument::REQUIRED, "The ImageMagick version to install.");
  }

  /**
   * @inheritdoc
   * @throws \RuntimeException
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = parent::execute($input, $output);
    $this->checkPrivileges();
    if ($this->askConfirmation("Install ligjpeg-dev and libpng-dev from Debian repositories?") === true && $this->system("aptitude update && aptitude install libjpeg-dev libpng-dev") === false) {
      throw new ConsoleException("Could not update and install dependencies via aptitude!");
    }
    $this->installImageMagick($input->getArgument("version"));
    return $options;
  }

  /**
   * Install ImageMagick.
   *
   * @param string $version
   *   The version to install.
   * @return this
   * @throws \InvalidArgumentException
   */
  protected function installImageMagick($version) {
    $this->setVersion($version);
    $name           = $this->getInstallationName();
    $nameAndVersion = "{$name}-{$version}";
    $this->uninstall();
    chdir(self::SOURCE_DIRECTORY);
    if (!file_exists("{$nameAndVersion}.tar.gz")) {
      $this->wget("http://www.imagemagick.org/download/{$nameAndVersion}.tar.gz");
      $this->tar("{$nameAndVersion}.tar.gz");
    }
    chdir($nameAndVersion);
    $this->configureInstallation([
      "--disable-static",
      "--enable-shared",
      "--with-jpeg",
      "--with-png",
      "--with-quantum-depth=8",
      "--with-rsvg",
      "--with-webp",
      "--without-bzlib",
      "--without-djvu",
      "--without-dps",
      "--without-fftw",
      "--without-fontconfig",
      "--without-freetype",
      "--without-gvc",
      "--without-jbig",
      "--without-jp2",
      "--without-lcms",
      "--without-lcms2",
      "--without-lqr",
      "--without-lzma",
      "--without-magick-plus-plus",
      "--without-openexr",
      "--without-pango",
      "--without-perl",
      "--without-tiff",
      "--without-wmf",
      "--without-x",
      "--without-xml",
      "--without-zlib"
    ], "CFLAGS='-O3 -m64 -pthread' CXXFLAGS='-O3 -m64 -pthread'");
    // The ImageMagick installer fails to create this directory!
    if ($this->version[0] == 6) {
      mkdir("/usr/local/include/ImageMagick-6", 0777, true);
    }
    $this->install();
    return $this;
  }

}
