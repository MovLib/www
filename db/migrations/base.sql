SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP SCHEMA IF EXISTS `movlib` ;
CREATE SCHEMA IF NOT EXISTS `movlib` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
SHOW WARNINGS;
USE `movlib` ;

-- -----------------------------------------------------
-- Table `movlib`.`movies`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies` (
  `movie_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `year` INT(4) NOT NULL DEFAULT 0000 ,
  `rating` FLOAT NOT NULL DEFAULT 0.0 ,
  `display` TINYINT(1) NOT NULL DEFAULT true ,
  PRIMARY KEY (`movie_id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`languages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`languages` (
  `language_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `iso_639-1` CHAR(2) NOT NULL ,
  `name_bg` VARCHAR(255) NULL ,
  `name_cs` VARCHAR(255) NULL ,
  `name_da` VARCHAR(255) NULL ,
  `name_de` VARCHAR(255) NULL ,
  `name_el` VARCHAR(255) NULL ,
  `name_en` VARCHAR(255) NOT NULL ,
  `name_es` VARCHAR(255) NULL ,
  `name_et` VARCHAR(255) NULL ,
  `name_fi` VARCHAR(255) NULL ,
  `name_fr` VARCHAR(255) NULL ,
  `name_hr` VARCHAR(255) NULL ,
  `name_hu` VARCHAR(255) NULL ,
  `name_is` VARCHAR(255) NULL ,
  `name_it` VARCHAR(255) NULL ,
  `name_lt` VARCHAR(255) NULL ,
  `name_lv` VARCHAR(255) NULL ,
  `name_mt` VARCHAR(255) NULL ,
  `name_nl` VARCHAR(255) NULL ,
  `name_no` VARCHAR(255) NULL ,
  `name_pl` VARCHAR(255) NULL ,
  `name_pt` VARCHAR(255) NULL ,
  `name_ro` VARCHAR(255) NULL ,
  `name_sk` VARCHAR(255) NULL ,
  `name_sl` VARCHAR(255) NULL ,
  `name_sv` VARCHAR(255) NULL ,
  `name_tr` VARCHAR(255) NULL ,
  PRIMARY KEY (`language_id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movie_titles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movie_titles` (
  `title_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `title` TEXT NOT NULL ,
  `is_original_title` TINYINT(1) NOT NULL DEFAULT FALSE ,
  `languages_language_id` INT UNSIGNED NOT NULL ,
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`title_id`) ,
  INDEX `fk_titles_languages1_idx` (`languages_language_id` ASC) ,
  INDEX `fk_titles_movies1_idx` (`movies_movie_id` ASC) ,
  CONSTRAINT `fk_titles_languages1`
    FOREIGN KEY (`languages_language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_titles_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`countries`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`countries` (
  `country_id` INT(3) UNSIGNED NOT NULL ,
  `iso_alpha_2` CHAR(2) NOT NULL ,
  `iso_alpha_3` CHAR(3) NOT NULL ,
  `name_en` VARCHAR(255) NOT NULL ,
  `name_de` VARCHAR(255) NULL ,
  `name_fr` VARCHAR(255) NULL ,
  `name_cs` VARCHAR(255) NULL ,
  `name_nl` VARCHAR(255) NULL ,
  `name_es` VARCHAR(255) NULL ,
  `name_it` VARCHAR(255) NULL ,
  PRIMARY KEY (`country_id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_has_countries`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_has_countries` (
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `countries_country_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`movies_movie_id`, `countries_country_id`) ,
  INDEX `fk_countries_has_movies_movies1_idx` (`movies_movie_id` ASC) ,
  INDEX `fk_countries_has_movies_countries_idx` (`countries_country_id` ASC) ,
  CONSTRAINT `fk_countries_has_movies_countries`
    FOREIGN KEY (`countries_country_id` )
    REFERENCES `movlib`.`countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_countries_has_movies_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_has_languages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_has_languages` (
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `languages_language_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`movies_movie_id`, `languages_language_id`) ,
  INDEX `fk_movies_has_languages_languages1_idx` (`languages_language_id` ASC) ,
  INDEX `fk_movies_has_languages_movies1_idx` (`movies_movie_id` ASC) ,
  CONSTRAINT `fk_movies_has_languages_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_has_languages_languages1`
    FOREIGN KEY (`languages_language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_en`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_en` (
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `display_title_id` BIGINT UNSIGNED NULL ,
  `synopsis` TEXT NULL ,
  PRIMARY KEY (`movies_movie_id`) ,
  INDEX `fk_movies_en_movies1_idx` (`movies_movie_id` ASC) ,
  INDEX `fk_movies_en_titles1_idx` (`display_title_id` ASC) ,
  CONSTRAINT `fk_movies_en_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_en_titles1`
    FOREIGN KEY (`display_title_id` )
    REFERENCES `movlib`.`movie_titles` (`title_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`genres`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`genres` (
  `genre_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name_en` VARCHAR(255) NOT NULL ,
  `name_de` VARCHAR(255) NULL ,
  PRIMARY KEY (`genre_id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_has_genres`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_has_genres` (
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `genres_genre_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`movies_movie_id`, `genres_genre_id`) ,
  INDEX `fk_movies_has_genres_genres1_idx` (`genres_genre_id` ASC) ,
  INDEX `fk_movies_has_genres_movies1_idx` (`movies_movie_id` ASC) ,
  CONSTRAINT `fk_movies_has_genres_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_has_genres_genres1`
    FOREIGN KEY (`genres_genre_id` )
    REFERENCES `movlib`.`genres` (`genre_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`styles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`styles` (
  `style_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name_en` VARCHAR(255) NOT NULL ,
  `name_de` VARCHAR(255) NULL ,
  PRIMARY KEY (`style_id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_has_styles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_has_styles` (
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `styles_style_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`movies_movie_id`, `styles_style_id`) ,
  INDEX `fk_movies_has_styles_styles1_idx` (`styles_style_id` ASC) ,
  INDEX `fk_movies_has_styles_movies1_idx` (`movies_movie_id` ASC) ,
  CONSTRAINT `fk_movies_has_styles_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_has_styles_styles1`
    FOREIGN KEY (`styles_style_id` )
    REFERENCES `movlib`.`styles` (`style_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_de`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_de` (
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `display_title_id` BIGINT UNSIGNED NULL ,
  `synopsis` TEXT NULL ,
  PRIMARY KEY (`movies_movie_id`) ,
  INDEX `fk_movies_de_titles1_idx` (`display_title_id` ASC) ,
  CONSTRAINT `fk_movies_de_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_de_titles1`
    FOREIGN KEY (`display_title_id` )
    REFERENCES `movlib`.`movie_titles` (`title_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`images`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`images` (
  `file_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `file_name` VARCHAR(255) NOT NULL ,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `users_user_id` BIGINT UNSIGNED NOT NULL ,
  `width` TINYINT NOT NULL ,
  `height` TINYINT NOT NULL ,
  `extension` VARCHAR(5) NOT NULL ,
  PRIMARY KEY (`file_id`) ,
  INDEX `fk_files_users1_idx` (`users_user_id` ASC) ,
  CONSTRAINT `fk_files_users1`
    FOREIGN KEY (`users_user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`avatars`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`avatars` (
  `images_file_id` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`images_file_id`) ,
  CONSTRAINT `fk_avatars_files1`
    FOREIGN KEY (`images_file_id` )
    REFERENCES `movlib`.`images` (`file_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`users` (
  `user_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique user’s ID.' ,
  `name` VARCHAR(40) NOT NULL COMMENT 'Unique user’s name.' ,
  `email` VARCHAR(255) NOT NULL COMMENT 'Unique user’s email address.' ,
  `pass` VARCHAR(60) NOT NULL COMMENT 'User’s password (hashed).' ,
  `created` TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Timestamp for when user was created.' ,
  `access` TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Timestamp for previous time user accessed the site.' ,
  `login` TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Timestamp for user’s last login.' ,
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether the user is active (1) or blocked (0).' ,
  `timezone` VARCHAR(32) NULL DEFAULT NULL COMMENT 'User’s time zone.' ,
  `language` VARCHAR(2) NULL COMMENT 'User’s default language.' ,
  `init` VARCHAR(255) NULL COMMENT 'Email address used for initial account creation.' ,
  `avatar_file_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'File ID of the user’s avatar image.' ,
  `dynamic_data` BLOB NULL DEFAULT NULL COMMENT 'Dynamic column for storing temporary data related to this user.' ,
  PRIMARY KEY (`user_id`) ,
  UNIQUE INDEX `username_UNIQUE` (`name` ASC) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) ,
  INDEX `fk_users_avatars1_idx` (`avatar_file_id` ASC) ,
  CONSTRAINT `fk_users_avatars1`
    FOREIGN KEY (`avatar_file_id` )
    REFERENCES `movlib`.`avatars` (`images_file_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`ratings`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`ratings` (
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `users_user_id` BIGINT UNSIGNED NOT NULL ,
  `rating` INT(1) NOT NULL ,
  PRIMARY KEY (`movies_movie_id`, `users_user_id`) ,
  INDEX `fk_ratings_movies1_idx` (`movies_movie_id` ASC) ,
  INDEX `fk_ratings_users1_idx` (`users_user_id` ASC) ,
  CONSTRAINT `fk_ratings_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ratings_users1`
    FOREIGN KEY (`users_user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movie_revisions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movie_revisions` (
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `users_user_id` BIGINT UNSIGNED NOT NULL ,
  `rev_nr` BIGINT NOT NULL ,
  `commit_id` VARCHAR(40) NOT NULL ,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `languages_language_id` INT UNSIGNED NULL ,
  PRIMARY KEY (`movies_movie_id`, `users_user_id`) ,
  INDEX `fk_rev_movies_movies1_idx` (`movies_movie_id` ASC) ,
  INDEX `fk_rev_movies_users1_idx` (`users_user_id` ASC) ,
  INDEX `fk_movie_revisions_languages1_idx` (`languages_language_id` ASC) ,
  CONSTRAINT `fk_rev_movies_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_rev_movies_users1`
    FOREIGN KEY (`users_user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movie_revisions_languages1`
    FOREIGN KEY (`languages_language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons` (
  `person_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `first_name` VARCHAR(255) NOT NULL ,
  `last_name` VARCHAR(255) NOT NULL ,
  `transcription` VARCHAR(255) NULL ,
  `birthdate` DATE NULL ,
  `born_name` VARCHAR(255) NULL ,
  `countries_country_id` INT UNSIGNED NULL ,
  `display` TINYINT(1) NOT NULL DEFAULT true ,
  PRIMARY KEY (`person_id`) ,
  INDEX `fk_persons_countries1_idx` (`countries_country_id` ASC) ,
  CONSTRAINT `fk_persons_countries1`
    FOREIGN KEY (`countries_country_id` )
    REFERENCES `movlib`.`countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_en`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons_en` (
  `persons_person_id` BIGINT UNSIGNED NOT NULL ,
  `biography` TEXT NULL ,
  `birthplace` VARCHAR(255) NULL ,
  PRIMARY KEY (`persons_person_id`) ,
  CONSTRAINT `fk_persons_en_persons1`
    FOREIGN KEY (`persons_person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_de`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons_de` (
  `persons_person_id` BIGINT UNSIGNED NOT NULL ,
  `biography` TEXT NULL ,
  `birthplace` VARCHAR(255) NULL ,
  PRIMARY KEY (`persons_person_id`) ,
  CONSTRAINT `fk_persons_en_persons10`
    FOREIGN KEY (`persons_person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movie_roles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movie_roles` (
  `role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `role_en` VARCHAR(255) NOT NULL ,
  `role_de` VARCHAR(255) NULL ,
  PRIMARY KEY (`role_id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_has_persons`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_has_persons` (
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `persons_person_id` BIGINT UNSIGNED NOT NULL ,
  `roles_role_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`movies_movie_id`, `persons_person_id`, `roles_role_id`) ,
  INDEX `fk_movies_has_persons_persons1_idx` (`persons_person_id` ASC) ,
  INDEX `fk_movies_has_persons_movies1_idx` (`movies_movie_id` ASC) ,
  INDEX `fk_movies_has_persons_movie_roles1_idx` (`roles_role_id` ASC) ,
  CONSTRAINT `fk_movies_has_persons_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_has_persons_persons1`
    FOREIGN KEY (`persons_person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_has_persons_movie_roles1`
    FOREIGN KEY (`roles_role_id` )
    REFERENCES `movlib`.`movie_roles` (`role_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_revisions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons_revisions` (
  `persons_person_id` BIGINT UNSIGNED NOT NULL ,
  `users_user_id` BIGINT UNSIGNED NOT NULL ,
  `rev_nr` BIGINT NOT NULL ,
  `commit_id` VARCHAR(40) NOT NULL ,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `languages_language_id` INT UNSIGNED NULL ,
  PRIMARY KEY (`persons_person_id`, `users_user_id`) ,
  INDEX `fk_rev_movies_users1_idx` (`users_user_id` ASC) ,
  INDEX `fk_movie_revisions_languages1_idx` (`languages_language_id` ASC) ,
  INDEX `fk_persons_revisions_persons1_idx` (`persons_person_id` ASC) ,
  CONSTRAINT `fk_rev_movies_users10`
    FOREIGN KEY (`users_user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movie_revisions_languages10`
    FOREIGN KEY (`languages_language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_revisions_persons1`
    FOREIGN KEY (`persons_person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`labels`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`labels` (
  `label_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  `description_en` TEXT NULL ,
  `description_de` TEXT NULL ,
  PRIMARY KEY (`label_id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`releases` (
  `release_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `release_title` VARCHAR(255) NULL DEFAULT 'unknown' ,
  `release_date` DATE NULL ,
  `countries_country_id` INT UNSIGNED NOT NULL ,
  `runtime_credits` TIME NULL ,
  `runtime` TIME NULL ,
  `description` TEXT NULL ,
  `bonus` TEXT NULL ,
  `labels_label_id` BIGINT UNSIGNED NOT NULL ,
  `aspect_ratio` VARCHAR(45) NULL ,
  PRIMARY KEY (`release_id`) ,
  INDEX `fk_releases_countries1_idx` (`countries_country_id` ASC) ,
  INDEX `fk_releases_labels1_idx` (`labels_label_id` ASC) ,
  CONSTRAINT `fk_releases_countries1`
    FOREIGN KEY (`countries_country_id` )
    REFERENCES `movlib`.`countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_labels1`
    FOREIGN KEY (`labels_label_id` )
    REFERENCES `movlib`.`labels` (`label_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_alias`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons_alias` (
  `persons_person_id` BIGINT UNSIGNED NOT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`persons_person_id`) ,
  CONSTRAINT `fk_persons_alias_persons1`
    FOREIGN KEY (`persons_person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_dvd`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`releases_dvd` (
  `releases_release_id` BIGINT UNSIGNED NOT NULL ,
  `region_code` VARCHAR(10) NOT NULL ,
  `dvd_format` VARCHAR(5) NULL ,
  `tv_standard` VARCHAR(4) NULL ,
  PRIMARY KEY (`releases_release_id`) ,
  CONSTRAINT `fk_releases_dvd_releases1`
    FOREIGN KEY (`releases_release_id` )
    REFERENCES `movlib`.`releases` (`release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`audio_formats`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`audio_formats` (
  `audio_format_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name_en` VARCHAR(255) NOT NULL ,
  `name_de` VARCHAR(255) NULL ,
  PRIMARY KEY (`audio_format_id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`release_audio_formats`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`release_audio_formats` (
  `audio_formats_audio_format_id` INT UNSIGNED NOT NULL ,
  `releases_release_id` BIGINT UNSIGNED NOT NULL ,
  `languages_language_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`audio_formats_audio_format_id`, `releases_release_id`, `languages_language_id`) ,
  INDEX `fk_release_audio_formats_releases1_idx` (`releases_release_id` ASC) ,
  INDEX `fk_release_audio_formats_languages1_idx` (`languages_language_id` ASC) ,
  CONSTRAINT `fk_release_audio_formats_audio_formats1`
    FOREIGN KEY (`audio_formats_audio_format_id` )
    REFERENCES `movlib`.`audio_formats` (`audio_format_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_release_audio_formats_releases1`
    FOREIGN KEY (`releases_release_id` )
    REFERENCES `movlib`.`releases` (`release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_release_audio_formats_languages1`
    FOREIGN KEY (`languages_language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`release_subtitles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`release_subtitles` (
  `releases_release_id` BIGINT UNSIGNED NOT NULL ,
  `languages_language_id` INT UNSIGNED NOT NULL ,
  `hearing_impaired` TINYINT(1) NOT NULL DEFAULT false ,
  PRIMARY KEY (`releases_release_id`, `languages_language_id`) ,
  INDEX `fk_release_subtitles_releases1_idx` (`releases_release_id` ASC) ,
  CONSTRAINT `fk_release_subtitles_languages1`
    FOREIGN KEY (`languages_language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_release_subtitles_releases1`
    FOREIGN KEY (`releases_release_id` )
    REFERENCES `movlib`.`releases` (`release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_bluray`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`releases_bluray` (
  `releases_release_id` BIGINT UNSIGNED NOT NULL ,
  `region_code` VARCHAR(10) NULL ,
  `blu_ray_format` VARCHAR(45) NULL ,
  PRIMARY KEY (`releases_release_id`) ,
  CONSTRAINT `fk_releases_bluray_releases1`
    FOREIGN KEY (`releases_release_id` )
    REFERENCES `movlib`.`releases` (`release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_revisions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`releases_revisions` (
  `releases_release_id` BIGINT UNSIGNED NOT NULL ,
  `users_user_id` BIGINT UNSIGNED NOT NULL ,
  `rev_nr` BIGINT NOT NULL ,
  `commit_id` VARCHAR(40) NOT NULL ,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `languages_language_id` INT UNSIGNED NULL ,
  PRIMARY KEY (`releases_release_id`, `users_user_id`) ,
  INDEX `fk_rev_movies_users1_idx` (`users_user_id` ASC) ,
  INDEX `fk_movie_revisions_languages1_idx` (`languages_language_id` ASC) ,
  INDEX `fk_releases_revisions_releases1_idx` (`releases_release_id` ASC) ,
  CONSTRAINT `fk_rev_movies_users100`
    FOREIGN KEY (`users_user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movie_revisions_languages100`
    FOREIGN KEY (`languages_language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_releases_revisions_releases1`
    FOREIGN KEY (`releases_release_id` )
    REFERENCES `movlib`.`releases` (`release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_has_releases`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_has_releases` (
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `releases_release_id` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`movies_movie_id`, `releases_release_id`) ,
  INDEX `fk_movies_has_releases_releases1_idx` (`releases_release_id` ASC) ,
  INDEX `fk_movies_has_releases_movies1_idx` (`movies_movie_id` ASC) ,
  CONSTRAINT `fk_movies_has_releases_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movies_has_releases_releases1`
    FOREIGN KEY (`releases_release_id` )
    REFERENCES `movlib`.`releases` (`release_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`posters`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`posters` (
  `images_file_id` BIGINT UNSIGNED NOT NULL ,
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `languages_language_id` INT UNSIGNED NOT NULL ,
  `rating` BIGINT UNSIGNED NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`images_file_id`) ,
  INDEX `fk_posters_movies1_idx` (`movies_movie_id` ASC) ,
  INDEX `fk_posters_languages1_idx` (`languages_language_id` ASC) ,
  CONSTRAINT `fk_posters_files1`
    FOREIGN KEY (`images_file_id` )
    REFERENCES `movlib`.`images` (`file_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_posters_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_posters_languages1`
    FOREIGN KEY (`languages_language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`lobby_cards`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`lobby_cards` (
  `files_file_id` BIGINT UNSIGNED NOT NULL ,
  `movies_movie_id` BIGINT UNSIGNED NOT NULL ,
  `languages_language_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`files_file_id`) ,
  INDEX `fk_table1_movies1_idx` (`movies_movie_id` ASC) ,
  INDEX `fk_lobby_cards_languages1_idx` (`languages_language_id` ASC) ,
  CONSTRAINT `fk_table1_files1`
    FOREIGN KEY (`files_file_id` )
    REFERENCES `movlib`.`images` (`file_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_table1_movies1`
    FOREIGN KEY (`movies_movie_id` )
    REFERENCES `movlib`.`movies` (`movie_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_lobby_cards_languages1`
    FOREIGN KEY (`languages_language_id` )
    REFERENCES `movlib`.`languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_images`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons_images` (
  `images_file_id` BIGINT UNSIGNED NOT NULL ,
  `persons_person_id` BIGINT UNSIGNED NOT NULL ,
  `rating` BIGINT NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`images_file_id`) ,
  INDEX `fk_persons_images_persons1_idx` (`persons_person_id` ASC) ,
  CONSTRAINT `fk_persons_images_files1`
    FOREIGN KEY (`images_file_id` )
    REFERENCES `movlib`.`images` (`file_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_images_persons1`
    FOREIGN KEY (`persons_person_id` )
    REFERENCES `movlib`.`persons` (`person_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`labels_logos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`labels_logos` (
  `images_file_id` BIGINT UNSIGNED NOT NULL ,
  `labels_label_id` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`images_file_id`) ,
  INDEX `fk_labels_logos_labels1_idx` (`labels_label_id` ASC) ,
  CONSTRAINT `fk_table1_images1`
    FOREIGN KEY (`images_file_id` )
    REFERENCES `movlib`.`images` (`file_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_labels_logos_labels1`
    FOREIGN KEY (`labels_label_id` )
    REFERENCES `movlib`.`labels` (`label_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`images_descriptions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`images_descriptions` (
  `images_file_id` BIGINT UNSIGNED NOT NULL ,
  `description_en` TEXT NULL ,
  `description_de` TEXT NULL ,
  PRIMARY KEY (`images_file_id`) ,
  CONSTRAINT `fk_table1_images2`
    FOREIGN KEY (`images_file_id` )
    REFERENCES `movlib`.`images` (`file_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`posters_ratings`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`posters_ratings` (
  `posters_files_file_id` BIGINT UNSIGNED NOT NULL ,
  `users_user_id` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`posters_files_file_id`, `users_user_id`) ,
  INDEX `fk_posters_ratings_users1_idx` (`users_user_id` ASC) ,
  CONSTRAINT `fk_posters_ratings_posters1`
    FOREIGN KEY (`posters_files_file_id` )
    REFERENCES `movlib`.`posters` (`images_file_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_posters_ratings_users1`
    FOREIGN KEY (`users_user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_images_ratings`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons_images_ratings` (
  `persons_images_files_file_id` BIGINT UNSIGNED NOT NULL ,
  `users_user_id` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`persons_images_files_file_id`, `users_user_id`) ,
  INDEX `fk_persons_images_ratings_users1_idx` (`users_user_id` ASC) ,
  CONSTRAINT `fk_persons_images_ratings_persons_images1`
    FOREIGN KEY (`persons_images_files_file_id` )
    REFERENCES `movlib`.`persons_images` (`images_file_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_persons_images_ratings_users1`
    FOREIGN KEY (`users_user_id` )
    REFERENCES `movlib`.`users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
USE `movlib` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
