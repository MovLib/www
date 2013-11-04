#! /bin/bash

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
# Helper script containing global configuration for all installer scripts.
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

function msgline() {
  for i in {1..80}; do
    echo -n "-"
  done
}

function msgerror() {
  local COLOR="\033[01;31m" # red
  local RESET="\033[00;00m" # white
  echo -e "${COLOR}${1}${RESET}"
}

function msginfo() {
  local COLOR="\033[01;34m" # blue
  local RESET="\033[00;00m" # white
  echo -e "${COLOR}${1}${RESET}"
}

function msgsuccess() {
  local COLOR="\033[01;32m" # green
  local RESET="\033[00;00m" # white
  echo -e "${COLOR}${1}${RESET}"
}

function exitonerror() {
  if [ $? != 0 ]; then
    msgerror "Last command failed!"
    exit 1
  fi
}

if [ "$(whoami)" != "root" ]; then
  msgerror "You are using a non-privileged account, exiting!"
  exit 1
fi

WD="$(pwd)/"          # Work directory
ID="${WD}/inc/"       # Include directory
SD="/usr/local/src/"  # Source directory
cd ${SD}
