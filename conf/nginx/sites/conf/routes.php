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

# The route /my is the same for all languages! "My MovLib"
location = /my {
  set $movlib_presenter "Home";
  include sites/conf/fastcgi_params.conf;
}

location = / {
  # If the user is logged in, redirect to the dashboard.
  if ($http_cookie ~ "MOVSID") {
    return 302 /my;
  }

  # Otherwise file from cache or ask PHP.
  set $movlib_presenter "Home";
  try_files $movlib_cache/$movlib_presenter @php;
}


# ---------------------------------------------------------------------------------------------------------------------- movie(s)


location = <?= $rp("/movies") ?> {
  set $movlib_presenter "Movies\\Show";
  try_files $movlib_cache @php;
}

location = <?= $rp("/movies/charts") ?> {
  set $movlib_presenter "Movies\\Charts";
  try_files $movlib_cache @php;
}

location = <?= $r("/movie/create") ?> {
  set $movlib_presenter "Movie\\Create";
  try_files $movlib_cache @php;
}

location = <?= $r("/movie/random") ?> {
  set $movlib_presenter "Movie\\Random";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/movie") ?> {

  #
  # ---------------------------------------- Movie
  #

  location ~* "^<?= $r("/movie/{0}", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Show";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/history", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\History";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/discussion", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Discussion";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/edit", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Edit";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Delete";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  #
  # ---------------------------------------- Movie Backdrop(s)
  #

  location ~* "^<?= $rp("/movie/{0}/backdrops", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Images\\Backdrops";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/backdrop/upload", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageUpload";
    set $movlib_movie_id $1;
    set $movlib_image_class "Backdrop";
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/backdrop/{1}", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Image";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    set $movlib_image_class "Backdrop";
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/backdrop/{1}/edit", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageEdit";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    set $movlib_image_class "Backdrop";
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/backdrop/{1}/delete", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageDelete";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    set $movlib_image_class "Backdrop";
    try_files $movlib_cache @php;
  }

  #
  # ---------------------------------------- Movie Poster(s)
  #

  location ~* "^<?= $rp("/movie/{0}/posters", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Images\\Posters";
    set $movlib_movie_id $1;
    set $movlib_image_class "Poster";
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/poster/upload", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageUpload";
    set $movlib_movie_id $1;
    set $movlib_image_class "Poster";
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/poster/{1}", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Image";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    set $movlib_image_class "Poster";
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/poster/{1}/edit", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageEdit";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    set $movlib_image_class "Poster";
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/poster/{1}/delete", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageDelete";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    set $movlib_image_class "Poster";
    try_files $movlib_cache @php;
  }

  #
  # ---------------------------------------- Movie Lobby Card(s)
  #

  location ~* "^<?= $rp("/movie/{0}/lobby-cards", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Images\\LobbyCards";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/lobby-card/upload", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageUpload";
    set $movlib_movie_id $1;
    set $movlib_image_class "LobbyCard";
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/lobby-card/{1}", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Image";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    set $movlib_image_class "LobbyCard";
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/lobby-card/{1}/edit", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageEdit";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    set $movlib_image_class "LobbyCard";
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/lobby-card/{1}/delete", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\ImageDelete";
    set $movlib_movie_id $1;
    set $movlib_image_id $2;
    set $movlib_image_class "LobbyCard";
    try_files $movlib_cache @php;
  }

  #
  # ---------------------------------------- Release(s)
  #

  location ~* "^<?= $r("/movie/{0}/release/create", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Release\\Create";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/release/{1}", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Release\\Show";
    set $movlib_movie_id $1;
    set $movlib_release_id $2;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/release/{1}/discussion", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Release\\Discussion";
    set $movlib_movie_id $1;
    set $movlib_release_id $2;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/release/{1}/edit", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Release\\Edit";
    set $movlib_movie_id $1;
    set $movlib_release_id $2;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/release/{1}/delete", [ $idRegExp, $idRegExp ]) ?>$" {
    set $movlib_presenter "Release\\Delete";
    set $movlib_movie_id $1;
    set $movlib_release_id $2;
    try_files $movlib_cache @php;
  }

  #
  # ---------------------------------------- Movie Title(s)
  #

  location ~* "^<?= $rp("/movie/{0}/titles", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Movie\\Titles";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  #
  # ---------------------------------------- History
  #

  location ~* "^<?= $r("/movie/{0}/history", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "History\\Movie\\MovieRevisions";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/diff/{1}", [ $idRegExp, "([a-f0-9]{40})" ]) ?>$" {
    set $movlib_presenter "History\\Movie\\MovieDiff";
    set $movlib_movie_id $1;
    set $movlib_revision_hash $2;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/titles/history", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "History\\Movie\\MovieTitlesRevisions";
    set $movlib_movie_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/movie/{0}/titles/diff/{1}", [ $idRegExp, "([a-f0-9]{40})" ]) ?>$" {
    set $movlib_presenter "History\\Movie\\MovieTitlesDiff";
    set $movlib_movie_id $1;
    set $movlib_revision_hash $2;
    try_files $movlib_cache @php;
  }

  rewrite .* /error/NotFound last;
}


