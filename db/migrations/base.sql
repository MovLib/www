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
  `original_title` VARCHAR(255) NOT NULL COMMENT 'The movie\'s original title.' ,
  `rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The Bayes\'theorem rating of this movie.\n\nrating = (s / (s + m)) * N + (m / (s + m)) * K\n\nN: arithmetic mean rating\ns: vote count\nm: minimum vote count\nK: arithmetic mean vote\n\nThe same formula is used by IMDb and OFDb.' ,
  `mean_rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s arithmetic mean rating.' ,
  `votes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s vote count.' ,
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'TRUE (1) if this movie was deleted, default is FALSE (0).' ,
  `year` SMALLINT NULL COMMENT 'The movie’s initial release year.' ,
  `runtime` SMALLINT UNSIGNED NULL COMMENT 'The movie’s approximate runtime.' ,
  `rank` BIGINT UNSIGNED NULL COMMENT 'The movie’s global rank.' ,
  `dyn_synopses` BLOB NOT NULL COMMENT 'The movie’s translatable synopses.' ,
  `bin_relationships` BLOB NULL ,
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
  `name` VARCHAR(100) NOT NULL COMMENT 'Unique style’s English name.' ,
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
-- Table `movlib`.`images`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`images` (
  `image_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The image’s unique ID.' ,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Unique ID of the user who uploaded this image.' ,
  `filename` TINYBLOB NOT NULL COMMENT 'The image’s filename without extensions.' ,
  `width` SMALLINT UNSIGNED NOT NULL COMMENT 'The image’s width.' ,
  `height` SMALLINT UNSIGNED NOT NULL COMMENT 'The image’s height.' ,
  `size` INT UNSIGNED NOT NULL COMMENT 'The image’s size in Bytes.' ,
  `ext` VARCHAR(5) NOT NULL COMMENT 'The image’s extension without leading dot.' ,
  `changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The last time this image was updated.' ,
  `created` TIMESTAMP NOT NULL COMMENT 'The image’s creation time.' ,
  `rating` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The image’s upvotes.' ,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The image’s translatable descriptions.' ,
  PRIMARY KEY (`image_id`) ,
  INDEX `fk_images_users` (`user_id` ASC) ,
  CONSTRAINT `fk_images_users`
    FOREIGN KEY (`user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'Base table for all kinds of uploaded images.'
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
-- Table `movlib`.`users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`users` (
  `user_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The user’s unique ID.' ,
  `name` VARCHAR(40) NOT NULL COMMENT 'The user’s unique name.' ,
  `mail` VARCHAR(254) NOT NULL COMMENT 'The user’s unique email address.' ,
  `pass` TINYBLOB NOT NULL COMMENT 'The user’s unique password (hashed).' ,
  `created` TIMESTAMP NOT NULL COMMENT 'Timestamp for when user was created.' ,
  `access` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp for previous time user accessed the site.' ,
  `login` TIMESTAMP NOT NULL COMMENT 'Timestamp for user’s last login.' ,
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'TRUE (1) if this account was deleted (or blocked), default is TRUE (1).' ,
  `timezone` TINYTEXT NOT NULL COMMENT 'User’s time zone: http://php.net/manual/en/timezones.php' ,
  `init` VARCHAR(254) NOT NULL COMMENT 'Email address used for initial account creation.' ,
  `dyn_data` BLOB NOT NULL COMMENT 'Temporary data related to this user (e.g. hash for reseting the password).' ,
  `image_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'The unique image ID of the user’s avatar.' ,
  `profile` BLOB NULL DEFAULT NULL COMMENT 'The user’s profile text.' ,
  `website` TINYBLOB NULL DEFAULT NULL COMMENT 'The user’s website URL.' ,
  `facebook` TINYBLOB NULL DEFAULT NULL COMMENT 'The user’s Facebook data.' ,
  `twitter` TINYBLOB NULL DEFAULT NULL COMMENT 'The user’s Twitter data.' ,
  `real_name` TINYBLOB NULL DEFAULT NULL COMMENT 'The user’s real name.' ,
  `country_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'The user’s country.' ,
  `language_id` INT UNSIGNED NOT NULL COMMENT 'The user’s language.' ,
  PRIMARY KEY (`user_id`) ,
  INDEX `fk_users_images` (`image_id` ASC) ,
  INDEX `fk_users_countries` (`country_id` ASC) ,
  INDEX `fk_users_languages` (`language_id` ASC) ,
  UNIQUE INDEX `uq_users_name` (`name` ASC) ,
  UNIQUE INDEX `uq_users_mail` (`mail` ASC) ,
  CONSTRAINT `fk_users_images`
    FOREIGN KEY (`image_id` )
    REFERENCES `movlib`.`images` (`image_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_users_countries`
    FOREIGN KEY (`country_id` )
    REFERENCES `movlib`.`countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_languages`
    FOREIGN KEY (`language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
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
  `gender` VARCHAR(6) NULL COMMENT 'The person’s gender (female or male).' ,
  `dyn_aliases` BLOB NOT NULL COMMENT 'The person’s aliases.' ,
  `dyn_biographies` BLOB NOT NULL COMMENT 'The person’s translatable biographies.' ,
  `dyn_links` BLOB NOT NULL COMMENT 'The person’s external weblinks.' ,
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
  `crew_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The crew’s unique ID.' ,
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
-- Table `movlib`.`posters`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`posters` (
  `image_id` BIGINT UNSIGNED NOT NULL COMMENT 'The poster’s unique ID.' ,
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.' ,
  `country_id` INT UNSIGNED NULL ,
  PRIMARY KEY (`image_id`, `movie_id`) ,
  INDEX `fk_posters_movies` (`movie_id` ASC) ,
  INDEX `fk_posters_images` (`image_id` ASC) ,
  INDEX `fk_posters_countries1_idx` (`country_id` ASC) ,
  CONSTRAINT `fk_posters_images`
    FOREIGN KEY (`image_id` )
    REFERENCES `movlib`.`images` (`image_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_posters_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_posters_countries1`
    FOREIGN KEY (`country_id` )
    REFERENCES `movlib`.`countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'Extends images table with unique movie’s ID.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_photos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons_photos` (
  `image_id` BIGINT UNSIGNED NOT NULL COMMENT 'The image’s unique ID.' ,
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique ID.' ,
  PRIMARY KEY (`image_id`, `person_id`) ,
  INDEX `fk_persons_photos_persons` (`person_id` ASC) ,
  INDEX `fk_persons_photos_images` (`image_id` ASC) ,
  CONSTRAINT `fk_persons_photos_images`
    FOREIGN KEY (`image_id` )
    REFERENCES `movlib`.`images` (`image_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_persons_photos_persons`
    FOREIGN KEY (`person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'Extends images table with unique person’s ID.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`companies_images`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`companies_images` (
  `image_id` BIGINT UNSIGNED NOT NULL COMMENT 'The images’s unique ID.' ,
  `company_id` BIGINT UNSIGNED NOT NULL COMMENT 'The company’s unique ID.' ,
  PRIMARY KEY (`image_id`, `company_id`) ,
  INDEX `fk_companies_images_companies` (`company_id` ASC) ,
  INDEX `fk_companies_images_images` (`image_id` ASC) ,
  CONSTRAINT `fk_companies_images_images`
    FOREIGN KEY (`image_id` )
    REFERENCES `movlib`.`images` (`image_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_companies_images_companies`
    FOREIGN KEY (`company_id` )
    REFERENCES `movlib`.`companies` (`company_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COMMENT = 'Extends images table with unique company’s ID.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_images`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_images` (
  `image_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie image’s unique ID.' ,
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the movie this image belongs to.' ,
  `type` VARCHAR(50) NOT NULL COMMENT 'The movie image’s type (e.g. “photo”).' ,
  INDEX `fk_movies_images_movies` (`movie_id` ASC) ,
  PRIMARY KEY (`image_id`, `movie_id`) ,
  INDEX `fk_movies_images_images` (`image_id` ASC) ,
  CONSTRAINT `fk_movies_images_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_images_images`
    FOREIGN KEY (`image_id` )
    REFERENCES `movlib`.`images` (`image_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
COMMENT = 'Extends images table with unique movie’s ID and type.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`ratings`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`ratings` (
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique ID.' ,
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.' ,
  `rating` TINYINT UNSIGNED NOT NULL COMMENT 'The rating itself (between 1 to 5).' ,
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
  `route` VARCHAR(254) NOT NULL COMMENT 'The route’s unique English pattern.' ,
  `dyn_translations` BLOB NOT NULL COMMENT 'The route’s translations.' ,
  PRIMARY KEY (`route_id`) ,
  UNIQUE INDEX `uq_routes_route` (`route` ASC) )
COMMENT = 'Contains all routes (relative URIs).'
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
  `key` VARCHAR(255) NOT NULL COMMENT 'The entry’s unique key.' ,
  `dyn_data` BLOB NOT NULL COMMENT 'The entry’s dynamic data.' ,
  PRIMARY KEY (`key`) )
COMMENT = 'Used to store temporary data.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`awards`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`awards` (
  `award_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The award’s unique ID.' ,
  `name` VARCHAR(100) NOT NULL COMMENT 'The awards unique English name.' ,
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
  `award_id` INT UNSIGNED NOT NULL COMMENT 'The award\'s unique ID.' ,
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie\'s unique ID.' ,
  `year` SMALLINT UNSIGNED NOT NULL COMMENT 'The year the award has been given to the movie.' ,
  PRIMARY KEY (`award_id`, `movie_id`) ,
  INDEX `fk_awards_movies_movies1_idx` (`movie_id` ASC) ,
  INDEX `fk_awards_movies_awards1_idx` (`award_id` ASC) ,
  CONSTRAINT `fk_movies_awards_awards`
    FOREIGN KEY (`award_id` )
    REFERENCES `movlib`.`awards` (`award_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_awards_movies`
    FOREIGN KEY (`movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
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
  `is_display_title` TINYINT(1) NOT NULL DEFAULT false ,
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
USE `movlib` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
