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
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The creation date of the movie as timestamp.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether the movie is marked as deleted (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `dyn_links` BLOB NOT NULL COMMENT 'External links to the official website in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_synopses` BLOB NOT NULL COMMENT 'The synopsis of the movie in various languages. Keys are ISO alpha-2 language codes.',
  `mean_rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s arithmetic mean rating.',
  `original_title` BLOB NOT NULL COMMENT 'The movie’s original title.',
  `original_title_language_code` CHAR(2) NOT NULL COMMENT 'The movie’s original title ISO alpha-2 language code.',
  `rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The Bayes\'theorem rating of this movie.\n\nrating = (s / (s + m)) * N + (m / (s + m)) * K\n\nN: arithmetic mean rating\ns: vote count\nm: minimum vote count\nK: arithmetic mean vote\n\nThe same formula is used by IMDb and OFDb.',
  `votes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s vote count.',
  `commit` CHAR(40) NULL COMMENT 'The movie\'s last history commit sha-1 hash.',
  `rank` BIGINT UNSIGNED NULL COMMENT 'The movie’s global rank.',
  `runtime` SMALLINT UNSIGNED NULL COMMENT 'The movie’s approximate runtime in minutes.',
  `year` SMALLINT(4) ZEROFILL UNSIGNED NULL COMMENT 'The movie’s initial release year.',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uq_movies_rank` (`rank` ASC),
  INDEX `movies_deleted` (`deleted` ASC),
  INDEX `movies_created` (`created` DESC))
ENGINE = InnoDB
COMMENT = 'Contains all basic movie data.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`genres`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`genres` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The genre’s unique ID.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The genre’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names` BLOB NOT NULL COMMENT 'The genre’s name in various languages. Keys are ISO alpha-2 language codes.',
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
  `genre_id` BIGINT UNSIGNED NOT NULL COMMENT 'The genre’s unique ID.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  PRIMARY KEY (`genre_id`, `movie_id`),
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
-- Table `movlib`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The user’s unique ID.',
  `name` VARCHAR(40) NOT NULL COMMENT 'The user’s unique name.',
  `access` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The user’s last access timestamp.',
  `admin` TINYINT(1) NULL DEFAULT FALSE,
  `birthday` DATE NULL DEFAULT NULL COMMENT 'The user’s date of birth.',
  `country_code` CHAR(2) NULL COMMENT 'The user’s ISO alpha-2 country code.',
  `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The user’s accout creation timestamp.',
  `currency_code` CHAR(3) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL COMMENT 'The user’s ISO 4217  (3 letter) currency code.',
  `dyn_about_me` BLOB NULL COMMENT 'The user’s about me text in various languages. Keys are ISO alpha-2 language codes.',
  `edits` BIGINT UNSIGNED NULL DEFAULT 0 COMMENT 'The user’s edit counter.',
  `email` VARCHAR(254) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL COMMENT 'The user’s unique email address.',
  `image_changed` TIMESTAMP NULL DEFAULT NULL COMMENT 'The last changed timestamp of the user’s avatar.',
  `image_extension` CHAR(3) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL DEFAULT NULL COMMENT 'The file extension of the user’s avatar.',
  `password` VARBINARY(255) NULL COMMENT 'The user’s password (hashed).',
  `private` TINYINT(1) NULL DEFAULT false COMMENT 'The flag that determines whether this user allows us to display private data on his profile page (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `profile_views` BIGINT UNSIGNED NULL DEFAULT 0 COMMENT 'The user’s profile view count.',
  `real_name` TINYBLOB NULL COMMENT 'The user’s real name.',
  `reputation` BIGINT UNSIGNED NULL DEFAULT 0 COMMENT 'The user’s reputation.',
  `sex` TINYINT UNSIGNED NULL DEFAULT 0 COMMENT 'The user\'s sex according to ISO 5218.\n\n0 = not known\n1 = male\n2 = female\n9 = not applicable',
  `system_language_code` CHAR(2) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL DEFAULT 'en' COMMENT 'The user’s preferred system language’s code (e.g. en).',
  `time_zone_identifier` VARCHAR(30) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL DEFAULT 'UTC' COMMENT 'User’s time zone ID.',
  `website` TINYTEXT NULL COMMENT 'The user’s website URL.',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uq_users_name` (`name` ASC),
  UNIQUE INDEX `uq_users_email` (`email` ASC),
  INDEX `users_created` (`created` ASC))
