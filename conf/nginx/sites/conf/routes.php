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


# ---------------------------------------------------------------------------------------------------------------------- movie(s)


location @movie_poster_upload {
  set $movlib_multipart 1;
  set $movlib_presenter "Upload\\Movie\\Poster";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $rp("/movies") ?> {
  set $movlib_presenter "Movies\\Show";
  try_files $movlib_cache @php;
}

location = <?= $r("/movie/create") ?> {
  set $movlib_presenter "Movie\\Create";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/movie") ?> {

  #
  # Movie
  #

  location ~ "^<?= $r("/movie/{0}", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Show";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/discussion", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Discussion";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/edit", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Edit";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Delete";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  #
  # Movie Posters
  #

  location ~ "^<?= $r("/movie/{0}/posters", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Gallery\\Posters";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/poster/upload", [ $idRegExp ]) ?>$" {
    error_page 413 @movie_poster_upload;
    set $movlib_multipart 0;
    set $movlib_presenter "Movie\\Upload\\Poster";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/poster/{1}", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageDetails\\PosterShow";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/poster/{1}/edit", [ $idRegExp, $idRegExp ]) ?>$" {
    error_page 413 @movie_poster_upload;
    set $movlib_multipart 0;
    set $movlib_presenter "Movie\\ImageDetails\\PosterEdit";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/poster/{1}/delete", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageDetails\\PosterDelete";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
  }

  #
  # Movie Lobby Cards
  #

  location ~ "^<?= $r("/movie/{0}/lobby-cards", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Gallery\\LobbyCards";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/lobby-card/upload", [ $idRegExp ]) ?>$" {
    error_page 413 @movie_lobby_card_upload;
    set $movlib_multipart 0;
    set $movlib_presenter "Movie\\Upload\\LobbyCard";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/lobby-card/{1}", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageDetails\\LobbyCardShow";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/lobby-card/{1}/edit", [ $idRegExp, $idRegExp ]) ?>$" {
    error_page 413 @movie_lobby_card_upload;
    set $movlib_multipart 0;
    set $movlib_presenter "Movie\\ImageDetails\\LobbyCardEdit";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/lobby-card/{1}/delete", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageDetails\\LobbyCardDelete";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
  }

  #
  # Movie Images
  #

  location ~ "^<?= $r("/movie/{0}/photos", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Gallery\\Photos";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/photo/upload", [ $idRegExp ]) ?>$" {
    error_page 413 @movie_photo_upload;
    set $movlib_multipart 0;
    set $movlib_presenter "Movie\\Upload\\Photo";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/photo/{1}", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageDetails\\PhotoShow";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/photo/{1}/edit", [ $idRegExp, $idRegExp ]) ?>$" {
    error_page 413 @movie_photo_upload;
    set $movlib_multipart 0;
    set $movlib_presenter "Movie\\ImageDetails\\PhotoEdit";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/photo/{1}/delete", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageDetails\\PhotoDelete";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
  }

  #
  # Release(s)
  #

  location ~ "^<?= $r("/movie/{0}/release/create", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Release\\Create";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/release/{1}", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Release\\Show";
    set $movlib_movie_id $1;
    set $movlib_release_id $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/release/{1}/discussion", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Release\\Discussion";
    set $movlib_movie_id $1;
    set $movlib_release_id $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/release/{1}/edit", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Release\\Edit";
    set $movlib_movie_id $1;
    set $movlib_release_id $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/release/{1}/delete", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Release\\Delete";
    set $movlib_movie_id $1;
    set $movlib_release_id $2;
    try_files $movlib_cache @php;
  }

  #
  # Movie Titles
  #

  location ~ "^<?= $r("/movie/{0}/titles", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\MovieTitles";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  #
  # History
  #

  location ~ "^<?= $r("/movie/{0}/history", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "History\\Movie\\MovieRevisions";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/diff/{1}", [ $idRegExp, "([a-f0-9]{40})" ]) ?>$" {
    set $movlib_presenter "History\\Movie\\MovieDiff";
    set $movlib_movie_id $1;
    set $movlib_revision_hash $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/titles/history", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "History\\Movie\\MovieTitlesRevisions";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/movie/{0}/titles/diff/{1}", [ $idRegExp, "([a-f0-9]{40})" ]) ?>$" {
    set $movlib_presenter "History\\Movie\\MovieTitlesDiff";
    set $movlib_movie_id $1;
    set $movlib_revision_hash $2;
    try_files $movlib_cache @php;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- person(s)


location @person_photos_upload {
  set $movlib_multipart 1;
  set $movlib_presenter "Upload\\Person\\Photo";
  include sites/conf/fastcgi_params.conf;
}

location @person_photo_update {
  set $movlib_multipart 1;
  set $movlib_presenter "Upload\\Person\\Photo";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $rp("/persons") ?> {
  set $movlib_presenter "Persons\\Show";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/person") ?> {

  #
  # Person
  #

  location = <?= $r("/person/create") ?> {
    set $movlib_presenter "Person\\Create";
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/person/{0}", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\Show";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^(<?= $r("/person/{0}", [ $idRegExp ]) ?>)/$" {
    return 301 $1;
  }

  location ~ "^<?= $r("/person/{0}/create", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\Create";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/person/{0}/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\Delete";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  #
  # Person Photos
  #

  location ~ "^<?= $r("/person/{0}/photos", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Gallery\\Person\\Photos";
    set $movlib_id $1;
    try_files $movlib_cache @gallery;
  }

  location ~ "^<?= $r("/person/{0}/photos/upload", [ $idRegExp ]) ?>$" {
    error_page 413 @person_photos_upload;
    set $movlib_multipart 0;
    set $movlib_presenter "Upload\\Person\\Photo";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/person/{0}/photo/{1}", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "ImageDetails\\Person\\Photo";
    set $movlib_person_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/person/{0}/photo/{1}/edit", [ $idRegExp, $idRegExp ]) ?>$" {
    error_page 413 @person_photo_update;
    set $movlib_multipart 0;
    set $movlib_presenter "Upload\\Person\\Photo";
    set $movlib_person_id $1;
    set $movlib_image_id $2;
    try_files $movlib_cache @php;
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

  location = <?= $r("/profile/sign-in") ?> {
    set $movlib_presenter "Profile\\SignIn";
    try_files $movlib_cache @php;
  }

  location = <?= $r("/profile/join") ?> {
    set $movlib_presenter "Profile\\Join";
    try_files $movlib_cache @php;
  }

  location = <?= $r("/profile/reset-password") ?> {
    set $movlib_presenter "Profile\\ResetPassword";
    include sites/conf/fastcgi_params.conf;
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

  location = <?= $r("/profile/danger-zone") ?> {
    set $movlib_presenter "Profile\\DangerZone";
    include sites/conf/fastcgi_params.conf;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- user(s)


location = <?= $rp("/users") ?> {
  set $movlib_presenter "Users\\Show";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/user") ?> {

  location ~ "^<?= $r("/user/{0}/collection", [ "(.+)" ]) ?>$" {
    set $movlib_presenter "User\\Collection";
    set $movlib_user_name $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/user/{0}/contact", [ "(.+)" ]) ?>$" {
    set $movlib_presenter "User\\Contact";
    set $movlib_user_name $1;
    try_files $movlib_cache @php;
  }

  # Must be last! Otherwise above location won't match.
  location ~ "^<?= $r("/user/{0}", [ "(.+)" ]) ?>$" {
    set $movlib_presenter "User\\Show";
    set $movlib_user_name $1;
    try_files $movlib_cache @php;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- style(s)


location = <?= $rp("/styles") ?> {
  set $movlib_presenter "Styles\\Show";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/style") ?> {

  location ~ "^<?= $r("/style/create") ?>$" {
    set $movlib_presenter "Style\\Create";
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/style/{0}", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Style\\Show";
    set $movlib_style_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/style/{0}/discussion", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Style\\Discussion";
    set $movlib_style_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/style/{0}/edit", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Style\\Edit";
    set $movlib_style_id $1;
    try_files $movlib_cache @php;
  }

  location ~"^<?= $r("/style/{0}/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Style\\Delete";
    set $movlib_style_id $1;
    try_files $movlib_cache @php;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- article(s)


location = <?= $rp("/articles") ?> {
  set $movlib_presenter "Articles\\Show";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/article") ?> {

  location ~ "^<?= $r("/article/create") ?>$" {
    set $movlib_presenter "Article\\Create";
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/article/{0}", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Article\\Show";
    set $movlib_article_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/article/{0}/discussion", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Article\\Discussion";
    set $movlib_article_id $1;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/article/{0}/edit", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Article\\Edit";
    set $movlib_article_id $1;
    try_files $movlib_cache @php;
  }

  location ~"^<?= $r("/article/{0}/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Article\\Delete";
    set $movlib_article_id $1;
    try_files $movlib_cache @php;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- help


location = <?= $rp("/help") ?> {
  set $movlib_presenter "Help\\Categories";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/help") ?> {

  location ~ "^<?= $r("/help/create") ?>$" {
    set $movlib_presenter "Help\\CreateCategory";
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/help/{0}/{0}/edit", [ "(.*)" ]) ?>$" {
    set $movlib_presenter "Help\\Edit";
    set $movlib_help_category $1;
    set $movlib_help_title $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/help/{0}/{0}/delete", [ "(.*)" ]) ?>$" {
    set $movlib_presenter "Help\\Delete";
    set $movlib_help_category $1;
    set $movlib_help_title $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/help/{0}/{0}", [ "(.*)" ]) ?>$" {
    set $movlib_presenter "Help\\Show";
    set $movlib_help_category $1;
    set $movlib_help_title $2;
    try_files $movlib_cache @php;
  }

  location ~ "^<?= $r("/help/{0}", [ "(.*)" ]) ?>$" {
    set $movlib_presenter "Help\\Category";
    set $movlib_help_category $1;
    try_files $movlib_cache @php;
  }

  return 404;
}


# ---------------------------------------------------------------------------------------------------------------------- system pages


<?php
$result = $db->query("SELECT `id`, COLUMN_GET(`dyn_titles`, ? AS CHAR(255)) FROM `protected_pages`", "s", [ $i18n->defaultLanguageCode ])->get_result();
while ($route = $result->fetch_row()):
  $id    = $route[0];
  $route = \MovLib\Data\FileSystem::sanitizeFilename($route[1]);
?>

location = <?= $r("/{$route}") ?> {
  set $movlib_presenter "ProtectedPage\\Show";
  set $movlib_id <?= $id ?>;
  try_files $movlib_cache @php;
}

location = <?= $r("/{0}/edit", [ $route ]) ?> {
  set $movlib_presenter "ProtectedPage\\Edit";
  set $movlib_id <?= $id ?>;
  try_files $movlib_cache @php;
}

location = <?= $r("/{0}/delete", [ $route ]) ?> {
  set $movlib_presenter "ProtectedPage\\Delete";
  set $movlib_id <?= $id ?>;
  try_files $movlib_cache @php;
}
<?php endwhile ?>
