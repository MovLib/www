SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP SCHEMA IF EXISTS `test` ;
CREATE SCHEMA IF NOT EXISTS `test` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
SHOW WARNINGS;
USE `test` ;

-- -----------------------------------------------------
-- Table `test`.`dynamic`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`dynamic` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`id`) )
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`json`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`json` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`id`) )
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`columns`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`columns` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `de` BLOB NULL ,
  `en` BLOB NULL ,
  `nl` BLOB NULL ,
  `es` BLOB NULL ,
  `fr` BLOB NULL ,
  `ja` BLOB NULL ,
  `ru` BLOB NULL ,
  `xx` BLOB NULL ,
  `xy` BLOB NULL ,
  `xz` BLOB NULL ,
  PRIMARY KEY (`id`) )
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`parent`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`parent` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  PRIMARY KEY (`id`) )
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`child_de`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`child_de` (
  `parent_id` BIGINT UNSIGNED NOT NULL ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`parent_id`) ,
  CONSTRAINT `fk_table1_parent`
    FOREIGN KEY (`parent_id` )
    REFERENCES `test`.`parent` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`child_en`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`child_en` (
  `parent_id` BIGINT UNSIGNED NOT NULL ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`parent_id`) ,
  CONSTRAINT `fk_table1_parent0`
    FOREIGN KEY (`parent_id` )
    REFERENCES `test`.`parent` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`child_nl`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`child_nl` (
  `parent_id` BIGINT UNSIGNED NOT NULL ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`parent_id`) ,
  CONSTRAINT `fk_table1_parent1`
    FOREIGN KEY (`parent_id` )
    REFERENCES `test`.`parent` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`child_fr`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`child_fr` (
  `parent_id` BIGINT UNSIGNED NOT NULL ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`parent_id`) ,
  CONSTRAINT `fk_table1_parent2`
    FOREIGN KEY (`parent_id` )
    REFERENCES `test`.`parent` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`child_ja`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`child_ja` (
  `parent_id` BIGINT UNSIGNED NOT NULL ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`parent_id`) ,
  CONSTRAINT `fk_table1_parent3`
    FOREIGN KEY (`parent_id` )
    REFERENCES `test`.`parent` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`child_xz`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`child_xz` (
  `parent_id` BIGINT UNSIGNED NOT NULL ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`parent_id`) ,
  CONSTRAINT `fk_table1_parent4`
    FOREIGN KEY (`parent_id` )
    REFERENCES `test`.`parent` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`child_xy`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`child_xy` (
  `parent_id` BIGINT UNSIGNED NOT NULL ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`parent_id`) ,
  CONSTRAINT `fk_table1_parent5`
    FOREIGN KEY (`parent_id` )
    REFERENCES `test`.`parent` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`child_xx`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`child_xx` (
  `parent_id` BIGINT UNSIGNED NOT NULL ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`parent_id`) ,
  CONSTRAINT `fk_table1_parent6`
    FOREIGN KEY (`parent_id` )
    REFERENCES `test`.`parent` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`child_ru`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`child_ru` (
  `parent_id` BIGINT UNSIGNED NOT NULL ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`parent_id`) ,
  CONSTRAINT `fk_table1_parent7`
    FOREIGN KEY (`parent_id` )
    REFERENCES `test`.`parent` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `test`.`child_es`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`child_es` (
  `parent_id` BIGINT UNSIGNED NOT NULL ,
  `synopsis` BLOB NULL ,
  PRIMARY KEY (`parent_id`) ,
  CONSTRAINT `fk_table1_parent8`
    FOREIGN KEY (`parent_id` )
    REFERENCES `test`.`parent` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ROW_FORMAT = COMPRESSED;

SHOW WARNINGS;
USE `test` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
