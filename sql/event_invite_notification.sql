CREATE TABLE IF NOT EXISTS `civicrm_eventinvite_notifications`(
  `id` INT(10) NOT NULL AUTO_INCREMENT ,
  `event_id` INT(10) NOT NULL ,
  `recipients` INT(10) NOT NULL,
  `from_email` VARCHAR(128),
  `msg_text` TEXT, `contacts` TEXT NULL,
  `notification_date` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_event_id` (`event_id`)
) ENGINE = InnoDB;
