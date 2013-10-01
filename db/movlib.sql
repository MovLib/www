SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

DROP SCHEMA IF EXISTS `movlib` ;
CREATE SCHEMA IF NOT EXISTS `movlib` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
SHOW WARNINGS;
USE `movlib` ;

-- -----------------------------------------------------
-- Table `movlib`.`movies`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies` (
  `movie_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The movie’s unique ID.' ,
  `original_title` BLOB NOT NULL COMMENT 'The movie\'s original title.' ,
  `rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The Bayes\'theorem rating of this movie.\n\nrating = (s / (s + m)) * N + (m / (s + m)) * K\n\nN: arithmetic mean rating\ns: vote count\nm: minimum vote count\nK: arithmetic mean vote\n\nThe same formula is used by IMDb and OFDb.' ,
  `mean_rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s arithmetic mean rating.' ,
  `votes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s vote count.' ,
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'TRUE (1) if this movie was deleted, default is FALSE (0).' ,
  `year` SMALLINT NULL COMMENT 'The movie’s initial release year.' ,
  `runtime` SMALLINT UNSIGNED NULL COMMENT 'The movie’s approximate runtime in minutes.' ,
  `rank` BIGINT UNSIGNED NULL COMMENT 'The movie’s global rank.' ,
  `dyn_synopses` BLOB NOT NULL COMMENT 'The movie’s translatable synopses.' ,
  `created` TIMESTAMP NOT NULL COMMENT 'The timestamp this movie was created.' ,
  `commit` CHAR(40) NULL COMMENT 'The movie\'s last commit sha-1 hash.' ,
  PRIMARY KEY (`movie_id`) ,
  UNIQUE INDEX `uq_movies_rank` (`rank` ASC) )
COMMENT = 'Contains all movie’s data.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`genres`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`genres` (
  `genre_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The genre’s unique ID.' ,
  `name` VARCHAR(100) NOT NULL COMMENT 'The genre’s unique English name.' ,
  `description` BLOB NOT NULL COMMENT 'The genre’s English description.' ,
  `dyn_names` BLOB NOT NULL COMMENT 'The genre name’s translations.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The genre description’s translations.' ,
  PRIMARY KEY (`genre_id`) ,
  UNIQUE INDEX `uq_genres_name` (`name` ASC) )
