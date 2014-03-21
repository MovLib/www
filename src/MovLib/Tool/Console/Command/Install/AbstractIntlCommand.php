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
namespace MovLib\Tool\Console\Command\Install;

use \MovLib\Data\I18n;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputArgument;

/**
 * Base class for Intl related seed commands.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractIntlCommand extends \MovLib\Tool\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __construct($name = null) {
    global $kernel;
    parent::__construct($name);
    $this->addArgument(
      "locale",
      InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
      "The system locales for which translations should be generated, either a language code or a locale. Note that " .
      "the default value 'all' is a special keyword, if 'all' is part of your supplied arguments any other argument " .
      "is simply ignored and translations for all available system locales will be generated. The following system " .
      "locales are currently available: " . implode(", ", $kernel->systemLanguages),
      [ "all" ]
    );
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the translation's source directory name.
   *
   * The translations are based on Intl ICU source files checked out via SVN from their repository. Each command that
   * extends this class will have to provide the name of the directory within the Intl ICU source date directory in
   * which the <i>.txt</i> files with the translations reside for automated translation.
   *
   * @link https://ssl.icu-project.org/repos/icu/icu/tags/latest/source/data/
   * @return string
   *   The translation's source directory name.
   */
//  abstract protected function getSourceDirectoryName();

  /**
   * Get the translations's target directory name.
   *
   * All translated data is stored in the directory defined by the {@see \MovLib\Data\StreamWrapper\I18nStreamWrapper},
   * this method should return the directory name within that directory under which the translations should be stored.
   * For example if we want to have translations for country names we'd use the directory name <code>"countries"</code>.
   * This allows the data layer to access the translated files via the URI <code>"i18n://countries"</code>, they'll
   * directly receive access to the correct file. For exact implementation see aforementioned stream wrapper.
   *
   * @return string
   *   The translations's target directory name.
   */
//  abstract protected function getTargetDirectoryName();

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
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Tool\Kernel $kernel
   * @param \Symfony\Component\Console\Input\InputInterface $input {@inheritdoc}
   * @param \Symfony\Component\Console\Output\OutputInterface $output {@inheritdoc}
   * @return integer {@inheritdoc}
   * @throws \InvalidArgumentException
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $i18n, $kernel;

    // Build array containing all desired locales.
    $args = $input->getArgument("locale");
    if (in_array("all", $args)) {
      $this->writeVerbose("Found special 'all' keyword, generating translations for all system locales...");
      $locales = $kernel->systemLanguages;
    }
    else {
      foreach ($args as $arg) {
        if (!isset($kernel->systemLanguages[$arg]) && !in_array($arg, $kernel->systemLanguages)) {
          throw new \InvalidArgumentException("Supplied locale '{$arg}' is not a valid system locale");
        }
        $lc = "{$arg[0]}{$arg[1]}";
        $locales[$lc] = $kernel->systemLanguages[$lc];
      }
    }

    $this->writeDebug("Creating target URI for translations based on the command's name...");
    $target = "i18n://" . str_replace("seed-", "", $this->getName());
    $this->writeVeryVerbose("Translation file will be: '{$target}'...");

    foreach ($locales as $locale) {
      $this->writeDebug("Creating new global I18n instance for '{$locale}' (important for stream wrapper)...");
      $i18n = new I18n($locale);

      if (file_exists($target)) {
        $this->writeDebug("Changing file mode of translations file to read and write...");
        chmod($target, 0666);
      }

      $this->writeVerbose("Creating translations for '{$locale}'...");
      file_put_contents($target, "<?php return [{$this->translate()}];");

      $this->writeDebug("Changing file mode of translations file to read only!", self::MESSAGE_TYPE_COMMENT);
      chmod($target, 0444);
    }

    $this->writeDebug("Successfully created translations for " . implode(", ", $locales) . "!", self::MESSAGE_TYPE_INFO);
    return 0;
  }

}