ENGINE = InnoDB
COMMENT = 'Contains all users.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`places`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`places` (
  `place_id` BIGINT UNSIGNED NOT NULL COMMENT 'The place’s unique OpenStreetMap node ID.',
  `country_code` CHAR(2) NOT NULL COMMENT 'The place’s ISO alpha-2 country code.',
  `dyn_names` BLOB NOT NULL COMMENT 'The place’s translated name.',
  `latitude` FLOAT NOT NULL COMMENT 'The place’s latitude.',
  `longitude` FLOAT NOT NULL COMMENT 'The place’s longitude.',
  PRIMARY KEY (`place_id`))
ENGINE = InnoDB
COMMENT = 'Contains unique place information (OpenStreetMap node ID, la /* comment truncated */ /*titude, longitude).*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`causes_of_death`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`causes_of_death` (
  `id` BIGINT UNSIGNED NOT NULL COMMENT 'The cause’s unique identifier.',
  `dyn_names` BLOB NOT NULL COMMENT 'The translated cause.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all available causes of death including translation /* comment truncated */ /*s.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`persons` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The person’s unique ID.',
  `created` TIMESTAMP NOT NULL COMMENT 'The creation date of the person as timestamp.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this person is marked as deleted (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `dyn_biographies` BLOB NOT NULL COMMENT 'The person’s biography in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_wikipedia` BLOB NOT NULL COMMENT 'The person\'s Wikipedia link in various languages. The language code serves as key.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The person’s full name.',
  `sex` TINYINT NOT NULL DEFAULT 0 COMMENT 'The person\'s sex according to ISO 5218.\n\n0 = not known\n1 = male\n2 = female\n9 = not applicable',
  `aliases` BLOB NULL COMMENT 'The person’s aliases (serialized PHP array).',
  `birthdate` DATE NULL COMMENT 'The person’s date of birth.',
  `birthplace_id` BIGINT UNSIGNED NULL COMMENT 'The person’s birthplace.',
  `born_name` MEDIUMTEXT NULL COMMENT 'The person’s born name.',
  `cause_of_death_id` BIGINT UNSIGNED NULL,
  `city` TINYBLOB NULL COMMENT 'The person’s birth city.',
  `commit` CHAR(40) NULL COMMENT 'The person’s last history commit sha-1 hash.',
  `country` CHAR(2) NULL COMMENT 'The person’s birth country.',
  `deathdate` DATE NULL COMMENT 'The person’s date of death.',
  `deathplace_id` BIGINT UNSIGNED NULL COMMENT 'The person’s death place.',
  `links` BLOB NULL COMMENT 'External links belonging to this person. Stored as serialized PHP array.',
  `nickname` MEDIUMTEXT NULL COMMENT 'The person’s nickname.',
  `region` TINYBLOB NULL COMMENT 'The person’s birth region.',
  PRIMARY KEY (`id`),
  INDEX `fk_persons_places1_idx` (`birthplace_id` ASC),
  INDEX `fk_persons_places2_idx` (`deathplace_id` ASC),
  INDEX `fk_persons_causes_of_death1_idx` (`cause_of_death_id` ASC),
  CONSTRAINT `fk_persons_places1`
    FOREIGN KEY (`birthplace_id`)
    REFERENCES `movlib`.`places` (`place_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_places2`
    FOREIGN KEY (`deathplace_id`)
    REFERENCES `movlib`.`places` (`place_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_causes_of_death1`
    FOREIGN KEY (`cause_of_death_id`)
    REFERENCES `movlib`.`causes_of_death` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all persons.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`jobs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The job’s unique ID.',
  `created` TIMESTAMP NOT NULL COMMENT 'The timestamp this job was created.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The job’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_titles` BLOB NOT NULL COMMENT 'The job’s title in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all jobs.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

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
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all companies.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_crew`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_crew` (
  `crew_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The crew’s unique ID within the movie.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `job_id` BIGINT UNSIGNED NOT NULL COMMENT 'The job’s unique ID.',
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
    REFERENCES `movlib`.`jobs` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_crew_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_crew_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the crew of a movie.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`licenses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`licenses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The license’s unique ID.',
  `abbreviation` VARCHAR(20) NOT NULL COMMENT 'The license’s abbreviation.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The license’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names` BLOB NOT NULL COMMENT 'The license’s name in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_url` BLOB NOT NULL COMMENT 'The license’s url pointing to various languages. Keys are ISO alpha-2 language codes.',
  `icon_changed` TIMESTAMP NOT NULL COMMENT 'The license’s icon changed timestamp.',
  `icon_extension` CHAR(3) NOT NULL COMMENT 'The license’s icon extension.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all licenses.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_images`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`persons_images` (
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
  INDEX `fk_persons_images_persons` (`person_id` ASC),
  INDEX `fk_persons_images_licenses` (`license_id` ASC),
  INDEX `persons_images_order_by_upvotes` (`upvotes` ASC),
  INDEX `fk_persons_images_users` (`user_id` ASC),
  CONSTRAINT `fk_persons_images_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_images_licenses`
    FOREIGN KEY (`license_id`)
    REFERENCES `movlib`.`licenses` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_images_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = '<double-click to overwrite multiple objects>'
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
COMMENT = '<double-click to overwrite multiple objects>'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_cast`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_cast` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique ID.',
  `roles` BLOB NOT NULL COMMENT 'The names of the role the person played in the movie as comma separated list.',
  `weight` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The weight (display order) of the movie’s cast. Default is 0.',
  PRIMARY KEY (`movie_id`, `person_id`),
  INDEX `fk_movies_cast_movies` (`movie_id` ASC),
  INDEX `fk_movies_cast_persons` (`person_id` ASC),
  CONSTRAINT `fk_movies_cast_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_cast_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all the movie casts.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`messages`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The message’s unique ID.',
  `message` TEXT NOT NULL COMMENT 'The message’s unique english pattern.',
  `dyn_translations` BLOB NOT NULL COMMENT 'The message’s translations.',
  `comment` BLOB NULL COMMENT 'The message’s optional comment for translators.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all messages and their translations (I18n).'
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
  `weight` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The weight (display order) of the movie’s director. Default is 0.',
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
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the directors of a movie.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`tmp`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`tmp` (
  `key` VARCHAR(255) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NOT NULL COMMENT 'The record’s unique key.',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The record’s creation timestamp.',
  `data` BLOB NOT NULL COMMENT 'The record’s serialized data.',
  `ttl` VARCHAR(16) NOT NULL COMMENT 'The record’s time to life.',
  PRIMARY KEY (`key`),
  INDEX `tmp_created` (`created` ASC),
  INDEX `tmp_cron` (`ttl` ASC))
ENGINE = InnoDB
COMMENT = 'Contains temporary data.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`awards`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`awards` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The award’s unique ID.',
  `created` TIMESTAMP NOT NULL COMMENT 'The timestamp on which this award was created.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The award’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names` BLOB NOT NULL COMMENT 'The award’s name in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all awards.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_awards`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_awards` (
  `id` BIGINT NOT NULL COMMENT 'The movie award’s unique ID within a movie.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `company_id` BIGINT UNSIGNED NULL COMMENT 'The company’s unique ID (who received the award).',
  `person_id` BIGINT UNSIGNED NULL COMMENT 'The person’s unique ID (who received the award).',
  `won` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this award has been won (TRUE(1)) or not (FALSE(0), default is FALSE (0).',
  `year` SMALLINT(4) UNSIGNED ZEROFILL NOT NULL COMMENT 'The year in which the movie won the award.',
  `award_category_id` BIGINT UNSIGNED NULL COMMENT 'The award category’s unique ID.',
  PRIMARY KEY (`id`, `movie_id`),
  INDEX `fk_awards_movies_movies` (`movie_id` ASC),
  INDEX `fk_persons_awards_persons` (`person_id` ASC),
  INDEX `fk_persons_awards_companies` (`company_id` ASC),
  CONSTRAINT `fk_movies_awards_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_awards_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_awards_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all awards belonging to movies.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`titles` (
  `id` BIGINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The title’s unique ID within the movie.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `display` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this title is the display title for its language (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `dyn_comments` BLOB NOT NULL COMMENT 'The title’s comment in various languages. Keys are ISO alpha-2 language codes.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The title’s ISO alpha-2 language code.',
  `title` BLOB NOT NULL COMMENT 'The movie’s title.',
  INDEX `fk_titles_movies` (`movie_id` ASC),
  PRIMARY KEY (`id`, `movie_id`),
  CONSTRAINT `fk_titles_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains movie titles.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`taglines`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`taglines` (
  `id` BIGINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The tagline’s unique ID within the movie.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `display` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this tagline is the display title for its language (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `dyn_comments` BLOB NOT NULL COMMENT 'The taglines’s comment in various languages. Keys are ISO alpha-2 language codes.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The tagline’s ISO alpha-2 language code.',
  `tagline` BLOB NOT NULL COMMENT 'The movie’s tagline.',
  INDEX `fk_taglines_movies` (`movie_id` ASC),
  PRIMARY KEY (`id`, `movie_id`),
  CONSTRAINT `fk_taglines_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all movie taglines.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`relationship_types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`relationship_types` (
  `id` BIGINT NOT NULL AUTO_INCREMENT COMMENT 'The relationship type\'s unique ID.',
  `dyn_names` BLOB NOT NULL COMMENT 'The relationship type’s name in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The relationship type’s description in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains relationship types.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_trailers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_trailers` (
  `id` BIGINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The movie trailer’s unique ID within the movie.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The movie trailer’s ISO alpha-2 language code.',
  `url` VARCHAR(255) NOT NULL COMMENT 'The movie trailer’s url, e.g. youtube.',
  `weight` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The weight (display order) of this trailer, default is 0.',
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
  `country_code` CHAR(2) NULL COMMENT 'The movie image’s ISO 3166-1 alpha-2 country code.',
  `created` TIMESTAMP NULL COMMENT 'The movie image’s creation time as timestamp.',
  `dyn_descriptions` BLOB NULL COMMENT 'The movie image’s description in various languages. Keys are ISO alpha-2 language codes.',
  `extension` CHAR(3) NULL COMMENT 'The movie image’s extension without leading dot.',
  `filesize` INT UNSIGNED NULL COMMENT 'The movie image’s original size in Bytes.',
  `height` SMALLINT UNSIGNED NULL COMMENT 'The movie image’s original height.',
  `language_code` CHAR(2) NULL COMMENT 'The movie image’s ISO 639-1 alpha-2 language code.',
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
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the series.',
  `created` TIMESTAMP NOT NULL COMMENT 'The creation date of the series as timestamp.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this series is marked as deleted (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `dyn_synopses` BLOB NOT NULL COMMENT 'The synopsis of the series in various languages. Keys are ISO alpha-2 language codes.',
  `original_title` BLOB NOT NULL COMMENT 'The original title of the series.',
  `original_title_language_code` CHAR(2) NOT NULL COMMENT 'The original title’s ISO alpha-2 language code of the series.',
  `mean_rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The arithmetic mean rating of the series.',
  `rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The Bayes\'theorem rating of the series.\n\nrating = (s / (s + m)) * N + (m / (s + m)) * K\n\nN: arithmetic mean rating\ns: vote count\nm: minimum vote count\nK: arithmetic mean vote\n\nThe same formula is used by IMDb and OFDb.',
  `votes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The vote count of the series.',
  `bin_relationships` BLOB NULL COMMENT 'The relations of the series to other series, e.g. sequel.\nStored in igbinary serialized format.',
  `commit` CHAR(40) NULL COMMENT 'The last history commit sha-1 hash of the series.',
  `end_year` SMALLINT(4) UNSIGNED ZEROFILL NULL COMMENT 'The year the series was cancelled.',
  `rank` BIGINT UNSIGNED NULL COMMENT 'The global rank of the series.',
  `start_year` SMALLINT(4) UNSIGNED ZEROFILL NULL COMMENT 'The year the series was aired for the first time.',
  PRIMARY KEY (`series_id`))
ENGINE = InnoDB
COMMENT = 'Contains all series.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_seasons`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_seasons` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the series.',
  `seasons_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season’s  number within the series.',
  `created` TIMESTAMP NOT NULL COMMENT 'The creation date of the season as timestamp.',
  `end_year` SMALLINT(4) UNSIGNED ZEROFILL NULL COMMENT 'The year the season ended.',
  `start_year` SMALLINT(4) UNSIGNED ZEROFILL NULL COMMENT 'The year the season started airing.',
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
  `id` BIGINT UNSIGNED NOT NULL COMMENT 'The series title’s ID within the series.',
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the series.',
  `display` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this series title is the display title for its language (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `dyn_comments` BLOB NOT NULL COMMENT 'The series title’s comment in various languages. Keys are ISO alpha-2 language codes.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The series title’s ISO 3166-1 aplha-2 language code.',
  `title` BLOB NOT NULL COMMENT 'The title of the series.',
  INDEX `fk_series_titles_series` (`series_id` ASC),
  PRIMARY KEY (`id`, `series_id`),
  CONSTRAINT `fk_series_titles_series`
    FOREIGN KEY (`series_id`)
    REFERENCES `movlib`.`series` (`series_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains language specific series titles.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`seasons_episodes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`seasons_episodes` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of he series.',
  `seasons_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season’s number this episode belongs to.',
  `position` SMALLINT UNSIGNED NOT NULL COMMENT 'The episode’s chronological position within the season.',
  `created` TIMESTAMP NOT NULL COMMENT 'The creation date of the episode as timestamp.',
  `original_title` BLOB NOT NULL COMMENT 'The episode’s original title.',
  `original_title_language_code` CHAR(2) NOT NULL COMMENT 'The episode’s original title ISO alpha-2 language code.',
  `episode_number` TINYTEXT NULL COMMENT 'The episodes number within the season (e.g. 01, but also 0102 if it contains two episodes).',
  `original_air_date` DATE NULL COMMENT 'The date the episode was originally aired.',
  PRIMARY KEY (`series_id`, `seasons_number`, `position`),
  INDEX `fk_seasons_episodes_series_seasons` (`series_id` ASC, `seasons_number` ASC),
  CONSTRAINT `fk_seasons_episodes_series_seasons`
    FOREIGN KEY (`series_id` , `seasons_number`)
    REFERENCES `movlib`.`series_seasons` (`series_id` , `seasons_number`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all episode data of episodes belonging to seasons w /* comment truncated */ /*hich belong to series.*/'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`episodes_titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`episodes_titles` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the series.',
  `seasons_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season number within the series.',
  `position` SMALLINT UNSIGNED NOT NULL COMMENT 'The episode’s chronological position within the season.',
  `display` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this episode title is the display title for its language (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `dyn_comments` BLOB NOT NULL COMMENT 'The episode title’s comment in various languages. Keys are ISO alpha-2 language codes.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The episode title’s ISO aplha-2 language code.',
  `title` BLOB NOT NULL COMMENT 'The episode’s title.',
  INDEX `fk_episodes_titles_seasons_episodes` (`series_id` ASC, `seasons_number` ASC, `position` ASC),
  CONSTRAINT `fk_episodes_titles_seasons_episodes`
    FOREIGN KEY (`series_id` , `seasons_number` , `position`)
    REFERENCES `movlib`.`seasons_episodes` (`series_id` , `seasons_number` , `position`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains episode’s titles.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_genres`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_genres` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the series.',
  `genre_id` BIGINT UNSIGNED NOT NULL COMMENT 'The genre’s unique ID.',
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
ENGINE = InnoDB
COMMENT = 'A series has many genres, a genre has many series.';

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
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_releases`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_releases` (
  `master_release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The master release’s unique ID.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  PRIMARY KEY (`master_release_id`, `movie_id`),
  INDEX `fk_movies_releases_movies` (`movie_id` ASC),
  INDEX `fk_movies_releases_master_releases_idx` (`master_release_id` ASC),
  CONSTRAINT `fk_movies_releases_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_releases_master_releases`
    FOREIGN KEY (`master_release_id`)
    REFERENCES `movlib`.`master_releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'A movie has many releases, a release has many movies.';

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
ENGINE = InnoDB;

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
  `impaired` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Flag that determines whether the subtitle is hearing impaired or not (defaults to FALSE).',
  `language_code` CHAR(2) NOT NULL COMMENT 'The releases subtitle’s ISO alpha-2 language code.',
  `release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The release subtitle’s unique ID.',
  `dyn_comments` BLOB NOT NULL COMMENT 'The release subtitle’s comment in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`impaired`, `language_code`, `release_id`),
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
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The award category’s ID within the award.',
  `award_id` BIGINT UNSIGNED NOT NULL COMMENT 'The award’s unique ID.',
  `created` TIMESTAMP NOT NULL COMMENT 'The timestamp on which this award category was created.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The award categorie’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names` BLOB NOT NULL COMMENT 'The award categorie’s name in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`id`, `award_id`),
  INDEX `fk_awards_categories_awards_idx` (`award_id` ASC),
  CONSTRAINT `fk_awards_categories_awards`
    FOREIGN KEY (`award_id`)
    REFERENCES `movlib`.`awards` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all award categories'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_ratings`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_ratings` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique identifier.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique identifier.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The creation date and time of the movie rating as timestamp.',
  `rating` TINYINT(1) UNSIGNED NOT NULL COMMENT 'The user’s rating for a certain movie (1-5).',
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
  `first_movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The first movie’s unique ID.',
  `second_movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The second movie’s unique ID.',
  `relationship_type_id` BIGINT NOT NULL COMMENT 'The movie relationship type’s unique ID.',
  PRIMARY KEY (`first_movie_id`, `second_movie_id`, `relationship_type_id`),
  INDEX `fk_movies_relationships_relationship_types` (`relationship_type_id` ASC),
  INDEX `fk_movies_relationships_first_movie_idx` (`first_movie_id` ASC),
  INDEX `fk_movies_relationships_second_movie_idx` (`second_movie_id` ASC),
  CONSTRAINT `fk_movies_relationships_relationship_types`
    FOREIGN KEY (`relationship_type_id`)
    REFERENCES `movlib`.`relationship_types` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_relationships_first_movie`
    FOREIGN KEY (`first_movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_relationships_second_movie`
    FOREIGN KEY (`second_movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains relationships between movies.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`sessions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`sessions` (
  `id` VARBINARY(86) NOT NULL COMMENT 'The session’s unique ID.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique ID.',
  `authentication` TIMESTAMP NOT NULL COMMENT 'Timestamp when this session was initialized.',
  `ip_address` VARBINARY(128) NOT NULL COMMENT 'The session’s IP address.',
  `user_agent` TINYBLOB NOT NULL COMMENT 'The session’s user agent string.',
  PRIMARY KEY (`id`, `user_id`),
  INDEX `fk_sessions_users` (`user_id` ASC),
  CONSTRAINT `fk_sessions_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Persistent session storage.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_titles` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `commit` CHAR(40) NULL COMMENT 'The last history commit sha-1 hash for a set of titles belonging to a movie.',
  INDEX `fk_movies_titles_movies` (`movie_id` ASC),
  PRIMARY KEY (`movie_id`),
  CONSTRAINT `fk_movies_titles_movie_id`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the sha-1 commit hashes used in the movie title his /* comment truncated */ /*tory.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_taglines`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_taglines` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `commit` CHAR(40) NULL COMMENT 'The last history commit sha-1 hash for a set of taglines belonging to a movie.',
  INDEX `fk_movies_taglines_movies` (`movie_id` ASC),
  PRIMARY KEY (`movie_id`),
  CONSTRAINT `fk_movies_taglines_movie`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the sha-1 commit hashes used in the movie tagline h /* comment truncated */ /*istory.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users_collections`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`users_collections` (
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique ID.',
  `master_release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The master release’s unique ID.',
  `count` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The amount of identical releases.',
  `currency_code` CHAR(3) NULL COMMENT 'The user’s ISO 4217 (3 letter) currency code.',
  `price` FLOAT UNSIGNED NULL COMMENT 'The purchase price of the release.',
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
COMMENT = 'Contains all user collections.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users_lists`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`users_lists` (
  `id` BIGINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The list’s unique ID within the user.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique ID.',
  `type_id` TINYINT UNSIGNED NOT NULL COMMENT 'The list’s type (enum from Data class).',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The list’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names` BLOB NOT NULL COMMENT 'The list’s name in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`id`, `user_id`, `type_id`),
  INDEX `fk_users_lists_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_users_lists_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users_movies_lists`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`users_movies_lists` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique ID.',
  `users_lists_id` BIGINT UNSIGNED NOT NULL COMMENT 'The users-list’s unique ID within the user.',
  PRIMARY KEY (`movie_id`, `user_id`, `users_lists_id`),
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
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users_persons_lists`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`users_persons_lists` (
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique ID.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique ID.',
  `users_lists_id` BIGINT UNSIGNED NOT NULL COMMENT 'The users-list’s unique ID within the user.',
  PRIMARY KEY (`person_id`, `user_id`, `users_lists_id`),
  INDEX `fk_users_person_lists_persons_idx` (`person_id` ASC),
  INDEX `fk_users_persons_lists_users1_idx` (`user_id` ASC),
  CONSTRAINT `fk_users_person_lists_users_lists`
    FOREIGN KEY (`users_lists_id`)
    REFERENCES `movlib`.`users_lists` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_person_lists_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_persons_lists_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains person lists of users.';

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

-- -----------------------------------------------------
-- Table `movlib`.`series_relationships`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_relationships` (
  `first_series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the first series.',
  `second_series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the second series.',
  `relationship_type_id` BIGINT NOT NULL COMMENT 'The relationship type’s unique ID.',
  PRIMARY KEY (`first_series_id`, `second_series_id`, `relationship_type_id`),
  INDEX `fk_series_relationships_second_series_idx` (`second_series_id` ASC),
  INDEX `fk_series_relationships_relationship_types1_idx` (`relationship_type_id` ASC),
  INDEX `fk_series_relationships_first_series_idx` (`first_series_id` ASC),
  CONSTRAINT `fk_series_relationships_first_series`
    FOREIGN KEY (`first_series_id`)
    REFERENCES `movlib`.`series` (`series_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_relationships_second_series`
    FOREIGN KEY (`second_series_id`)
    REFERENCES `movlib`.`series` (`series_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_relationships_relationship_types`
    FOREIGN KEY (`relationship_type_id`)
    REFERENCES `movlib`.`relationship_types` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains relationships between series.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`help_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`help_categories` (
  `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The help category’s unique identifier.',
  `dyn_titles` BLOB NOT NULL COMMENT 'The help category’s translated titles.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`help_articles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`help_articles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The help’s unique identifier.',
  `category_id` TINYINT UNSIGNED NOT NULL,
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The creation date of the help as timestamp.',
  `dyn_texts` BLOB NOT NULL COMMENT 'The help’s text in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_titles` BLOB NOT NULL COMMENT 'The help’s title in various languages. Keys are ISO alpha-2 language codes.',
  `commit` CHAR(40) NULL COMMENT 'The help’s last history commit sha-1 hash.',
  PRIMARY KEY (`id`),
  INDEX `fk_help_articles_help_categories1_idx` (`category_id` ASC),
  CONSTRAINT `fk_help_articles_help_categories1`
    FOREIGN KEY (`category_id`)
    REFERENCES `movlib`.`help_categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all help articles.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`system_pages`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`system_pages` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT COMMENT 'The page’s unique identifier.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The creation date of the page as timestamp.',
  `dyn_titles` BLOB NOT NULL COMMENT 'Thepage’s text in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_texts` BLOB NOT NULL COMMENT 'The help’s title in various languages. Keys are ISO alpha-2 language codes.',
  `commit` CHAR(40) NULL COMMENT 'The article’s last history commit sha-1 hash.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all system pages, e.g. Imprint.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_images_votes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_images_votes` (
  `image_id` BIGINT UNSIGNED NOT NULL COMMENT 'The image’s identifier.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique identifier.',
  `type_id` TINYINT UNSIGNED NOT NULL COMMENT 'The movie image’s type identifier.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique identifier.',
  INDEX `fk_movies_images_votes_movies_images` (`image_id` ASC, `movie_id` ASC, `type_id` ASC),
  INDEX `fk_movies_images_votes_users` (`user_id` ASC),
  PRIMARY KEY (`image_id`, `movie_id`, `type_id`, `user_id`),
  CONSTRAINT `fk_movies_images_votes_movies_images`
    FOREIGN KEY (`image_id` , `movie_id` , `type_id`)
    REFERENCES `movlib`.`movies_images` (`id` , `movie_id` , `type_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_images_votes_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all user votes for movie images.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_images_votes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`persons_images_votes` (
  `image_id` BIGINT UNSIGNED NOT NULL COMMENT 'The image’s identifier.',
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique identifier.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique identifier.',
  INDEX `fk_persons_images_votes_persons_images` (`image_id` ASC, `person_id` ASC),
  INDEX `fk_persons_images_votes_users` (`user_id` ASC),
  PRIMARY KEY (`image_id`, `person_id`, `user_id`),
  CONSTRAINT `fk_persons_images_votes_persons_images`
    FOREIGN KEY (`image_id` , `person_id`)
    REFERENCES `movlib`.`persons_images` (`id` , `person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_images_votes_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all user votes for person images.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`companies_images_votes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`companies_images_votes` (
  `image_id` BIGINT UNSIGNED NOT NULL COMMENT 'The image’s identifier.',
  `company_id` BIGINT UNSIGNED NOT NULL COMMENT 'The company’s unique identifier.',
  `type_id` TINYINT UNSIGNED NOT NULL COMMENT 'The image’s type identifier.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique identifier.',
  INDEX `fk_companies_images_votes_companies_images` (`image_id` ASC, `company_id` ASC, `type_id` ASC),
  INDEX `fk_companies_images_votes_users` (`user_id` ASC),
  PRIMARY KEY (`image_id`, `company_id`, `type_id`, `user_id`),
  CONSTRAINT `fk_companies_images_votes_companies_images`
    FOREIGN KEY (`image_id` , `company_id` , `type_id`)
    REFERENCES `movlib`.`companies_images` (`id` , `company_id` , `type_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_companies_images_votes_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all user votes for company images.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`deletions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`deletions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The deletion requests unique identifier.',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The deletion requests creation time.',
  `language_code` CHAR(2) CHARACTER SET 'ascii' NOT NULL COMMENT 'The deletion’s language code.',
  `reason` TEXT NOT NULL COMMENT 'The user supplied reason for the deletion request.',
  `url` VARCHAR(255) NOT NULL COMMENT 'The URL of the content that should be deleted.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user who requested the deletion.',
  PRIMARY KEY (`id`),
  INDEX `fk_deletions_users` (`user_id` ASC),
  CONSTRAINT `fk_deletions_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains deletion requests for any kind of content.';

SHOW WARNINGS;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
