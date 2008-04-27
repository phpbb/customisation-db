#
# @version $Id$
#

CREATE TABLE customisation_contribs (
  contrib_id int(11) NOT NULL auto_increment,
  contrib_type tinyint(1) unsigned NOT NULL default '1',
  contrib_name varchar(255) collate utf8_bin NOT NULL,
  contrib_description mediumtext collate utf8_bin NOT NULL,
  contrib_desc_bitfield varchar(255) collate utf8_bin NOT NULL,
  contrib_desc_options int(11) unsigned NOT NULL default '7',
  contrib_desc_uid varchar(8) collate utf8_bin NOT NULL,
  contrib_status tinyint(2) unsigned NOT NULL default '0',
  contrib_version varchar(15) collate utf8_bin NOT NULL,
  contrib_version_name varchar(255) collate utf8_bin NOT NULL,
  contrib_author_id int(11) unsigned NOT NULL default '0',
  contrib_downloads int(11) unsigned NOT NULL default '0',
  contrib_views int(11) unsigned NOT NULL default '0',
  contrib_phpbb_version varchar(8) collate utf8_bin NOT NULL,
  contrib_release_date int(11) unsigned NOT NULL default '0',
  contrib_update_date int(11) unsigned NOT NULL default '0',
  contrib_visibility tinyint(1) unsigned NOT NULL default '0',
  contrib_rating decimal(11,9) NOT NULL,
  contrib_rating_count int(11) unsigned NOT NULL default '0',
  contrib_demo varchar(255) collate utf8_bin NOT NULL,
  PRIMARY KEY  (contrib_id),
  KEY contrib_author_id (contrib_author_id),
  KEY contrib_status (contrib_status),
  KEY contrib_visibility (contrib_visibility),
  KEY contrib_phpbb_version (contrib_phpbb_version),
  KEY contrib_rating (contrib_rating),
  KEY contrib_downloads (contrib_downloads)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;