# `/var/public/uploads` : User uploaded files

## Purpose

`/var/public/uploads` contains files that were uploaded by users. The originals are stored in `/var/lib/uploads`, this
ensures that files can be restored if some are lost.

## Requirements

This directory should be a symbolic link that points to another directory that isn’t part of the source files that are
upgraded during rollout of new versions.
