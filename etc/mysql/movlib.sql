SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

DROP SCHEMA IF EXISTS `movlib` ;
CREATE SCHEMA IF NOT EXISTS `movlib` DEFAULT CHARACTER SET utf8mb4 ;
SHOW WARNINGS;
USE `movlib` ;

-- -----------------------------------------------------
-- Table `movlib`.`movies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The movie’s unique ID.',
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the movie was last changed.',
  `count_awards` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s total number of awards.',
  `count_releases` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s total number of releases.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the movie was created.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether the movie is marked as deleted (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `dyn_synopses` BLOB NOT NULL COMMENT 'The synopsis of the movie in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_wikipedia` BLOB NOT NULL COMMENT 'The movie’s translated Wikipedia links.',
  `mean_rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s arithmetic mean rating.',
  `rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The Bayes\'theorem rating of this movie.\n\nrating = (s / (s + m)) * N + (m / (s + m)) * K\n\nN: arithmetic mean rating\ns: vote count\nm: minimum vote count\nK: arithmetic mean vote\n\nThe same formula is used by IMDb and OFDb.',
  `votes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The movie’s vote count.',
  `commit` CHAR(40) NULL COMMENT 'The movie\'s last history commit sha-1 hash.',
  `rank` BIGINT UNSIGNED NULL COMMENT 'The movie’s global rank.',
  `runtime` MEDIUMINT UNSIGNED NULL COMMENT 'The movie’s approximate runtime in seconds.',
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
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The genre’s unique identifier.',
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the genre was last changed.',
  `count_movies` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The genre’s total number of movie occurrences.',
  `count_series` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The genre’s total number of series occurences.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the genre was created.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Whether the genre was deleted or not.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The genre’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names` BLOB NOT NULL COMMENT 'The genre’s name in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_wikipedia` BLOB NOT NULL COMMENT 'The event’s translated Wikipedia links.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all genres.'
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
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_genres_genres`
    FOREIGN KEY (`genre_id`)
    REFERENCES `movlib`.`genres` (`id`)
    ON DELETE CASCADE
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
  `access` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The user’s last access date and time.',
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the user was last changed.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The user’s accout creation date and time.',
  `admin` TINYINT(1) NULL DEFAULT FALSE,
  `birthdate` DATE NULL COMMENT 'The user’s date of birth.',
  `country_code` CHAR(2) NULL COMMENT 'The user’s ISO alpha-2 country code.',
  `currency_code` CHAR(3) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL COMMENT 'The user’s ISO 4217  (3 letter) currency code.',
  `dyn_about_me` BLOB NULL COMMENT 'The user’s about me text in various languages. Keys are ISO alpha-2 language codes.',
  `edits` BIGINT UNSIGNED NULL DEFAULT 0 COMMENT 'The user’s edit counter.',
  `email` VARCHAR(254) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL COMMENT 'The user’s unique email address.',
  `image_cache_buster` BINARY(16) NULL COMMENT 'The user’s MD5 cache buster.',
  `image_extension` CHAR(3) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL COMMENT 'The file extension of the user’s avatar.',
  `image_styles` BLOB NULL COMMENT 'The user’s serialized image styles.',
  `password` VARBINARY(255) NULL COMMENT 'The user’s password (hashed).',
  `private` TINYINT(1) NULL DEFAULT false COMMENT 'The flag that determines whether this user allows us to display private data on his profile page (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `profile_views` BIGINT UNSIGNED NULL DEFAULT 0 COMMENT 'The user’s profile view count.',
  `real_name` TINYBLOB NULL COMMENT 'The user’s real name.',
  `reputation` BIGINT UNSIGNED NULL DEFAULT 0 COMMENT 'The user’s reputation.',
  `sex` TINYINT UNSIGNED NULL DEFAULT 0 COMMENT 'The user\'s sex according to ISO 5218.\n\n0 = not known\n1 = male\n2 = female\n9 = not applicable',
  `language_code` CHAR(2) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL COMMENT 'The user’s preferred system language code (e.g. en).',
  `timezone` VARCHAR(30) CHARACTER SET 'ascii' COLLATE 'ascii_general_ci' NULL COMMENT 'User’s time zone ID.',
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
  `id` BIGINT UNSIGNED NOT NULL COMMENT 'The place’s unique OpenStreetMap node ID.',
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the place was last changed.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `country_code` CHAR(2) NOT NULL COMMENT 'The place’s ISO alpha-2 country code.',
  `dyn_names` BLOB NOT NULL COMMENT 'The place’s translated name.',
  `latitude` FLOAT NOT NULL COMMENT 'The place’s latitude.',
  `longitude` FLOAT NOT NULL COMMENT 'The place’s longitude.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The place’s native name.',
  PRIMARY KEY (`id`))
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
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the person was last changed.',
  `count_awards` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The person’s total number of awards.',
  `count_movies` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The person’s total number of movie participations.',
  `count_releases` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The person’s total number of release participations.',
  `count_series` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The person’s total number of series participations.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the person was created.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this person is marked as deleted (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `dyn_biographies` BLOB NOT NULL COMMENT 'The person’s biography in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_wikipedia` BLOB NOT NULL COMMENT 'The person\'s Wikipedia link in various languages. The language code serves as key.',
  `dyn_image_descriptions` BLOB NOT NULL COMMENT 'The person’s translated photo description.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The person’s full name.',
  `sex` TINYINT NOT NULL DEFAULT 0 COMMENT 'The person\'s sex according to ISO 5218.\n\n0 = not known\n1 = male\n2 = female\n9 = not applicable',
  `birthdate` DATE NULL COMMENT 'The person’s date of birth.',
  `birthplace_id` BIGINT UNSIGNED NULL COMMENT 'The person’s birthplace.',
  `born_name` MEDIUMTEXT NULL COMMENT 'The person’s born name.',
  `cause_of_death_id` BIGINT UNSIGNED NULL,
  `commit` CHAR(40) NULL COMMENT 'The person’s last history commit sha-1 hash.',
  `deathdate` DATE NULL COMMENT 'The person’s date of death.',
  `deathplace_id` BIGINT UNSIGNED NULL COMMENT 'The person’s death place.',
  `image_cache_buster` BINARY(16) NULL COMMENT 'The person photo’s MD5 cache buster.',
  `image_extension` CHAR(3) NULL COMMENT 'The person photo’s extension without leading dot.',
  `image_filesize` INT NULL COMMENT 'The person photo’s original size in Bytes.',
  `image_height` SMALLINT NULL COMMENT 'The person photo’s original height.',
  `image_styles` BLOB NULL COMMENT 'Serialized array containing width and height of various image styles.',
  `image_uploader_id` BIGINT UNSIGNED NULL COMMENT 'The uploader\'s unique user ID.',
  `image_width` SMALLINT NULL COMMENT 'The person photo’s original width.',
  `links` BLOB NULL COMMENT 'The person’s weblinks as serialized PHP array.',
  PRIMARY KEY (`id`),
  INDEX `fk_persons_places_birth` (`birthplace_id` ASC),
  INDEX `fk_persons_places_death` (`deathplace_id` ASC),
  INDEX `fk_persons_causes_of_death` (`cause_of_death_id` ASC),
  INDEX `fk_persons_users` (`image_uploader_id` ASC),
  INDEX `persons_created` (`created` ASC),
  CONSTRAINT `fk_persons_places_birth`
    FOREIGN KEY (`birthplace_id`)
    REFERENCES `movlib`.`places` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_places_death`
    FOREIGN KEY (`deathplace_id`)
    REFERENCES `movlib`.`places` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_causes_of_death`
    FOREIGN KEY (`cause_of_death_id`)
    REFERENCES `movlib`.`causes_of_death` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_users`
    FOREIGN KEY (`image_uploader_id`)
    REFERENCES `movlib`.`users` (`id`)
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
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the job was last changed.',
  `count_companies` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The job’s total number of companies.',
  `count_persons` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The job’s total number of persons.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the job was created.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Whether the job was deleted or not.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The job’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names_sex0` BLOB NOT NULL COMMENT 'The job’s unisex name in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names_sex1` BLOB NOT NULL COMMENT 'The job’s male name in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names_sex2` BLOB NOT NULL COMMENT 'The job’s female name in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_wikipedia` BLOB NOT NULL COMMENT 'The job’s translated Wikipedia links.',
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
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the company was last changed.',
  `count_awards` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The company’s total number of awards.',
  `count_movies` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The company’s total number of movie participations.',
  `count_releases` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The company’s total number of release participations.',
  `count_series` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The company’s total number of series participations.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the company was created.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Whether the company was deleted or not.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The company’s translated descriptions.',
  `dyn_wikipedia` BLOB NOT NULL COMMENT 'The company’s translated Wikipedia links.',
  `dyn_image_descriptions` BLOB NOT NULL COMMENT 'The company’s translated logo description.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The company’s name.',
  `aliases` BLOB NULL COMMENT 'The company’s aliases.',
  `founding_date` DATE NULL COMMENT 'The company’s founding date.',
  `defunct_date` DATE NULL COMMENT 'The company’s defunct date.',
  `image_changed` TIMESTAMP NULL COMMENT 'The company’s logo changed timestamp.',
  `image_extension` CHAR(3) NULL COMMENT 'The company’s logo extension.',
  `image_filesize` INT NULL COMMENT 'The company’s logo filesize.',
  `image_height` SMALLINT NULL COMMENT 'The company’s logo height.',
  `image_styles` BLOB NULL COMMENT 'The company’s logo styles.',
  `image_uploader_id` BIGINT UNSIGNED NULL COMMENT 'The company’s logo unique uploader identifier.',
  `image_width` SMALLINT NULL COMMENT 'The company’s logo width.',
  `links` BLOB NULL COMMENT 'The company’s weblinks as serialized PHP array.',
  `place_id` BIGINT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_companies_user_id` (`image_uploader_id` ASC),
  INDEX `fk_companies_place_id` (`place_id` ASC),
  CONSTRAINT `fk_companies_users`
    FOREIGN KEY (`image_uploader_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_companies_places`
    FOREIGN KEY (`place_id`)
    REFERENCES `movlib`.`places` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all companies.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_aliases`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`persons_aliases` (
  `id` BIGINT NOT NULL AUTO_INCREMENT COMMENT 'The alias\' unique ID.',
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s unique ID.',
  `alias` MEDIUMTEXT NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_persons_aliases_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_crew`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_crew` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The crew’s unique ID within the movie.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `job_id` BIGINT UNSIGNED NOT NULL COMMENT 'The job’s unique identifier. Several important jobs have fixed identifiers.\n1: Acting\n2: Direction\n3: Production\n4: Screenwriting',
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the movie crew entry was last changed.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the movie crew entry was created.',
  `dyn_role` BLOB NOT NULL COMMENT 'The translated role names (if role_id is null) for cast jobs.',
  `weight` SMALLINT NOT NULL DEFAULT 0 COMMENT 'The weight (display order) of the movie’s crew. Default is 0.',
  `alias_id` BIGINT NULL COMMENT 'The person’s alias identifier.',
  `company_id` BIGINT UNSIGNED NULL COMMENT 'The company’s unique identifier.',
  `person_id` BIGINT UNSIGNED NULL COMMENT 'The person’s unique identifier.',
  `role_id` BIGINT UNSIGNED NULL COMMENT 'The person’s identifier if a person plays another person.',
  PRIMARY KEY (`id`),
  INDEX `fk_movies_crew_movies` (`movie_id` ASC),
  INDEX `fk_movies_crew_jobs` (`job_id` ASC),
  INDEX `fk_movies_crew_companies` (`company_id` ASC),
  INDEX `fk_movies_crew_persons` (`person_id` ASC),
  INDEX `fk_movies_crew_persons_aliases` (`alias_id` ASC),
  INDEX `fk_movies_crew_persons_role` (`role_id` ASC),
  CONSTRAINT `fk_movies_crew_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE CASCADE
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
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_crew_persons_aliases`
    FOREIGN KEY (`alias_id`)
    REFERENCES `movlib`.`persons_aliases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_crew_persons_role`
    FOREIGN KEY (`role_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the crew of a movie.'
ROW_FORMAT = COMPRESSED;

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
    ON DELETE CASCADE
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
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'A movie has many languages, a language has many movies.';

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
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the award was last changed.',
  `count_companies` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The award’s total number of companies.',
  `count_categories` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The award’s total number of categories.',
  `count_events` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The award’s total number of events.',
  `count_movies` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The award’s total number of movie participations.',
  `count_persons` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The award’s total number of persons.',
  `count_series` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The award’s total number of series participations.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the award was created.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Whether the award was deleted or not.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The award’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_image_descriptions` BLOB NOT NULL COMMENT 'The award’s translated logo description.',
  `dyn_wikipedia` BLOB NOT NULL COMMENT 'The award’s translated Wikipedia links.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The award’s name.',
  `aliases` BLOB NULL COMMENT 'The award’s aliases.',
  `first_event_year` SMALLINT(4) UNSIGNED NULL COMMENT 'The first year this award was awarded.',
  `image_changed` DATETIME NULL COMMENT 'The date and time the image was last changed',
  `image_extension` CHAR(3) NULL COMMENT 'The award’s image extension.',
  `image_filesize` INT NULL COMMENT 'The award’s image filesize.',
  `image_height` SMALLINT NULL COMMENT 'The award’s image height.',
  `image_styles` BLOB NULL COMMENT 'The award’s image styles.',
  `image_uploader_id` BIGINT UNSIGNED NULL COMMENT 'The  award’s image unique uploader identifier.',
  `image_width` SMALLINT NULL COMMENT 'The award’s image width.',
  `last_event_year` SMALLINT(4) UNSIGNED NULL COMMENT 'The last year this award was awarded.',
  `links` BLOB NULL COMMENT 'The company’s weblinks as serialized PHP array.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all awards.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`awards_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`awards_categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The award category’s unique ID.',
  `award_id` BIGINT UNSIGNED NOT NULL COMMENT 'The award’s unique ID.',
  `changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The timestamp on which this category was changed.',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The timestamp on which this category was created.',
  `count_companies` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The category’s total number of companies.',
  `count_movies` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The category’s total number of movie participations.',
  `count_persons` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The category’s total number of persons.',
  `count_series` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The category’s total number of series participations.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Whether the award category was deleted or not.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The award categorie’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_names` BLOB NOT NULL COMMENT 'The award categorie’s name in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_wikipedia` BLOB NOT NULL COMMENT 'The award’s translated Wikipedia links.',
  `first_year` SMALLINT(4) NULL COMMENT 'The first year this award category existed.',
  `last_year` SMALLINT(4) NULL COMMENT 'The last year this award category existed.',
  PRIMARY KEY (`id`),
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
-- Table `movlib`.`events`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The event’s unique ID.',
  `award_id` BIGINT UNSIGNED NOT NULL COMMENT 'The award’s unique ID.',
  `changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The timestamp on which this event was changed.',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The timestamp on which this event was created.',
  `count_companies` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The event’s total number of companies.',
  `count_movies` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The event’s total number of movie participations.',
  `count_persons` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The event’s total number of persons.',
  `count_series` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The event’s total number of series participations.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Whether the event was deleted or not.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The event’s description in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_wikipedia` BLOB NOT NULL COMMENT 'The event’s translated Wikipedia links.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The event’s name.',
  `start_date` DATE NOT NULL COMMENT 'The event’s start date.',
  `aliases` BLOB NULL COMMENT 'The event’s aliases.',
  `end_date` DATE NULL COMMENT 'The event’s end date.',
  `links` BLOB NULL COMMENT 'The event’s weblinks as serialized PHP array.',
  `place_id` BIGINT UNSIGNED NULL COMMENT 'The  event’s unique place identifier.',
  PRIMARY KEY (`id`),
  INDEX `fk_awards_events_award_id` (`award_id` ASC),
  INDEX `fk_awards_events_place_id` (`place_id` ASC),
  CONSTRAINT `fk_awards_events_award_id`
    FOREIGN KEY (`award_id`)
    REFERENCES `movlib`.`awards` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_awards_events_place_id`
    FOREIGN KEY (`place_id`)
    REFERENCES `movlib`.`places` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all events.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_awards`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_awards` (
  `id` BIGINT NOT NULL AUTO_INCREMENT COMMENT 'The movie award’s unique ID.',
  `movie_id` BIGINT UNSIGNED NULL COMMENT 'The movie’s unique ID.',
  `award_category_id` BIGINT UNSIGNED NOT NULL COMMENT 'The award category’s unique ID.',
  `award_id` BIGINT UNSIGNED NOT NULL COMMENT 'The award’s unique ID.',
  `event_id` BIGINT UNSIGNED NOT NULL COMMENT 'The award event’s unique ID.',
  `company_id` BIGINT UNSIGNED NULL COMMENT 'The company’s unique ID (who received the award).',
  `person_id` BIGINT UNSIGNED NULL COMMENT 'The person’s unique ID (who received the award).',
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the movie award entry was last changed.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the movie award entry was created.',
  `won` TINYINT NOT NULL DEFAULT 0 COMMENT 'The flag that determines whether this award has been won (TRUE(1)) or not (FALSE(0), default is FALSE (0).',
  PRIMARY KEY (`id`),
  INDEX `fk_movies_awards_movies` (`movie_id` ASC),
  INDEX `fk_movies_awards_persons` (`person_id` ASC),
  INDEX `fk_movies_awards_companies` (`company_id` ASC),
  INDEX `fk_movies_awards_awards_categories` (`award_category_id` ASC),
  INDEX `fk_movies_awards_events` (`event_id` ASC),
  CONSTRAINT `fk_movies_awards_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_awards_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_awards_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_awards_awards_categories`
    FOREIGN KEY (`award_category_id`)
    REFERENCES `movlib`.`awards_categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_awards_awards`
    FOREIGN KEY (`award_id`)
    REFERENCES `movlib`.`awards` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_awards_events`
    FOREIGN KEY (`event_id`)
    REFERENCES `movlib`.`events` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all awards belonging to movies.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_titles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The title’s unique identifier.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `dyn_comments` BLOB NOT NULL COMMENT 'The title’s comment in various languages. Keys are ISO alpha-2 language codes.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The title’s ISO alpha-2 language code.',
  `title` TEXT NOT NULL COMMENT 'The movie’s title.',
  INDEX `fk_movies_titles_movies` (`movie_id` ASC),
  PRIMARY KEY (`id`, `movie_id`),
  CONSTRAINT `fk_movies_titles_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains movie titles.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_taglines`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_taglines` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The tagline’s unique identifier.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `dyn_comments` BLOB NOT NULL COMMENT 'The taglines’s comment in various languages. Keys are ISO alpha-2 language codes.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The tagline’s ISO alpha-2 language code.',
  `tagline` TEXT NOT NULL COMMENT 'The movie’s tagline.',
  INDEX `fk_movies_taglines_movies` (`movie_id` ASC),
  PRIMARY KEY (`id`, `movie_id`),
  CONSTRAINT `fk_movies_taglines_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE CASCADE
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
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The movie trailer’s unique ID.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique ID.',
  `video_quality` TINYTEXT NULL,
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The trailer\'s translated descriptions.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The movie trailer’s ISO alpha-2 language code.',
  `url` VARCHAR(255) NOT NULL COMMENT 'The movie trailer’s url, e.g. youtube.',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_movies_trailers_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all movie trailers.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The unique ID of the series.',
  `changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The timestamp on which this series was changed.',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The timestamp on which this series was created.',
  `count_awards` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The series’ total number of awards.',
  `count_releases` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The series’ total number of releases.',
  `count_seasons` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The series’ total number of seasons.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this series is marked as deleted (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `dyn_synopses` BLOB NOT NULL COMMENT 'The synopsis of the series in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_wikipedia` BLOB NOT NULL COMMENT 'The movie’s translated Wikipedia links.',
  `mean_rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The arithmetic mean rating of the series.',
  `rating` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The Bayes\'theorem rating of the series.\n\nrating = (s / (s + m)) * N + (m / (s + m)) * K\n\nN: arithmetic mean rating\ns: vote count\nm: minimum vote count\nK: arithmetic mean vote\n\nThe same formula is used by IMDb and OFDb.',
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'One of the \\\\MovLib\\\\Data\\\\Series\\\\Series::STATUS_ constants.\n0 => unknown,\n1 => new,\n2 => returning,\n3 => final season,\n4 => ended,\n5 => cancelled.',
  `votes` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The vote count of the series.',
  `commit` CHAR(40) NULL COMMENT 'The last history commit sha-1 hash of the series.',
  `end_year` SMALLINT(4) UNSIGNED ZEROFILL NULL COMMENT 'The year the series was cancelled.',
  `rank` BIGINT UNSIGNED NULL COMMENT 'The global rank of the series.',
  `start_year` SMALLINT(4) UNSIGNED ZEROFILL NULL COMMENT 'The year the series was aired for the first time.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all series.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_titles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The series title’s ID within the series.',
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the series.',
  `dyn_comments` BLOB NOT NULL COMMENT 'The series title’s comment in various languages. Keys are ISO alpha-2 language codes.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The series title’s ISO 3166-1 aplha-2 language code.',
  `title` BLOB NOT NULL COMMENT 'The title of the series.',
  INDEX `fk_series_titles_series` (`series_id` ASC),
  PRIMARY KEY (`id`, `series_id`),
  CONSTRAINT `fk_series_titles_series`
    FOREIGN KEY (`series_id`)
    REFERENCES `movlib`.`series` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains series titles.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_episodes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_episodes` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series’ identifier.',
  `season_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season’s number this episode belongs to.',
  `position` SMALLINT UNSIGNED NOT NULL COMMENT 'The episode’s chronological position within the season.',
  `count_releases` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The episode’s total number of releases.',
  `created` TIMESTAMP NOT NULL COMMENT 'The creation date of the episode as timestamp.',
  `original_title` BLOB NOT NULL COMMENT 'The episode’s original title.',
  `original_title_language_code` CHAR(2) NOT NULL COMMENT 'The episode’s original title ISO alpha-2 language code.',
  `episode_number` TINYTEXT NULL COMMENT 'The episodes number within the season (e.g. 01, but also 0102 if it contains two episodes).',
  `original_air_date` DATE NULL COMMENT 'The date the episode was originally aired.',
  PRIMARY KEY (`series_id`, `season_number`, `position`),
  INDEX `fk_series_episodes_series` (`series_id` ASC),
  CONSTRAINT `fk_series_episodes_series`
    FOREIGN KEY (`series_id`)
    REFERENCES `movlib`.`series` (`id`)
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
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The episode title’s unique identifier.',
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the series.',
  `seasons_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season number within the series.',
  `position` SMALLINT UNSIGNED NOT NULL COMMENT 'The episode’s chronological position within the season.',
  `display` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this episode title is the display title for its language (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `dyn_comments` BLOB NOT NULL COMMENT 'The episode title’s comment in various languages. Keys are ISO alpha-2 language codes.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The episode title’s ISO aplha-2 language code.',
  `title` BLOB NOT NULL COMMENT 'The episode’s title.',
  PRIMARY KEY (`id`),
  INDEX `fk_episodes_titles_series_episodes` (`series_id` ASC, `seasons_number` ASC, `position` ASC),
  CONSTRAINT `fk_episodes_titles_series_episodes`
    FOREIGN KEY (`series_id` , `seasons_number` , `position`)
    REFERENCES `movlib`.`series_episodes` (`series_id` , `season_number` , `position`)
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
    REFERENCES `movlib`.`series` (`id`)
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
-- Table `movlib`.`media`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`media` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The medium’s unique identifier.',
  `changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The medium’s last update date and time.',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The medium’s insert date and time.',
  `bootleg` TINYINT(1) NOT NULL DEFAULT FALSE COMMENT 'Flag if this medium is a bootleg or not.',
  `format` VARCHAR(20) NOT NULL COMMENT 'The medium’s type as enumeration of one of \\\\MovLib\\Presentation\\\\Partial\\\\Format\\\\FormatFactory class constants.',
  `dyn_notes` BLOB NOT NULL COMMENT 'The medium’s notes in various languages. Keys are ISO alpha-2 language codes.',
  `bin_format` BLOB NULL COMMENT 'The medium’s release format (e.g. DVD) as serialized PHP object (\\\\MovLib\\\\Data\\\\Format\\\\AbstractFormat).',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all single units of one or more releases.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`releases` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The release’s unique identifier.',
  `changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The release’s last update date and time.',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The release’s insert date and time.',
  `country_code` CHAR(2) NOT NULL COMMENT 'The release’s ISO alpha-2 country code.',
  `deleted` TINYINT(1) NOT NULL DEFAULT FALSE COMMENT 'The release’s deleted flag.',
  `dyn_notes` BLOB NOT NULL COMMENT 'The release’s notes in various languages. Keys are ISO alpha-2 language codes.',
  `title` TEXT NOT NULL COMMENT 'The release’s title.',
  `type` TINYINT NOT NULL COMMENT 'The release’s type as one of the \\\\MovLib\\Data\\\\Release\\\\Release constants.',
  `publishing_date_sale` DATE NULL COMMENT 'The release’s publishing date for sale.',
  `publishing_date_rental` DATE NULL COMMENT 'The release’s publishing date for rental.',
  `edition` TEXT NULL COMMENT 'The release’s edition.',
  `bin_identifiers` BLOB NULL COMMENT 'The release’s additional identifiers as serialized PHP object (\\\\MovLib\\\\Stub\\\\Data\\\\Release\\\\Identifier).',
  `bin_media_counts` BLOB NULL COMMENT 'The release’s counts of media formats as serialized PHP array.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all releases. A release contains one or more media.'
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
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_releases_master_releases`
    FOREIGN KEY (`master_release_id`)
    REFERENCES `movlib`.`releases` (`id`)
    ON DELETE CASCADE
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
  `job_id` BIGINT UNSIGNED NOT NULL COMMENT 'The job’s identifier for the label job.',
  `catalog_number` TINYTEXT NULL COMMENT 'The catalog number associated with the release.',
  PRIMARY KEY (`company_id`, `release_id`),
  INDEX `fk_releases_labels_companies` (`company_id` ASC),
  INDEX `fk_releases_labels_master_releases` (`release_id` ASC),
  INDEX `fk_releases_labels_jobs1_idx` (`job_id` ASC),
  CONSTRAINT `fk_master_releases_labels_releases`
    FOREIGN KEY (`release_id`)
    REFERENCES `movlib`.`releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_master_releases_labels_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_labels_jobs1`
    FOREIGN KEY (`job_id`)
    REFERENCES `movlib`.`jobs` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the label a master release is related to.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_ratings`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_ratings` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s unique identifier.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique identifier.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The creation date and time of the movie rating as timestamp.',
  `rating` TINYINT(1) UNSIGNED NOT NULL COMMENT 'The user’s rating for a certain movie (1-5).',
  PRIMARY KEY (`movie_id`, `user_id`),
  INDEX `fk_movies_ratings_users` (`user_id` ASC),
  INDEX `fk_movies_ratings_movies` (`movie_id` ASC),
  CONSTRAINT `fk_movies_ratings_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_ratings_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all movie ratings by users.';

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
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_relationships_first_movie`
    FOREIGN KEY (`first_movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_relationships_second_movie`
    FOREIGN KEY (`second_movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains relationships between movies.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`sessions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`sessions` (
  `ssid` VARBINARY(86) NOT NULL COMMENT 'The session’s unique identifier.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user’s unique ID.',
  `authentication` DATETIME NOT NULL COMMENT 'Timestamp when this session was initialized.',
  `remote_address` VARBINARY(128) NOT NULL COMMENT 'The session’s IP address.',
  `user_agent` TINYBLOB NOT NULL COMMENT 'The session’s user agent string.',
  PRIMARY KEY (`ssid`),
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
    REFERENCES `movlib`.`releases` (`id`)
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
    REFERENCES `movlib`.`series` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_relationships_second_series`
    FOREIGN KEY (`second_series_id`)
    REFERENCES `movlib`.`series` (`id`)
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
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the help category was last changed.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the help category was created.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this help category is marked as deleted (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `title` VARCHAR(255) NOT NULL COMMENT 'The help category’s title in default language. Translation is handled by intl.',
  `description` TEXT NOT NULL COMMENT 'The help category’s description in default language. Translation is handled by intl.',
  `icon` VARCHAR(255) NOT NULL COMMENT 'The icon of a category.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all help categories.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`help_subcategories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`help_subcategories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The help subcategory’s unique identifier.',
  `help_category_id` TINYINT UNSIGNED NOT NULL COMMENT 'The help category’s unique id.',
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the help subcategory was last changed.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the help subcategory was created.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this help subcategory is marked as deleted (TRUE(1)) or not (FALSE(0)), default is FALSE(0).',
  `title` VARCHAR(255) NOT NULL COMMENT 'The help subcategory’s title in default language. Translation is handled by intl.',
  `icon` VARCHAR(255) NOT NULL COMMENT 'The icon of a subcategory.',
  PRIMARY KEY (`id`),
  INDEX `fk_help_subcategories_help_category_id` (`help_category_id` ASC),
  CONSTRAINT `fk_help_subcategories_help_categories`
    FOREIGN KEY (`help_category_id`)
    REFERENCES `movlib`.`help_categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all help subcategories.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`help_articles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`help_articles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The help’s unique identifier.',
  `help_category_id` TINYINT UNSIGNED NOT NULL COMMENT 'The help category’s unique identifier.',
  `help_subcategory_id` INT UNSIGNED NULL COMMENT 'The help subcategory’s unique identifier.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The timestamp on which this help article was created.',
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The timestamp on which this help article was changed.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'Whether the help article was deleted or not.',
  `dyn_texts` BLOB NOT NULL COMMENT 'The help article’s text in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_titles` BLOB NOT NULL COMMENT 'The help article’s title in various languages. Keys are ISO alpha-2 language codes.',
  `view_count` BIGINT NOT NULL DEFAULT 0 COMMENT 'The help article’s view count.',
  PRIMARY KEY (`id`),
  INDEX `fk_help_articles_help_category_id` (`help_category_id` ASC),
  INDEX `fk_help_articles_help_subcategory_id` (`help_subcategory_id` ASC),
  CONSTRAINT `fk_help_articles_help_categories`
    FOREIGN KEY (`help_category_id`)
    REFERENCES `movlib`.`help_categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_help_articles_help_subcategories`
    FOREIGN KEY (`help_subcategory_id`)
    REFERENCES `movlib`.`help_subcategories` (`id`)
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
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The creation date of the page as timestamp.',
  `dyn_titles` BLOB NOT NULL COMMENT 'Thepage’s text in various languages. Keys are ISO alpha-2 language codes.',
  `dyn_texts` BLOB NOT NULL COMMENT 'The help’s title in various languages. Keys are ISO alpha-2 language codes.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Contains all system pages, e.g. Imprint.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`deletion_requests`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`deletion_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The deletion request’s unique identifier.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The user who requested the deletion.',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The deletion request’s creation time.',
  `language_code` CHAR(2) NOT NULL COMMENT 'The deletion request’s (system) language code.',
  `reason_id` TINYINT UNSIGNED NOT NULL COMMENT 'The deletion request’s unique reason identifier.',
  `routes` VARCHAR(255) NOT NULL COMMENT 'The routes of the content that should be deleted as serialized array.',
  `info` TEXT NULL COMMENT 'The user supplied additional information for the deletion request.',
  PRIMARY KEY (`id`),
  INDEX `fk_deletions_users` (`user_id` ASC),
  INDEX `deletions_created` (`created` ASC),
  CONSTRAINT `fk_deletions_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains deletion requests for any kind of content.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`posters`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`posters` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The poster’s unique identifier.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The poster’s unique movie’s identifier.',
  `uploader_id` BIGINT UNSIGNED NOT NULL COMMENT 'The poster’s unique uploader’s identifier.',
  `deletion_request_id` BIGINT UNSIGNED NULL COMMENT 'The poster’s deletion request identifier.',
  `cache_buster` BINARY(16) NOT NULL COMMENT 'The poster’s MD5 cache buster.',
  `changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The date and time the poster was last changed.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date and time the poster was last created.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The poster’s deletion flag.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The poster’s translated descriptions.',
  `extension` CHAR(3) CHARACTER SET 'ascii' NOT NULL DEFAULT 'jpg' COMMENT 'The poster’s image extension.',
  `filesize` INT NOT NULL COMMENT 'The poster’s filesize.',
  `height` SMALLINT NOT NULL COMMENT 'The poster’s height in pixel.',
  `language_code` CHAR(2) CHARACTER SET 'ascii' NOT NULL DEFAULT 'xx' COMMENT 'The poster’s ISO alpha-2 language code.',
  `width` SMALLINT NOT NULL COMMENT 'The poster’s width in pixel.',
  `country_code` CHAR(2) CHARACTER SET 'ascii' NULL COMMENT 'The poster’s ISO alpha-2 country code.',
  `publishing_date` DATE NULL COMMENT 'The poster’s publishing date.',
  `styles` BLOB NULL COMMENT 'The poster’s styles.',
  PRIMARY KEY (`id`, `movie_id`),
  INDEX `fk_posters_movies_idx` (`movie_id` ASC),
  INDEX `fk_posters_users_idx` (`uploader_id` ASC),
  INDEX `fk_posters_deletion_requests_idx` (`deletion_request_id` ASC),
  CONSTRAINT `fk_posters_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_posters_users`
    FOREIGN KEY (`uploader_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_posters_deletion_requests`
    FOREIGN KEY (`deletion_request_id`)
    REFERENCES `movlib`.`deletion_requests` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Table containing all information related to movie posters.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`lobby_cards`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`lobby_cards` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The lobby card’s unique identifier.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The lobby card’s unique movie’s identifier.',
  `uploader_id` BIGINT UNSIGNED NOT NULL COMMENT 'The lobby card’s unique uploader’s identifier.',
  `deletion_request_id` BIGINT UNSIGNED NULL COMMENT 'The lobby card’s deletion request identifier.',
  `changed` TIMESTAMP NOT NULL COMMENT 'The lobby card’s changed timestamp.',
  `created` TIMESTAMP NOT NULL COMMENT 'The lobby card’s creation timestamp.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The lobby card’s deletion flag.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The lobby card’s translated descriptions.',
  `extension` CHAR(3) CHARACTER SET 'ascii' NOT NULL DEFAULT 'jpg' COMMENT 'The lobby card’s image extension.',
  `filesize` INT NOT NULL COMMENT 'The lobby card’s filesize.',
  `height` SMALLINT NOT NULL COMMENT 'The lobby card’s height in pixel.',
  `language_code` CHAR(2) CHARACTER SET 'ascii' NOT NULL DEFAULT 'xx' COMMENT 'The lobby card’s ISO alpha-2 language code.',
  `width` SMALLINT NOT NULL COMMENT 'The lobby card’s width in pixel.',
  `country_code` CHAR(2) CHARACTER SET 'ascii' NULL COMMENT 'The lobby card’s ISO alpha-2 country code.',
  `publishing_date` DATE NULL COMMENT 'The lobby card’s publishing date.',
  `styles` BLOB NULL COMMENT 'The lobby card’s styles.',
  PRIMARY KEY (`id`, `movie_id`),
  INDEX `fk_lobby_cards_movies_idx` (`movie_id` ASC),
  INDEX `fk_lobby_cards_users_idx` (`uploader_id` ASC),
  INDEX `fk_lobby_cards_deletion_requests_idx` (`deletion_request_id` ASC),
  CONSTRAINT `fk_lobby_cards_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_lobby_cards_users`
    FOREIGN KEY (`uploader_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_lobby_cards_deletion_requests`
    FOREIGN KEY (`deletion_request_id`)
    REFERENCES `movlib`.`deletion_requests` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Table containing all information related to movie lobby card /* comment truncated */ /*s.*/'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`backdrops`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`backdrops` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The backdrop’s unique identifier.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The backdrop’s unique movie’s identifier.',
  `uploader_id` BIGINT UNSIGNED NOT NULL COMMENT 'The backdrop’s unique uploader’s identifier.',
  `deletion_request_id` BIGINT UNSIGNED NULL COMMENT 'The backdrop’s deletion request identifier.',
  `changed` TIMESTAMP NOT NULL COMMENT 'The backdrop’s changed timestamp.',
  `created` TIMESTAMP NOT NULL COMMENT 'The backdrop’s creation timestamp.',
  `deleted` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The backdrop’s deletion flag.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The backdrop’s translated descriptions.',
  `extension` CHAR(3) CHARACTER SET 'ascii' NOT NULL DEFAULT 'jpg' COMMENT 'The backdrop’s image extension.',
  `filesize` INT NOT NULL COMMENT 'The backdrop’s filesize.',
  `height` SMALLINT NOT NULL COMMENT 'The backdrop’s height in pixel.',
  `language_code` CHAR(2) CHARACTER SET 'ascii' NOT NULL DEFAULT 'xx' COMMENT 'The backdrop’s ISO alpha-2 language code.',
  `width` SMALLINT NOT NULL COMMENT 'The backdrop’s width in pixel.',
  `country_code` CHAR(2) CHARACTER SET 'ascii' NULL COMMENT 'The backdrop’s ISO alpha-2 country code.',
  `publishing_date` DATE NULL COMMENT 'The backdrop’s publishing date.',
  `styles` BLOB NULL COMMENT 'The backdrop’s styles.',
  PRIMARY KEY (`id`, `movie_id`),
  INDEX `fk_posters_movies1_idx` (`movie_id` ASC),
  INDEX `fk_posters_users1_idx` (`uploader_id` ASC),
  INDEX `fk_backdrops_deletion_requests_idx` (`deletion_request_id` ASC),
  CONSTRAINT `fk_posters_movies11`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_posters_users11`
    FOREIGN KEY (`uploader_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_backdrops_deletion_requests`
    FOREIGN KEY (`deletion_request_id`)
    REFERENCES `movlib`.`deletion_requests` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Table containing all information related to movie backdrops.'
ROW_FORMAT = COMPRESSED
KEY_BLOCK_SIZE = 8;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`display_posters`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`display_posters` (
  `poster_id` BIGINT UNSIGNED NOT NULL COMMENT 'The display poster’s unique poster identifier.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The display poster’s unique movie identifier.',
  `language_code` CHAR(2) CHARACTER SET 'ascii' COLLATE 'ascii_bin' NOT NULL COMMENT 'The display poster’s ISO alpha-2 language code.',
  PRIMARY KEY (`poster_id`, `movie_id`, `language_code`),
  INDEX `fk_display_posters_posters_idx` (`poster_id` ASC, `movie_id` ASC),
  CONSTRAINT `fk_display_posters_posters`
    FOREIGN KEY (`poster_id` , `movie_id`)
    REFERENCES `movlib`.`posters` (`id` , `movie_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Table containing information on which poster should be used  /* comment truncated */ /*for which language as display poster on the movie details page.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_display_titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_display_titles` (
  `language_code` CHAR(2) CHARACTER SET 'ascii' COLLATE 'ascii_bin' NOT NULL COMMENT 'The movie’s display title’s ISO alpha-2 language code.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s display title’s unique movie identifier.',
  `title_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s display title’s unique title identifier.',
  PRIMARY KEY (`language_code`, `movie_id`, `title_id`),
  INDEX `fk_movies_display_titles_idx` (`title_id` ASC, `movie_id` ASC),
  CONSTRAINT `fk_movies_display_titles`
    FOREIGN KEY (`title_id` , `movie_id`)
    REFERENCES `movlib`.`movies_titles` (`id` , `movie_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Table containing information on which title should be used f /* comment truncated */ /*or which language as display title on various listings.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_display_taglines`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_display_taglines` (
  `language_code` CHAR(2) CHARACTER SET 'ascii' COLLATE 'ascii_bin' NOT NULL COMMENT 'The movie’s display tagline’s ISO alpha-2 language code.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s display tagline’s unique movie identifier.',
  `tagline_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s display tagline’s unique tagline identifier.',
  PRIMARY KEY (`language_code`, `movie_id`, `tagline_id`),
  INDEX `fk_movies_display_taglines_idx` (`tagline_id` ASC, `movie_id` ASC),
  CONSTRAINT `fk_movies_display_taglines`
    FOREIGN KEY (`tagline_id` , `movie_id`)
    REFERENCES `movlib`.`movies_taglines` (`id` , `movie_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Table containing information on which tagline should be used /* comment truncated */ /* for which language as display tagline on various listings.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`links_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`links_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The links category’s unique identifier.',
  `commit` CHAR(40) NOT NULL COMMENT 'The links category’s last commit hash.',
  `dyn_titles` BLOB NOT NULL COMMENT 'The links category’s translated titles.',
  `dyn_descriptions` BLOB NOT NULL COMMENT 'The links category’s translated description.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Table containing categories for weblinks.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_links`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_links` (
  `category_id` INT UNSIGNED NOT NULL,
  `movie_id` BIGINT UNSIGNED NOT NULL,
  `language_code` CHAR(2) NOT NULL,
  `url` VARCHAR(255) CHARACTER SET 'ascii' COLLATE 'ascii_bin' NOT NULL,
  PRIMARY KEY (`category_id`, `movie_id`, `language_code`, `url`),
  INDEX `fk_movies_links_movies_idx` (`movie_id` ASC),
  INDEX `fk_movies_links_categories_idx` (`category_id` ASC),
  CONSTRAINT `fk_movies_links_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_links_categories`
    FOREIGN KEY (`category_id`)
    REFERENCES `movlib`.`links_categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_original_titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`movies_original_titles` (
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The original title’s unique movie’s identifier.',
  `title_id` BIGINT UNSIGNED NOT NULL COMMENT 'The original title’s unique title’s identifier.',
  PRIMARY KEY (`movie_id`, `title_id`),
  INDEX `fk_movies_original_titles_movies_idx` (`movie_id` ASC),
  CONSTRAINT `fk_movies_original_titles_movies_titles`
    FOREIGN KEY (`title_id`)
    REFERENCES `movlib`.`movies_titles` (`id`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_original_titles_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Table to create correct circular reference between titles an /* comment truncated */ /*d the original title of a movie.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`episodes_crew`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`episodes_crew` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The episode crew’s unique identifier.',
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique series ID.',
  `season_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season number within a series.',
  `position` SMALLINT UNSIGNED NOT NULL COMMENT 'The episode number within a season.',
  `job_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique job ID.',
  `dyn_role` BLOB NOT NULL COMMENT 'The cast’s translated role names (if role_id is null).',
  `weight` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The weight (display order) of the series’ crew. Default is 0.',
  `alias_id` BIGINT NULL COMMENT 'The person’s alias identifier.',
  `company_id` BIGINT UNSIGNED NULL COMMENT 'The unique company ID.',
  `person_id` BIGINT UNSIGNED NULL COMMENT 'The unique person ID.',
  `role_id` BIGINT UNSIGNED NULL COMMENT 'The episode cast’s role identifier (for persons who play other real persons).',
  PRIMARY KEY (`id`),
  INDEX `fk_episodes_crew_jobs` (`job_id` ASC),
  INDEX `fk_episodes_crew_companies` (`company_id` ASC),
  INDEX `fk_episodes_crew_persons` (`person_id` ASC),
  INDEX `fk_episodes_crew_series_episodes` (`series_id` ASC, `season_number` ASC, `position` ASC),
  INDEX `fk_episodes_crew_persons_aliases` (`alias_id` ASC),
  INDEX `fk_episodes_crew_persons1_role` (`role_id` ASC),
  CONSTRAINT `fk_episodes_crew_series_episodes`
    FOREIGN KEY (`series_id` , `season_number` , `position`)
    REFERENCES `movlib`.`series_episodes` (`series_id` , `season_number` , `position`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_episodes_crew_jobs`
    FOREIGN KEY (`job_id`)
    REFERENCES `movlib`.`jobs` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_episodes_crew_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_episodes_crew_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_episodes_crew_persons_aliases`
    FOREIGN KEY (`alias_id`)
    REFERENCES `movlib`.`persons_aliases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_episodes_crew_persons_role`
    FOREIGN KEY (`role_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the crew of an episode.'
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_media`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`releases_media` (
  `release_id` BIGINT UNSIGNED NOT NULL COMMENT 'The release’s identifier.',
  `medium_id` BIGINT UNSIGNED NOT NULL COMMENT 'The medium’s identifier.',
  PRIMARY KEY (`release_id`, `medium_id`),
  INDEX `fk_releases_media_media_idx` (`medium_id` ASC),
  CONSTRAINT `fk_releases_media_releases`
    FOREIGN KEY (`release_id`)
    REFERENCES `movlib`.`releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_media_media`
    FOREIGN KEY (`medium_id`)
    REFERENCES `movlib`.`media` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`media_movies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`media_movies` (
  `medium_id` BIGINT UNSIGNED NOT NULL COMMENT 'The medium’s identifier.',
  `movie_id` BIGINT UNSIGNED NOT NULL COMMENT 'The movie’s identifier.',
  `bin_medium_movie` BLOB NOT NULL COMMENT 'The movie’s medium specific data as serialized PHP object.',
  PRIMARY KEY (`medium_id`, `movie_id`),
  INDEX `fk_media_movies_movies_idx` (`movie_id` ASC),
  CONSTRAINT `fk_media_movies_media`
    FOREIGN KEY (`medium_id`)
    REFERENCES `movlib`.`media` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_media_movies_movies`
    FOREIGN KEY (`movie_id`)
    REFERENCES `movlib`.`movies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_awards`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`persons_awards` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The person award’s unique identifier.',
  `award_id` BIGINT UNSIGNED NOT NULL COMMENT 'The award’s identifier.',
  `award_category_id` BIGINT UNSIGNED NOT NULL COMMENT 'The award category’s identifier.',
  `person_id` BIGINT UNSIGNED NOT NULL COMMENT 'The person’s identifier.',
  `event_id` BIGINT UNSIGNED NOT NULL COMMENT 'The event’s identifier.',
  `won` TINYINT(1) NOT NULL DEFAULT FALSE COMMENT 'The flag that determines whether this award has been won (TRUE(1)) or not (FALSE(0), default is FALSE (0).',
  INDEX `fk_persons_awards_awards_categories` (`award_category_id` ASC),
  INDEX `fk_persons_awards_awards` (`award_id` ASC),
  PRIMARY KEY (`id`),
  INDEX `fk_persons_awards_events` (`event_id` ASC),
  CONSTRAINT `fk_persons_awards_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_awards_awards_categories`
    FOREIGN KEY (`award_category_id`)
    REFERENCES `movlib`.`awards_categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_awards_awards`
    FOREIGN KEY (`award_id`)
    REFERENCES `movlib`.`awards` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_awards_events`
    FOREIGN KEY (`event_id`)
    REFERENCES `movlib`.`events` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all awards belonging to persons alone.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_awards`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_awards` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The series award’s unique identifier.',
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series’  identifier.',
  `award_category_id` BIGINT UNSIGNED NOT NULL COMMENT 'The award category’s unique identifier.',
  `award_id` BIGINT UNSIGNED NOT NULL COMMENT 'The award’s unique identifier.',
  `event_id` BIGINT UNSIGNED NOT NULL COMMENT 'The award event’s unique identifier.',
  `company_id` BIGINT UNSIGNED NULL COMMENT 'The company’s unique identifier (who received the award).',
  `person_id` BIGINT UNSIGNED NULL COMMENT 'The person’s unique identifier (who received the award).',
  `won` TINYINT(1) NOT NULL DEFAULT false COMMENT 'The flag that determines whether this award has been won (TRUE(1)) or not (FALSE(0), default is FALSE (0).',
  PRIMARY KEY (`id`),
  INDEX `fk_series_awards_persons` (`person_id` ASC),
  INDEX `fk_series_awards_companies` (`company_id` ASC),
  INDEX `fk_series_awards_awards_categories` (`award_category_id` ASC),
  INDEX `fk_series_awards_events` (`event_id` ASC),
  INDEX `fk_series_awards_series` (`series_id` ASC),
  CONSTRAINT `fk_series_awards_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_awards_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_awards_awards_categories`
    FOREIGN KEY (`award_category_id`)
    REFERENCES `movlib`.`awards_categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_awards_awards`
    FOREIGN KEY (`award_id`)
    REFERENCES `movlib`.`awards` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_awards_events`
    FOREIGN KEY (`event_id`)
    REFERENCES `movlib`.`events` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_awards_series`
    FOREIGN KEY (`series_id`)
    REFERENCES `movlib`.`series` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all awards belonging to series.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_crew`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`releases_crew` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `release_id` BIGINT UNSIGNED NOT NULL,
  `job_id` BIGINT UNSIGNED NOT NULL,
  `company_id` BIGINT UNSIGNED NULL,
  `person_id` BIGINT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_releases_crew_releases` (`release_id` ASC),
  INDEX `fk_releases_crew_persons` (`person_id` ASC),
  INDEX `fk_releases_crew_companies` (`company_id` ASC),
  INDEX `fk_releases_crew_jobs` (`job_id` ASC),
  CONSTRAINT `fk_releases_crew_releases`
    FOREIGN KEY (`release_id`)
    REFERENCES `movlib`.`releases` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_crew_persons`
    FOREIGN KEY (`person_id`)
    REFERENCES `movlib`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_crew_companies`
    FOREIGN KEY (`company_id`)
    REFERENCES `movlib`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_crew_jobs`
    FOREIGN KEY (`job_id`)
    REFERENCES `movlib`.`jobs` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains the crew of a release.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`media_episodes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`media_episodes` (
  `medium_id` BIGINT UNSIGNED NOT NULL COMMENT 'The medium’s identifier.',
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series’ identifier.',
  `season_number` SMALLINT UNSIGNED NOT NULL COMMENT 'The season’s number.',
  `position` SMALLINT UNSIGNED NOT NULL COMMENT 'The episode’s position in the season.',
  `bin_medium_episode` BLOB NOT NULL COMMENT 'The episode’s medium specific data as serialized PHP object.',
  PRIMARY KEY (`medium_id`, `series_id`, `season_number`, `position`),
  INDEX `fk_media_episodes_series_episodes` (`series_id` ASC, `season_number` ASC, `position` ASC),
  CONSTRAINT `fk_media_episodes_media`
    FOREIGN KEY (`medium_id`)
    REFERENCES `movlib`.`media` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_media_episodes_series_episodes`
    FOREIGN KEY (`series_id` , `season_number` , `position`)
    REFERENCES `movlib`.`series_episodes` (`series_id` , `season_number` , `position`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_ratings`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_ratings` (
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the series.',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The unique ID of the user.',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The creation date and time of the series rating as timestamp.',
  `rating` TINYINT(1) UNSIGNED NOT NULL COMMENT 'The user’s rating for a certain series (1-5).',
  PRIMARY KEY (`series_id`, `user_id`),
  INDEX `fk_series_ratings_users1_idx` (`user_id` ASC),
  CONSTRAINT `fk_series_ratings_series1`
    FOREIGN KEY (`series_id`)
    REFERENCES `movlib`.`series` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_ratings_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `movlib`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Contains all series ratings by users.';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_display_titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_display_titles` (
  `language_code` CHAR(2) NOT NULL COMMENT 'The series’s display title’s ISO alpha-2 language code.',
  `title_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series’s display title’s unique title identifier.',
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series’s unique identifier.',
  PRIMARY KEY (`language_code`, `title_id`, `series_id`),
  INDEX `fk_series_display_titles_series_idx` (`series_id` ASC),
  INDEX `fk_series_display_titles_series_titles_idx` (`title_id` ASC),
  CONSTRAINT `fk_series_display_titles_series_titles`
    FOREIGN KEY (`title_id`)
    REFERENCES `movlib`.`series_titles` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_display_titles_series`
    FOREIGN KEY (`series_id`)
    REFERENCES `movlib`.`series` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Table containing information on which title should be used f /* comment truncated */ /*or which language as display title on various listings.*/';

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`series_original_titles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movlib`.`series_original_titles` (
  `title_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series’s original title’s unique title identifier.',
  `series_id` BIGINT UNSIGNED NOT NULL COMMENT 'The series’s unique identifier.',
  PRIMARY KEY (`title_id`, `series_id`),
  INDEX `fk_series_display_titles_series_titles0_idx` (`series_id` ASC),
  CONSTRAINT `fk_series_original_titles_series`
    FOREIGN KEY (`series_id`)
    REFERENCES `movlib`.`series` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_series_original_titles_series_titles`
    FOREIGN KEY (`title_id`)
    REFERENCES `movlib`.`series_titles` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Table to create correct circular reference between titles an /* comment truncated */ /*d the original title of a series.*/';

SHOW WARNINGS;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
