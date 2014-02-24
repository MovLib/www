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
# Display system tray balloon (notification) tip.
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

# Display system tray balloon (notification) tip.
#
# PARAMS:
#   -Title
#     The balloon tip's title.
#   -Message
#     The balloon tip's message.
#   -BalloonType
#     The balloon tip's type, one of: 'Info', 'Warning', 'Error' (defaults to 'Info'
#   -Duration
#     The balloon tip's display duration in milliseconds, defaults to 5000.
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
