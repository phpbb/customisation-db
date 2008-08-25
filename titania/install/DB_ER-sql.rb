/* SQLEditor (MySQL)*/


CREATE TABLE `customisation_authors`
(
`author_id` MEDIUMINT(8) unsigned  NOT NULL AUTO_INCREMENT ,
`user_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`phpbb_user_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`author_username` VARCHAR(255) NOT NULL,
`author_username_clean` VARCHAR(255) NOT NULL,
`author_realname` VARCHAR(255) NOT NULL,
`author_website` VARCHAR(200) NOT NULL,
`author_email` VARCHAR(100) NOT NULL,
`author_email_hash` BIGINT(20) DEFAULT 0 NOT NULL,
`author_rating` DECIMAL(11,9) unsigned  DEFAULT 0.000000000 NOT NULL,
`author_rating_count` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`author_contribs` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`author_snippets` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`author_mods` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`author_styles` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
PRIMARY KEY (`author_id`)
) TYPE=InnoDB;



CREATE TABLE `customisation_tag_types`
(
`tag_type_id` MEDIUMINT(8) unsigned  NOT NULL AUTO_INCREMENT ,
`tag_type_name` VARCHAR(255) NOT NULL,
PRIMARY KEY (`tag_type_id`)
) TYPE=InnoDB;



CREATE TABLE `customisation_contribs`
(
`contrib_id` MEDIUMINT(8) unsigned  NOT NULL AUTO_INCREMENT ,
`contrib_type` TINYINT(1) unsigned  DEFAULT 1 NOT NULL,
`contrib_name` VARCHAR(255) NOT NULL,
`contrib_description` MEDIUMTEXT NOT NULL,
`contrib_desc_bitfield` VARCHAR(255) NOT NULL,
`contrib_desc_options` INT(11) unsigned  DEFAULT 7 NOT NULL,
`contrib_desc_uid` VARCHAR(8) NOT NULL,
`contrib_status` TINYINT(2) unsigned  DEFAULT 0 NOT NULL,
`contrib_version` VARCHAR(15) NOT NULL,
`contrib_revision` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`contrib_validated_revision` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`contrib_author_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`contrib_maintainer` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`contrib_downloads` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`contrib_views` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`contrib_phpbb_version` TINYINT(2) unsigned  DEFAULT 0 NOT NULL,
`contrib_release_date` INT(11) unsigned  DEFAULT 0 NOT NULL,
`contrib_update_date` INT(11) unsigned  DEFAULT 0 NOT NULL,
`contrib_visibility` TINYINT(1) unsigned  DEFAULT 0 NOT NULL,
`contrib_rating` DECIMAL(11,9) unsigned  DEFAULT 0.000000000 NOT NULL,
`contrib_rating_count` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`contrib_demo` VARCHAR(255) NOT NULL,
PRIMARY KEY (`contrib_id`)
) TYPE=InnoDB;



CREATE TABLE `customisation_watch`
(
`contrib_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`user_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`mark_time` INT(11) unsigned  DEFAULT 0 NOT NULL,
PRIMARY KEY (`contrib_id`,`user_id`)
) TYPE=InnoDB;



CREATE TABLE `customisation_tag_fields`
(
`tag_id` MEDIUMINT(8) unsigned  NOT NULL AUTO_INCREMENT ,
`tag_type_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`tag_field_name` VARCHAR(255) NOT NULL,
`tag_field_desc` VARCHAR(255) NOT NULL,
`contrib_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
PRIMARY KEY (`tag_id`)
) TYPE=InnoDB;



CREATE TABLE `customisation_contrib_tags`
(
`contrib_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`tag_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`tag_value` VARCHAR(255) NOT NULL,
PRIMARY KEY (`contrib_id`,`tag_id`)
) TYPE=InnoDB;



CREATE TABLE `customisation_revisions`
(
`revision_id` MEDIUMINT(8) unsigned  NOT NULL AUTO_INCREMENT ,
`contrib_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`contrib_type` TINYINT(1) unsigned  DEFAULT 0 NOT NULL,
`revision_name` VARCHAR(100) NOT NULL,
`revision_time` INT(11) unsigned  DEFAULT 0 NOT NULL,
PRIMARY KEY (`revision_id`)
) TYPE=InnoDB;



