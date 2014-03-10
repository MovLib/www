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
# Alternative approach for executing the Git bash. This allows coloring of the output, but has some flaws:
# * sometimes the prompt is displayed incorrectly
# * sometimes error messages appear after the prompt
# I decided to go back to the default white output and synchronous execution.
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

$psi = New-Object System.Diagnostics.ProcessStartInfo
$psi.Arguments = '/C ""'
$psi.Arguments += Split-Path(Split-Path(Get-Command git | Select-Object -ExpandProperty Definition) -Parent) -Parent
$psi.Arguments += '\bin\sh.exe" --login -i -c "'
$psi.Arguments += "printf '\n# vagrant up\n\n' '' && vagrant up"
$psi.Arguments += '""'
$psi.CreateNoWindow = $true
$psi.FileName = 'cmd'
$psi.RedirectStandardOutput = $true
$psi.RedirectStandardError = $true
$psi.UseShellExecute = $false
$psi.WorkingDirectory = $pwd

# Asynchronously read standard output and error, we want to ensure proper coloring.
# LINK: http://xiaalex.wordpress.com/2013/05/03/use-asynchronous-read-on-standard-output-of-a-process/
$p = New-Object System.Diagnostics.Process
$p.StartInfo = $psi
$p.EnableRaisingEvents = $true

# Subscribe to the Exited event, which is fired when the called process has finished.
Register-ObjectEvent -InputObject $p -EventName 'Exited' -Action {
  # Check if provisioning succeeded.
  if ($p.ExitCode -ne 0) {
    Display-Message -Title 'Provisioning Failed!' -Message 'Something went wrong while setting up your MovDev VM. Please report this issue.'
  }

  # Let the user know that provisioning is finished and ensure that the balloon tip is correctly disposed upon exit.
  Script-Continue(Display-Message -Title 'Finished Provisioning!' -Message 'Your MovDev VM is now ready to use.' -MessageType 'Info')
} | Out-Null

# Subscribe to the default output.
Register-ObjectEvent -InputObject $p -EventName 'OutputDataReceived' -SourceIdentifier processOutputDataReceived -Action {
  Write-Host $Event.SourceEventArgs.Data
} | Out-Null

# Subscribe to the error output.
Register-ObjectEvent -InputObject $p -EventName 'ErrorDataReceived' -SourceIdentifier processErrorDataReceived -Action {
  Write-Host $Event.SourceEventArgs.Data -ForegroundColor 'Red'
} | Out-Null

$p.Start() | Out-Null
$p.BeginOutputReadLine()
$p.BeginErrorReadLine()
