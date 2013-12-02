SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

DROP SCHEMA IF EXISTS `movlib` ;
CREATE SCHEMA IF NOT EXISTS `movlib` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
SHOW WARNINGS;
USE `movlib` ;

-- -----------------------------------------------------
-- Table `movlib`.`movies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The movie’s unique ID.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The movie’s creation time.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'TRUE (1) if this movie was deleted, default is FALSE (0).',
  `dyn_synopses` BLOB NOT NULL COMMENT 'The movie’s translatable synopses.',
  `mean_rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s arithmetic mean rating.',
  `original_title` BLOB NOT NULL COMMENT 'The movie\'s original title.',
  `rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The Bayes\'theorem rating of this movie.\n\nrating = (s / (s + m)) * N + (m / (s + m)) * K\n\nN: arithmetic mean rating\ns: vote count\nm: minimum vote count\nK: arithmetic mean vote\n\nThe same formula is used by IMDb and OFDb.',
  `votes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s vote count.',
  `commit` CHAR(40) NULL COMMENT 'The movie\'s last commit sha-1 hash.',
  `rank` BIGINT UNSIGNED NULL COMMENT 'The movie’s global rank.',
  `runtime` SMALLINT UNSIGNED NULL COMMENT 'The movie’s approximate runtime in minutes.',
  `website` TINYTEXT NULL COMMENT 'The movie\'s official website URL.',
  `year` SMALLINT NULL COMMENT 'The movie’s initial release year.',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uq_movies_rank` (`rank` ASC),
  INDEX `movies_deleted` (`deleted` ASC),
  INDEX `movies_created` (`created` DESC))
ENGINE = InnoDB
COMMENT = 'Contains all basic movie data.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`genres`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`genres` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The genre’s unique ID.',
  `dyn_names` BLOB NOT NULL COMMENT 'The genre’s names.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The genre’s descriptions.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all movie genres.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_genres`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_genres` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `genre_id` INT UNSIGNED NOT NULL COMMENT 'The genre’s unique ID.',
  PRIMARY KEY (`movie_id`, `genre_id`),
  INDEX `fk_movies_genres_genres` (`genre_id` ASC),
  INDEX `fk_movies_genres_movies` (`movie_id` ASC),
  CONSTRAINT `fk_movies_genres_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_genres_genres`
    FOREIGN KEY (`genre_id`)
    REFERENCES `movlib`.`genres` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'A movie has many genres, a genre has many movies.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`styles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`styles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The style’s unique identifier.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The style description’s translations.',
  `dyn_names` BLOB NOT NULL COMMENT 'The style’s names.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all movie styles.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_styles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_styles` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `style_id` INT UNSIGNED NOT NULL COMMENT 'The style’s unique ID.',
  PRIMARY KEY (`movie_id`, `style_id`),
  INDEX `fk_movies_styles_styles` (`style_id` ASC),
  INDEX `fk_movies_styles_movies` (`movie_id` ASC),
  CONSTRAINT `fk_movies_styles_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_styles_styles`
    FOREIGN KEY (`style_id`)
    REFERENCES `movlib`.`styles` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'A movie has many styles, a style has many movies.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The user’s unique ID.',
  `name` VARCHAR(40) NOT NULL COMMENT 'The user’s unique name.',
  `access` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp for user’s last access.',
  `birthday` DATE NULL DEFAULT NULL COMMENT 'The user\'s date of birth.',
  `country_code` CHAR(2) NULL COMMENT 'The user’s ISO alpha-2 country code.',
  `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp for user’s creation datetime.',
  `currency_code` CHAR(3) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL COMMENT 'The user’s ISO 4217 currency code.',
  `dyn_about_me` BLOB NULL COMMENT 'The user’s about me text (translatable).',
  `edits` BIGINT UNSIGNED NULL DEFAULT 0 COMMENT 'The user’s edit counter.',
  `email` VARCHAR(254) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL COMMENT 'The user’s unique email address.',
  `image_changed` TIMESTAMP NULL DEFAULT NULL COMMENT 'The avatar’s last change timestamp.',
  `image_extension` CHAR(3) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL DEFAULT NULL COMMENT 'The avatar’s file extension.',
  `password` VARBINARY(255) NULL COMMENT 'The user’s unique password (hashed).',
  `private` TINYINT(1) NULL DEFAULT false COMMENT 'The flag if the user is willing to display their private date on the profile page.',
  `profile_views` BIGINT UNSIGNED NULL DEFAULT 0 COMMENT 'The user’s profile views.',
  `real_name` TINYBLOB NULL COMMENT 'The user’s real name.',
  `reputation` BIGINT UNSIGNED NULL DEFAULT 0,
  `sex` TINYINT UNSIGNED NULL DEFAULT 0 COMMENT 'The user\'s sex according to ISO 5218.',
  `system_language_code` CHAR(2) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL DEFAULT 'en' COMMENT 'The user’s preferred system language’s code (e.g. en).',
  `time_zone_identifier` VARCHAR(30) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL DEFAULT 'UTC' COMMENT 'User’s time zone ID.',
  `website` VARCHAR(255) NULL COMMENT 'The user’s website URL.',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uq_users_name` (`name` ASC),
  UNIQUE INDEX `uq_users_email` (`email` ASC),
  INDEX `users_created` (`created` ASC))
