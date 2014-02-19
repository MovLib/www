# Create Custom Base Box

How I created the Vagrant Base Box for MovLib:

## Prerequisite

1. Download and install [Oracle VirtualBox](https://www.virtualbox.org/)
2. Download and install [Vagrant](http://www.vagrantup.com/)
3. Download [Debian](http://www.debian.org/)

## VirtualBox Setup

1. Start VirtualBox and click *New* in the top left corner
2. Enter *MovLib* as *Name* and choose *Linux*, *Debian (64)*
3. Give the VM *4096 MB* of RAM
4. *Create a virtual hard drive now*
5. Hard drive file type: *VMDK (Virtual Machine Disk)*
6. Storage on physical hard drive: *Dynamically allocated*
7. File location and size, set the name to *MovLib* and allow up to *100 GB* (be sure to sanitize the file path)
8. Go to *Settings* of the newly created VM (Ctrl + S) and change to the *Advanced* tab of the *General* settings:
  * Sanitize the *Snapshot Folder*
  * Enable *Shared Clipboard*
  * Enable *Drag'n'Drop*
  * Leave the rest as is
9. Change to the *System* menu:
  * Uncheck *Floppy*
  * Chipset *ICH9*
  * Pointing Device *PS/2 Mouse*
  * Check *Enable I/O APIC*
10. Change to the *Processor* tab:
  * Set *Processor(s)* to 2
  * Check *Enable PAE/NX*
11. Change to the *Display* menu:
  * Video Memory *128 MB*
  * I leave the monitor count at *1*, this can easyil be customized via the *Vagrantfile*
12. Change to the *Storage* menu:
  * Change the type of the IDE drive to *ICH6* and check *Use Host I/O Cache*
  * Same with the SATA
  * Insert the Debian ISO Image you downloaded in the CD drive
13. Change to the *Audio* menu and disable it
14. Change to the *USB* menu and disable it
15. OK start up the VM

## Debian Setup

1. Use either *Install* or *Graphical Install*
2. Language *English - English*
3. Location *United States*
4. Keyboard *German* (well, we're all used to it)
5. Hostname *movlib*
6. Domain name *.org*
7. root password *vagrant*
8. User account full name, user name, and password *vagrant*
9. Time zone doesn't matter because we'll reset this to *UTC* later on
10. Partition disks *Guided - use entire disk*, keep everything at its defaults and write the changes to disk
11. Package manager *Austria* (you might want to choose the one closest to you)
12. Choose one of the FTP servers
13. No proxy
14. Popularity *Yes* (we like Debian)
15. Software selection, check the following:
  * Debian desktop environment
  * SSH server
  * Standard system utilities
16. GRUB *Yes*

## Debian Setup

We'll setup the base box including VirtualBox guest additions, Ruby, and Puppet. Let Debian shutdown and start, login
with the *vagrant* user and open a Terminal (Ctrl + Shift + T). I'll continue with bash code only now which you could
simply copy/paste of course.

```
$ mkdir -pm 0700 ~/.ssh/authorized_keys
$ wget -O ~/.ssh/authorized_keys/vagrant.pub https://raw.github.com/mitchellh/vagrant/master/keys/vagrant.pub
$ chmod 0600 ~/.ssh/authorized_keys
$ su -
# wget https://raw.github.com/jedi4ever/veewee/master/templates/Debian-7.3.0-amd64-netboot/base.sh
# sh base.sh
# date > /etc/vagrant_box_build_time
# echo 'Welcome to your MovLib development virtual machine.' > /var/run/motd
# aptitude -y purge virtualbox-ose-*
```

Now hit VirtualBox-Host-Key + D (or select *Insert Guest Additions CD image...*`from the *Devices* menu) and go the CD
ROM drive.

```
# cd /media/cdrom0
# sh VBoxLinuxAdditions.run uninstall
yes
# sh VBoxLinuxAdditions.run
```

I had to restart the VM at this point.

```
# /etc/init.d/vboxadd start
# aptitude -y install ruby ruby-dev libopenssl-ruby1.8 irb ri rdoc
# wget -O /tmp/rubygems-1.8.22.zip http://production.cf.rubygems.org/rubygems/rubygems-1.8.22.zip
# cd /tmp
# unzip rubygems-1.8.22.zip
# cd rubygems-1.8.22
# ruby setup.rb --no-format-executable
# rm -rf /tmp/rubygems-1.8.22*
# cd
# wget http://apt.puppetlabs.com/puppetlabs-release-wheezy.deb
# dpkg -i puppetlabs-release-wheezy.deb
# rm -f puppetlabs-release-wheezy.deb
# aptitude update
# aptitude -y install puppet facter
# aptitude -y purge linux-headers-$(uname -r) build-essential
# aptitude -y clean
# rm /var/lib/dhcp/*
# echo "pre-up sleep 2" >> /etc/network/interfaces
# dd if=/dev/zero of=/EMPTY bs=1M
# rm -f /EMPTY
```

## Vagrant Base Box

Shutdown the VM and execute the following command on the host machine:

```
$ vagrant package --base MovLib --output MovLib.box
```

That's it basically, you can test your base box locally if you want.

```
$ vagrant add MovLib MovLib.box
$ vagrant init
$ vagrant up
```

## Weblinks

* [Vagrant Documentation](http://docs.vagrantup.com/v2/boxes/base.html)
* [VeeWee templates](https://github.com/jedi4ever/veewee/tree/master/templates/Debian-7.3.0-amd64-netboot)
