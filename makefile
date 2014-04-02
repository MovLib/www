#!/bin/bash

# ----------------------------------------------------------------------------------------------------------------------
# This file is part of {@link https://github.com/MovLib MovLib}.
#
# Copyright © 2013-present {@link https://movlib.org/ MovLib}.
#
# MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
# License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
# version.
#
# MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY# without even the implied warranty
# of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License along with MovLib.
# If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
# ----------------------------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------------------------
# Build environment.
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2014 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

# Relative path to console commands.
CP:=src/MovLib/Console/Command

all: ngins-routes seed-aspect-ratios seed-countries seed-currencies seed-languages seed-subtitles translation

ngins-routes: $(CP)/Admin/NginxRoutes.php
	movadmin nginx-routes -v

seed-aspect-ratios: $(CP)/Install/SeedAspectRatios.php
	movinstall seed-aspect-ratios -v

seed-countries: $(CP)/Install/SeedCountries.php
	movinstall seed-countries -v

seed-currencies: $(CP)/Install/SeedCurrencies.php
	movinstall seed-currencies -v

seed-languages: $(CP)/Install/SeedLanguages.php
	movinstall seed-languages -v

seed-subtitles: $(CP)/Install/SeedSubtitles.php
	movinstall seed-subtitles -v

translation: $(CP)/Install/Translation.php
	movinstall translation compile -v

clean:
	rm -rf var/intl/*