ENGINE = InnoDB
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`persons` (
  `person_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The person’s unique ID.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The person’s full name.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'TRUE (1) if this person was deleted, default is FALSE (0).',
  `born_name` MEDIUMBLOB NULL COMMENT 'The person’s born name.',
  `birthdate` DATE NULL COMMENT 'The person’s date of birth.',
  `deathdate` DATE NULL COMMENT 'The person’s date of death.',
  `country` CHAR(2) NULL COMMENT 'The person’s birth country.',
  `city` TINYBLOB NULL COMMENT 'The person’s birth city.',
  `region` TINYBLOB NULL COMMENT 'The person’s birth region.',
  `sex` TINYINT NOT NULL DEFAULT 0 COMMENT 'The person\'s sex according to ISO 5218.',
  `rank` BIGINT UNSIGNED NULL COMMENT 'The person\'s global rank.',
  `dyn_aliases` BLOB NOT NULL COMMENT 'The person’s aliases.',
  `dyn_biographies` BLOB NOT NULL COMMENT 'The person’s translatable biographies.',
  `dyn_links` BLOB NOT NULL COMMENT 'The person’s external weblinks.',
  `created` TIMESTAMP NOT NULL COMMENT 'The timestamp this person was created.',
  `commit` CHAR(40) NULL COMMENT 'The movie\'s last commit sha-1 hash.',
  PRIMARY KEY (`person_id`))
ENGINE = InnoDB
COMMENT = 'Contains all person related data.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`jobs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`jobs` (
  `job_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The job’s unique ID.',
  `title` VARCHAR(100) NOT NULL COMMENT 'The job’s unique English title.',
  `description` BLOB NOT NULL COMMENT 'The job’s English description.',
  `dyn_titles` BLOB NOT NULL COMMENT 'The job title’s translations.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The job description’s translations.',
  `created` TIMESTAMP NOT NULL COMMENT 'The timestamp this job was created.',
  PRIMARY KEY (`job_id`),
  UNIQUE INDEX `uq_jobs_title` (`title` ASC))
ENGINE = InnoDB
COMMENT = 'Contains all job related data.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`companies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`companies` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The company’s unique ID.',
  `created` TIMESTAMP NOT NULL COMMENT 'The creation date of the company as timestamp.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this company was marked as deleted or not. TRUE (1) if this company was marked as deleted, default is FALSE (0).',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The company’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_links` BLOB NOT NULL COMMENT 'External links belonging to this company. The link’s hostname serves as key.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The company’s name.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all movie companies.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_crew`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_crew` (
  `crew_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The crew’s unique ID within the movie.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `job_id` INT UNSIGNED NOT NULL COMMENT 'The job’s unique ID.',
  `company_id` BIGINT UNSIGNED NULL COMMENT 'The company’s unique ID.',
  `person_id` BIGINT UNSIGNED NULL COMMENT 'The person’s unique ID.',
  PRIMARY KEY (`crew_id`, `movie_id`),
  INDEX `fk_movies_crew_movies` (`movie_id` ASC),
  INDEX `fk_movies_crew_jobs` (`job_id` ASC),
  INDEX `fk_movies_crew_companies` (`company_id` ASC),
  INDEX `fk_movies_crew_persons` (`person_id` ASC),
  CONSTRAINT `fk_movies_crew_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_crew_jobs`
    FOREIGN KEY (`job_id`)
    REFERENCES `movlib`.`jobs` (`job_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_crew_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_crew_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the crew of a movie.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`licenses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`licenses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The license’s unique identifier.',
  `dyn_names` BLOB NOT NULL COMMENT 'The license’s translated names.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The license’s translated descriptions.',
  `dyn_url` BLOB NOT NULL COMMENT 'The license’s absolute URL.',
  `abbreviation` VARCHAR(20) NOT NULL COMMENT 'The license’s abbreviation.',
  `icon_changed` TIMESTAMP NOT NULL COMMENT 'The license’s icon changed timestamp.',
  `icon_extension` CHAR(3) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NOT NULL COMMENT 'The license’s icon extension.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_photos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`persons_photos` (
  `id` BIGINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The person photo’s unique ID within the person.',
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique ID.',
  `deleted` TINYINT(1) NOT NULL DEFAULT true COMMENT 'The flag that determines whether this person photo is marked as deleted (TRUE(1)) or not (FALSE(0)), default is TRUE(1).',
  `upvotes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The person photo’s upvote count.',
  `license_id` BIGINT UNSIGNED NULL COMMENT 'The license’s unique ID.',
  `user_id` BIGINT UNSIGNED NULL COMMENT 'The user’s unique ID.',
  `changed` TIMESTAMP NULL COMMENT 'The last time this person photo was updated as timestamp.',
  `created` TIMESTAMP NULL COMMENT 'The person photo’s creation time as timestamp.',
  `dyn_descriptions` BLOB NULL COMMENT 'The person photo’s description in various languages. Keys are ISO alpha-2 language codes.',
  `extension` CHAR(3) NULL COMMENT 'The person photo’s extension without leading dot.',
  `filesize` INT UNSIGNED NULL COMMENT 'The person photo’s original size in Bytes.',
  `height` SMALLINT UNSIGNED NULL COMMENT 'The person photo’s original height.',
  `styles` BLOB NULL COMMENT 'Serialized array containing width and height of various image styles.',
  `width` SMALLINT UNSIGNED NULL COMMENT 'The person photo’s original width.',
  PRIMARY KEY (`id`, `person_id`),
  INDEX `fk_persons_photos_persons` (`person_id` ASC),
  INDEX `fk_persons_photos_images` (`id` ASC),
  INDEX `fk_persons_photos_licenses` (`license_id` ASC),
  INDEX `persons_photos_order_by_upvotes` (`upvotes` ASC),
  INDEX `fk_persons_photos_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_persons_photos_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_photos_licenses`
    FOREIGN KEY (`license_id`)
    REFERENCES `movlib`.`licenses` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_photos_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all person photos.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`companies_images`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`companies_images` (
  `id` BIGINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The company image’s unique ID within the company.',
  `company_id` BIGINT UNSIGNED NOT NULL COMMENT 'The company’s unique ID.',
  `type_id` TINYINT UNSIGNED NOT NULL COMMENT 'The company image’s type (enum from Data class).',
  `deleted` TINYINT(1) NOT NULL DEFAULT true COMMENT 'The flag that determines whether this person photo is marked as deleted (TRUE(1)) or not (FALSE(0)), default is TRUE(1).',
  `upvotes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The company image’s upvote count.',
  `license_id` BIGINT UNSIGNED NULL COMMENT 'The license’s unique ID.',
  `user_id` BIGINT UNSIGNED NULL COMMENT 'The user’s unique ID.',
  `changed` TIMESTAMP NULL COMMENT 'The last time this company image was updated as timestamp.',
  `created` TIMESTAMP NULL COMMENT 'The company image’s creation time as timestamp.',
  `dyn_descriptions` BLOB NULL COMMENT 'The company image’s description in various languages. Keys are ISO alpha-2 language codes.',
  `extension` CHAR(3) NULL COMMENT 'The company image’s extension without leading dot.',
  `filesize` INT UNSIGNED NULL COMMENT 'The company image’s original size in Bytes.',
  `height` SMALLINT UNSIGNED NULL COMMENT 'The company image’s original height.',
  `styles` BLOB NULL COMMENT 'Serialized array containing width and height of various image styles.',
  `width` SMALLINT UNSIGNED NULL COMMENT 'The company image’s original width.',
  PRIMARY KEY (`id`, `company_id`, `type_id`),
  INDEX `fk_companies_images_companies` (`company_id` ASC),
  INDEX `fk_companies_images_images` (`id` ASC),
  INDEX `fk_companies_images_licenses` (`license_id` ASC),
  INDEX `companies_images_order_by_upvotes` (`upvotes` ASC),
  INDEX `fk_companies_images_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_companies_images_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_companies_images_licenses`
    FOREIGN KEY (`license_id`)
    REFERENCES `movlib`.`licenses` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_companies_images_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all company images.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_cast`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_cast` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique ID.',
  `roles` BLOB NULL COMMENT 'The names of the role the person played in the movie.',
  `weight` INT NULL COMMENT 'The weight (display order) of the movie\'s cast.',
  PRIMARY KEY (`movie_id`, `person_id`),
  INDEX `fk_movies_cast_movies` (`movie_id` ASC),
  INDEX `fk_movies_cast_persons` (`person_id` ASC),
  CONSTRAINT `fk_movies_cast_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_cast_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'A movie has many actors, an actor has many movies.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`messages`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`messages` (
  `message_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The message’s unique ID.',
  `message` TEXT NOT NULL COMMENT 'The message’s unique English pattern.',
  `comment` BLOB NULL COMMENT 'The message’s optional comment for translators.',
  `dyn_translations` BLOB NOT NULL COMMENT 'The message’s translations.',
  PRIMARY KEY (`message_id`))
ENGINE = InnoDB
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_countries`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_countries` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `country_code` CHAR(2) NOT NULL COMMENT 'The country’s unique ISO alpha-2 code.',
  PRIMARY KEY (`movie_id`, `country_code`),
  INDEX `fk_movies_countries_movies` (`movie_id` ASC),
  CONSTRAINT `fk_movies_countries_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'A movie has many countries, a country has many movies.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_languages`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_languages` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The language’s unique iso alpha-2 code.',
  PRIMARY KEY (`movie_id`, `language_code`),
  INDEX `fk_movies_languages_movies` (`movie_id` ASC),
  CONSTRAINT `fk_movies_languages_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'A movie has many languages, a language has many movies.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_directors`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_directors` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique ID.',
  PRIMARY KEY (`movie_id`, `person_id`),
  INDEX `fk_movies_directors_persons` (`person_id` ASC),
  INDEX `fk_movies_directors_movies` (`movie_id` ASC),
  CONSTRAINT `fk_movies_directors_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_directors_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'A movie has many directors, a director has many movies.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`tmp`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`tmp` (
  `key` VARCHAR(255) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NOT NULL COMMENT 'The record’s unique key.',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The record’s creation timestamp.',
  `data` BLOB NOT NULL COMMENT 'The record’s serialized data.',
  `ttl` VARCHAR(16) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NOT NULL COMMENT 'The record’s time to life.',
  PRIMARY KEY (`key`),
  INDEX `tmp_created` (`created` ASC),
  INDEX `tmp_cron` (`ttl` ASC))
ENGINE = InnoDB
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`awards`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`awards` (
  `award_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The award’s unique ID.',
  `name` VARCHAR(100) NOT NULL COMMENT 'The award\'s unique English name.',
  `description` BLOB NULL COMMENT 'The award’s English description.',
  `dyn_names` BLOB NOT NULL COMMENT 'The award’s title translations.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The award’s description translations.',
  `created` TIMESTAMP NOT NULL COMMENT 'The timestamp this award was created.',
  PRIMARY KEY (`award_id`),
  UNIQUE INDEX `uq_awards_name` (`name` ASC))
ENGINE = InnoDB
COMMENT = 'Contains all job related data.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_awards`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_awards` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie\'s unique ID.',
  `award_count` INT NOT NULL COMMENT 'The award\'s number for a movie.',
  `award_id` INT UNSIGNED NOT NULL COMMENT 'The award\'s unique ID.',
  `award_category_id` INT UNSIGNED NULL COMMENT 'The award category´s ID.',
  `person_id` BIGINT UNSIGNED NULL COMMENT 'The unique ID of the person who received the award.',
  `company_id` BIGINT UNSIGNED NULL COMMENT 'The unique ID of the company which received the award.',
  `year` SMALLINT UNSIGNED NOT NULL COMMENT 'The year the award has been given to the movie.',
  `won` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Flag, whether the award has been won (true), or if there was only the nomination (false).',
  PRIMARY KEY (`movie_id`, `award_count`),
  INDEX `fk_awards_movies_movies` (`movie_id` ASC),
  INDEX `fk_awards_movies_awards` (`award_id` ASC),
  INDEX `fk_persons_awards_persons` (`person_id` ASC),
  INDEX `fk_persons_awards_companies` (`company_id` ASC),
  CONSTRAINT `fk_movies_awards_awards`
    FOREIGN KEY (`award_id`)
    REFERENCES `movlib`.`awards` (`award_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_awards_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_awards_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_awards_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`titles` (
  `id` INT UNSIGNED NOT NULL DEFAULT 1,
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie\'s unique ID this title relates to.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The title\'s ISO alpha-2 language code.',
  `title` BLOB NOT NULL COMMENT 'The movie\'s title.',
  `dyn_comments` BLOB NOT NULL COMMENT 'The translatable comment for this title.',
  INDEX `fk_titles_movies` (`movie_id` ASC),
  PRIMARY KEY (`id`, `movie_id`),
  CONSTRAINT `fk_titles_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`taglines`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`taglines` (
  `id` INT UNSIGNED NOT NULL DEFAULT 1,
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie\'s unique ID this tagline relates to.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The tagline\'s ISO alpha-2 language code.',
  `tagline` BLOB NOT NULL COMMENT 'The movie\'s tagline.',
  `dyn_comments` BLOB NOT NULL COMMENT 'The tagline\'s translatable comment.',
  INDEX `fk_taglines_movies` (`movie_id` ASC),
  PRIMARY KEY (`id`, `movie_id`),
  CONSTRAINT `fk_taglines_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`relationship_types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`relationship_types` (
  `relationship_type_id` BIGINT NOT NULL AUTO_INCREMENT COMMENT 'The relationship type\'s unique ID.',
  `name` VARCHAR(100) NOT NULL COMMENT 'The relationship type\'s unique English name.',
  `description` BLOB NULL COMMENT 'The relationship type\'s English description.',
  `dyn_names` BLOB NOT NULL COMMENT 'The relationship type\'s name translations.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The relationship type\'s description translations.',
  PRIMARY KEY (`relationship_type_id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_trailers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_trailers` (
  `id` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The movie trailer’s unique ID within the movie.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The movie trailer’s ISO alpha-2 language code.',
  `url` VARCHAR(255) NOT NULL,
  `country_code` CHAR(2) NULL COMMENT 'The movie trailer’s ISO alpha-2 country code.',
  PRIMARY KEY (`id`, `movie_id`),
  CONSTRAINT `fk_movies_trailers_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all movie trailsers.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_images`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_images` (
  `id` BIGINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The movie image’s unique ID within the movie.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `type_id` TINYINT UNSIGNED NOT NULL COMMENT 'The movie image’s type (enum from Data class).',
  `deleted` TINYINT(1) NOT NULL DEFAULT true COMMENT 'The flag that determines whether this movie image is marked as deleted (TRUE(1)) or not (FALSE(0)), default is TRUE(1).',
  `upvotes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie image’s upvote count.',
  `license_id` BIGINT UNSIGNED NULL COMMENT 'The license’s unique ID.',
  `user_id` BIGINT UNSIGNED NULL COMMENT 'The user’s unique ID.',
  `changed` TIMESTAMP NULL COMMENT 'The last time this movie image was updated as timestamp.',
  `country_code` CHAR(2) NULL COMMENT 'The movie image’s ISO alpha-2 country code.',
  `created` TIMESTAMP NULL COMMENT 'The movie image’s creation time as timestamp.',
  `dyn_descriptions` BLOB NULL COMMENT 'The movie image’s description in various languages. Keys are ISO alpha-2 language codes.',
  `extension` CHAR(3) NULL COMMENT 'The movie image’s extension without leading dot.',
  `filesize` INT UNSIGNED NULL COMMENT 'The movie image’s original size in Bytes.',
  `height` SMALLINT UNSIGNED NULL COMMENT 'The movie image’s original height.',
  `styles` BLOB NULL COMMENT 'Serialized array containing width and height of various image styles.',
  `width` SMALLINT UNSIGNED NULL COMMENT 'The movie image’s original width.',
  PRIMARY KEY (`id`, `movie_id`, `type_id`),
  INDEX `fk_posters_movies` (`movie_id` ASC),
  INDEX `fk_movies_images_licenses` (`license_id` ASC),
  INDEX `movies_images_type_id` (`type_id` ASC, `upvotes` ASC),
  INDEX `fk_movies_images_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_movies_images_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_images_licenses`
    FOREIGN KEY (`license_id`)
    REFERENCES `movlib`.`licenses` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_images_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all movie images.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series` unique ID.',
  `original_title` BLOB NOT NULL COMMENT 'The series\' original title.',
  `rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The Bayes\'theorem rating of this series.\n\nrating = (s / (s + m)) * N + (m / (s + m)) * K\n\nN: arithmetic mean rating\ns: vote count\nm: minimum vote count\nK: arithmetic mean vote\n\nThe same formula is used by IMDb and OFDb.',
  `mean_rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The series’ arithmetic mean rating.',
  `votes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The series’ vote count.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'TRUE (1) if this series was deleted, default is FALSE (0).',
  `start_year` SMALLINT NULL COMMENT 'The year the series was aired for the first time.',
  `end_year` SMALLINT NULL COMMENT 'The year the series was cancelled.',
  `rank` BIGINT UNSIGNED NULL COMMENT 'The series’ global rank.',
  `dyn_synopses` BLOB NOT NULL COMMENT 'The series’ translatable synopses.',
  `bin_relationships` BLOB NULL COMMENT 'The series´ relations to other series, e.g. sequel.\nStored in igbinary serialized format.',
  `created` TIMESTAMP NOT NULL COMMENT 'The timestamp this series was created.',
  `commit` CHAR(40) NULL COMMENT 'The series\' last commit sha-1 hash.',
  PRIMARY KEY (`series_id`))
ENGINE = InnoDB
COMMENT = 'Contains all series data.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_seasons`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_seasons` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series´ unique ID.',
  `seasons_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season´s  number within the series.',
  `start_year` SMALLINT NULL COMMENT 'The year the season started airing for the first time.',
  `end_year` SMALLINT NULL COMMENT 'The year the season ended for the first time.',
  `created` TIMESTAMP NOT NULL COMMENT 'The timestamp this season was created.',
  PRIMARY KEY (`series_id`, `seasons_number`),
  INDEX `fk_series_seasons_series` (`series_id` ASC),
  CONSTRAINT `fk_series_seasons_series`
    FOREIGN KEY (`series_id`)
    REFERENCES `movlib`.`series` (`series_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains seasons data for a series.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_titles` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series` unique ID.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The series title\'s ISO aplha-2 language code.',
  `title` BLOB NOT NULL COMMENT 'The series´ title.',
  `dyn_comments` BLOB NOT NULL COMMENT 'The translatable comment for this title.',
  `is_display_title` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Determines whether this is the title to diplay in the localized site or not.',
  INDEX `fk_series_titles_series` (`series_id` ASC),
  CONSTRAINT `fk_series_titles_series`
    FOREIGN KEY (`series_id`)
    REFERENCES `movlib`.`series` (`series_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'A series has many titles, a title belongs to one series. Con /* comment truncated */ /*tains language specific titles for series.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`seasons_episodes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`seasons_episodes` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series´ unique ID.',
  `seasons_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season´s number this episode belongs to.',
  `position` SMALLINT UNSIGNED NOT NULL COMMENT 'The episode´s chronological position within the season.',
  `episode_number` TINYTEXT NULL COMMENT 'The episodes number within the season (e.g. 01, but also 0102 if it contains two episodes).',
  `original_air_date` DATE NULL COMMENT 'The date the episode was originally aired.',
  `original_title` BLOB NOT NULL COMMENT 'The episode´s original title.',
  `created` TIMESTAMP NOT NULL COMMENT 'The timestamp this episode was created.',
  PRIMARY KEY (`series_id`, `seasons_number`, `position`),
  INDEX `fk_seasons_episodes_series_seasons` (`series_id` ASC, `seasons_number` ASC),
  CONSTRAINT `fk_seasons_episodes_series_seasons`
    FOREIGN KEY (`series_id` , `seasons_number`)
    REFERENCES `movlib`.`series_seasons` (`series_id` , `seasons_number`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all episode data of episodes belonging to seasons w /* comment truncated */ /*hich belong to series.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`episodes_titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`episodes_titles` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series\' unique ID.',
  `seasons_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season number within the series.',
  `position` SMALLINT UNSIGNED NOT NULL COMMENT 'The episode´s chronological position within the season.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The language\'s unique ID this title is in.',
  `title` BLOB NOT NULL COMMENT 'The episode´s title.',
  `dyn_comments` BLOB NOT NULL COMMENT 'The translatable comment for this episode title.',
  `is_display_title` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Determine if this episode title is the display title in the specified language.',
  INDEX `fk_episodes_titles_seasons_episodes` (`series_id` ASC, `seasons_number` ASC, `position` ASC),
  CONSTRAINT `fk_episodes_titles_seasons_episodes`
    FOREIGN KEY (`series_id` , `seasons_number` , `position`)
    REFERENCES `movlib`.`seasons_episodes` (`series_id` , `seasons_number` , `position`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all episode data of episodes belonging to seasons w /* comment truncated */ /*hich belong to series.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_genres`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_genres` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series\' unique ID.',
  `genre_id` INT UNSIGNED NOT NULL COMMENT 'The genre\'s unique ID.',
  PRIMARY KEY (`series_id`, `genre_id`),
  INDEX `fk_series_genres_genres` (`genre_id` ASC),
  INDEX `fk_series_genres_series` (`series_id` ASC),
  CONSTRAINT `fk_series_genres_series`
    FOREIGN KEY (`series_id`)
    REFERENCES `movlib`.`series` (`series_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_genres_genres`
    FOREIGN KEY (`genre_id`)
    REFERENCES `movlib`.`genres` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_styles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_styles` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series\' unique ID.',
  `style_id` INT UNSIGNED NOT NULL COMMENT 'The style\'s unique ID.',
  PRIMARY KEY (`series_id`, `style_id`),
  INDEX `fk_series_styles_styles` (`style_id` ASC),
  INDEX `fk_series_styles_series` (`series_id` ASC),
  CONSTRAINT `fk_series_styles_series`
    FOREIGN KEY (`series_id`)
    REFERENCES `movlib`.`series` (`series_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_styles_styles`
    FOREIGN KEY (`style_id`)
    REFERENCES `movlib`.`styles` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`articles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`articles` (
  `article_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The article´s unique identifier.',
  `dyn_titles` BLOB NOT NULL COMMENT 'The article´s translated titles.',
  `dyn_texts` BLOB NOT NULL COMMENT 'The article´s translated text.',
  `admin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Determines whether the article can be edited by users (FALSE - 0) or not (TRUE - 1). Defaults to FALSE (0).',
  `commit` CHAR(40) NULL COMMENT 'The article\'s last commit sha-1 hash.',
  PRIMARY KEY (`article_id`))
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`aspect_ratios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`aspect_ratios` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The aspect ratio’s unique ID.',
  `name` TINYTEXT NOT NULL COMMENT 'The aspect ratio’s name.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all aspect ratios.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`packaging`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`packaging` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The packaging’s unique ID.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The packaging’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names` BLOB NOT NULL COMMENT 'The packaging´s translatable names.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all available packaging variants.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`master_releases`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`master_releases` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The master release’s unique ID.',
  `country_code` CHAR(2) NOT NULL COMMENT 'The master release’s ISO alpha-2 country code.',
  `created` TIMESTAMP NOT NULL COMMENT 'The creation date of the master release as timestamp.',
  `dyn_notes` BLOB NOT NULL COMMENT 'The master release’s release notes in various languages. Keys are ISO alpha-2 language codes.',
  `title` BLOB NOT NULL COMMENT 'The master release’s title.',
  `commit` CHAR(40) NULL COMMENT 'The master release’s last history commit sha-1 hash.',
  `packaging_id` INT UNSIGNED NULL COMMENT 'The master release’s packaging. Only present if the master release contains multiple releases (e.g. a movie collection box).',
  `release_date` DATE NULL COMMENT 'The publishing date of the master release.',
  PRIMARY KEY (`id`),
  INDEX `fk_master_releases_packaging` (`packaging_id` ASC),
  CONSTRAINT `fk_master_releases_packaging`
    FOREIGN KEY (`packaging_id`)
    REFERENCES `movlib`.`packaging` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all master releases. A master release contains one  /* comment truncated */ /*or more (e.g. a movie collection box) releases.*/'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`releases_types` (
  `id` INT UNSIGNED NOT NULL COMMENT 'The release type’s unique ID.',
  `abbreviation` TINYBLOB NOT NULL COMMENT 'The release type’s abbreviation.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The release type’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names` BLOB NOT NULL COMMENT 'The release type’s name in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'The release types (e.g. DVD, VHS,..).'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`releases` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The release’s unique ID.',
  `aspect_ratio_id` INT UNSIGNED NOT NULL COMMENT 'The aspect ratio’s unique ID.',
  `dyn_extras` BLOB NOT NULL COMMENT 'The description of the release’s extras in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_notes` BLOB NOT NULL COMMENT 'The release’s release notes in various languages. Keys are ISO alpha-2 language codes.',
  `is_cut` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this release is cut or not. TRUE (1) if this release is marked as cutted, default is FALSE (0).',
  `master_release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The master release’s unique ID this release belongs to.',
  `packaging_id` INT UNSIGNED NOT NULL COMMENT 'The packaging’s unique ID.',
  `releases_type_id` INT UNSIGNED NOT NULL COMMENT 'The release type’s unique ID.',
  `length` TIME NULL COMMENT 'The release’s length without credits.',
  `length_bonus` TIME NULL COMMENT 'The length of this release\'s bonus material.',
  `length_credits` TIME NULL COMMENT 'The release’s length with credits.',
  INDEX `fk_releases_aspect_ratios` (`aspect_ratio_id` ASC),
  PRIMARY KEY (`id`),
  INDEX `fk_releases_packaging` (`packaging_id` ASC),
  INDEX `fk_releases_master_release` (`master_release_id` ASC),
  INDEX `fk_releases_releases_types1_idx` (`releases_type_id` ASC),
  CONSTRAINT `fk_releases_aspect_ratios`
    FOREIGN KEY (`aspect_ratio_id`)
    REFERENCES `movlib`.`aspect_ratios` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_master_releases`
    FOREIGN KEY (`master_release_id`)
    REFERENCES `movlib`.`master_releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_packaging`
    FOREIGN KEY (`packaging_id`)
    REFERENCES `movlib`.`packaging` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_releases_types1`
    FOREIGN KEY (`releases_type_id`)
    REFERENCES `movlib`.`releases_types` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all releases. Every release has to belong to a mast /* comment truncated */ /*er release.*/'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_releases`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_releases` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie´s unique ID.',
  `release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The release´s unique ID that is related to the movie.',
  PRIMARY KEY (`movie_id`, `release_id`),
  INDEX `fk_movies_releases_releases` (`release_id` ASC),
  INDEX `fk_movies_releases_movies` (`movie_id` ASC),
  CONSTRAINT `fk_movies_releases_movies1`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_releases_releases1`
    FOREIGN KEY (`release_id`)
    REFERENCES `movlib`.`releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_labels`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`releases_labels` (
  `company_id` BIGINT UNSIGNED NOT NULL COMMENT 'The company’s unique ID.',
  `release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The release’s unique ID.',
  PRIMARY KEY (`company_id`, `release_id`),
  INDEX `fk_releases_labels_companies` (`company_id` ASC),
  INDEX `fk_releases_labels_releases` (`release_id` ASC),
  CONSTRAINT `fk_releases_companies_releases`
    FOREIGN KEY (`release_id`)
    REFERENCES `movlib`.`releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_companies_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the label a release is related to.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`sound_formats`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`sound_formats` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The sound format’s unique ID.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The sound format’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names` BLOB NOT NULL COMMENT 'The sound format’s name in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all available sound formats.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_sound_formats`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`releases_sound_formats` (
  `language_code` CHAR(2) NOT NULL COMMENT 'The releases sound format’s ISO alpha-2 language code.',
  `release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The release’s unique ID.',
  `sound_format_id` INT UNSIGNED NOT NULL COMMENT 'The sound format’s unique ID.',
  `dyn_comments` BLOB NOT NULL COMMENT 'The release sound format’s comment in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`language_code`, `release_id`, `sound_format_id`),
  INDEX `fk_releases_sound_formats_sound_formats` (`sound_format_id` ASC),
  INDEX `fk_releases_sound_formats_releases` (`release_id` ASC),
  CONSTRAINT `fk_releases_sound_formats_releases`
    FOREIGN KEY (`release_id`)
    REFERENCES `movlib`.`releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_sound_formats_sound_formats`
    FOREIGN KEY (`sound_format_id`)
    REFERENCES `movlib`.`sound_formats` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the sound formats a release has.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_subtitles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`releases_subtitles` (
  `is_hearing_impaired` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Flag that determines whether the subtitle is hearing impaired or not (defaults to FALSE).',
  `language_code` CHAR(2) NOT NULL COMMENT 'The releases subtitle’s ISO alpha-2 language code.',
  `release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The release subtitle’s unique ID.',
  `dyn_comments` BLOB NOT NULL COMMENT 'The release subtitle’s comment in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`is_hearing_impaired`, `language_code`, `release_id`),
  CONSTRAINT `fk_releases_subtitles_releases`
    FOREIGN KEY (`release_id`)
    REFERENCES `movlib`.`releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains information about subtitles belonging to releases.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`master_releases_labels`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`master_releases_labels` (
  `company_id` BIGINT UNSIGNED NOT NULL COMMENT 'The company’s unique ID.',
  `master_release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The master release’s unique ID.',
  PRIMARY KEY (`company_id`, `master_release_id`),
  INDEX `fk_master_releases_labels_companies` (`company_id` ASC),
  INDEX `fk_master_releases_labels_master_releases` (`master_release_id` ASC),
  CONSTRAINT `fk_master_releases_labels_master_releases`
    FOREIGN KEY (`master_release_id`)
    REFERENCES `movlib`.`master_releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_master_releases_labels_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the label a master release is related to.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`awards_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`awards_categories` (
  `award_id` INT UNSIGNED NOT NULL COMMENT 'The award’s unique ID.',
  `award_category_id` INT UNSIGNED NOT NULL COMMENT 'The award category’s ID within the award.',
  `name` VARCHAR(100) NOT NULL COMMENT 'The award category´s English name.',
  `description` BLOB NULL COMMENT 'The award category’s English description.',
  `dyn_names` BLOB NOT NULL COMMENT 'The award category’s title translations.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The award category’s description translations.',
  PRIMARY KEY (`award_id`, `award_category_id`),
  CONSTRAINT `fk_awards_categories_awards`
    FOREIGN KEY (`award_id`)
    REFERENCES `movlib`.`awards` (`award_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_ratings`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_ratings` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique identifier.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique identifier.',
  `rating` TINYINT(1) UNSIGNED NOT NULL COMMENT 'The user’s rating for this movie (1-5).',
  PRIMARY KEY (`movie_id`, `user_id`),
  INDEX `fk_movies_ratings_users` (`user_id` ASC),
  INDEX `fk_movies_ratings_movies` (`movie_id` ASC),
  CONSTRAINT `fk_movies_ratings_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_ratings_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all movie ratings by users.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_relationships`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_relationships` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The first movie\'s unique ID in this relationship.',
  `movie_id_other` BIGINT UNSIGNED NOT NULL COMMENT 'The second movie\'s unique ID in this relationship.',
  `relationship_type_id` BIGINT NOT NULL COMMENT 'The unique ID of the relationship type.',
  PRIMARY KEY (`movie_id`, `movie_id_other`, `relationship_type_id`),
  INDEX `fk_movies_relationships_relationship_types` (`relationship_type_id` ASC),
  INDEX `fk_movies_relationships_movies` (`movie_id` ASC),
  INDEX `fk_movies_relationships_movies_other` (`movie_id_other` ASC),
  CONSTRAINT `fk_movies_relationships_relationship_types`
    FOREIGN KEY (`relationship_type_id`)
    REFERENCES `movlib`.`relationship_types` (`relationship_type_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_relationships_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_relationships_movies_other`
    FOREIGN KEY (`movie_id_other`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`sessions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`sessions` (
  `session_id` VARBINARY(86) NOT NULL COMMENT 'The session\'s unique ID.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The session\'s unique user ID.',
  `authentication` TIMESTAMP NOT NULL COMMENT 'Timestamp when this session was initialized.',
  `ip_address` BLOB NOT NULL COMMENT 'The session\'s IP address.',
  `user_agent` BLOB NOT NULL COMMENT 'The session\'s user agent string.',
  PRIMARY KEY (`session_id`, `user_id`),
  INDEX `fk_sessions_users` (`user_id` ASC),
  CONSTRAINT `fk_sessions_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Persistent session storage.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 16;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_titles` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie\'s unique ID this title relates to.',
  `commit` CHAR(40) NULL,
  `display_title_en` INT NULL,
  `display_title_de` INT NULL,
  INDEX `fk_movies_titles_movies` (`movie_id` ASC),
  PRIMARY KEY (`movie_id`),
  INDEX `fk_movies_titles_display_title_en_idx` (`display_title_en` ASC),
  INDEX `fk_movies_titles_display_title_de_idx` (`display_title_de` ASC),
  CONSTRAINT `fk_movies_titles_movie_id`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_titles_display_title_en`
    FOREIGN KEY (`display_title_en`)
    REFERENCES `movlib`.`titles` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_titles_display_title_de`
    FOREIGN KEY (`display_title_de`)
    REFERENCES `movlib`.`titles` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_taglines`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_taglines` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie\'s unique ID this tagline relates to.',
  `commit` CHAR(40) NULL,
  INDEX `fk_movies_taglines_movies` (`movie_id` ASC),
  PRIMARY KEY (`movie_id`),
  CONSTRAINT `fk_movies_taglines_movie`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users_collections`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`users_collections` (
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique identifier.',
  `master_release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The Master release’s unique identifier.',
  `count` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The amount of identical releases.',
  `currency_code` CHAR(3) NULL COMMENT 'The user’s ISO 4217 currency code.',
  `price` FLOAT UNSIGNED NULL COMMENT 'The price of the release.',
  `purchased_at` TINYTEXT NULL COMMENT 'The location where the release was purchased.',
  PRIMARY KEY (`user_id`, `master_release_id`),
  INDEX `fk_user_collection_users_idx` (`user_id` ASC),
  INDEX `fk_users_collections_master_releases_idx` (`master_release_id` ASC),
  CONSTRAINT `fk_user_collection_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_collections_master_releases`
    FOREIGN KEY (`master_release_id`)
    REFERENCES `movlib`.`master_releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all related data to an user collection.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users_lists`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`users_lists` (
  `id` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The user’s list’s unique ID.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique ID.',
  `type_id` TINYINT UNSIGNED NOT NULL COMMENT 'The user’s list’s type (enum from Data class).',
  `dyn_names` BLOB NOT NULL COMMENT 'The user’s list’s translated names.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The user’s list’s translated descriptions.',
  PRIMARY KEY (`id`, `user_id`, `type_id`),
  INDEX `fk_users_lists_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_users_lists_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all related data to users lists.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users_movies_lists`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`users_movies_lists` (
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique ID.',
  `users_lists_id` INT UNSIGNED NOT NULL COMMENT 'The user’s list’s unique ID.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  PRIMARY KEY (`user_id`, `users_lists_id`, `movie_id`),
  INDEX `fk_users_movielists_movies_idx` (`movie_id` ASC),
  INDEX `fk_users_movies_lists_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_users_movielists_users_lists`
    FOREIGN KEY (`users_lists_id`)
    REFERENCES `movlib`.`users_lists` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_movielists_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_movies_lists_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all related data to users movies lists.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users_persons_lists`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`users_persons_lists` (
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique ID.',
  `users_lists_id` INT UNSIGNED NOT NULL COMMENT 'The user’s list’s unique ID.',
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique ID.',
  PRIMARY KEY (`user_id`, `users_lists_id`, `person_id`),
  INDEX `fk_users_person_lists_persons_idx` (`person_id` ASC),
  INDEX `fk_users_persons_lists_users1_idx` (`user_id` ASC),
  CONSTRAINT `fk_users_person_lists_users_lists`
    FOREIGN KEY (`users_lists_id`)
    REFERENCES `movlib`.`users_lists` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_person_lists_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_persons_lists_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all related data to users persons lists.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_identifiers_types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`releases_identifiers_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The release identifier type’s unique ID.',
  `abbreviation` TINYBLOB NOT NULL COMMENT 'The release identifier type’s abbreviation.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The release identifier type’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names` BLOB NOT NULL COMMENT 'The release identifier type’s name in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all release identifier types (e.g. EAN, GTIN, JAN,. /* comment truncated */ /*.)*/'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_identifiers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`releases_identifiers` (
  `releases_identifiers_type_id` INT UNSIGNED NOT NULL COMMENT 'The release identifier type’s unique ID.',
  `release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The release’s unique ID.',
  `identifier` TINYBLOB NOT NULL COMMENT 'The release identifier’s unique identifier.',
  INDEX `fk_releases_identifiers_releases_identifiers_types_idx` (`releases_identifiers_type_id` ASC),
  INDEX `fk_releases_identifiers_releases_idx` (`release_id` ASC),
  CONSTRAINT `fk_releases_identifiers_releases_identifiers_types`
    FOREIGN KEY (`releases_identifiers_type_id`)
    REFERENCES `movlib`.`releases_identifiers_types` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_identifiers_releases`
    FOREIGN KEY (`release_id`)
    REFERENCES `movlib`.`releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all release identifiers belonging to releases.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`master_releases_identifiers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`master_releases_identifiers` (
  `releases_identifiers_type_id` INT UNSIGNED NOT NULL COMMENT 'The release identifier type’s unique ID.',
  `master_release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The master release’s unique ID.',
  `identifier` TINYBLOB NOT NULL COMMENT 'The release identifier’s unique identifier.',
  INDEX `fk_master_releases_identifiers_releases_identifiers_types_idx` (`releases_identifiers_type_id` ASC),
  INDEX `fk_master_releases_identifiers_master_releases_idx` (`master_release_id` ASC),
  CONSTRAINT `fk_master_releases_identifiers_releases_identifiers_types`
    FOREIGN KEY (`releases_identifiers_type_id`)
    REFERENCES `movlib`.`releases_identifiers_types` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_master_releases_identifiers_master_releases`
    FOREIGN KEY (`master_release_id`)
    REFERENCES `movlib`.`master_releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all release identifiers belonging to master release /* comment truncated */ /*s.*/';

SHOW WARNINGS;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
