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


# Make all errors terminating.
$ErrorActionPreference = 'Stop'

# Ensure current path is available (always available in PS3+).
$PSScriptRoot = Split-Path -Parent -Path $MyInvocation.MyCommand.Definition

# Export current working directory to variable.
$pwd = Get-Location | Select-Object -ExpandProperty Path

# Include the icon change script.
. "$PSScriptRoot\Set-ConsoleIcon.ps1"

# Change the icon of our application.
Set-ConsoleIcon "$PSScriptRoot\Visual Studio Project\MovLib Vagrant Bootstrap\vagrant.ico"

# Change the title of our application.
$host.UI.RawUI.WindowTitle = 'MovLib Vagrant Bootstrap'


# ---------------------------------------------------------------------------------------------------------------------- Variables


$gitURL = 'http://git-scm.com/downloads'
$vagrantURL = 'http://www.vagrantup.com/downloads.html'
$virtualBoxURL = 'https://www.virtualbox.org/wiki/Downloads'


# ---------------------------------------------------------------------------------------------------------------------- Functions


# Ask the user for a keystroke to exit or continue.
#
# PARAMS:
#   -Balloon
#     You can pass in any balloon tip for proper dispose.
#     Default: $null
# RETURN:
#   $null
#     This function will always exit the script.
function Script-Continue {
  [cmdletbinding()]

  # Function parameters and defaults.
  param(
    [System.Windows.Forms.NotifyIcon]$Balloon = $null
  )

  Write-Host 'Press [ESC] to exit or any key to continue...'
  $keyCode = $host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown') | Select-Object -ExpandProperty VirtualKeyCode

  if ($Balloon -ne $null) {
    $Balloon.Dispose()
  }

  if ($keyCode -eq 27) {
    Stop-Process -Id $PID | Out-Null
  }

  exit
}

# Display system tray balloon (notification) tip.
#
# PARAMS:
#   -Title
#     The balloon tip's title.
#   -Message
#     The balloon tip's message.
#   -BalloonType
#     The balloon tip's type, one of Info, Warning, Error.
#     Default: Info
#   -Duration
#     The balloon tip's display duration in milliseconds.
#     Default: 5000
# RETURN:
#   System.Windows.Forms.NotifyIcon
#     The balloon tip.
function Show-BalloonTip {
  [cmdletbinding()]

  # Function parameters and defaults.
  param(
    [parameter(Mandatory = $true)]
    [string]$Title,
    [parameter(Mandatory = $true)]
    [string]$Message,
    [ValidateSet('Info', 'Warning', 'Error')]
    [string]$BalloonType = 'Info',
    [string]$Duration = 5000
  )

  # Load required assemblies.
  [System.Reflection.Assembly]::LoadWithPartialName('System.Windows.Forms') | Out-Null

  # Remove any registered events related to balloon tips.
  Remove-Event BalloonClicked_event -ea SilentlyContinue
  Unregister-Event -SourceIdentifier BalloonClicked_event -ea silentlycontinue
  Remove-Event BalloonClosed_event -ea SilentlyContinue
  Unregister-Event -SourceIdentifier BalloonClosed_event -ea silentlycontinue
  Remove-Event Disposed -ea SilentlyContinue
  Unregister-Event -SourceIdentifier Disposed -ea silentlycontinue

  # Create the balloon tip.
  $balloon = New-Object System.Windows.Forms.NotifyIcon
  $path = Get-Process -id $pid | Select-Object -ExpandProperty Path
  $icon = [System.Drawing.Icon]::ExtractAssociatedIcon($path)
  $balloon.Icon = $icon
  $balloon.BalloonTipIcon = $BalloonType
  $balloon.BalloonTipText = $Message
  $balloon.BalloonTipTitle = $Title

  # Make balloon tip visible when called.
  $balloon.Visible = $true
  $balloon.ShowBalloonTip($Duration)

  # Dispose balloon tip upon click.
  Register-ObjectEvent $balloon BalloonTipClosed BalloonClosed_event -Action {$balloon.Dispose()} | Out-Null

  return $balloon
}

# Display info, warning, or error message.
#
# PARAMS:
#   -Title
#     The message's title.
#   -Message
#     The message's body.
#   -MessageType
#     The message's type, one of Info, Warning, Error
#     Default: Error
#   -URL
#     URL to open in the user's web browser.
#     Default: $null
#   -Duration
#     The balloon tip's display duration in milliseconds.
#     Default: 5000
# RETURN:
#   $null
#     If message type is Error the function will exit the script.
function Display-Message {
  [cmdletbinding()]

  # Function parameters and defaults.
  param(
    [parameter(Mandatory = $true)]
    [string]$Title,
    [parameter(Mandatory = $true)]
    [string]$Message,
    [ValidateSet('Info', 'Warning', 'Error')]
    [string]$MessageType = 'Error',
    [string]$URL = $null,
    [string]$Duration = 5000
  )

  # Display balloon tip to ensure that the user knows about the problem.
  Show-BalloonTip -Title $Title -Message $Message -BalloonType 'Error' | Out-Null

  # Open web browser with given URL (if any).
  if ($URL -ne $null) {
    Start-Process $URL
  }

  # Switch the color, depending on the message's type.
  switch ($MessageType) {
    'Info'    { $fc = 'Cyan'       }
    'Warning' { $fc = 'DarkYellow' }
    'Error'   { $fc = 'Red'        }
  }

  # Display message, error messages are displayed without stack.
  Write-Host
  Write-Host "$MessageType! $Title" -ForegroundColor $fc
  Write-Host
  Write-Host $Message -ForegroundColor $fc
  Write-Host

  # Only exit on error.
  if ($MessageType -eq 'Error') {
    Script-Continue
  }
}


