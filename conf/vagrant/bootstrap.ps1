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
# Ensure all Git submodules and Vagrant plugins are installed and start Vagrant MovDev machine.
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

#
# Windows Shortcut (also set the icon to use the UAC shield)
#
# Target:   %windir%\system32\WindowsPowerShell\v1.0\powershell.exe -NoLogo -Sta -ExecutionPolicy Unrestricted -File .\conf\vagrant\bootstrap.ps1
# Start In: %cd%
#

#
# Some links that helped a lot to create this script:
# * http://blogs.msdn.com/b/virtual_pc_guy/archive/2010/09/23/a-self-elevating-powershell-script.aspx
#

# Get the ID and security principal of the current user account
$principal = new-object System.Security.Principal.WindowsPrincipal([System.Security.Principal.WindowsIdentity]::GetCurrent())

# Get the security principal for the Administrator role
$adminRole = [System.Security.Principal.WindowsBuiltInRole]::Administrator

# Check to see if we are currently running "as Administrator".
if ($principal.IsInRole($adminRole)) {
  # Make all errors terminating.
  $ErrorActionPreference = 'Stop'

  # Ensure that we are in the root directory of the repository.
  Set-Location(Split-Path $myInvocation.MyCommand.Path -Parent)
  Set-Location(Resolve-Path '..\..\')

  Write-Host 'MovLib Vagrant Bootstrap'
  Write-Host 'Copyright (c) 2014 MovLib, the free movie library.'
  Write-Host 'MovLib is free software, see LICENSE.txt for more information.'
  Write-Host ''
  Write-Host 'Validating environment...' -ForegroundColor Yellow

  #
  # Validate that git is installed, in our PATH, and at least version 1.9.0.0
  #

  try {
    Get-Command git | Out-Null
  }
  catch {
    Throw 'Please install git on your system and ensure that it is in your PATH'
  }

  $version = git --version -ireplace '[a-z ]+([0-9]+\.[0-9]+\.[0-9]+)\.[a-z]+(\.[0-9]+)', '$1$2'
  Write-Debug $version
  if (!$version -or $version.CompareTo('1.9.0.0') -lt 0) {
    Throw 'Please install at least git 1.9.0.0'
  }

  # We now know that we have Git installed, export ssh.exe to PATH if it's missing.
  try {
    Get-Command ssh | Out-Null
  }
  catch {
    $sh = Split-Path(Split-Path(Get-Command git | Select-Object -ExpandProperty Definition) -Parent) -Parent
    $env:PATH += ";$sh\bin"
    [System.Environment]::SetEnvironmentVariable('PATH', $env:PATH, 'Machine')
  }

  #
  # Validate that vagrant is installed, in our PATH, and at least version 1.4.3
  #

  try {
    Get-Command vagrant | Out-Null
  }
  catch {
    Throw 'Please install Vagrant on your system and ensure that it is in your PATH'
  }

  $version = vagrant --version -ireplace 'Vagrant ([0-9]+\.[0-9]+\.[0-9]+)', '$1.0'
  Write-Debug $version
  if (!$version -or $version.CompareTo('1.4.3') -lt 0) {
    Throw 'Please install at least Vagrant 1.4.3'
  }

  Write-Host 'All good, great job!' -ForegroundColor Green

  #
  # Validate VirtualBox is installed and at least version 4.3.6
  #

  try {
    $version = (Get-Item -Path HKLM:\Software\Oracle\VirtualBox).getValue('VersionExt')
  }
  catch {
    Throw 'Please install Oracle VirtualBox on your system'
  }

  Write-Debug $version
  if (!$version -or $version.CompareTo('4.3.6') -lt 0) {
    Throw 'Please install Oracle VirtualBox 4.3.6'
  }

  #
  # Install/Update all Puppet modules and Vagrant plugins.
  #

  Write-Host ''
  Write-Host 'Updating git submodules...' -ForegroundColor Yellow

  git submodule update --remote

  Write-Host ''
  Write-Host 'Installing and updating Vagrant plugins...' -ForegroundColor Yellow

  $installed = vagrant plugin list
  $plugins   = @('hostsupdater', 'vbguest', 'puppet-install')
  foreach ($plugin in $plugins) {
    $found = 0
    foreach ($i in $installed) {
      if ($i -match "vagrant-$plugin") {
        vagrant plugin update "vagrant-$plugin" | ForEach-Object {
          $output = $_ -ireplace 'Installing', 'Updating' -ireplace 'Installed', 'Updated'
          Write-Host $output
        }
        $found = 1
        break
      }
    }
    if (!$found) {
      vagrant plugin install "vagrant-$plugin"
    }
  }

  #
  # Start the MovDev VM.
  #

  Write-Host ''
  Write-Host 'Starting Vagrant...' -ForegroundColor Yellow
  vagrant up
  Write-Host ''
}
# We are not running "as Administrator" - so relaunch as administrator.
else {
  # Create a new process object that starts PowerShell and start it.
  $elevated           = new-object System.Diagnostics.ProcessStartInfo 'PowerShell'
  $elevated.Arguments = '-NoExit ' + $myInvocation.MyCommand.Definition
  $elevated.Verb      = 'runAs'
  [System.Diagnostics.Process]::Start($elevated)
  exit
}
