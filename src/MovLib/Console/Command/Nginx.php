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

use \MovLib\Console\Command\AbstractCommand;
use \MovLib\Exception\FileSystemException;
use \MovLib\Utility\FileSystem;
use \PharData;
use \Exception;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \ZipArchive;

/**
 * Build nginx web server. Automatically invokes routes compilation before updating configuration files.
 *
 * Why do we need this? The nginx release cicle is pretty fast compared to other software and unlike PHP and MariaDB
 * there are no good repositories for the latest dev version of nginx. Because we want to have the latest features
 * (e.g. SPDY, PCRE JIT, ...) we have to build nginx ourselfs. Of course we could do all of this stuff manually, but
 * this script is simply made to automate the process. It also enables us to keep the configuration files for nginx
 * within our standard configuration layout and document each and everything as good as possible. The configuration
 * files are stripped (comments are removed and some files are combined) for easy hacking while developing.
 *
 * @todo Validate the configuration file.
 * @see \MovLib\Console\Command\Routes
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Nginx extends AbstractCommand {

  /**
   * Absolute path to the nginx configuration directory.
   *
   * @var string
   */
  private $configurationPath;

  /**
   * The configuration options which decide how nginx will be compiled.
   *
   * @var array
   */
  private $deployConfiguration;

  /**
   * Array containing absolute paths to directories that should be deleted if an error occures.
   *
   * @var array
   */
  private $rollbackPaths = [];

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct('nginx');
    $this->setDescription('Download and compile nginx.');
    $this->configurationPath = IP . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'nginx';
    $this->deployConfiguration = json_decode(file_get_contents($this->configurationPath . DIRECTORY_SEPARATOR . 'deploy.json'), true);
  }

  /**
   * Rollback any changes that where made if an error occures.
   */
  protected function rollback() {
    try {
      foreach ($this->rollbackPaths as $delta => $path) {
        FileSystem::unlinkRecursive($path);
      }
    }
    /* @var $e FileSystemException */
    catch (FileSystemException $e) {
      $this->exitOnError($e->getMessage(), $e->getTraceAsString());
    }
  }

  /**
   * Absolute path on the local filesystem to a directory, file or symbolic link that should be deleted on rollback.
   *
   * @param string $path
   *   The absolute path to the directory, file or symbolic link.
   * @return string
   *   Returns the unaltered path.
   */
  private function addRollbackPath($path) {
    if (realpath($path) && file_exists($path)) {
      $this->rollbackPaths[] = $path;
    }
    return $path;
  }

  /**
   * Copy (download) given source to our local filesystem.
   *
   * @param string $source
   *   Absolute path (or valid URL) to the file from which we should create a local copy.
   * @param string $fileExtension
   *   [optional] The file extension to append to the unique filename.
   * @return string
   *   Absolute path to the file on our filesystem.
   */
  private function copyToTmp($source, $fileExtension = '') {
    // If the file is already on disk, do nothing!
    if (realpath($source) && file_exists($source)) {
      return $source;
    }

    try {
      return $this->addRollbackPath(FileSystem::temporaryCopy($source, $fileExtension));
    }
    /* @var $e \MovLib\Exception\FileSystemException */
    catch (FileSystemException $e) {
      $this->exitOnError($e->getMessage(), $e->getTraceAsString());
    }
  }

  /**
   * Extract the archive identified by the given absolute or relative path (or URL).
   *
   * @param string $filePath
   *   Absolute or relative path (or URL) to the archive.
   * @return type
   *   The absolute path to the extracted content of the archive.
   */
  private function extract($filePath) {
    /* @var $fileExtension string */
    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

    // The file extension is maybe part of a query string.
    if (empty($fileExtension)) {
      $fileExtension = substr($filePath, -3);
    }

    /* @var $fn string */
    $fn = __FUNCTION__ . ucfirst($fileExtension);

    if ($fn === __FUNCTION__ || !method_exists($this, $fn)) {
      $this->exitOnError('There is no method to extract archives of type "' . $fileExtension . '"!');
    }

    if ($fileExtension === 'gz') {
      $fileExtension = 'tar.gz';
    }

    $filePath = $this->copyToTmp($filePath, $fileExtension);

    try {
      /* @var $extractedDataPath string */
      $extractedDataPath = $this->addRollbackPath($this->{$fn}($filePath));
    }
    /* @var $e \Exception */
    catch (Exception $e) {
      // @todo Move to finally block in PHP 5.5
      if (file_exists($filePath)) {
        unlink($filePath);
      }
      $this->exitOnError($e->getMessage(), $e->getTraceAsString());
    }

    return $extractedDataPath;
  }

  /**
   * Extracts the given tarball.
   *
   * @todo Right now this relies on the fact that the archive contains only a single folder. This works just fine for
   *       the current sources we have, but we may have to extend this in the future. Or even better, change to Git or
   *       SVN repositories? (At least the nginx SVN is a big mess.)
   * @param string $tarFilePath
   *   Absolute path to the tarball.
   * @return string
   *   The absolute path to the extracted files.
   */
  private function extractTar($tarFilePath) {
    /* @var $pharData \PharData */
    $pharData = new PharData($tarFilePath);
    $pharData->extractTo(FileSystem::getTemporaryDirectory());
    unlink($tarFilePath);
    return FileSystem::getTemporaryDirectory() . DIRECTORY_SEPARATOR . $pharData->getFilename();
  }

  /**
   * Extracts the given compressed tarball.
   *
   * @param string $tarGzFilePath
   *   Absolute path to the compressed tarball.
   * @return string
   *   The absolut path to the extracted files.
   */
  private function extractGz($tarGzFilePath) {
    (new PharData($tarGzFilePath))->decompress();
    $tarFilePath = substr($tarGzFilePath, 0, -3);
    unlink($tarGzFilePath);
    return $this->extractTar($tarFilePath);
  }

  /**
   * Extracts the given ZIP archive.
   *
   * @param string $zipFilePath
   *   Absolute path to the compressed ZIP archive.
   * @return string
   *   The absolute path to the extracted files.
   */
  private function extractZip($zipFilePath) {
    /* @var $zipArchive \ZipArchive */
    $zipArchive = new ZipArchive();
    $zipArchive->open($zipFilePath);
    $zipArchive->extractTo(FileSystem::getTemporaryDirectory());
    /* @var $destination string */
    $destination = FileSystem::getTemporaryDirectory() . DIRECTORY_SEPARATOR . $zipArchive->getNameIndex(0);
    $zipArchive->close();
    unlink($zipFilePath);
    return $destination;
  }

  /**
   * Helper method to clone or checkout from a repository.
   *
   * @param string $key
   *   Name of the directory into which we should clone / check out.
   * @param string $url
   *   The URL of the repository.
   * @param string $cmd
   *   The shell command that should be executed.
   * @return string
   *   The absolute path to the local repository.
   */
  private function repo($key, $url, $cmd) {
    chdir(FileSystem::getTemporaryDirectory());
    system($cmd . ' "' . $url . '" ' . $key);
    return $this->addRollbackPath(FileSystem::getTemporaryDirectory() . DIRECTORY_SEPARATOR . $key);
  }

  /**
   * Clone a git repository.
   *
   * @param string $key
   *   Name of the directory into which we should clone.
   * @param string $url
   *   The git URL of the repository.
   * @return string
   *   The absolute path to the local repository.
   */
  private function gitClone($key, $url) {
    return $this->repo($key, $url, 'git clone');
  }

  /**
   * Check out an svn repository.
   *
   * @param string $key
   *   Name of the directory into which we should check out.
   * @param string $url
   *   The svn URL of the repository.
   * @return string
   *   The absolute path to the local repository.
   */
  private function svnCheckout($key, $url) {
    return $this->repo($key, $url, 'svn co');
  }

  /**
   * Downloads all necessary source files.
   *
   * This method is only suitable for downloading a single software.
   *
   * @param string $key
   *   The name of the key in the <tt>deploy.json</tt> file of the software that should be downloaded.
   * @param string $url
   *   The absolute path to the source of the software.
   * @return \MovLib\Console\Command\Nginx
   */
  private function download($key, $url) {
    switch (($url[0] . $url[1] . $url[2])) {
      case 'git':
        $this->output->writeln('<info>Cloning git ' . $key . ':</info> ' . $url);
        return $this->gitClone($key, $url);

      case 'svn':
        $this->output->writeln('<info>Checking out SVN ' . $key . ':</info> ' . $url);
        return $this->svnCheckout($key, $url);

      default:
        $this->output->writeln('<info>Downloading ' . $key . ':</info> ' . $url);
        return $this->extract($url);
    }
  }

  /**
   * Downloads all necessary source files and extracts them if needed.
   *
   * @param string $key
   *   The name of the key in the <tt>deploy.json</tt> file of the software that should be downloaded or to an array of
   *   multiple software downloads.
   * @return \MovLib\Console\Command\Nginx
   */
  private function downloadAndExtract($key) {
    /* @var $download string|array */
    if (!($download = $this->deployConfiguration[$key])) {
      $this->exitOnError('The requested configuration key "' . $key . '" does not exist!');
    }

    $this->writeInfo('Starting download of ' . $key . ' ...');

    if (is_array($download)) {
      /*
       * @var $downloadKey string
       * @var $downloadUrl string
       */
      foreach ($download as $downloadKey => $downloadUrl) {
        $this->deployConfiguration[$key][$downloadKey] = $this->download($downloadKey, $downloadUrl);
      }
    }
    else {
      $this->deployConfiguration[$key] = $this->download($key, $download);
    }

    return $this;
  }

  /**
   * Put everything together and compile nginx.
   *
   * @return \MovLib\Console\Command\Nginx
   */
  private function configureMakeAndInstall() {
    /* @var $configureArgs array */
    $configureArgs = [];

    /*
     * @var $option string
     * @var $path string
     */
    foreach ($this->deployConfiguration['configure']['paths'] as $option => $path) {
      $configureArgs[] = $option . '=' . $path;
    }

    /*
     * @var $library string
     * @var $path string
     */
    foreach ($this->deployConfiguration['libraries'] as $library => $path) {
      // PCRE is a real problem. The tar.gz files have broken checksums and the configure script has the wrong
      // permissions.
      if ($library === 'pcre') {
        chmod($path . DIRECTORY_SEPARATOR . 'configure', 755);
      }
      $configureArgs[] = 'with-' . $library . '=' . $path;
    }

    /*
     * @var $delta int
     * @var $module string
     */
    foreach ($this->deployConfiguration['configure']['withModules'] as $delta => $module) {
      if (strpos($module, 'PATH') !== false) {
        /* @var $matches array */
        $matches = [];
        preg_match('#(.*)PATH{([a-z0-9]+)\.([a-z0-9]+)}#iuU', $module, $matches);
        $module = $matches[1] . $this->deployConfiguration[$matches[2]][$matches[3]];
      }
      $configureArgs[] = 'with-' . $module;
    }

    /*
     * @var $delta int
     * @var $module string
     */
    foreach ($this->deployConfiguration['configure']['withoutModules'] as $delta => $module) {
      $configureArgs[] = 'without-' . $module;
    }

    /*
     * @var $module string
     * @var $path string
     */
    foreach ($this->deployConfiguration['modules'] as $module => $path) {
      $configureArgs[] = 'add-module=' . $path;
    }

    $this->writeInfo('./configure --' . implode(' --', $configureArgs));
//    chdir($this->deployConfiguration['nginx']);
//
//    foreach ([ './configure --' . implode(' --', $configureArgs), 'make', 'make install' ] as $delta => $cmd) {
//      /* @var $returnCode int */
//      if (system($cmd, $returnCode) === false || $returnCode !== 0) {
//        $this->exitOnError('Nginx compilation failed!', $cmd);
//      }
//    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->setInput($input);
    $this->setOutput($output);

    // In order to create the binary and the init script, we need elevated privileges.
    $this->writeError([ 'IMPORTANT!', 'This command should be executed as root user (or with sudo)!' ]);

    if ($this->getHelperSet()->get('dialog')->askConfirmation($output, '<question>Continue with compiling nginx?</question> (y/n) ', false)) {
      $this
//        ->downloadAndExtract('nginx')
//        ->downloadAndExtract('libraries')
//        ->downloadAndExtract('modules')
        ->configureMakeAndInstall()
        ->writeInfo('Finished!')
//        ->rollback()
      ;
    }
  }

}
