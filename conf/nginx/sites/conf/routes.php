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
# COPYRIGHT: © 2013-present, MovLib
# LICENSE: http://www.gnu.org/licenses/agpl.html AGPL-3.0
# SINCE: 0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------


# ---------------------------------------------------------------------------------------------------------------------- gallery


# This gets used within movies and persons in different variants for different galleries.
location @gallery {
  set $movlib_presenter "Gallery";
  include sites/conf/fastcgi.conf;
}


# ---------------------------------------------------------------------------------------------------------------------- movies


location @movies {
  set $movlib_presenter "Movies";
  include sites/conf/fastcgi.conf;
}

location ^~ /<?= $r("movies") ?> {

  location = /<?= $r("movies") ?> {
    try_files $movlib_cache @movies;
  }

  location = /<?= $r("movies") ?>/ {
    return 301 /<?= $r("movies") ?>;
  }

  location ~ ^/<?= $r("movies") ?>/([0-9]+)$ {
    return 301 /<?= $r("movie") ?>/$1;
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

location ^~ /<?= $r("movie") ?> {

  location = /<?= $r("movie") ?> {
    return 301 /<?= $r("movies") ?>;
  }

  location = /<?= $r("movie") ?>/ {
    return 301 /<?= $r("movies") ?>;
  }

  location ~ ^/<?= $r("movie") ?>/([0-9]+)$ {
    set $movlib_movie_id $1;
    try_files $movlib_cache @movie;
  }

  location ~ ^/(<?= $r("movie") ?>)/([0-9]+)/(<?= $r("poster") ?>|<?= $r("lobby-card") ?>|<?= $r("photo") ?>)-<?= $r("gallery") ?>$ {
    set $movlib_action $1;
    set $movlib_id $2;
    set $movlib_tab $3;
    try_files $movlib_cache @gallery;
  }

  location ~ ^/<?= $r("movie") ?>/([0-9]+)/<?= $r("release") ?>/([0-9]+)$ {
    set $movlib_movie_id $1;
    set $movlib_release_id $2;
    try_files $movlib_cache @release;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- persons


location ^~ /<?= $r("persons") ?> {
  set $movlib_presenter "Persons";

  location = /<?= $r("persons") ?> {
    try_files $movlib_cache @php;
  }

  location = /<?= $r("persons") ?>/ {
    return 301 /<?= $r("persons") ?>;
  }

  location ~ ^/<?= $r("persons") ?>/([0-9]+)$ {
    return 301 /<?= $r("person") ?>/$2;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- person


location @person {
  set $movlib_presenter "Person";
  include sites/conf/fastcgi.conf;
}

location ^~ /<?= $r("person") ?> {

  location = /<?= $r("person") ?> {
    return 301 /<?= $r("persons") ?>;
  }

  location = /<?= $r("person") ?>/ {
    return 301 /<?= $r("persons") ?>;
  }

  location ~ ^/<?= $r("person") ?>/([0-9]+)$ {
    set $movlib_person_id $1;
    try_files $movlib_cache @person;
  }

  location ~ ^/(<?= $r("person") ?>)/([0-9]+)/<?= $r("photo-gallery") ?>$ {
    set $movlib_action $1;
    set $movlib_id $2;
    try_files $movlib_cache @gallery;
  }
  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- user


location @user {
  set $movlib_presenter "User";
  include sites/conf/fastcgi.conf;
}

location ^~ /<?= $r("user") ?> {

  location = /<?= $r("user") ?> {
    try_files $movlib_cache @user;
  }

  location = /<?= $r("user/login") ?> {
    set $movlib_action "Login";
    try_files $movlib_cache @user;
  }

  location = /<?= $r("user/logout") ?> {
    set $movlib_action "Logout";
    try_files $movlib_cache @user;
  }

  location = /<?= $r("user/reset-password") ?> {
    set $movlib_action "ResetPassword";
    try_files $movlib_cache @user;
  }

  location = /<?= $r("user/register") ?> {
    set $movlib_action "Register";
    try_files $movlib_cache @user;
  }

  location ~ ^/<?= $r("user/register") ?>=([0-9a-z]*)$ {
    set $movlib_action "Register";
    set $movlib_token $1;
    try_files $movlib_cache @user;
  }

  location ~ ^/<?= $r("user/reset-password") ?>=([0-9a-z]*)$ {
    set $movlib_action "ResetPassword";
    set $movlib_token $1;
    try_files $movlib_cache @user;
  }

  location ~ ^/<?= $r("user") ?>/(<?= $r("account") ?>|<?= $r("notification") ?>|<?= $r("mail") ?>|<?= $r("password") ?>|<?= $r("dangerzone") ?>)-<?= $r("settings") ?>$ {
    set $movlib_action "Settings";
    set $movlib_tab $1;
    try_files $movlib_cache @user;
  }

  location ~ ^/<?= $r("user") ?>/(.+)$ {
    set $movlib_action "Profile";
    set $movlib_user_name $1;
    try_files $movlib_cache @user;
  }

  return 404;
}