# ---------------------------------------------------------------------------------------------------------------------- releases


location = <?= $rp("/releases") ?> {
  set $movlib_presenter "Releases\\Show";
  try_files $movlib_cache @php;
}


# ---------------------------------------------------------------------------------------------------------------------- company/companies


location = <?= $rp("/companies") ?> {
  set $movlib_presenter "Companies\\Show";
  try_files $movlib_cache @php;
}

location = <?= $r("/company/create") ?> {
  set $movlib_presenter "Company\\Create";
  try_files $movlib_cache @php;
}

location = <?= $r("/company/random") ?> {
  set $movlib_presenter "Company\\Random";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/company") ?> {

  #
  # ---------------------------------------- Company
  #

  location ~* "^<?= $r("/company/{0}", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Company\\Show";
    set $movlib_company_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/company/{0}/discussion", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Company\\Discussion";
    set $movlib_company_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/company/{0}/edit", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Company\\Edit";
    set $movlib_company_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/company/{0}/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Company\\Delete";
    set $movlib_company_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/company/{0}/history", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Company\\History";
    set $movlib_company_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/company/{0}/movies", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Company\\Movies";
    set $movlib_company_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/company/{0}/series", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Company\\Series";
    set $movlib_company_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/company/{0}/releases", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Company\\Releases";
    set $movlib_company_id $1;
    try_files $movlib_cache @php;
  }

  #
  # ---------------------------------------- Company Photo
  #

  location ~* "^<?= $r("/company/{0}/photo", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Company\\Photo";
    set $movlib_company_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/company/{0}/photo/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Company\\PhotoDelete";
    set $movlib_company_id $1;
    try_files $movlib_cache @php;
  }

  rewrite .* /error/NotFound last;
}


# ---------------------------------------------------------------------------------------------------------------------- series


location = <?= $rp("/series") ?> {
  set $movlib_presenter "Series\\Index";
  try_files $movlib_cache @php;
}

location = <?= $rp("/series/charts") ?> {
  set $movlib_presenter "Series\\Charts";
  try_files $movlib_cache @php;
}

location = <?= $r("/series/create") ?> {
  set $movlib_presenter "Series\\Create";
  try_files $movlib_cache @php;
}

location = <?= $r("/series/random") ?> {
  set $movlib_presenter "Series\\Random";
  try_files $movlib_cache @php;
}


# ---------------------------------------------------------------------------------------------------------------------- genre(s)


location = <?= $rp("/genres") ?> {
  set $movlib_presenter "Genres\\Show";
  try_files $movlib_cache @php;
}

location = <?= $r("/genre/create") ?> {
  set $movlib_presenter "Genre\\Create";
  try_files $movlib_cache @php;
}

location = <?= $r("/genre/random") ?> {
  set $movlib_presenter "Genre\\Random";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/genre") ?> {

  #
  # ---------------------------------------- Genre
  #

  location ~* "^<?= $r("/genre/{0}", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Genre\\Show";
    set $movlib_genre_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/genre/{0}/discussion", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Genre\\Discussion";
    set $movlib_genre_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/genre/{0}/edit", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Genre\\Edit";
    set $movlib_genre_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/genre/{0}/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Genre\\Delete";
    set $movlib_genre_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/genre/{0}/history", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Genre\\History";
    set $movlib_genre_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/genre/{0}/movies", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Genre\\Movies";
    set $movlib_genre_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/genre/{0}/series", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Genre\\Series";
    set $movlib_genre_id $1;
    try_files $movlib_cache @php;
  }

  rewrite .* /error/NotFound last;
}


