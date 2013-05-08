SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `movlib` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
SHOW WARNINGS;
USE `movlib` ;

-- -----------------------------------------------------
-- Table `movlib`.`languages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`languages` (
  `language_id` INT UNSIGNED NOT NULL ,
  `iso_alpha_2` CHAR(2) NOT NULL ,
  `iso_alpha_3` CHAR(3) NOT NULL ,
  `name_en` VARCHAR(255) NOT NULL ,
  `name_de` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`language_id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movie_titles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movie_titles` (
  `title_id` BIGINT UNSIGNED NOT NULL ,
  `title` TEXT NOT NULL ,
  `language_id` INT UNSIGNED NOT NULL ,
  `movie_id` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`title_id`) ,
  INDEX `fk_titles_languages1_idx` (`language_id` ASC) ,
  INDEX `fk_titles_movies1_idx` (`movie_id` ASC) );

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies` (
  `movie_id` BIGINT UNSIGNED NOT NULL ,
  `year` INT(4) NOT NULL DEFAULT 0000 ,
  `rating` FLOAT NOT NULL DEFAULT 0.0 ,
  `original_title_id` BIGINT UNSIGNED NOT NULL ,
  `display` TINYINT(1) NOT NULL DEFAULT true ,
  PRIMARY KEY (`movie_id`) ,
  INDEX `fk_movies_titles1_idx` (`original_title_id` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`countries`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`countries` (
  `country_id` INT UNSIGNED NOT NULL ,
  `iso_alpha_2` CHAR(2) NOT NULL ,
  `iso_alpha_3` CHAR(3) NOT NULL ,
  `name_en` VARCHAR(255) NOT NULL ,
  `name_de` VARCHAR(255) NOT NULL ,
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
  INDEX `fk_countries_has_movies_countries_idx` (`countries_country_id` ASC) )
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
  INDEX `fk_movies_has_languages_movies1_idx` (`movies_movie_id` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_en`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_en` (
  `movie_id` BIGINT UNSIGNED NOT NULL ,
  `display_title_id` BIGINT UNSIGNED NULL ,
  `synopsis` TEXT NULL ,
  PRIMARY KEY (`movie_id`) ,
  INDEX `fk_movies_en_movies1_idx` (`movie_id` ASC) ,
  INDEX `fk_movies_en_titles1_idx` (`display_title_id` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`genres`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`genres` (
  `genre_id` INT UNSIGNED NOT NULL ,
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
  INDEX `fk_movies_has_genres_movies1_idx` (`movies_movie_id` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`styles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`styles` (
  `style_id` INT UNSIGNED NOT NULL ,
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
  INDEX `fk_movies_has_styles_movies1_idx` (`movies_movie_id` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`movies_de`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`movies_de` (
  `movie_id` BIGINT UNSIGNED NOT NULL ,
  `display_title_id` BIGINT UNSIGNED NULL ,
  `synopsis` TEXT NULL ,
  PRIMARY KEY (`movie_id`) ,
  INDEX `fk_movies_de_titles1_idx` (`display_title_id` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`users` (
  `user_id` BIGINT UNSIGNED NOT NULL ,
  `username` VARCHAR(20) NOT NULL ,
  `email` VARCHAR(255) NOT NULL ,
  `pass` VARCHAR(60) NOT NULL ,
  PRIMARY KEY (`user_id`) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) )
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
  INDEX `fk_ratings_users1_idx` (`users_user_id` ASC) )
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
  INDEX `fk_movie_revisions_languages1_idx` (`languages_language_id` ASC) )
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
  INDEX `fk_persons_countries1_idx` (`countries_country_id` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_en`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons_en` (
  `persons_person_id` BIGINT UNSIGNED NOT NULL ,
  `biography` TEXT NULL ,
  `birthplace` VARCHAR(255) NULL ,
  PRIMARY KEY (`persons_person_id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_de`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons_de` (
  `persons_person_id` BIGINT UNSIGNED NOT NULL ,
  `biography` TEXT NULL ,
  `birthplace` VARCHAR(255) NULL ,
  PRIMARY KEY (`persons_person_id`) )
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
  INDEX `fk_movies_has_persons_movie_roles1_idx` (`roles_role_id` ASC) )
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
  INDEX `fk_persons_revisions_persons1_idx` (`persons_person_id` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`labels`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`labels` (
  `label_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
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
  `release_date` DATE NULL ,
  `releasescol` VARCHAR(45) NULL ,
  `countries_country_id` INT UNSIGNED NOT NULL ,
  `runtime_credits` TIME NULL ,
  `runtime` TIME NULL ,
  `description` TEXT NULL ,
  `bonus` TEXT NULL ,
  `labels_label_id` INT UNSIGNED NOT NULL ,
  `aspect_ratio` VARCHAR(45) NULL ,
  PRIMARY KEY (`release_id`) ,
  INDEX `fk_releases_countries1_idx` (`countries_country_id` ASC) ,
  INDEX `fk_releases_labels1_idx` (`labels_label_id` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`persons_alias`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`persons_alias` (
  `persons_person_id` BIGINT UNSIGNED NOT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`persons_person_id`) )
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
  PRIMARY KEY (`releases_release_id`) )
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
  INDEX `fk_release_audio_formats_languages1_idx` (`languages_language_id` ASC) )
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
  INDEX `fk_release_subtitles_releases1_idx` (`releases_release_id` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `movlib`.`releases_bluray`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `movlib`.`releases_bluray` (
  `releases_release_id` BIGINT UNSIGNED NOT NULL ,
  `region_code` VARCHAR(10) NULL ,
  `blu_ray_format` VARCHAR(45) NULL ,
  PRIMARY KEY (`releases_release_id`) )
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
  INDEX `fk_releases_revisions_releases1_idx` (`releases_release_id` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;
USE `movlib` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
