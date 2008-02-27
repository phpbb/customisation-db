#
# phpBB Backup Script
# Dump of tables for community_
# DATE : 16-02-2008 22:32:03 GMT
#
# Table: community_site_contrib_revisions
DROP TABLE IF EXISTS community_site_contrib_revisions;
CREATE TABLE `community_site_contrib_revisions` (
  `revision_id` int(11) NOT NULL auto_increment,
  `contrib_id` int(11) NOT NULL,
  `contrib_type` tinyint(4) NOT NULL,
  `revision_name` varchar(150) collate utf8_bin NOT NULL,
  `revision_version` varchar(16) character set utf8 NOT NULL,
  `revision_date` int(11) NOT NULL,
  `revision_filename` varchar(100) character set utf8 NOT NULL,
  `revision_filename_internal` varchar(44) character set utf8 NOT NULL,
  `revision_filesize` int(11) NOT NULL,
  `revision_md5` varchar(32) character set utf8 NOT NULL,
  `revision_phpbb_version` varchar(8) character set utf8 NOT NULL,
  `user_id` int(11) NOT NULL,
  `revision_repackager` int(11) NOT NULL default '0',
  PRIMARY KEY  (`revision_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4684 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

# Table: community_site_contrib_tags
DROP TABLE IF EXISTS community_site_contrib_tags;
CREATE TABLE `community_site_contrib_tags` (
  `tag_id` int(11) NOT NULL,
  `contrib_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

# Table: community_site_contrib_topics
DROP TABLE IF EXISTS community_site_contrib_topics;
CREATE TABLE `community_site_contrib_topics` (
  `contrib_id` int(11) NOT NULL,
  `topic_type` tinyint(4) NOT NULL,
  `topic_id` int(11) NOT NULL,
  KEY `contrib_id` (`contrib_id`,`topic_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

# Table: community_site_contribs
DROP TABLE IF EXISTS community_site_contribs;
CREATE TABLE `community_site_contribs` (
  `contrib_id` int(11) NOT NULL auto_increment,
  `contrib_type` tinyint(4) NOT NULL,
  `contrib_name` varchar(150) collate utf8_bin NOT NULL,
  `contrib_description` mediumtext collate utf8_bin NOT NULL,
  `contrib_status` smallint(6) NOT NULL,
  `contrib_status_update` smallint(6) NOT NULL default '0',
  `contrib_version` varchar(15) character set utf8 NOT NULL,
  `contrib_revision_name` varchar(64) character set utf8 NOT NULL,
  `contrib_filename` varchar(100) character set utf8 NOT NULL,
  `contrib_filename_internal` varchar(44) character set utf8 NOT NULL,
  `contrib_filesize` int(11) NOT NULL,
  `contrib_md5` char(32) character set utf8 NOT NULL,
  `contrib_phpbb_version` varchar(8) character set utf8 NOT NULL,
  `user_id` int(11) NOT NULL,
  `contrib_downloads` int(11) NOT NULL,
  `contrib_rating` int(11) NOT NULL,
  `contrib_rate_count` int(11) NOT NULL,
  `contrib_bbcode_bitfield` varchar(255) collate utf8_bin NOT NULL default '',
  `contrib_bbcode_uid` varchar(8) collate utf8_bin NOT NULL,
  `contrib_bbcode_flags` int(11) unsigned NOT NULL default '7',
  `contrib_status_update3` tinyint(1) unsigned NOT NULL default '0',
  `contrib_style_demo` varchar(150) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`contrib_id`),
  KEY `contrib_name` (`contrib_name`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3649 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

# Table: community_site_contributers
DROP TABLE IF EXISTS community_site_contributers;
CREATE TABLE `community_site_contributers` (
  `contrib_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL default '0',
  `contrib_role` int(11) NOT NULL,
  PRIMARY KEY  (`contrib_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

# Table: community_site_emails
DROP TABLE IF EXISTS community_site_emails;
CREATE TABLE `community_site_emails` (
  `contrib_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL default '0',
  `email_address` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Table: community_site_queue
DROP TABLE IF EXISTS community_site_queue;
CREATE TABLE `community_site_queue` (
  `queue_id` int(11) NOT NULL auto_increment,
  `contrib_id` int(11) NOT NULL,
  `contrib_type` tinyint(4) NOT NULL,
  `revision_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `queue_opened` int(11) NOT NULL,
  `queue_closed` int(11) NOT NULL,
  `queue_priority` int(11) NOT NULL,
  `queue_status` smallint(6) NOT NULL,
  `queue_action` smallint(6) NOT NULL,
  `queue_data` mediumtext character set utf8,
  PRIMARY KEY  (`queue_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4714 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

# Table: community_site_tags
DROP TABLE IF EXISTS community_site_tags;
CREATE TABLE `community_site_tags` (
  `tag_id` int(11) NOT NULL auto_increment,
  `tag_name` varchar(16) character set utf8 NOT NULL,
  `tag_label` varchar(32) character set utf8 NOT NULL,
  `tag_class` varchar(10) character set utf8 NOT NULL,
  `tag_group` varchar(16) character set utf8 NOT NULL,
  `tag_count` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`tag_id`)
) ENGINE=MyISAM AUTO_INCREMENT=120 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