# ---------------------------------------------------------------------------------------------------------------------- award(s)


location = <?= $rp("/awards") ?> {
  set $movlib_presenter "Awards\\Show";
  try_files $movlib_cache @php;
}

location = <?= $r("/award/create") ?> {
  set $movlib_presenter "Award\\Create";
  try_files $movlib_cache @php;
}

location = <?= $r("/award/random") ?> {
  set $movlib_presenter "Award\\Random";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/award") ?> {

  #
  # ---------------------------------------- Award
  #

  location ~* "^<?= $r("/award/{0}", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Award\\Show";
    set $movlib_award_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/award/{0}/discussion", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Award\\Discussion";
    set $movlib_award_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/award/{0}/edit", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Award\\Edit";
    set $movlib_award_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/award/{0}/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Award\\Delete";
    set $movlib_award_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/award/{0}/history", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Award\\History";
    set $movlib_award_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/award/{0}/movies", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Award\\Movies";
    set $movlib_award_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/award/{0}/series", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Award\\Series";
    set $movlib_award_id $1;
    try_files $movlib_cache @php;
  }

  rewrite .* /error/NotFound last;
}


# ---------------------------------------------------------------------------------------------------------------------- job(s)


location = <?= $rp("/jobs") ?> {
  set $movlib_presenter "Jobs\\Show";
  try_files $movlib_cache @php;
}

location = <?= $r("/job/create") ?> {
  set $movlib_presenter "Job\\Create";
  try_files $movlib_cache @php;
}

location = <?= $r("/job/random") ?> {
  set $movlib_presenter "Job\\Random";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/job") ?> {

  #
  # ---------------------------------------- Job
  #

  location ~* "^<?= $r("/job/{0}", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Job\\Show";
    set $movlib_job_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/job/{0}/discussion", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Job\\Discussion";
    set $movlib_job_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/job/{0}/edit", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Job\\Edit";
    set $movlib_job_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/job/{0}/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Job\\Delete";
    set $movlib_job_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/job/{0}/history", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Job\\History";
    set $movlib_job_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/job/{0}/movies", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Job\\Movies";
    set $movlib_job_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/job/{0}/series", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Job\\Series";
    set $movlib_job_id $1;
    try_files $movlib_cache @php;
  }

  rewrite .* /error/NotFound last;
}


# ---------------------------------------------------------------------------------------------------------------------- person(s)


location = <?= $rp("/persons") ?> {
  set $movlib_presenter "Persons\\Show";
  try_files $movlib_cache @php;
}

location = <?= $r("/person/create") ?> {
  set $movlib_presenter "Person\\Create";
  try_files $movlib_cache @php;
}

location = <?= $r("/person/random") ?> {
  set $movlib_presenter "Person\\Random";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/person") ?> {

  #
  # ---------------------------------------- Person
  #

  location ~* "^<?= $r("/person/{0}", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\Show";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/person/{0}/discussion", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\Discussion";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/person/{0}/edit", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\Edit";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/person/{0}/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\Delete";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/person/{0}/history", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\History";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/person/{0}/movies", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\Movies";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/person/{0}/serials", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\Serials";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $rp("/person/{0}/releases", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\Releases";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  #
  # ---------------------------------------- Person Photo
  #

  location ~* "^<?= $r("/person/{0}/photo", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\Photo";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/person/{0}/photo/delete", [ $idRegExp ]) ?>$" {
    set $movlib_presenter "Person\\PhotoDelete";
    set $movlib_person_id $1;
    try_files $movlib_cache @php;
  }

  rewrite .* /error/NotFound last;
}


# ---------------------------------------------------------------------------------------------------------------------- profile
# Most profile locations do not utilize the cache because they are only accessible for authenticated users.


