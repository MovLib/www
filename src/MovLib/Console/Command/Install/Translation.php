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

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Translation related insatll tasks.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Translation extends \MovLib\Console\Command\AbstractCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("extract-translation");
    $this->setDescription("Perform various translation related tasks.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $potPath = $this->fs->realpath("dr://tmp/MovLib.pot");

    $this->writeVerbose("Getting all translation keys from php files...", self::MESSAGE_TYPE_COMMENT);
    $command = "find {$this->fs->realpath("dr://src/MovLib")} -iname '*.php' | xargs xgettext";
    foreach ([
      "output"    => $potPath,
      "language"  => "PHP",
      "from-code" => "UTF-8",
      "keyword"   => 't',
      "no-wrap"   => null
    ] as $option => $arg) {
      if (isset($arg)) {
        $command .= " --{$option}=" . escapeshellarg($arg);
      }
      else {
        $command .= " --{$option}";
      }
    }
    $this->exec($command);

    $this->writeVerbose("Updating po files for all languages.", self::MESSAGE_TYPE_INFO);
    foreach ($this->intl->systemLocales as $code => $locale) {
      if ($code != $this->intl->defaultLanguageCode) {
        $poPath = $this->fs->realpath("dr://var/intl/{$locale}/messages.po");
        $command = "msgmerge --update {$poPath} {$potPath} && rm {$poPath}~";
        $this->exec($command);
      }
    }

    return 0;
  }

}
