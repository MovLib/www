# `/bin` : Essential user command binaries (for use by all users)

## Purpose

`/bin` contains commands that may be used by both the system administrator and by users, but which are required when no
other filesystems are mounted (e.g. in single user mode). It may also contain commands which are used indirectly by
scripts.

## Requirements

There must be no subdirectories in `/bin`.

## Weblinks

* [Linux Foundation: FHS 2.3](http://refspecs.linuxfoundation.org/FHS_2.3/fhs-2.3.html#BINESSENTIALUSERCOMMANDBINARIES)