location = <?= $r("/profile") ?> {
  set $movlib_presenter "Profile\\Show";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $r("/profile/account-settings") ?> {
  set $movlib_presenter "Profile\\AccountSettings";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $r("/profile/collection") ?> {
  set $movlib_presenter "Profile\\Collection";
  try_files $movlib_cache @php;
}

location = <?= $r("/profile/danger-zone") ?> {
  set $movlib_presenter "Profile\\DangerZone";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $r("/profile/email-settings") ?> {
  set $movlib_presenter "Profile\\EmailSettings";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $r("/profile/join") ?> {
  set $movlib_presenter "Profile\\Join";
  try_files $movlib_cache @php;
}

location = <?= $r("/profile/messages") ?> {
  set $movlib_presenter "Profile\\Messages";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $r("/profile/notification-settings") ?> {
  set $movlib_presenter "Profile\\NotificationSettings";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $r("/profile/lists") ?> {
  set $movlib_presenter "Profile\\Lists";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $r("/profile/password-settings") ?> {
  set $movlib_presenter "Profile\\PasswordSettings";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $r("/profile/reset-password") ?> {
  set $movlib_presenter "Profile\\ResetPassword";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $r("/profile/sign-in") ?> {
  set $movlib_presenter "Profile\\SignIn";
  try_files $movlib_cache @php;
}

location = <?= $r("/profile/sign-out") ?> {
  set $movlib_presenter "Profile\\SignOut";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $r("/profile/wantlist") ?> {
  set $movlib_presenter "Profile\\Wantlist";
  include sites/conf/fastcgi_params.conf;
}

location = <?= $r("/profile/watchlist") ?> {
  set $movlib_presenter "Profile\\Watchlist";
  include sites/conf/fastcgi_params.conf;
}


# ---------------------------------------------------------------------------------------------------------------------- user(s)


location = <?= $rp("/users") ?> {
  set $movlib_presenter "Users\\Show";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/user") ?> {

  location ~* "^<?= $r("/user/{0}/collection", [ "(.+)" ]) ?>$" {
    set $movlib_presenter "User\\Collection";
    set $movlib_user_name $1;
    try_files $movlib_cache @php;
  }

  location ~* "^<?= $r("/user/{0}/contact", [ "(.+)" ]) ?>$" {
    set $movlib_presenter "User\\Contact";
    set $movlib_user_name $1;
    try_files $movlib_cache @php;
  }

  # Must be last! Otherwise above location won't match.
  location ~* "^<?= $r("/user/{0}", [ "(.+)" ]) ?>$" {
    set $movlib_presenter "User\\Show";
    set $movlib_user_name $1;
    try_files $movlib_cache @php;
  }

  rewrite .* /error/NotFound last;
}


# ---------------------------------------------------------------------------------------------------------------------- search


location = <?= $r("/search") ?> {
  set $movlib_presenter "Search\\Show";
  try_files $movlib_cache @php;
}


# ---------------------------------------------------------------------------------------------------------------------- help


location = <?= $rp("/help") ?> {
  set $movlib_presenter "Help\\Categories";
  try_files $movlib_cache @php;
}

<?php
$stmt           = $db->query("SELECT `id`, COLUMN_GET(`dyn_titles`, ? AS CHAR(255)) AS `title` FROM `help_categories`", "s", [ $i18n->defaultLanguageCode ]);
$helpCategories = $stmt->get_result();
while ($helpCategory = $helpCategories->fetch_assoc()):
  $helpCategory["title"] = \MovLib\Data\FileSystem::sanitizeFilename($helpCategory["title"]);
?>

location = <?= $r("/help/{$helpCategory["title"]}") ?> {
  set $movlib_presenter "Help\\Category";
  set $movlib_help_category <?= $helpCategory["id"] ?>;
  try_files $movlib_cache @php;
}
<?php
endwhile;
$stmt->close();

$stmt = $db->query(
  "SELECT
    `help_articles`.`id` AS `article_id`,
    COLUMN_GET(`help_articles`.`dyn_titles`, ? AS CHAR(255)) AS `article_title`,
    `help_articles`.`category_id` AS `category_id`,
    COLUMN_GET(`help_categories`.`dyn_titles`, ? AS CHAR(255)) AS `category_title`
  FROM `help_articles`
  INNER JOIN `help_categories`
    ON `help_articles`.`category_id` = `help_categories`.`id`",
  "ss",
  [ $i18n->defaultLanguageCode, $i18n->defaultLanguageCode ]
);
$helpArticles = $stmt->get_result();
while ($helpArticle = $helpArticles->fetch_assoc()):
  $helpArticle["category_title"] = \MovLib\Data\FileSystem::sanitizeFilename($helpArticle["category_title"]);
  $helpArticle["article_title"]  = \MovLib\Data\FileSystem::sanitizeFilename($helpArticle["article_title"]);
?>

location = <?= $r("/help/{$helpArticle["category_title"]}/{$helpArticle["article_title"]}") ?> {
  set $movlib_presenter "Help\\Article";
  set $movlib_help_category <?= $helpArticle["category_id"] ?>;
  set $movlib_help_article <?= $helpArticle["article_id"] ?>;
  try_files $movlib_cache @php;
}

location = <?= $r("/help/{$helpArticle["category_title"]}/{$helpArticle["article_title"]}/edit") ?> {
  set $movlib_presenter "Help\\Edit";
  set $movlib_help_category <?= $helpArticle["category_id"] ?>;
  set $movlib_id <?= $helpArticle["article_id"] ?>;
  try_files $movlib_cache @php;
}
<?php
endwhile;
$stmt->close();
?>


# ---------------------------------------------------------------------------------------------------------------------- system pages

<?php
$stmt        = $db->query("SELECT `id`, COLUMN_GET(`dyn_titles`, ? AS CHAR(255)) AS `title`, `presenter` FROM `system_pages`", "s", [ $i18n->defaultLanguageCode ]);
$systemPages = $stmt->get_result();
while ($systemPage = $systemPages->fetch_assoc()):
  $systemPage["title"] = \MovLib\Data\FileSystem::sanitizeFilename($systemPage["title"]);
?>

location = <?= $r("/{$systemPage["title"]}") ?> {
  set $movlib_presenter "SystemPage\\<?= $systemPage["presenter"] ?>";
  set $movlib_id <?= $systemPage["id"] ?>;
  try_files $movlib_cache @php;
}

location = <?= $r("/{$systemPage["title"]}/edit") ?> {
  set $movlib_presenter "SystemPage\\Edit";
  set $movlib_id <?= $systemPage["id"] ?>;
  try_files $movlib_cache @php;
}
<?php
endwhile;
$stmt->close();
?>


# ---------------------------------------------------------------------------------------------------------------------- Country(ies)


location = <?= $rp("/countries") ?> {
  set $movlib_presenter "Countries\\Show";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/country") ?> {

  location ~* "^<?= $r("/country/{0}", [ $isoAlpha2RegExp ]) ?>$" {
    set $movlib_presenter "Country\\Show";
    set $movlib_id $1;
    try_files $movlib_cache @php;
  }
  <?php foreach (\MovLib\Presentation\Country\Filter::$filters as $id => $name): ?>

  location ~* "^<?= $rp("/country/{0}/{$name}", [ $isoAlpha2RegExp ]) ?>$" {
    set $movlib_presenter "Country\\Filter";
    set $movlib_id $1;
    set $movlib_filter <?= $id ?>;
    try_files $movlib_cache @php;
  }
  <?php endforeach ?>

  rewrite .* /error/NotFound last;
}


# ---------------------------------------------------------------------------------------------------------------------- Country(ies)


location = <?= $rp("/years") ?> {
  set $movlib_presenter "Countries\\Show";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/year") ?> {

  location ~* "^<?= $r("/year/{0}", [ "([0-9]{4})" ]) ?>$" {
    set $movlib_presenter "Year\\Show";
    set $movlib_id $1;
    try_files $movlib_cache @php;
  }
  <?php foreach (\MovLib\Presentation\Year\Filter::$filters as $id => $name): ?>

  location ~* "^<?= $rp("/year/{0}/{$name}", [ "([0-9]{4})" ]) ?>$" {
    set $movlib_presenter "Year\\Filter";
    set $movlib_id $1;
    set $movlib_filter <?= $id ?>;
    try_files $movlib_cache @php;
  }
  <?php endforeach ?>

  rewrite .* /error/NotFound last;
}


# ---------------------------------------------------------------------------------------------------------------------- Deletion(s)


location = <?= $rp("/deletion-requests") ?> {
  set $movlib_presenter "DeletionRequests";
  try_files $movlib_cache @php;
}
