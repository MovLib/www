# ----------------------------------------------------------------------------------------------------------------------
# This file is part of {@link https://github.com/MovLib MovLib}.
#
# Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
# The routes file that will be translated for each subdomain. Everything within this file has to be in English!
#
# AUTHOR: Richard Fussenegger <richard@fussenegger.info>
# AUTHOR: Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
# COPYRIGHT: © 2013-present, MovLib
# LICENSE: http://www.gnu.org/licenses/agpl.html AGPL-3.0
# SINCE: 0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------


# ---------------------------------------------------------------------------------------------------------------------- gallery


location @gallery_upload {
  set $movlib_presenter "GalleryUpload";
  include sites/conf/fastcgi.conf;
}


# ---------------------------------------------------------------------------------------------------------------------- movies


location @movies {
  set $movlib_presenter "Movies";
  include sites/conf/fastcgi.conf;
}

location ^~ <?= $r("/movies") ?> {

  location = <?= $r("/movies") ?> {
    try_files $movlib_cache @movies;
  }

  location = <?= $r("/movies") ?>/ {
    return 301 <?= $r("/movies") ?>;
  }

  location ~ ^<?= $r("/movies") ?>/([0-9]+)$ {
    return 301 <?= $r("/movie") ?>/$1;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- movie


location @movie {
  set $movlib_presenter "Movie";
  include sites/conf/fastcgi.conf;
}

location @release {
  set $movlib_presenter "Release";
  include sites/conf/fastcgi.conf;
}

location ^~ <?= $r("/movie") ?> {

  location = <?= $r("/movie") ?> {
    return 301 <?= $r("/movies") ?>;
  }

  location = <?= $r("/movie") ?>/ {
    return 301 <?= $r("/movies") ?>;
  }

  location ~ ^<?= $r("/movie") ?>/([0-9]+)$ {
    set $movlib_movie_id $1;
    try_files $movlib_cache @movie;
  }

  #
  # Galleries
  #

  location ~ ^<?= $r("/movie/{0}/posters", [ "([0-9]+)" ]) ?>$ {
    set $movlib_presenter "Gallery\\MovieGallery";
    set $movlib_action "movie";
    set $movlib_id $1;
    set $movlib_tab "poster";
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/movie/{0}/lobby-cards", [ "([0-9]+)" ]) ?>$ {
    set $movlib_presenter "Gallery\\MovieGallery";
    set $movlib_action "movie";
    set $movlib_id $1;
    set $movlib_tab "lobby-card";
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/movie/{0}/photos", [ "([0-9]+)" ]) ?>$ {
    set $movlib_presenter "Gallery\\MovieGallery";
    set $movlib_action "movie";
    set $movlib_id $1;
    set $movlib_tab "photo";
    try_files $movlib_cache @php;
  }

  #
  # Gallery Uploads
  #

  location ~ ^<?= $r("/movie/{0}/posters/upload", [ "([0-9]+)" ]) ?>$ {
    set $movlib_action "movie";
    set $movlib_id $1;
    set $movlib_tab "poster";
    try_files $movlib_cache @gallery_upload;
  }

  location ~ ^<?= $r("/movie/{0}/lobby-cards/upload", [ "([0-9]+)" ]) ?>$ {
    set $movlib_action "movie";
    set $movlib_id $1;
    set $movlib_tab "lobby-card";
    try_files $movlib_cache @gallery_upload;
  }

  location ~ ^<?= $r("/movie/{0}/photos/upload", [ "([0-9]+)" ]) ?>$ {
    set $movlib_action "movie";
    set $movlib_id $1;
    set $movlib_tab "photo";
    try_files $movlib_cache @gallery_upload;
  }

  #
  # Image Details
  #

  location ~ ^<?= $r("/movie/{0}/poster", [ "([0-9]+)" ]) ?>/([0-9]+)$ {
    set $movlib_presenter "Gallery\\MovieGalleryImage";
    set $movlib_action "movie";
    set $movlib_id $1;
    set $movlib_image_id $2;
    set $movlib_tab "poster";
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/movie/{0}/lobby-card", [ "([0-9]+)" ]) ?>/([0-9]+)$ {
    set $movlib_presenter "Gallery\\MovieGalleryImage";
    set $movlib_action "movie";
    set $movlib_id $1;
    set $movlib_image_id $2;
    set $movlib_tab "lobby-card";
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/movie/{0}/photo", [ "([0-9]+)" ]) ?>/([0-9]+)$ {
    set $movlib_presenter "Gallery\\MovieGalleryImage";
    set $movlib_action "movie";
    set $movlib_id $1;
    set $movlib_image_id $2;
    set $movlib_tab "photo";
    try_files $movlib_cache @php;
  }

  #
  # Releases
  #

  location ~ ^<?= $r("/movie/{0}/release", [ "([0-9]+)" ]) ?>/([0-9]+)$ {
    set $movlib_movie_id $1;
    set $movlib_release_id $2;
    try_files $movlib_cache @release;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- persons


location @persons {
  set $movlib_presenter "Persons";
  include sites/conf/fastcgi.conf;
}

location ^~ <?= $r("/persons") ?> {

  location = <?= $r("/persons") ?> {
    try_files $movlib_cache @persons;
  }

  location = <?= $r("/persons") ?>/ {
    return 301 <?= $r("/persons") ?>;
  }

  location ~ ^<?= $r("/persons") ?>/([0-9]+)$ {
    return 301 <?= $r("/person") ?>/$1;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- person


location @person {
  set $movlib_presenter "Person";
  include sites/conf/fastcgi.conf;
}

location ^~ <?= $r("/person") ?> {

  location = <?= $r("/person") ?> {
    return 301 <?= $r("/persons") ?>;
  }

  location = <?= $r("/person") ?>/ {
    return 301 <?= $r("/persons") ?>;
  }

  location ~ ^<?= $r("/person") ?>/([0-9]+)$ {
    set $movlib_person_id $1;
    try_files $movlib_cache @person;
  }

  location ~ ^<?= $r("/person/{0}/photos", [ "([0-9]+)" ]) ?>$ {
    set $movlib_action "person";
    set $movlib_id $1;
    try_files $movlib_cache @gallery;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- user
# Most user locations to not utilize the cache because they are only accessible for logged in users.


location @user {
  include sites/conf/fastcgi.conf;
}

location ^~ <?= $r("/user") ?> {

  location = <?= $r("/user") ?> {
    set $movlib_presenter "User\\Show";
    include sites/conf/fastcgi.conf;
  }

  location = <?= $r("/user/login") ?> {
    set $movlib_presenter "User\\Login";
    try_files $movlib_cache @user;
  }

  location = <?= $r("/user/sign-out") ?> {
    set $movlib_presenter "User\\Login";
    include sites/conf/fastcgi.conf;
  }

  location = <?= $r("/user/reset-password") ?> {
    set $movlib_presenter "User\\ResetPassword";
    include sites/conf/fastcgi.conf;
  }

  location = <?= $r("/user/registration") ?> {
    set $movlib_presenter "User\\Registration";
    try_files $movlib_cache @user;
  }

  location = <?= $r("/user/account-settings") ?> {
    set $movlib_presenter "User\\AccountSettings";
    include sites/conf/fastcgi.conf;
  }

  location = <?= $r("/user/notification-settings") ?> {
    set $movlib_presenter "User\\NotificationSettings";
    include sites/conf/fastcgi.conf;
  }

  location = <?= $r("/user/email-settings") ?> {
    set $movlib_presenter "User\\EmailSettings";
    include sites/conf/fastcgi.conf;
  }

  location = <?= $r("/user/sign-up") ?> {
    set $movlib_presenter "User\\PasswordSettings";
    include sites/conf/fastcgi.conf;
  }

  location = <?= $r("/user/password-settings") ?> {
    set $movlib_presenter "User\\PasswordSettings";
    include sites/conf/fastcgi.conf;
  }

  location = <?= $r("/user/danger-zone-settings") ?> {
    set $movlib_presenter "User\\DangerZoneSettings";
    include sites/conf/fastcgi.conf;
  }

  location ~ ^<?= $r("/user") ?>/(.+)$ {
    set $movlib_presenter "User\\Profile";
    set $movlib_user_name $1;
    try_files $movlib_cache @user;
  }

  return 404;
}
