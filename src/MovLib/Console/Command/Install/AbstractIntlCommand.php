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
namespace MovLib\Console\Command\Install;

use \MovLib\Core\Container;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for Intl related seed commands.
 *
 * <b>NOTE</b><br>
 * All translated data is stored in the directory defined by the {@see \MovLib\Data\StreamWrapper\I18nStreamWrapper},
 * this class will extract the appropriate sub-directory's name from the command name of the extending concrete class to
 * store the translations. This means, if your extending class's command name is <code>"seed-my-translations"</code>
 * then your translations will be stored in <code>"i18n://my-translations"</code>. For exact implementation see
 * aforementioned stream wrapper and the body of this class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractIntlCommand extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Intl ICU source data directory.
   *
   * @var string
   */
  const ICU_SOURCE_DATE_DIR = "dr://var/intl/icu-data";

  /**
   * Intl ICU version file URI.
   *
   * @todo We can concatenate constants in PHP 5.6.
   * @var string
   */
  const ICU_VERSION_URI = "dr://var/intl/icu-data/.version";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __construct(Container $container) {
    parent::__construct($container);
    $this->addArgument(
      "locale",
      InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
      str_replace("'all'", "<comment>all</comment>", wordwrap(
        "The system locales for which translations should be generated, either a language code or a locale. Note that " .
        "the default value 'all' is a special keyword, if 'all' is part of your supplied arguments any other argument " .
        "is simply ignored and translations for all available system locales will be generated. The following system " .
        "locales are currently available:\n\n<info>" . implode("</info>, <info>", [ "all" ] + $this->config->locales) .
        "</info>",
        120
      )),
      [ "all" ]
    );
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Translate for the given locale.
   *
   * <b>NOTE</b><br>
   * The global i18n instance will be set to the locale your translation method should translate to, use it within your
   * method to access the current locale, language code and translation methods.
   *
   * This method is called for each locale for which a translation should be generated. Note the your implementation has
   * to return the body of an array, usually an associative array with either a single key or another array (which is
   * usually casted to an object). If you're unsure how to handle this, generate a file and have a look at the exact
   * implementation and its output.
   *
   * @return string
   *   The translations.
   */
  abstract protected function translate();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   * @param \Symfony\Component\Console\Input\InputInterface $input {@inheritdoc}
   * @param \Symfony\Component\Console\Output\OutputInterface $output {@inheritdoc}
   * @return integer {@inheritdoc}
   * @throws \InvalidArgumentException
   */
  final protected function execute(InputInterface $input, OutputInterface $output) {
    // Build array containing all desired locales.
    $args = $input->getArgument("locale");
    if (in_array("all", $args)) {
      $this->writeVerbose("Found special <comment>all</comment> keyword, generating translations for all system locales");
      $locales = $this->config->locales;
    }
    else {
      foreach ($args as $arg) {
        if (!isset($this->config->locales[$arg]) && !in_array($arg, $this->config->locales)) {
          throw new \InvalidArgumentException("Supplied locale '{$arg}' is not a valid system locale.");
        }
        $languageCode           = "{$arg[0]}{$arg[1]}";
        $locales[$languageCode] = $this->config->locales[$languageCode];
      }
    }

    $this->writeDebug("Creating target URI for translations based on the command's name");
    $targetFilename = str_replace("seed-", "", $this->getName());
    $name = strtr($this->getName(), "-", " ");
    $this->writeVeryVerbose("Target filename will be <comment>dr://var/intl/<locale>/{$targetFilename}.php</comment>");

    foreach ($locales as $locale) {
      $this->writeDebug("Setting Intl locale to <comment>{$locale}</comment>");
      $this->intl->setLocale($locale);

      $this->writeVerbose("Creating <info>{$name}</info> translations for <comment>{$locale}</comment>");
      file_put_contents("dr://var/intl/{$locale}/{$targetFilename}.php", "<?php return[{$this->translate()}];");
    }

    $this->intl->setLocale($this->intl->defaultLocale);
    $this->writeDebug("Successfully created translations for " . implode(", ", $locales) . "!", self::MESSAGE_TYPE_INFO);
    return 0;
  }

  /**
   * Get resource bundle from the Intl ICU sources.
   *
   * @param string $dataSourceDirectoryName
   *   The name of the Intl ICU data source directory to generate the desired resource bundle.
   *
   *   The translations are based on Intl ICU source files checked out via SVN from their repository. This command will
   *   need to know the directory within the source data directory of the exported SVN directory of your specific
   *   translation task to generate the resource bundle. Go to {@link https://ssl.icu-project.org/repos/icu/icu/tags/latest/source/data/}
   *   to see all available default Intl ICU translations.
   * @return \ResourceBundle
   *   The desired resource bundle.
   * @throws \ErrorException
   * @throws \InvalidArgumentException
   */
  final protected function getResourceBundle($dataSourceDirectoryName) {
    $source  = self::ICU_SOURCE_DATE_DIR . "/{$dataSourceDirectoryName}";
    $version = $this->getVersion();

    // Make sure we have the latest sources available.
    if (is_dir(self::ICU_SOURCE_DATE_DIR) && is_file(self::ICU_VERSION_URI) && version_compare($version, file_get_contents(self::ICU_VERSION_URI), ">")) {
      unlink(self::ICU_SOURCE_DATE_DIR);
    }
    if (!is_dir(self::ICU_SOURCE_DATE_DIR)) {
      $this->svnExport();
    }

    // Make sure the desired directory exists.
    if (!is_dir($source)) {
      throw new \InvalidArgumentException("The desired source data directory '{$source}' doesn't exist");
    }

    // Load the best matching translations.
    $source    = "{$source}/{$this->intl->languageCode}.txt";
    $srcLocale = "{$source}/{$this->intl->locale}.txt";
    if (is_file($srcLocale)) {
      $source = $srcLocale;
    }
    elseif (!is_file($source)) {
      throw new \UnexpectedValueException("There are not translations available for '{$this->intl->languageCode}' ('{$dataSourceDirectoryName}')");
    }

    // Generate the resource bundle.
    $destination    = "dr://tmp/icu-resource-bundle";
    mkdir($destination);
    $destRealpath   = $this->fs->realpath($destination);
    $this->exec("genrb --encoding UTF-8 --destdir '{$destRealpath}' '{$this->fs->realpath($source)}'");
    $resourceBundle = new \ResourceBundle($this->intl->locale, $destRealpath);
    $this->fs->registerFileForDeletion($destination, true);

    return $resourceBundle;
  }

  /**
   * Get the installed ICU version.
   *
   * <b>NOTE</b><br>
   * The returned version has the format <code>"<major>-<minor>"</code>.
   *
   * @staticvar string $version
   *   Used to cache the version.
   * @return string
   *   The installed ICU version.
   * @throws \MovLib\Exception\ShellException
   */
  final protected function getVersion() {
    $this->writeDebug("Determining installed ICU version...");
    $this->getShell()->execute("icu-config --version", $version);
    return trim(strtr($version[0], ".", "-"));
  }

  /**
   * Export the Intl ICU source data directory via SVN.
   *
   * @return this
   * @throws \MovLib\Exception\ShellException
   */
  final protected function svnExport() {
    $srcRealpath = $this->fs->realpath(self::ICU_SOURCE_DATE_DIR);
    $version     = $this->getVersion();

    $this->writeVerbose("SVN exporting Intl ICU source data, this might some time...");
    $this->exec("svn export 'http://source.icu-project.org/repos/icu/icu/tags/release-{$version}/source/data' '{$srcRealpath}'");
    file_put_contents(self::ICU_VERSION_URI, $version);

    return $this;
  }

}
