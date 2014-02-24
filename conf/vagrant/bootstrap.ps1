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

# Ensure current path is available (always available in PS3+).
$PSScriptRoot = Split-Path -Parent -Path $MyInvocation.MyCommand.Definition

# Export current working directory to variable.
$pwd = Get-Location | Select-Object -ExpandProperty Path

# Make all errors terminating.
$ErrorActionPreference = 'Stop'

# Include the icon change script.
. "$PSScriptRoot\Set-ConsoleIcon.ps1"
. "$PSScriptRoot\Show-BalloonTip.ps1"

# Change the icon of our application.
Set-ConsoleIcon "$PSScriptRoot\Visual Studio Project\MovLib Vagrant Bootstrap\vagrant.ico"

# Change the title of our application.
$host.UI.RawUI.WindowTitle = 'MovLib Vagrant Bootstrap'

# ----------------------------------------------------------------------------------------------------------------------
# Start the application.

Write-Host
Write-Host 'MovLib Vagrant Bootstrap'
Write-Host 'Copyright (c) 2014 MovLib, the free movie library.'
Write-Host 'MovLib is free software, see LICENSE.txt for more information.'
Write-Host
Write-Host 'Validating environment...' -ForegroundColor Yellow

# ----------------------------------------------------------------------------------------------------------------------
# Validate that git is installed, in our PATH, and at least version 1.9.0.0

try {
  Get-Command git | Out-Null
}
catch {
  $message = 'Please install Git on your system and ensure that it is in your PATH'
  Show-BalloonTip -Title 'Missing Git!' -Message $message -BalloonType 'Error' | Out-Null
  Throw $message
}

$version = git --version -ireplace '[a-z ]+([0-9]+\.[0-9]+\.[0-9]+)\.[a-z]+(\.[0-9]+)', '$1$2'
Write-Debug $version
if (!$version -or $version.CompareTo('1.9.0.0') -lt 0) {
  $message = 'Please install at least Git 1.9.0.0'
  Show-BalloonTip -Title 'Old Git!' -Message $message -BalloonType 'Error' | Out-Null
  Throw $message
}

# ----------------------------------------------------------------------------------------------------------------------
# Validate that vagrant is installed, in our PATH, and at least version 1.4.3

try {
  Get-Command vagrant | Out-Null
}
catch {
  $message = 'Please install Vagrant on your system and ensure that it is in your PATH'
  Show-BalloonTip -Title 'Missing Vagrant!' -Message $message -BalloonType 'Error' | Out-Null
  Throw $message
}

$version = vagrant --version -ireplace 'Vagrant ([0-9]+\.[0-9]+\.[0-9]+)', '$1.0'
Write-Debug $version
if (!$version -or $version.CompareTo('1.4.3') -lt 0) {
  $message = 'Please install at least Vagrant 1.4.3'
  Show-BalloonTip -Title 'Old Vagrant!' -Message $message -BalloonType 'Error' | Out-Null
  Throw $message
}

Write-Host 'All good, great job!' -ForegroundColor Green

# ----------------------------------------------------------------------------------------------------------------------
# Validate VirtualBox is installed and at least version 4.3.6

try {
  $version = (Get-Item -Path HKLM:\Software\Oracle\VirtualBox).getValue('VersionExt')
}
catch {
  $message = 'Please install Oracle VirtualBox on your system'
  Show-BalloonTip -Title 'Missing VirtualBox!' -Message $message -BalloonType 'Error' | Out-Null
  Throw $message
}

Write-Debug $version
if (!$version -or $version.CompareTo('4.3.6') -lt 0) {
  $message = 'Please install Oracle VirtualBox 4.3.6'
  Show-BalloonTip -Title 'Old VirtualBox!' -Message $message -BalloonType 'Error' | Out-Null
  Throw $message
}

# ----------------------------------------------------------------------------------------------------------------------
# Install/Update all Puppet modules and Vagrant plugins.

Write-Host
Write-Host 'Updating git submodules, this might take several minutes...' -ForegroundColor Yellow

git submodule update --remote

Write-Host
Write-Host 'Installing and updating Vagrant plugins, this might take several minutes...' -ForegroundColor Yellow

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

# ----------------------------------------------------------------------------------------------------------------------
# Start the MovDev VM.

Write-Host
Write-Host 'Starting Vagrant, this might take several minutes...' -ForegroundColor Yellow

# We execute `vagrant up` with the installed GitBash because we cannot safely assume that the various executables from
# the bin folder are within our PATH and we need ssh.exe for `vagrant ssh` to work. Rather than altering the PATH, which
# would replace various Windows built-in commands like find, we make use of GitBash which has that path set and access
# to all necessary executables.
$arguments = '/C ""';
$arguments += Split-Path(Split-Path(Get-Command git | Select-Object -ExpandProperty Definition) -Parent) -Parent
$arguments += '\bin\sh.exe" --login -i -c "'
$arguments += "printf '\n# vagrant up\n' '' && vagrant up"
$arguments += '""'
$p = Start-Process -PassThru -NoNewWindow -WorkingDirectory $pwd -FilePath $env:WinDir\System32\cmd.exe -ArgumentList $arguments
$p.WaitForExit()

# ----------------------------------------------------------------------------------------------------------------------
# Let the user know that provisioning is finished and ensure that the balloon tip is correctly disposed upon exit.

Write-Host
$balloon = Show-BalloonTip -Title "Finished Provisioning!" -Message "Your MovDev VM is now ready to use."

Write-Host 'Press [ESC] to exit or any key to continue...'
$keyCode = $host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown') | Select-Object -ExpandProperty VirtualKeyCode
$balloon.Dispose()
if ($keyCode -eq 27) {
  Stop-Process -Id $PID | Out-Null
}
