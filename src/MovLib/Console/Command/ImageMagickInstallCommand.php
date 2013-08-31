<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Console\Command;

use \MovLib\Console\Command\AbstractInstallCommand;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Install ImageMagick.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ImageMagickInstallCommand extends AbstractInstallCommand {

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("install-imagemagick");
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setDescription("Install latest ImageMagick version.");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->setIO($input, $output)->checkPrivileges();
    $this->name = "ImageMagick";
    $this->version = $this->ask("Please enter the desired ImageMagick version", "6.8.6-9");
    if ($this->askConfirmation("Install ligjpeg-dev and libpng-dev from Debian repositories?") === true) {
      $this->system("aptitude update && aptitude install libjpeg-dev libpng-dev", "Could not update and install dependencies via aptitude");
    }
    $this->uninstall();
    chdir(self::INSTALL_DIRECTORY);
    if (!file_exists("{$this->name}-{$this->version}")) {
      $this->wget("http://www.imagemagick.org/download/{$this->name}-{$this->version}.tar.gz");
    }
    $archive = "{$this->name}-{$this->version}.tar.gz";
    if (file_exists($archive)) {
      $this->tar($archive);
    }
    chdir("{$this->name}-{$this->version}");
    $this->configureInstallation([
      'CFLAGS="-O3 -m64 -pthread"',
      'CXXFLAGS="-O3 -m64 -pthread"',
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
    ]);
    // The ImageMagick installer fails to create this directory!
    if ($this->version[0] == 6) {
      mkdir("/usr/local/include/ImageMagick-6", 0777, true);
    }
    $this->install();
  }

}
