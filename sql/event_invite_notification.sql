CREATE TABLE IF NOT EXISTS `civicrm_eventinvite_notifications`(
  `id` INT(10) NOT NULL AUTO_INCREMENT ,
  `event_id` INT(10) NOT NULL ,
  `recipients` INT(10) NOT NULL,
  `from_email` VARCHAR(128),
  `subject` VARCHAR(256) NULL DEFAULT NULL,
  `msg_text` TEXT, `contacts` TEXT NULL,
  `notification_date` DATETIME NULL,
  `msg_id` int   DEFAULT NULL COMMENT 'Use Message template for sending Notification',
  PRIMARY KEY (`id`),
  INDEX `idx_event_id` (`event_id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `civicrm_eventinvite_contact`(
  `id` INT(10) NOT NULL AUTO_INCREMENT ,
  `notification_id` INT(10) NOT NULL ,
  `contact_id` int unsigned    COMMENT 'FK to Contact',
  `status_id` int unsigned   DEFAULT 2 COMMENT 'Notification Status',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_eventinvite_contact_notification_id FOREIGN KEY (`notification_id`) REFERENCES `civicrm_eventinvite_notifications`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_eventinvite_contact_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB;