COMMENT = 'Contains all movie genres.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_genres`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_genres` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.' ,
  `genre_id` INT UNSIGNED NOT NULL COMMENT 'The genre’s unique ID.' ,
  PRIMARY KEY (`movie_id`, `genre_id`) ,
  INDEX `fk_movies_genres_genres` (`genre_id` ASC) ,
  INDEX `fk_movies_genres_movies` (`movie_id` ASC) ,
  CONSTRAINT `fk_movies_genres_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_genres_genres`
    FOREIGN KEY (`genre_id` )
    REFERENCES `movlib`.`genres` (`genre_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'A movie has many genres, a genre has many movies.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`styles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`styles` (
  `style_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The style’s unique ID.' ,
  `name` VARCHAR(100) NOT NULL COMMENT 'The style’s unique English name.' ,
  `description` BLOB NOT NULL COMMENT 'The style’s English description.' ,
  `dyn_names` BLOB NOT NULL COMMENT 'The style name’s translations.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The style description’s translations.' ,
  PRIMARY KEY (`style_id`) ,
  UNIQUE INDEX `uq_name` (`name` ASC) )
COMMENT = 'Contains all movie styles.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_styles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_styles` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.' ,
  `style_id` INT UNSIGNED NOT NULL COMMENT 'The style’s unique ID.' ,
  PRIMARY KEY (`movie_id`, `style_id`) ,
  INDEX `fk_movies_styles_styles` (`style_id` ASC) ,
  INDEX `fk_movies_styles_movies` (`movie_id` ASC) ,
  CONSTRAINT `fk_movies_styles_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_styles_styles`
    FOREIGN KEY (`style_id` )
    REFERENCES `movlib`.`styles` (`style_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'A movie has many styles, a style has many movies.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`countries`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`countries` (
  `country_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The country’s unique ID.' ,
  `iso_alpha-2` CHAR(2) NOT NULL COMMENT 'The country’s ISO 3166-1 alpha-2 code.' ,
  `name` TINYTEXT NOT NULL COMMENT 'The country’s unique English name.' ,
  `dyn_translations` BLOB NOT NULL COMMENT 'The country’s translated name.' ,
  PRIMARY KEY (`country_id`) ,
  UNIQUE INDEX `uq_countries_iso_alpha-2` (`iso_alpha-2` ASC) )
COMMENT = 'Contains all ISO 3166-1 alpha-2 countries.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`users` (
  `user_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The user’s unique ID.' ,
  `name` VARCHAR(40) NOT NULL COMMENT 'The user’s unique name.' ,
  `email` VARCHAR(254) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NOT NULL COMMENT 'The user’s unique email address.' ,
  `password` TINYBLOB NOT NULL COMMENT 'The user’s unique password (hashed).' ,
  `access` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp for user’s last access.' ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp for user’s creation datetime.' ,
  `login` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp for user’s last login.' ,
  `private` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag if the user is willing to display their private date on the profile page.' ,
  `deactivated` TINYINT(1) NOT NULL DEFAULT false COMMENT 'TRUE if this account was deleted or blocked, default is FALSE.' ,
  `time_zone_id` VARCHAR(30) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NOT NULL DEFAULT 'UTC' COMMENT 'User’s time zone ID.' ,
  `edits` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The user’s edit counter.' ,
  `dyn_profile` BLOB NOT NULL COMMENT 'The user’s profile text (translatable).' ,
  `sex` TINYINT NOT NULL DEFAULT 0 COMMENT 'The user\'s sex according to ISO 5218.' ,
  `system_language_code` CHAR(2) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NOT NULL DEFAULT 'en' COMMENT 'The user’s preferred system language’s code (e.g. en).' ,
  `avatar_name` VARCHAR(40) NOT NULL COMMENT 'The avatar\'s name (md5 hash of the user\'s name).' ,
  `avatar_changed` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The timestamp the avatar has been changed.' ,
  `avatar_type` TINYINT NULL COMMENT 'The file extension of the user\'s avatar without leading dot.' ,
  `birthday` DATE NULL COMMENT 'The user\'s date of birth.' ,
  `country_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'The user’s country.' ,
  `real_name` TINYBLOB NULL DEFAULT NULL COMMENT 'The user’s real name.' ,
  `website` TINYBLOB NULL DEFAULT NULL COMMENT 'The user’s website URL.' ,
  `facebook` TINYBLOB NULL DEFAULT NULL COMMENT 'The user’s Facebook data.' ,
  `google_plus` TINYBLOB NULL DEFAULT NULL COMMENT 'The user’s Google+ data.' ,
  `twitter` TINYBLOB NULL DEFAULT NULL COMMENT 'The user’s Twitter data.' ,
  PRIMARY KEY (`user_id`) ,
  INDEX `fk_users_countries` (`country_id` ASC) ,
  UNIQUE INDEX `uq_users_name` (`name` ASC) ,
  UNIQUE INDEX `uq_users_mail` (`email` ASC) ,
  CONSTRAINT `fk_users_countries`
    FOREIGN KEY (`country_id` )
    REFERENCES `movlib`.`countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_general_ci
COMMENT = 'Contains all user related data.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons` (
  `person_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The person’s unique ID.' ,
  `name` BLOB NOT NULL COMMENT 'The person’s full name.' ,
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'TRUE (1) if this person was deleted, default is FALSE (0).' ,
  `born_name` MEDIUMBLOB NULL COMMENT 'The person’s born name.' ,
  `birthdate` DATE NULL COMMENT 'The person’s date of birth.' ,
  `deathdate` DATE NULL COMMENT 'The person’s date of death.' ,
  `country` CHAR(2) NULL COMMENT 'The person’s birth country.' ,
  `city` TINYBLOB NULL COMMENT 'The person’s birth city.' ,
  `region` TINYBLOB NULL COMMENT 'The person’s birth region.' ,
  `sex` TINYINT NOT NULL DEFAULT 0 COMMENT 'The person\'s sex according to ISO 5218.' ,
  `rank` BIGINT UNSIGNED NULL COMMENT 'The person\'s global rank.' ,
  `dyn_aliases` BLOB NOT NULL COMMENT 'The person’s aliases.' ,
  `dyn_biographies` BLOB NOT NULL COMMENT 'The person’s translatable biographies.' ,
  `dyn_links` BLOB NOT NULL COMMENT 'The person’s external weblinks.' ,
  `commit` CHAR(40) NULL COMMENT 'The movie\'s last commit sha-1 hash.' ,
  PRIMARY KEY (`person_id`) )
COMMENT = 'Contains all person related data.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`jobs`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`jobs` (
  `job_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The job’s unique ID.' ,
  `title` VARCHAR(100) NOT NULL COMMENT 'The job’s unique English title.' ,
  `description` BLOB NOT NULL COMMENT 'The job’s English description.' ,
  `dyn_titles` BLOB NOT NULL COMMENT 'The job title’s translations.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The job description’s translations.' ,
  PRIMARY KEY (`job_id`) ,
  UNIQUE INDEX `uq_jobs_title` (`title` ASC) )
COMMENT = 'Contains all job related data.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`companies`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`companies` (
  `company_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The company’s unique ID.' ,
  `name` BLOB NOT NULL COMMENT 'The company’s unique name.' ,
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'TRUE (1) if this company was deleted, default is FALSE (0).' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The company’s translatable descriptions.' ,
  `dyn_links` BLOB NOT NULL COMMENT 'The company’s external links.' ,
  PRIMARY KEY (`company_id`) )
COMMENT = 'Contains all company related data.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_crew`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_crew` (
  `crew_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The crew’s unique ID within the movie.' ,
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.' ,
  `job_id` INT UNSIGNED NOT NULL COMMENT 'The job’s unique ID.' ,
  `company_id` BIGINT UNSIGNED NULL COMMENT 'The company’s unique ID.' ,
  `person_id` BIGINT UNSIGNED NULL COMMENT 'The person’s unique ID.' ,
  PRIMARY KEY (`crew_id`, `movie_id`) ,
  INDEX `fk_movies_crew_movies` (`movie_id` ASC) ,
  INDEX `fk_movies_crew_jobs` (`job_id` ASC) ,
  INDEX `fk_movies_crew_companies` (`company_id` ASC) ,
  INDEX `fk_movies_crew_persons` (`person_id` ASC) ,
  CONSTRAINT `fk_movies_crew_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_crew_jobs`
    FOREIGN KEY (`job_id` )
    REFERENCES `movlib`.`jobs` (`job_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_crew_companies`
    FOREIGN KEY (`company_id` )
    REFERENCES `movlib`.`companies` (`company_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_crew_persons`
    FOREIGN KEY (`person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'Contains the crew of a movie.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`licenses`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`licenses` (
  `license_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The license\'s unique ID.' ,
  `name` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The license\'s english name.' ,
  `description` BLOB NOT NULL COMMENT 'The license\'s english description.' ,
  `dyn_names` BLOB NOT NULL COMMENT 'The license\'s translated names.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The license\'s translated descriptions.' ,
  `url` VARCHAR(255) NULL COMMENT 'The license\'s URL.' ,
  `abbr` VARCHAR(20) NULL COMMENT 'The license\'s abbreviation.' ,
  `icon_extension` VARCHAR(5) NULL COMMENT 'The file extension of the license icon.' ,
  `icon_hash` CHAR(10) NULL COMMENT 'The hash of the license icon.' ,
  `admin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Flag which determines whether this license can be edited by ever user (FALSE - 0) or only by admins (TRUE - 1).\nDefaults to 0.' ,
  PRIMARY KEY (`license_id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_general_ci
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_photos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons_photos` (
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique ID.' ,
  `image_id` BIGINT UNSIGNED NOT NULL COMMENT 'The photo’s unique ID within the person.' ,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Unique ID of the user who uploaded this photo.' ,
  `license_id` INT UNSIGNED NOT NULL COMMENT 'The license\'s unique ID this image is under.' ,
  `filename` TINYBLOB NOT NULL COMMENT 'The photo’s filename without extensions.' ,
  `width` SMALLINT NOT NULL COMMENT 'The photo’s width.' ,
  `height` SMALLINT NOT NULL COMMENT 'The photo’s height.' ,
  `size` INT NOT NULL COMMENT 'The photo’s size in Bytes.' ,
  `ext` VARCHAR(5) NOT NULL COMMENT 'The photo’s extension without leading dot.' ,
  `changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The last time this photo was updated.' ,
  `created` TIMESTAMP NOT NULL COMMENT 'The photo’s creation time.' ,
  `upvotes` BIGINT UNSIGNED NOT NULL COMMENT 'The photo’s upvotes.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The photo’s translatable descriptions.' ,
  `hash` BINARY(16) NOT NULL COMMENT 'The file\'s modification timestamp (UNIX format) for cache busting.' ,
  `source` BLOB NOT NULL COMMENT 'The photo\'s source.' ,
  PRIMARY KEY (`person_id`, `image_id`) ,
  INDEX `fk_persons_photos_persons` (`person_id` ASC) ,
  INDEX `fk_persons_photos_images` (`image_id` ASC) ,
  INDEX `fk_persons_photos_users` (`user_id` ASC) ,
  INDEX `fk_persons_photos_licenses1_idx` (`license_id` ASC) ,
  CONSTRAINT `fk_persons_photos_persons`
    FOREIGN KEY (`person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_photos_users`
    FOREIGN KEY (`user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_photos_licenses`
    FOREIGN KEY (`license_id` )
    REFERENCES `movlib`.`licenses` (`license_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'Extends images table with unique person’s ID.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`companies_images`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`companies_images` (
  `company_id` BIGINT UNSIGNED NOT NULL COMMENT 'The company image’s unique ID.' ,
  `image_id` BIGINT UNSIGNED NOT NULL COMMENT 'The company image’s unique ID within the company.' ,
  `type` TINYINT NOT NULL COMMENT 'The company image’s type (enum from Data class).' ,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Unique ID of the user who uploaded this company image.' ,
  `license_id` INT UNSIGNED NOT NULL ,
  `filename` TINYBLOB NOT NULL COMMENT 'The company image’s filename without extensions.' ,
  `width` SMALLINT NOT NULL COMMENT 'The company image’s width.' ,
  `height` SMALLINT NOT NULL COMMENT 'The company image’s height.' ,
  `size` INT NOT NULL COMMENT 'company image’s size in Bytes.' ,
  `ext` VARCHAR(5) NOT NULL COMMENT 'The company image’s extension without leading dot.' ,
  `changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The last time this company image was updated.' ,
  `created` TIMESTAMP NOT NULL COMMENT 'The company image’s creation time.' ,
  `upvotes` BIGINT UNSIGNED NOT NULL COMMENT 'The company image’s upvotes.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The company image’s translatable descriptions.' ,
  `hash` BINARY(16) NOT NULL COMMENT 'The file\'s modification timestamp (UNIX format) for cache busting.' ,
  `source` BLOB NOT NULL COMMENT 'The image\'s source.' ,
  PRIMARY KEY (`company_id`, `image_id`, `type`) ,
  INDEX `fk_companies_images_companies` (`company_id` ASC) ,
  INDEX `fk_companies_images_images` (`image_id` ASC) ,
  INDEX `fk_companies_images_users` (`user_id` ASC) ,
  INDEX `fk_companies_images_licenses1_idx` (`license_id` ASC) ,
  CONSTRAINT `fk_companies_images_companies`
    FOREIGN KEY (`company_id` )
    REFERENCES `movlib`.`companies` (`company_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_companies_images_users`
    FOREIGN KEY (`user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_companies_images_licenses`
    FOREIGN KEY (`license_id` )
    REFERENCES `movlib`.`licenses` (`license_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'Extends images table with unique company’s ID.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`ratings`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`ratings` (
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique ID.' ,
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.' ,
  `rating` TINYINT UNSIGNED NOT NULL COMMENT 'The rating itself (between 1 and 5).' ,
  PRIMARY KEY (`user_id`, `movie_id`) ,
  INDEX `fk_ratings_movies` (`movie_id` ASC) ,
  INDEX `fk_ratings_users` (`user_id` ASC) ,
  CONSTRAINT `fk_ratings_users`
    FOREIGN KEY (`user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ratings_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'A movie has many ratings, a user has many ratings.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_cast`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_cast` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.' ,
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique ID.' ,
  `roles` BLOB NULL COMMENT 'The names of the role the person played in the movie.' ,
  PRIMARY KEY (`movie_id`, `person_id`) ,
  INDEX `fk_movies_cast_movies` (`movie_id` ASC) ,
  INDEX `fk_movies_cast_persons` (`person_id` ASC) ,
  CONSTRAINT `fk_movies_cast_persons`
    FOREIGN KEY (`person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_cast_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'A movie has many actors, an actor has many movies.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`messages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`messages` (
  `message_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The message’s unique ID.' ,
  `message` TEXT NOT NULL COMMENT 'The message’s unique English pattern.' ,
  `comment` BLOB NULL COMMENT 'The message’s optional comment for translators.' ,
  `dyn_translations` BLOB NOT NULL COMMENT 'The message’s translations.' ,
  PRIMARY KEY (`message_id`) )
COMMENT = 'Contains all translatable system messages.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`routes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`routes` (
  `route_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The route’s unique ID.' ,
  `route` VARCHAR(254) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NOT NULL COMMENT 'The route’s unique English pattern.' ,
  `dyn_translations` BLOB NOT NULL COMMENT 'The route’s translations.' ,
  PRIMARY KEY (`route_id`) ,
  UNIQUE INDEX `uq_routes_route` (`route` ASC) )
COMMENT = 'Contains all routes (relative URIs).'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`languages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`languages` (
  `language_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The language’s unique ID.' ,
  `iso_alpha-2` CHAR(2) NOT NULL COMMENT 'The language’s ISO 639-1 alpha-2 code.' ,
  `name` TINYTEXT NOT NULL COMMENT 'The language’s unique English name.' ,
  `dyn_translations` BLOB NOT NULL COMMENT 'The language’s translated name.' ,
  PRIMARY KEY (`language_id`) ,
  UNIQUE INDEX `unique_languages_iso_alpha-2` (`iso_alpha-2` ASC) )
COMMENT = 'Contains all ISO 639-1 alpha-2 languages.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_countries`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_countries` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.' ,
  `country_id` INT UNSIGNED NOT NULL COMMENT 'The country’s unique ID.' ,
  PRIMARY KEY (`movie_id`, `country_id`) ,
  INDEX `fk_movies_countries_countries` (`country_id` ASC) ,
  INDEX `fk_movies_countries_movies` (`movie_id` ASC) ,
  CONSTRAINT `fk_movies_countries_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_countries_countries`
    FOREIGN KEY (`country_id` )
    REFERENCES `movlib`.`countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'A movie has many countries, a country has many movies.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_languages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_languages` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.' ,
  `language_id` INT UNSIGNED NOT NULL COMMENT 'The language’s unique ID.' ,
  PRIMARY KEY (`movie_id`, `language_id`) ,
  INDEX `fk_movies_languages_languages` (`language_id` ASC) ,
  INDEX `fk_movies_languages_movies` (`movie_id` ASC) ,
  CONSTRAINT `fk_movies_languages_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_languages_languages`
    FOREIGN KEY (`language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'A movie has many languages, a language has many movies.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_directors`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_directors` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.' ,
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique ID.' ,
  PRIMARY KEY (`movie_id`, `person_id`) ,
  INDEX `fk_movies_directors_persons` (`person_id` ASC) ,
  INDEX `fk_movies_directors_movies` (`movie_id` ASC) ,
  CONSTRAINT `fk_movies_directors_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_directors_persons`
    FOREIGN KEY (`person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'A movie has many directors, a director has many movies.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`tmp`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`tmp` (
  `key` VARCHAR(255) NOT NULL COMMENT 'The record’s unique key.' ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The record’s creation timestamp.' ,
  `data` BLOB NOT NULL COMMENT 'The record’s serialized data.' ,
  `ttl` TINYTEXT NOT NULL COMMENT 'The record’s time to life.' ,
  PRIMARY KEY (`key`) )
COMMENT = 'Used to store temporary data.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`awards`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`awards` (
  `award_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The award’s unique ID.' ,
  `name` VARCHAR(100) NOT NULL COMMENT 'The award\'s unique English name.' ,
  `description` BLOB NULL COMMENT 'The award’s English description.' ,
  `dyn_names` BLOB NOT NULL COMMENT 'The award’s title translations.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The award’s description translations.' ,
  PRIMARY KEY (`award_id`) ,
  UNIQUE INDEX `uq_jobs_title` (`name` ASC) )
COMMENT = 'Contains all job related data.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_awards`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_awards` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie\'s unique ID.' ,
  `award_count` INT NOT NULL COMMENT 'The award\'s number for a movie.' ,
  `award_id` INT UNSIGNED NOT NULL COMMENT 'The award\'s unique ID.' ,
  `award_category_id` INT UNSIGNED NULL COMMENT 'The award category´s ID.' ,
  `person_id` BIGINT UNSIGNED NULL COMMENT 'The unique ID of the person who received the award.' ,
  `company_id` BIGINT UNSIGNED NULL COMMENT 'The unique ID of the company which received the award.' ,
  `year` SMALLINT UNSIGNED NOT NULL COMMENT 'The year the award has been given to the movie.' ,
  `won` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Flag, whether the award has been won (true), or if there was only the nomination (false).' ,
  PRIMARY KEY (`movie_id`, `award_count`) ,
  INDEX `fk_awards_movies_movies1_idx` (`movie_id` ASC) ,
  INDEX `fk_awards_movies_awards1_idx` (`award_id` ASC) ,
  INDEX `fk_persons_awards_persons2_idx` (`person_id` ASC) ,
  INDEX `fk_persons_awards_companies1_idx` (`company_id` ASC) ,
  CONSTRAINT `fk_movies_awards_awards`
    FOREIGN KEY (`award_id` )
    REFERENCES `movlib`.`awards` (`award_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_awards_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_awards_persons`
    FOREIGN KEY (`person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_awards_companies`
    FOREIGN KEY (`company_id` )
    REFERENCES `movlib`.`companies` (`company_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_titles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_titles` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie\'s unique ID this title relates to.' ,
  `language_id` INT UNSIGNED NOT NULL COMMENT 'The language\'s unique ID this title is in.' ,
  `title` BLOB NOT NULL COMMENT 'The movie\'s title.' ,
  `dyn_comments` BLOB NOT NULL COMMENT 'The translatable comment for this title.' ,
  `is_display_title` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Determine if this title is the display title in the specified language.' ,
  INDEX `fk_movies_titles_languages1_idx` (`language_id` ASC) ,
  INDEX `fk_movies_titles_movies` (`movie_id` ASC) ,
  CONSTRAINT `fk_movies_titles_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_titles_languages`
    FOREIGN KEY (`language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_taglines`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_taglines` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie\'s unique ID this tagline relates to.' ,
  `language_id` INT UNSIGNED NOT NULL COMMENT 'The language\'s unique ID this tagline is in.' ,
  `tagline` BLOB NOT NULL COMMENT 'The movie\'s tagline.' ,
  `dyn_comments` BLOB NOT NULL COMMENT 'The tagline\'s translatable comment.' ,
  INDEX `fk_movies_taglines_languages1_idx` (`language_id` ASC) ,
  INDEX `fk_movies_taglines_movies` (`movie_id` ASC) ,
  CONSTRAINT `fk_movies_taglines_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_taglines_languages`
    FOREIGN KEY (`language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`relationship_types`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`relationship_types` (
  `relationship_type_id` BIGINT NOT NULL AUTO_INCREMENT COMMENT 'The relationship type\'s unique ID.' ,
  `name` VARCHAR(100) NOT NULL COMMENT 'The relationship type\'s unique English name.' ,
  `description` BLOB NULL COMMENT 'The relationship type\'s English description.' ,
  `dyn_names` BLOB NOT NULL COMMENT 'The relationship type\'s name translations.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The relationship type\'s description translations.' ,
  PRIMARY KEY (`relationship_type_id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_links`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_links` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie\'s unique ID.' ,
  `language_id` INT UNSIGNED NOT NULL COMMENT 'The language\'s unique ID.' ,
  `title` VARCHAR(100) NULL COMMENT 'The link\'s title attribute.' ,
  `text` VARCHAR(100) NOT NULL COMMENT 'The link\'s display text.' ,
  `url` VARCHAR(255) NOT NULL COMMENT 'The link\'s URL target' ,
  INDEX `fk_movies_links_languages1_idx` (`language_id` ASC) ,
  INDEX `fk_movies_links_movies` (`movie_id` ASC) ,
  CONSTRAINT `fk_movies_links_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_links_languages`
    FOREIGN KEY (`language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_general_ci
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_trailers`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_trailers` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movies\'s unique ID.' ,
  PRIMARY KEY (`movie_id`) ,
  CONSTRAINT `fk_movies_trailers_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_images`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_images` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.' ,
  `image_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie image\'s unique ID within the movie.' ,
  `type` TINYINT NOT NULL COMMENT 'The movie image’s type (enum from Data class).' ,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Unique ID of the user who uploaded this movie image.' ,
  `license_id` INT UNSIGNED NOT NULL COMMENT 'The license\'s unique ID this image is under.' ,
  `country_id` INT UNSIGNED NULL ,
  `filename` TINYBLOB NOT NULL COMMENT 'The movie image’s filename without extensions.' ,
  `width` SMALLINT NOT NULL COMMENT 'The movie image’s width.' ,
  `height` SMALLINT NOT NULL COMMENT 'The movie image’s height.' ,
  `size` INT NOT NULL COMMENT 'The movie image’s size in Bytes.' ,
  `ext` VARCHAR(5) NOT NULL COMMENT 'The movie image’s extension without leading dot.' ,
  `changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The last time this movie image was updated.' ,
  `created` TIMESTAMP NOT NULL COMMENT 'The movie image’s creation time.' ,
  `upvotes` BIGINT UNSIGNED NOT NULL COMMENT 'The movie image’s upvotes.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The movie image’s translatable descriptions.' ,
  `hash` BINARY(16) NOT NULL COMMENT 'The file\'s modification timestamp (UNIX format) for cache busting.' ,
  `source` BLOB NOT NULL COMMENT 'The image\'s source.' ,
  PRIMARY KEY (`movie_id`, `image_id`, `type`) ,
  INDEX `fk_posters_movies` (`movie_id` ASC) ,
  INDEX `fk_movies_images_users` (`user_id` ASC) ,
  INDEX `fk_movies_images_licenses1_idx` (`license_id` ASC) ,
  INDEX `fk_movies_images_countries1_idx` (`country_id` ASC) ,
  CONSTRAINT `fk_movies_images_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_images_users`
    FOREIGN KEY (`user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_images_licenses`
    FOREIGN KEY (`license_id` )
    REFERENCES `movlib`.`licenses` (`license_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_images_countries1`
    FOREIGN KEY (`country_id` )
    REFERENCES `movlib`.`countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'Extends images table with unique movie’s ID.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`series` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series` unique ID.' ,
  `original_title` BLOB NOT NULL COMMENT 'The series\' original title.' ,
  `rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The Bayes\'theorem rating of this series.\n\nrating = (s / (s + m)) * N + (m / (s + m)) * K\n\nN: arithmetic mean rating\ns: vote count\nm: minimum vote count\nK: arithmetic mean vote\n\nThe same formula is used by IMDb and OFDb.' ,
  `mean_rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The series’ arithmetic mean rating.' ,
  `votes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The series’ vote count.' ,
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'TRUE (1) if this series was deleted, default is FALSE (0).' ,
  `start_year` SMALLINT NULL COMMENT 'The year the series was aired for the first time.' ,
  `end_year` SMALLINT NULL COMMENT 'The year the series was cancelled.' ,
  `rank` BIGINT UNSIGNED NULL COMMENT 'The series’ global rank.' ,
  `dyn_synopses` BLOB NOT NULL COMMENT 'The series’ translatable synopses.' ,
  `bin_relationships` BLOB NULL COMMENT 'The series´ relations to other series, e.g. sequel.\nStored in igbinary serialized format.' ,
  `commit` CHAR(40) NULL COMMENT 'The series\' last commit sha-1 hash.' ,
  PRIMARY KEY (`series_id`) )
ENGINE = InnoDB
COMMENT = 'Contains all series data.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_seasons`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`series_seasons` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series´ unique ID.' ,
  `seasons_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season´s  number within the series.' ,
  `start_year` SMALLINT NULL COMMENT 'The year the season started airing for the first time.' ,
  `end_year` SMALLINT NULL COMMENT 'The year the season ended for the first time.' ,
  PRIMARY KEY (`series_id`, `seasons_number`) ,
  INDEX `fk_series_seasons_series1_idx` (`series_id` ASC) ,
  CONSTRAINT `fk_series_seasons_series`
    FOREIGN KEY (`series_id` )
    REFERENCES `movlib`.`series` (`series_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains seasons data for a series.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_titles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`series_titles` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series` unique ID.' ,
  `language_id` INT UNSIGNED NOT NULL COMMENT 'The language\'s unique ID this title is in.' ,
  `title` BLOB NOT NULL COMMENT 'The series´ title.' ,
  `dyn_comments` BLOB NOT NULL COMMENT 'The translatable comment for this title.' ,
  `is_display_title` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Determines whether this is the title to diplay in the localized site or not.' ,
  INDEX `fk_series_titles_series1_idx` (`series_id` ASC) ,
  INDEX `fk_series_titles_languages_idx` (`language_id` ASC) ,
  CONSTRAINT `fk_series_titles_series`
    FOREIGN KEY (`series_id` )
    REFERENCES `movlib`.`series` (`series_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_titles_languages`
    FOREIGN KEY (`language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'A series has many titles, a title belongs to one series. Con /* comment truncated */ /*tains language specific titles for series.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`seasons_episodes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`seasons_episodes` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series´ unique ID.' ,
  `seasons_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season´s number this episode belongs to.' ,
  `position` SMALLINT UNSIGNED NOT NULL COMMENT 'The episode´s chronological position within the season.' ,
  `episode_number` TINYTEXT NULL COMMENT 'The episodes number within the season (e.g. 01, but also 0102 if it contains two episodes).' ,
  `original_air_date` DATE NULL COMMENT 'The date the episode was originally aired.' ,
  `original_title` BLOB NOT NULL COMMENT 'The episode´s original title.' ,
  PRIMARY KEY (`series_id`, `seasons_number`, `position`) ,
  INDEX `fk_seasons_episodes_series_seasons1_idx1` (`series_id` ASC, `seasons_number` ASC) ,
  CONSTRAINT `fk_seasons_episodes_series_seasons`
    FOREIGN KEY (`series_id` , `seasons_number` )
    REFERENCES `movlib`.`series_seasons` (`series_id` , `seasons_number` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all episode data of episodes belonging to seasons w /* comment truncated */ /*hich belong to series.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`episodes_titles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`episodes_titles` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series\' unique ID.' ,
  `seasons_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season number within the series.' ,
  `position` SMALLINT UNSIGNED NOT NULL COMMENT 'The episode´s chronological position within the season.' ,
  `language_id` INT UNSIGNED NOT NULL COMMENT 'The language\'s unique ID this title is in.' ,
  `title` BLOB NOT NULL COMMENT 'The episode´s title.' ,
  `dyn_comments` BLOB NOT NULL COMMENT 'The translatable comment for this episode title.' ,
  `is_display_title` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Determine if this episode title is the display title in the specified language.' ,
  INDEX `fk_episodes_titles_seasons_episodes1_idx` (`series_id` ASC, `seasons_number` ASC, `position` ASC) ,
  INDEX `fk_episodes_titles_languages1_idx` (`language_id` ASC) ,
  CONSTRAINT `fk_episodes_titles_seasons_episodes`
    FOREIGN KEY (`series_id` , `seasons_number` , `position` )
    REFERENCES `movlib`.`seasons_episodes` (`series_id` , `seasons_number` , `position` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_episodes_titles_languages`
    FOREIGN KEY (`language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all episode data of episodes belonging to seasons w /* comment truncated */ /*hich belong to series.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_genres`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`series_genres` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series\' unique ID.' ,
  `genre_id` INT UNSIGNED NOT NULL COMMENT 'The genre\'s unique ID.' ,
  PRIMARY KEY (`series_id`, `genre_id`) ,
  INDEX `fk_series_genres_genres1_idx` (`genre_id` ASC) ,
  INDEX `fk_series_genres_series1_idx` (`series_id` ASC) ,
  CONSTRAINT `fk_series_genres_series1`
    FOREIGN KEY (`series_id` )
    REFERENCES `movlib`.`series` (`series_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_genres_genres1`
    FOREIGN KEY (`genre_id` )
    REFERENCES `movlib`.`genres` (`genre_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_styles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`series_styles` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series\' unique ID.' ,
  `style_id` INT UNSIGNED NOT NULL COMMENT 'The style\'s unique ID.' ,
  PRIMARY KEY (`series_id`, `style_id`) ,
  INDEX `fk_series_styles_styles1_idx` (`style_id` ASC) ,
  INDEX `fk_series_styles_series1_idx` (`series_id` ASC) ,
  CONSTRAINT `fk_series_styles_series1`
    FOREIGN KEY (`series_id` )
    REFERENCES `movlib`.`series` (`series_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_styles_styles1`
    FOREIGN KEY (`style_id` )
    REFERENCES `movlib`.`styles` (`style_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`articles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`articles` (
  `article_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The article´s unique ID.' ,
  `title` VARCHAR(255) NOT NULL COMMENT 'The article´s English title.' ,
  `dyn_titles` BLOB NOT NULL COMMENT 'The article´s translated titles.' ,
  `text` BLOB NOT NULL COMMENT 'The article´s text.' ,
  `dyn_texts` BLOB NOT NULL COMMENT 'The article´s translated text.' ,
  `admin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Determines whether the article can be edited by users (FALSE - 0) or not (TRUE - 1). Defaults to FALSE (0).' ,
  `commit` CHAR(40) NULL COMMENT 'The article\'s last commit sha-1 hash.' ,
  PRIMARY KEY (`article_id`) ,
  UNIQUE INDEX `uq_title` (`title` ASC) )
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`aspect_ratios`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`aspect_ratios` (
  `aspect_ratio_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The aspect ratio\'s unique ID.' ,
  `name` TINYTEXT NOT NULL COMMENT 'The aspect ratio\'s English name.' ,
  PRIMARY KEY (`aspect_ratio_id`) )
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`packaging`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`packaging` (
  `packaging_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The packaging´s unique ID.' ,
  `name` TINYBLOB NOT NULL COMMENT 'The packaging´s English name.' ,
  `dyn_names` BLOB NOT NULL COMMENT 'The packaging´s translatable names.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The packaging´s translatable descriptions' ,
  PRIMARY KEY (`packaging_id`) )
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`master_releases`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`master_releases` (
  `master_release_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The master release´s unique ID.' ,
  `title` BLOB NOT NULL COMMENT 'The master release´s title.' ,
  `country_id` INT UNSIGNED NOT NULL COMMENT 'The unique ID of the master release´s country.' ,
  `dyn_notes` BLOB NOT NULL COMMENT 'The master release´s translatable release notes.' ,
  `release_date` DATE NULL COMMENT 'The date this master release has been published.' ,
  `packaging_id` INT UNSIGNED NULL COMMENT 'The master release´s packaging (Only present if there are more than 1 releases in this master release).' ,
  `commit` CHAR(40) NULL COMMENT 'The master release\'s last commit sha-1 hash.' ,
  PRIMARY KEY (`master_release_id`) ,
  INDEX `fk_master_releases_countries1_idx` (`country_id` ASC) ,
  INDEX `fk_master_releases_packaging1_idx` (`packaging_id` ASC) ,
  CONSTRAINT `fk_master_releases_countries1`
    FOREIGN KEY (`country_id` )
    REFERENCES `movlib`.`countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_master_releases_packaging1`
    FOREIGN KEY (`packaging_id` )
    REFERENCES `movlib`.`packaging` (`packaging_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`releases` (
  `release_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The release´s unique ID.' ,
  `master_release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The master release´s unique ID this release belongs to.' ,
  `is_cut` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this release is cut or not.' ,
  `ean` TINYBLOB NULL COMMENT 'The global identification number (EAN) of this release.' ,
  `length` TIME NULL COMMENT 'The length of this release without the credits.' ,
  `length_credits` TIME NULL COMMENT 'The length of this release with the credits.' ,
  `length_bonus` TIME NULL COMMENT 'The length of this release\'s bonus material.' ,
  `dyn_extras` BLOB NOT NULL COMMENT 'The release´s translatable extras free text field.' ,
  `dyn_notes` BLOB NOT NULL COMMENT 'The translatable release notes (free text) field for this release, if there is more than one release in the master release.' ,
  `aspect_ratio_id` INT UNSIGNED NOT NULL COMMENT 'The unique ID of the release´s aspect ratio.' ,
  `packaging_id` INT UNSIGNED NOT NULL COMMENT 'The packaging´s unique ID this release is boxed in.' ,
  `type` VARCHAR(20) NOT NULL COMMENT 'The release´s type in lowercase letters, e.g. dvd or bluray.' ,
  `bin_type_data` BLOB NOT NULL COMMENT 'The release´s type specific fields in serialized igbinary format.' ,
  INDEX `fk_releases_aspect_ratios` (`aspect_ratio_id` ASC) ,
  PRIMARY KEY (`release_id`) ,
  INDEX `fk_releases_packaging1_idx` (`packaging_id` ASC) ,
  CONSTRAINT `fk_releases_aspect_ratios`
    FOREIGN KEY (`aspect_ratio_id` )
    REFERENCES `movlib`.`aspect_ratios` (`aspect_ratio_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_master_releases`
    FOREIGN KEY (`master_release_id` )
    REFERENCES `movlib`.`master_releases` (`master_release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_packaging1`
    FOREIGN KEY (`packaging_id` )
    REFERENCES `movlib`.`packaging` (`packaging_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_releases`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_releases` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie´s unique ID.' ,
  `release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The release´s unique ID that is related to the movie.' ,
  PRIMARY KEY (`movie_id`, `release_id`) ,
  INDEX `fk_movies_releases_releases1_idx` (`release_id` ASC) ,
  INDEX `fk_movies_releases_movies1_idx` (`movie_id` ASC) ,
  CONSTRAINT `fk_movies_releases_movies1`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_releases_releases1`
    FOREIGN KEY (`release_id` )
    REFERENCES `movlib`.`releases` (`release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_labels`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`releases_labels` (
  `release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The release´s unique ID.' ,
  `company_id` BIGINT UNSIGNED NOT NULL COMMENT 'The company´s unique ID of the(additional)  label this release is related to.' ,
  `cat_no` TINYBLOB NULL COMMENT 'The release´s catalog number on this label.' ,
  PRIMARY KEY (`release_id`, `company_id`) ,
  INDEX `fk_releases_companies_companies1_idx` (`company_id` ASC) ,
  INDEX `fk_releases_companies_releases1_idx` (`release_id` ASC) ,
  CONSTRAINT `fk_releases_companies_releases`
    FOREIGN KEY (`release_id` )
    REFERENCES `movlib`.`releases` (`release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_companies_companies`
    FOREIGN KEY (`company_id` )
    REFERENCES `movlib`.`companies` (`company_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`articles_routes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`articles_routes` (
  `article_id` BIGINT UNSIGNED NOT NULL COMMENT 'The article’s unique ID.' ,
  `language_id` INT UNSIGNED NOT NULL COMMENT 'The article route’s language ID.' ,
  `section` VARCHAR(255) NOT NULL COMMENT 'The article’s section (e.g. help).' ,
  `route` VARCHAR(255) NOT NULL COMMENT 'The article’s route format.' ,
  PRIMARY KEY (`article_id`, `language_id`, `section`, `route`) ,
  INDEX `articles_routes_language` (`language_id` ASC) ,
  INDEX `articles_routes_section_route` (`section` ASC, `route` ASC) ,
  CONSTRAINT `fk_articles_routes_articles`
    FOREIGN KEY (`article_id` )
    REFERENCES `movlib`.`articles` (`article_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_articles_routes_languages`
    FOREIGN KEY (`language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`sound_formats`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`sound_formats` (
  `sound_format_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The sound format´s unique ID.' ,
  `name` TINYTEXT NOT NULL COMMENT 'The sound format´s English name.' ,
  `dyn_names` BLOB NOT NULL COMMENT 'The sound format´s translations, if there are any.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The sound format´s translatable descriptions.' ,
  PRIMARY KEY (`sound_format_id`) )
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_sound_formats`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`releases_sound_formats` (
  `release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The release´s unique ID.' ,
  `sound_format_id` INT UNSIGNED NOT NULL COMMENT 'The sound format´s unique ID.' ,
  `language_id` INT UNSIGNED NOT NULL COMMENT 'The language this sound format is in.' ,
  `dyn_comments` BLOB NOT NULL COMMENT 'The translatable comment field for this sound format.' ,
  PRIMARY KEY (`release_id`, `sound_format_id`, `language_id`) ,
  INDEX `fk_releases_sound_formats_sound_formats` (`sound_format_id` ASC) ,
  INDEX `fk_releases_sound_formats_releases` (`release_id` ASC) ,
  INDEX `fk_releases_sound_formats_languages` (`language_id` ASC) ,
  CONSTRAINT `fk_releases_sound_formats_releases`
    FOREIGN KEY (`release_id` )
    REFERENCES `movlib`.`releases` (`release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_sound_formats_sound_formats`
    FOREIGN KEY (`sound_format_id` )
    REFERENCES `movlib`.`sound_formats` (`sound_format_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_sound_formats_languages`
    FOREIGN KEY (`language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_subtitles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`releases_subtitles` (
  `release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The release´s unique ID this subtitle track belongs to.' ,
  `language_id` INT UNSIGNED NOT NULL COMMENT 'The language´s unique ID this subtitle track is in.' ,
  `is_hearing_impaired` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Flag that determines whether the subtitle track is for the hearing impaired or not (defaults to FALSE).' ,
  `dyn_comments` BLOB NOT NULL COMMENT 'The translatable comment field for this subtitle track.' ,
  PRIMARY KEY (`release_id`, `language_id`, `is_hearing_impaired`) ,
  INDEX `fk_releases_subtitles_languages` (`language_id` ASC) ,
  CONSTRAINT `fk_releases_subtitles_releases`
    FOREIGN KEY (`release_id` )
    REFERENCES `movlib`.`releases` (`release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_subtitles_languages`
    FOREIGN KEY (`language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`master_releases_labels`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`master_releases_labels` (
  `master_release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The master release´s unique ID.' ,
  `company_id` BIGINT UNSIGNED NOT NULL COMMENT 'The company´s unique ID of the label this (master) release is related to.' ,
  `cat_no` TINYBLOB NULL COMMENT 'The master release´s catalog number on this label.' ,
  PRIMARY KEY (`master_release_id`, `company_id`) ,
  INDEX `fk_master_releases_companies_companies1_idx` (`company_id` ASC) ,
  INDEX `fk_master_releases_companies_master_releases1_idx` (`master_release_id` ASC) ,
  CONSTRAINT `fk_master_releases_companies_master_releases1`
    FOREIGN KEY (`master_release_id` )
    REFERENCES `movlib`.`master_releases` (`master_release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_master_releases_companies_companies1`
    FOREIGN KEY (`company_id` )
    REFERENCES `movlib`.`companies` (`company_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`awards_categories`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`awards_categories` (
  `award_id` INT UNSIGNED NOT NULL COMMENT 'The award’s unique ID.' ,
  `award_category_id` INT UNSIGNED NOT NULL COMMENT 'The award category’s ID within the award.' ,
  `name` VARCHAR(100) NOT NULL COMMENT 'The award category´s English name.' ,
  `description` BLOB NULL COMMENT 'The award category’s English description.' ,
  `dyn_names` BLOB NOT NULL COMMENT 'The award category’s title translations.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The award category’s description translations.' ,
  PRIMARY KEY (`award_id`, `award_category_id`) ,
  CONSTRAINT `fk_awards_categories_awards1`
    FOREIGN KEY (`award_id` )
    REFERENCES `movlib`.`awards` (`award_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_ratings`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_ratings` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie\'s unique ID.' ,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user\'s unique ID.' ,
  `rating` TINYINT(1) UNSIGNED NOT NULL COMMENT 'The user\'s rating for this movie (1-5).' ,
  PRIMARY KEY (`movie_id`, `user_id`) ,
  INDEX `fk_movies_ratings_users1_idx` (`user_id` ASC) ,
  CONSTRAINT `fk_movies_ratings_movies1`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_ratings_users1`
    FOREIGN KEY (`user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_relationships`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_relationships` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The first movie\'s unique ID in this relationship.' ,
  `movie_id_other` BIGINT UNSIGNED NOT NULL COMMENT 'The second movie\'s unique ID in this relationship.' ,
  `relationship_type_id` BIGINT NOT NULL COMMENT 'The unique ID of the relationship type.' ,
  PRIMARY KEY (`movie_id`, `movie_id_other`, `relationship_type_id`) ,
  INDEX `fk_movies_relationships_relationship_types1_idx` (`relationship_type_id` ASC) ,
  INDEX `fk_movies_relationships_movies1_idx` (`movie_id` ASC) ,
  INDEX `fk_movies_relationships_movies2_idx` (`movie_id_other` ASC) ,
  CONSTRAINT `fk_movies_relationships_relationship_types`
    FOREIGN KEY (`relationship_type_id` )
    REFERENCES `movlib`.`relationship_types` (`relationship_type_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_relationships_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_relationships_movies_other`
    FOREIGN KEY (`movie_id_other` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`sessions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`sessions` (
  `session_id` VARBINARY(86) NOT NULL COMMENT 'The session\'s unique ID.' ,
  `user_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The session\'s unique user ID.' ,
  `authentication` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp when this session was initialized.' ,
  `ip_address` BLOB NOT NULL COMMENT 'The session\'s IP address.' ,
  `user_agent` BLOB NOT NULL COMMENT 'The session\'s user agent string.' ,
  PRIMARY KEY (`session_id`) ,
  INDEX `fk_sessions_users1_idx` (`user_id` ASC) ,
  CONSTRAINT `fk_sessions_users1`
    FOREIGN KEY (`user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Persistent session storage.';

SHOW WARNINGS;
USE `movlib` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