# ---------------------------------------------------------------------------------------------------------------------- Start


Write-Host
Write-Host 'MovLib Vagrant Bootstrap'
Write-Host 'Copyright (c) 2014 MovLib, the free movie library.'
Write-Host 'MovLib is free software, see LICENSE.txt for more information.'
Write-Host
Write-Host 'Validating environment...' -ForegroundColor 'Cyan'

# ----------------------------------------------------------------------------------------------------------------------
# Validate that git is installed, in our PATH, and at least version 1.9.0.0

try {
  Get-Command git | Out-Null
}
catch {
  Display-Message -Title 'Missing Git!' -Message 'Please install Git on your system and ensure that it is in your PATH.' -URL $gitURL
}

$version = git --version -ireplace '[a-z ]+([0-9]+\.[0-9]+\.[0-9]+)\.[a-z]+(\.[0-9]+)', '$1$2'
Write-Debug $version
if (!$version -or $version.CompareTo('1.9.0') -lt 0) {
  Display-Message -Title 'Missing Git!' -Message 'Please install at least Git "1.9.0".' -URL $gitURL
}

# ----------------------------------------------------------------------------------------------------------------------
# Validate that vagrant is installed, in our PATH, and at least version 1.4.3

try {
  Get-Command vagrant | Out-Null
}
catch {
  Display-Message -Title 'Missing Vagrant!' -Message 'Please install Vagrant on your system and ensure that it is in your PATH.' -URL $vagrantURL
}

$version = vagrant --version -ireplace 'Vagrant ([0-9]+\.[0-9]+\.[0-9]+)', '$1.0'
Write-Debug $version
if (!$version -or $version.CompareTo('1.4.3') -lt 0) {
  Display-Message -Title 'Old Vagrant!' -Message 'Please install at least Vagrant "1.4.3".' -URL $vagrantURL
}

# ----------------------------------------------------------------------------------------------------------------------
# Validate VirtualBox is installed and at least version 4.3.6

try {
  $version = (Get-Item -Path HKLM:\Software\Oracle\VirtualBox).getValue('VersionExt')
}
catch {
  Display-Message -Title 'Missing VirtualBox!' -Message 'Please install Oracle VirtualBox on your system.' -URL $virtualBoxURL
}

Write-Debug $version
if (!$version -or $version.CompareTo('4.3.6') -lt 0) {
  Display-Message -Title 'Old VirtualBox!' -Message 'Please install Oracle VirtualBox "4.3.6".' -URL $virtualBoxURL
}

# ----------------------------------------------------------------------------------------------------------------------
# Install/Update all Puppet modules and Vagrant plugins.

Write-Host 'All good, great job!' -ForegroundColor 'Green'
Write-Host
Write-Host 'Updating git submodules, this might take several minutes...' -ForegroundColor 'Cyan'

git submodule update --remote

Write-Host
Write-Host 'Installing and updating Vagrant plugins, this might take several minutes...' -ForegroundColor 'Cyan'

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
Write-Host 'Starting Vagrant, this might take several minutes...' -ForegroundColor 'Cyan'

# Work around net-ssh bug related to pageant, see: https://github.com/mitchellh/vagrant/issues/1455
$pageant = Get-Process pageant -ErrorAction SilentlyContinue
if ($pageant -ne $null) {
  kill -name pageant
  Display-Message `
    -Title 'Killed Pageant!' `
    -Message = 'The Ruby SSH implementation has a bug related to Pageant, therefore I had to close the running process.' `
    -MessageType 'Warning' `
    -Duration 20000
}

# We execute `vagrant up` with the installed GitBash because we cannot safely assume that the various executables from
# the bin folder are within our PATH and we need ssh.exe for `vagrant ssh` to work. Rather than altering the PATH, which
# would replace various Windows built-in commands like find, we make use of GitBash which has that path set and access
# to all necessary executables.
$arguments = '/C ""';
$arguments += Split-Path(Split-Path(Get-Command git | Select-Object -ExpandProperty Definition) -Parent) -Parent
$arguments += '\bin\sh.exe" --login -i -c "'
$arguments += "printf '\n# vagrant up\n\n' '' && vagrant up"
$arguments += '""'
$p = Start-Process -PassThru -NoNewWindow -WorkingDirectory $pwd -FilePath $env:WinDir\System32\cmd.exe -ArgumentList $arguments
$p.WaitForExit()

# ----------------------------------------------------------------------------------------------------------------------
# Let the user know that provisioning is finished and ensure that the balloon tip is correctly disposed upon exit.

Write-Host
$balloon = Show-BalloonTip -Title 'Finished Provisioning!' -Message 'Your MovDev VM is now ready to use.'
Script-Continue -Balloon $balloon
