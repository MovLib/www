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
# The routes file that will be translated for each subdomain. Everything within this file has to be in English!
#
# LINK:       https://github.com/MovLib/www/wiki/How-to-create-a-multipart-form
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# AUTHOR:     Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
# AUTHOR:     Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------


# ---------------------------------------------------------------------------------------------------------------------- movies


location ^~ <?= $r("/movies") ?> {

  location = <?= $r("/movies") ?> {
    set $movlib_presenter "Movies\\Show";
    try_files $movlib_cache @php;
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


location @movie_posters_upload {
  set $movlib_multipart 1;
  set $movlib_presenter "Movie\\UploadPoster";
  include sites/conf/fastcgi_params.conf;
}

location ^~ <?= $r("/movie") ?> {

  location = <?= $r("/movie") ?> {
    return 301 <?= $r("/movies") ?>;
  }

  location = <?= $r("/movie") ?>/ {
    return 301 <?= $r("/movies") ?>;
  }

  location ~ ^<?= $r("/movie") ?>/([0-9]+)$ {
    set $movlib_presenter "Movie\\Show";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  #
  # Galleries
  #

  location ~ ^<?= $r("/movie/{0}/posters", [ "([0-9]+)" ]) ?>$ {
    set $movlib_presenter "Gallery\\MoviePosterGallery";
    set $movlib_movie_id $1;
    set $movlib_tab "poster";
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/movie/{0}/lobby-cards", [ "([0-9]+)" ]) ?>$ {
    set $movlib_presenter "Gallery\\MovieLobbyCardGallery";
    set $movlib_movie_id $1;
    set $movlib_tab "lobby-card";
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/movie/{0}/photos", [ "([0-9]+)" ]) ?>$ {
    set $movlib_presenter "Gallery\\MoviePhotoGallery";
    set $movlib_movie_id $1;
    set $movlib_tab "photo";
    try_files $movlib_cache @php;
  }

  #
  # Gallery Uploads
  #

  location ~ ^<?= $r("/movie/{0}/posters/upload", [ "([0-9]+)" ]) ?>$ {
    error_page 413 @movie_posters_upload;
    set $movlib_multipart 0;
    set $movlib_presenter "Movie\\UploadPoster";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/movie/{0}/lobby-cards/upload", [ "([0-9]+)" ]) ?>$ {
    set $movlib_presenter "Movie\\UploadLobbyCard";
    set $movlib_movie_id $1;
    set $movlib_tab "lobby-card";
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/movie/{0}/photos/upload", [ "([0-9]+)" ]) ?>$ {
    set $movlib_presenter "Movie\\UploadPhoto";
    set $movlib_movie_id $1;
    set $movlib_tab "photo";
    try_files $movlib_cache @php;
  }

  #
  # Image Details
  #

  location ~ ^<?= $r("/movie/{0}/poster", [ "([0-9]+)" ]) ?>/([0-9]+)$ {
    set $movlib_presenter "ImageDetails\\MoviePoster";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/movie/{0}/lobby-card", [ "([0-9]+)" ]) ?>/([0-9]+)$ {
    set $movlib_presenter "ImageDetails\\MovieLobbyCardDetails";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    set $movlib_tab "lobby-card";
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/movie/{0}/photo", [ "([0-9]+)" ]) ?>/([0-9]+)$ {
    set $movlib_presenter "ImageDetails\\MoviePhotoDetails";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    set $movlib_tab "photo";
    try_files $movlib_cache @php;
  }

  #
  # Movie Titles
  #

  location ~ ^<?= $r("/movie/{0}/titles", [ "([0-9]+)" ]) ?>$ {
    set $movlib_presenter "Movie\\MovieTitles";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  #
  # History
  #

  location ~ ^<?= $r("/movie/{0}/history", [ "([0-9]+)" ]) ?>$ {
    set $movlib_presenter "History\\Movie\\MovieRevisions";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ '^<?= $r("/movie/{0}/diff", [ "([0-9]+)" ]) ?>/([a-f0-9]{40})$' {
    set $movlib_presenter "History\\Movie\\MovieDiff";
    set $movlib_movie_id $1;
    set $movlib_revision_hash $2;
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/movie/{0}/titles/history", [ "([0-9]+)" ]) ?>$ {
    set $movlib_presenter "History\\Movie\\MovieTitlesRevisions";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ '^<?= $r("/movie/{0}/titles/diff", [ "([0-9]+)" ]) ?>/([a-f0-9]{40})$' {
    set $movlib_presenter "History\\Movie\\MovieTitlesDiff";
    set $movlib_movie_id $1;
    set $movlib_revision_hash $2;
    try_files $movlib_cache @php;
  }

  #
  # Releases
  #

  location ~ ^<?= $r("/movie/{0}/release", [ "([0-9]+)" ]) ?>/([0-9]+)$ {
    set $movlib_presenter "Release";
    set $movlib_movie_id $1;
    set $movlib_release_id $2;
    try_files $movlib_cache @php;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- persons


location ^~ <?= $r("/persons") ?> {

  location = <?= $r("/persons") ?> {
    set $movlib_presenter "Persons";
    try_files $movlib_cache @php;
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


location ^~ <?= $r("/person") ?> {

  location = <?= $r("/person") ?> {
    return 301 <?= $r("/persons") ?>;
  }

  location = <?= $r("/person") ?>/ {
    return 301 <?= $r("/persons") ?>;
  }

  location ~ ^<?= $r("/person") ?>/([0-9]+)$ {
    set $movlib_presenter "Person";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/person/{0}/photos", [ "([0-9]+)" ]) ?>$ {
    set $movlib_action "person";
    set $movlib_id $1;
    try_files $movlib_cache @gallery;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- profile
# Most profile locations to not utilize the cache because they are only accessible for authenticated users.


location @profile_account_settings {
  set $movlib_multipart 1;
  set $movlib_presenter "Profile\\AccountSettings";
  include sites/conf/fastcgi_params.conf;
}

location ^~ <?= $r("/profile") ?> {

  location = <?= $r("/profile") ?> {
    set $movlib_presenter "Profile\\Show";
    include sites/conf/fastcgi_params.conf;
  }

  location = <?= $r("/profile") ?>/ {
    return 301 <?= $r("/profile") ?>;
  }

  location = <?= $r("/profile/sign-out") ?> {
    set $movlib_presenter "Profile\\SignOut";
    include sites/conf/fastcgi_params.conf;
  }

  location = <?= $r("/profile/account-settings") ?> {
    error_page 413 @profile_account_settings;
    set $movlib_multipart 0;
    set $movlib_presenter "Profile\\AccountSettings";
    include sites/conf/fastcgi_params.conf;
  }

  location = <?= $r("/profile/notification-settings") ?> {
    set $movlib_presenter "Profile\\NotificationSettings";
    include sites/conf/fastcgi_params.conf;
  }

  location = <?= $r("/profile/email-settings") ?> {
    set $movlib_presenter "Profile\\EmailSettings";
    include sites/conf/fastcgi_params.conf;
  }

  location = <?= $r("/profile/password-settings") ?> {
    set $movlib_presenter "Profile\\PasswordSettings";
    include sites/conf/fastcgi_params.conf;
  }

  location = <?= $r("/profile/danger-zone-settings") ?> {
    set $movlib_presenter "Profile\\DangerZoneSettings";
    include sites/conf/fastcgi_params.conf;
  }

  location = <?= $r("/profile/deactivated") ?> {
    set $movlib_presenter "Profile\\Deactivated";
    include sites/conf/fastcgi_params.conf;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- users


location ^~ <?= $r("/users") ?> {

  location = <?= $r("/users") ?> {
    set $movlib_presenter "Users\\Show";
    try_files $movlib_cache @php;
  }

  location = <?= $r("/users") ?>/ {
    return 301 <?= $r("/users") ?>;
  }

  location = <?= $r("/users/login") ?> {
    set $movlib_presenter "Users\\Login";
    try_files $movlib_cache @php;
  }

  location = <?= $r("/users/registration") ?> {
    set $movlib_presenter "Users\\Registration";
    try_files $movlib_cache @php;
  }

  location = <?= $r("/users/reset-password") ?> {
    set $movlib_presenter "Users\\ResetPassword";
    include sites/conf/fastcgi_params.conf;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- user


location ^~ <?= $r("/user") ?> {

  location = <?= $r("/user") ?> {
    return 301 <?= $r("/users") ?>;
  }

  location = <?= $r("/user") ?>/ {
    return 301 <?= $r("/users") ?>;
  }

  # A username cannot contain spaces nor slashes. The slashes are very important, otherwise it would be impossible for
  # us to have routes beneath the user's page.
  location ~ '^<?= $r("/user") ?>/([^/ ]+)$' {
    set $movlib_presenter "User\\Show";
    set $movlib_user_name $1;
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/user") ?>/(.+)/<?= $r("contact") ?>$ {
    set $movlib_presenter "User\\Contact";
    set $movlib_user_name $1;
    try_files $movlib_cache @php;
  }

  location ~ ^<?= $r("/user") ?>/(.+)/<?= $r("collection") ?>$ {
    set $movlib_presenter "User\\Collection";
    set $movlib_user_name $1;
    try_files $movlib_cache @php;
  }

  return 404;
}
