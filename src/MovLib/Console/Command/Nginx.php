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

use MovLib\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Build nginx web server.
 *
 * Automatically invokes routes compilation before updating configuration files.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Nginx extends AbstractCommand {

  /**
   * The arguments of this command.
   *
   * <ul>
   *   <li>0 - The name of the argument</li>
   *   <li>1 - The default value of the argument</li>
   * </ul>
   *
   * @see \MovLib\Console\Command\Nginx::configure()
   * @var array
   */
  private $arguments = [
    [
      'name' => 'nginx',
      'version' => '1.3.16',
      'url' => 'http://nginx.org/download/nginx-1.3.16.tar.gz',
    ],
    [
      'name' => 'pcre',
      'version' => '8.32',
      'url' => 'http://vcs.pcre.org/viewvc/code/tags/pcre-8.32/?view=tar',
    ],
    [
      'name' => 'openssl',
      'version' => '1.0.1e',
      'url' => 'http://www.openssl.org/source/openssl-1.0.1e.tar.gz',
    ],
    [
      'name' => 'zlib',
      'version' => '1.2.7',
      'url' => 'http://zlib.net/zlib-1.2.7.tar.gz',
    ]
  ];

  /**
   * Absolute path to the nginx configuration directory.
   *
   * @var string
   */
  private $configurationPath;

  /**
   * Absolute path to temporary directory.
   *
   * @var string
   */
  private $tmpDirectory;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct('nginx');

    $this->configurationPath = IP . 'conf' . DIRECTORY_SEPARATOR . 'nginx' . DIRECTORY_SEPARATOR;
    $this->tmpDirectory = sys_get_temp_dir();

    // The behaviour of the last character is inconsistent in PHP. Sometime the last character is the directory
    // separator and sometimes not. Check for this problem and ensure that our path always ends with the directory
    // separator.
    if ($this->tmpDirectory[count($this->tmpDirectory - 1)] !== DIRECTORY_SEPARATOR) {
      $this->tmpDirectory .= DIRECTORY_SEPARATOR;
    }
  }

  /**
   * {@inheritdoc}
   *
   * @return \MovLib\Console\Command\Nginx
   */
  protected function configure() {
    $this->setDescription('Download and compile nginx.');
    foreach ($this->arguments as $delta => $argument) {
      $this->addArgument(
        $argument['name'],
        InputArgument::OPTIONAL,
        'The ' . $argument['name'] . ' version string to be compiled.',
        $argument['version']
      );
    }
    return $this;
  }

  /**
   * Validate the user supplied version string against our minimum requirements.
   *
   * @return \MovLib\Console\Command\Nginx
   */
  private function validateInput() {
    foreach ($this->arguments as $delta => $argument) {
      $version = $this->input->getArgument($argument['name']);

      if (version_compare($version, $argument['version'], '<')) {
        $this->exitOnError(ucfirst($argument['name'] . ' version must be at least ' . $argument['version']));
      }
      elseif (version_compare($version, $argument['version'], '>')) {
        $this->arguments[$delta]['version'] = $version;
        $argument['url'] = str_replace($argument['version'], $version, $argument['url']);
      }
    }
    return $this;
  }

  /**
   * Copy (download) given source to our local filesystem.
   *
   * @param string $source
   *   Absolute path (or valid URL) to the file from which we should create a local copy.
   * @param string $extension
   *   The file extension to append to the unique filename.
   * @return string
   *   Absolute path to the file on our filesystem.
   */
  private function copyToTmp($source, $extension) {
    // If the file is already on disk, do nothing!
    if (realpath($source) && file_exists($source)) {
      return $source;
    }

    /* @var $tmpFilePath string */
    $tmpFilePath = $this->tmpDirectory . uniqid() . '.' . $extension;

    if (!copy($source, $tmpFilePath)) {
      $this->exitOnError('Could not copy (download) ' . basename($source));
    }

    return $tmpFilePath;
  }

  /**
   * Download the given tarball and extracts its content.
   *
   * @todo Right now this relies on the fact that the archive contains only a single folder. This works just fine for
   *       the current sources we have, but we may have to extend this in the future. Or even better, change to Git or
   *       SVN repositories? (At least the nginx SVN is a big mess.)
   * @param string $tarFilePath
   *   Absolute path (or valid URL) to the tarball.
   * @return string
   *   The absolute path to the extracted files.
   */
  private function extractTar($tarFilePath) {
    $tarFilePath = $this->copyToTmp($tarFilePath, 'tar');

    try {
      /* @var $pharData \PharData */
      $pharData = new \PharData($tarFilePath);
      $pharData->extractTo($this->tmpDirectory);
      unlink($tarFilePath);
      return $this->tmpDirectory . $pharData->getFilename() . DIRECTORY_SEPARATOR;
    } catch (\PharException $e) {
      if (file_exists($tarFilePath)) {
        unlink($tarFilePath);
      }
      $this->exitOnError($e);
    }
  }

  /**
   * Downloads the given compressed tarball and extracts its content.
   *
   * @param string $tarGzFilePath
   *   Absolute path (or valid URL) to the compressed tarball.
   * @return string
   *   The absolut path to the extracted files.
   */
  private function extractTarGz($tarGzFilePath) {
    $tarGzFilePath = $this->copyToTmp($tarGzFilePath, 'tar.gz');

    try {
      (new \PharData($tarGzFilePath))->decompress();
      unlink($tarGzFilePath);
      return $this->extractTar(substr($tarGzFilePath, 0, -3));
    } catch (\PharException $e) {
      if (file_exists($tarGzFilePath)) {
        unlink($tarGzFilePath);
      }
      $this->exitOnError($e);
    }
  }

  /**
   * Downloads all necessary source files.
   *
   * @return \MovLib\Console\Command\Nginx
   */
  private function download() {
    foreach ($this->arguments as $delta => $argument) {
      /* @var $fn string */
      $fn = 'extractTar';

      if (pathinfo($argument['url'], PATHINFO_EXTENSION) === 'gz') {
        $fn .= 'Gz';
      }

      $this->writeInfo('Downloading and extracting ' . $argument['name'] . ' ' . $argument['version'] . ' ...');
      $this->arguments[$delta]['path'] = $this->{$fn}($argument['url']);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->input = $input;
    $this->output = $output;

    // In order to create the binary and the init script, we need elevated privileges.
    $this
      ->writeError([ 'IMPORTANT!', 'This command should be executed as root user (or with sudo)!' ])
      ->validateInput()
      ->download()
    ;
  }

}