CREATE TABLE `customisation_downloads`
(
`download_id` MEDIUMINT(8) unsigned  NOT NULL AUTO_INCREMENT ,
`revision_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`download_type` TINYINT(1) unsigned  DEFAULT 0 NOT NULL,
`download_status` TINYINT(1) unsigned  DEFAULT 0 NOT NULL,
`filesize` INT(11) unsigned  DEFAULT 0 NOT NULL,
`filetime` INT(11) unsigned  DEFAULT 0 NOT NULL,
`physical_filename` VARCHAR(255) NOT NULL,
`real_filename` VARCHAR(255) NOT NULL,
`download_count` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`extension` VARCHAR(100) NOT NULL,
`mimetype` VARCHAR(100) NOT NULL,
`download_hash` VARCHAR(32) NOT NULL,
`download_url` VARCHAR(255) NOT NULL,
`thumbnail` TINYINT(1) unsigned  DEFAULT 0 NOT NULL,
PRIMARY KEY (`download_id`)
) TYPE=InnoDB;



CREATE TABLE `customisation_reviews`
(
`review_id` MEDIUMINT(8) unsigned  NOT NULL AUTO_INCREMENT ,
`contrib_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`review_text` MEDIUMTEXT NOT NULL,
`review_text_bitfield` VARCHAR(255) NOT NULL,
`review_text_uid` VARCHAR(8) NOT NULL,
`review_text_options` INT(11) unsigned  DEFAULT 7 NOT NULL,
`review_rating` TINYINT(1) unsigned  DEFAULT 3 NOT NULL,
`review_user_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`review_status` TINYINT(1) unsigned  DEFAULT 1 NOT NULL,
`review_time` INT(11) unsigned  DEFAULT 0 NOT NULL,
PRIMARY KEY (`review_id`)
) TYPE=InnoDB;



CREATE TABLE `customisation_queue`
(
`queue_id` MEDIUMINT(8) unsigned  NOT NULL AUTO_INCREMENT ,
`revision_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`queue_type` TINYINT(1) unsigned  DEFAULT 0 NOT NULL,
`queue_status` TINYINT(1) unsigned  DEFAULT 0 NOT NULL,
`topic_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`contrib_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`submitter_user_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`queue_notes` MEDIUMTEXT NOT NULL,
`queue_notes_bitfield` VARCHAR(255) NOT NULL,
`queue_notes_options` INT(11) unsigned  DEFAULT 0 NOT NULL,
`queue_notes_uid` VARCHAR(8) NOT NULL,
`queue_progress` TINYINT(3) unsigned  DEFAULT 0 NOT NULL,
`queue_submit_time` INT(11) unsigned  DEFAULT 0 NOT NULL,
`queue_close_time` INT(11) unsigned  DEFAULT 0 NOT NULL,
PRIMARY KEY (`queue_id`)
) TYPE=InnoDB;



CREATE TABLE `customisation_queue_history`
(
`history_id` MEDIUMINT(8) unsigned  NOT NULL AUTO_INCREMENT ,
`queue_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
`user_id` MEDIUMINT(8) unsigned  DEFAULT 0 NOT NULL,
PRIMARY KEY (`history_id`)
) TYPE=InnoDB;


ALTER TABLE `customisation_contribs` ADD FOREIGN KEY (`contrib_author_id`) REFERENCES `customisation_authors`(`author_id`);
CREATE INDEX `customisation_watch_contrib_id_idxfk`  ON `customisation_watch`(`contrib_id`);
ALTER TABLE `customisation_watch` ADD FOREIGN KEY (`contrib_id`) REFERENCES `customisation_contribs`(`contrib_id`);
ALTER TABLE `customisation_tag_fields` ADD FOREIGN KEY (`tag_type_id`) REFERENCES `customisation_tag_types`(`tag_type_id`);
ALTER TABLE `customisation_tag_fields` ADD FOREIGN KEY (`contrib_id`) REFERENCES `customisation_contribs`(`contrib_id`);
CREATE INDEX `customisation_contrib_tags_contrib_id_idxfk`  ON `customisation_contrib_tags`(`contrib_id`);
ALTER TABLE `customisation_contrib_tags` ADD FOREIGN KEY (`contrib_id`) REFERENCES `customisation_contribs`(`contrib_id`);
CREATE INDEX `customisation_contrib_tags_tag_id_idxfk`  ON `customisation_contrib_tags`(`tag_id`);
ALTER TABLE `customisation_contrib_tags` ADD FOREIGN KEY (`tag_id`) REFERENCES `customisation_tag_fields`(`tag_id`);
ALTER TABLE `customisation_downloads` ADD FOREIGN KEY (`revision_id`) REFERENCES `customisation_revisions`(`revision_id`);
ALTER TABLE `customisation_reviews` ADD FOREIGN KEY (`contrib_id`) REFERENCES `customisation_contribs`(`contrib_id`);
ALTER TABLE `customisation_queue` ADD FOREIGN KEY (`revision_id`) REFERENCES `customisation_revisions`(`revision_id`);
ALTER TABLE `customisation_queue` ADD FOREIGN KEY (`contrib_id`) REFERENCES `customisation_contribs`(`contrib_id`);
ALTER TABLE `customisation_queue_history` ADD FOREIGN KEY (`queue_id`) REFERENCES `customisation_queue`(`queue_id`);
