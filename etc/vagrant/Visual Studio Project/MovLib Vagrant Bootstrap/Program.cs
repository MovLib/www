using System;
using System.Diagnostics;
using System.IO;

namespace MovLib_Vagrant_Setup {

  class Program {

    /**
     * The main entry point of our small application.
     */
    static void Main() {
      // Absolute path to the current directory (document root of the repository).
        string cd = Directory.GetCurrentDirectory();

        // Absolute path to the PowerShell bootstrap script.
        string script = cd + @"\conf\vagrant\bootstrap.ps1";

        // Create new process start info instance.
        ProcessStartInfo psi = new ProcessStartInfo();

        // The process we want to start.
        psi.FileName = "powershell";

        // Validate that the script is present.
        if (!File.Exists(script)) {
          psi.Arguments = "-NoLogo -Command & \"{Write-Host; Write-Host 'ERROR: \\conf\\vagrant\\bootstrap.ps1 script is missing!' -Foreground Red; Write-Host}\"";
        }
        // Build process arguments and set working directory.
        else {
          psi.Arguments = "-NoExit -WindowStyle Maximized -NoLogo -ExecutionPolicy Unrestricted -File \"" + script + "\"";
          psi.WorkingDirectory = cd;
        }

        // Exectue the script.
        Process.Start(psi);
      }

  }

}
