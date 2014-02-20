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
$ wget -O ~/.ssh/authorized_keys https://raw.github.com/mitchellh/vagrant/master/keys/vagrant.pub
$ chmod 0600 ~/.ssh/authorized_keys
$ su -
```

Now we're root (we've set the password *vagrant*, in case you forgot)…

```
# aptitude -y update
# aptitude -y install ruby ruby-dev libopenssl-ruby1.8 irb ri rdoc zlib1g-dev libssl-dev libreadline-gplv2-dev curl unzip
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
# aptitude -y update
# aptitude -y install puppet facter
# echo 'vagrant ALL=NOPASSWD:ALL' > /etc/sudoers.d/vagrant
# echo 'UseDNS no' >> /etc/ssh/sshd_config
# echo 'AuthorizedKeysFile %h/.ssh/authorized_keys' >> /etc/ssh/sshd_config
# service ssh restart
# cat <<EOF > /etc/default/grub
# If you change this file, run 'update-grub' afterwards to update
# /boot/grub/grub.cfg.

GRUB_DEFAULT=0
GRUB_TIMEOUT=0
GRUB_DISTRIBUTOR=`lsb_release -i -s 2> /dev/null || echo Debian`
GRUB_CMDLINE_LINUX_DEFAULT="quiet"
GRUB_CMDLINE_LINUX="debian-installer=en_US"
EOF
# update-grub
# echo 'Welcome to your MovLib development virtual machine.' > /var/run/motd
# aptitude search virtualbox
```

If any `virtualbox` package is installed, purge it.

Note that you may have to replace the `3.2.0-4-all-amd64` string with your kernel version, use `uname -r` to find out
which version you are using and add the `all` part to it. Insert the VirtualBox guest additions disk in your drive
(but don't autostart it).

```
# aptitude -y install linux-headers-3.2.0-4-all-amd64 build-essential module-assistant
# m-a prepare
# sh /media/cdrom/autorun.sh
```

Restart your virtual machine after installing the guest additions. We'll only perform some clean-up now to make sure
that our base box is as small as possible. Start up a terminal again.

```
$ su -
# service vboxadd start
# aptitude -y purge linux-headers-3.2.0-4-all-amd64 build-essential module-assistant
# aptitude -y clean
# aptitude -y autoclean
# rm /var/lib/dhcp/*
# echo "pre-up sleep 2" >> /etc/network/interfaces
# rm -rf /usr/share/doc /usr/src/vboxguest* /usr/src/virtualbox-guest* /usr/src/linux-headers*
# find /var/cache -type f -exec rm -rf {} \;
# rm -rf /usr/share/locale/{af,am,ar,as,ast,az,bal,be,bg,bn,bn_IN,br,bs,byn,ca,cr,cs,csb,cy,da,de,de_AT,dz,el,en_AU,en_CA,eo,es,et,et_EE,eu,fa,fi,fo,fr,fur,ga,gez,gl,gu,haw,he,hi,hr,hu,hy,id,is,it,ja,ka,kk,km,kn,ko,kok,ku,ky,lg,lt,lv,mg,mi,mk,ml,mn,mr,ms,mt,nb,ne,nl,nn,no,nso,oc,or,pa,pl,ps,pt,pt_BR,qu,ro,ru,rw,si,sk,sl,so,sq,sr,sr*latin,sv,sw,ta,te,th,ti,tig,tk,tl,tr,tt,ur,urd,ve,vi,wa,wal,wo,xh,zh,zh_HK,zh_CN,zh_TW,zu}
# aptitude -y install zerofree
# init 1
```

Wait for the prompt that asks for your root password and enter it (I set it to *vagrant* if you followed all steps
your's is too).

```
# mount -o remount,ro /dev/sda1
# zerofree /dev/sda1
# date > /etc/vagrant_box_build_time
# shutdown -Ph now
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
* [Mike Griffin: Creating a Debian Wheezy base box for vagrant](https://mikegriffin.ie/blog/20130418-creating-a-debian-wheezy-base-box-for-vagrant/)
