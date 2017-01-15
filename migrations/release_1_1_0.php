<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\titania\migrations;

use phpbb\titania\ext;

class release_1_1_0 extends base
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function effectively_installed()
	{
		return isset($this->config['titania_version']);
	}

	public function update_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'add_tables' => array(
				$table_prefix . 'attachments' => array(
					'COLUMNS'		=> array(
						'attachment_id'			=> array('UINT', NULL, 'auto_increment'),
						'object_type'			=> array('TINT:1', 0),
						'object_id'				=> array('UINT', 0),
						'attachment_access'		=> array('UINT', 2),
						'attachment_comment'	=> array('TEXT_UNI', ''),
						'attachment_directory'	=> array('VCHAR', ''),
						'physical_filename'		=> array('VCHAR', ''),
						'real_filename'			=> array('VCHAR', ''),
						'download_count'		=> array('UINT', 0),
						'filesize'				=> array('INT:11', 0),
						'filetime'				=> array('INT:11', 0),
						'extension'				=> array('VCHAR:100', ''),
						'mimetype'				=> array('VCHAR:100', ''),
						'hash'					=> array('VCHAR:32', ''),
						'thumbnail'				=> array('BOOL', 0),
						'is_orphan'				=> array('TINT:1', 1),
						'attachment_user_id'	=> array('UINT', 0),
						'is_preview'			=> array('TINT:1', 0),
						'attachment_order'		=> array('UINT:3', 0),
					),
					'PRIMARY_KEY'	=> 'attachment_id',
					'KEYS'			=> array(
						'o_type'		=> array('INDEX', 'object_type'),
						'o_id'			=> array('INDEX', 'object_id'),
						'a_acc'			=> array('INDEX', 'attachment_access'),
						'orphan'		=> array('INDEX', 'is_orphan'),
					),
				),
				$table_prefix . 'attention' => array(
					'COLUMNS'		=> array(
						'attention_id'					=> array('UINT', NULL, 'auto_increment'),
						'attention_type'				=> array('UINT', 0), // attention type constants (reported, needs approval, etc)
						'attention_object_type'			=> array('UINT', 0),
						'attention_object_id'			=> array('UINT', 0),
						'attention_url'					=> array('VCHAR_CI', ''),
						'attention_requester'			=> array('UINT', 0),
						'attention_time'				=> array('TIMESTAMP', 0),
						'attention_close_time'			=> array('TIMESTAMP', 0),
						'attention_close_user'			=> array('UINT', 0),
						'attention_title'				=> array('STEXT_UNI', ''),
						'attention_description'			=> array('MTEXT_UNI', ''),
						'attention_poster_id'			=> array('UINT', 0),
						'attention_post_time'			=> array('TIMESTAMP', 0),
						'notify_reporter'				=> array('TINT:1', 0),
					),
					'PRIMARY_KEY'	=> 'attention_id',
					'KEYS'			=> array(
						'a_type'			=> array('INDEX', 'attention_type'),
						'a_o_type'			=> array('INDEX', 'attention_object_type'),
						'a_o_id'			=> array('INDEX', 'attention_object_id'),
						'a_time'			=> array('INDEX', 'attention_time'),
						'a_c_time'			=> array('INDEX', 'attention_close_time'),
						'a_c_user'			=> array('INDEX', 'attention_close_user'),
						'a_p_uid'			=> array('INDEX', 'attention_poster_id'),
						'a_p_time'			=> array('INDEX', 'attention_post_time'),
					),
				),
				$table_prefix . 'automod_queue' => array(
					'COLUMNS'		=> array(
						'row_id'					=> array('UINT', NULL, 'auto_increment'),
						'revision_id'				=> array('UINT', 0),
						'phpbb_version_branch'		=> array('TINT:1', 0),
						'phpbb_version_revision'	=> array('VCHAR', ''),
					),
					'PRIMARY_KEY'	=> 'row_id',
					'KEYS'			=> array(
						'rev_id'				=> array('INDEX', 'revision_id'),
					),
				),
				$table_prefix . 'authors' => array(
					'COLUMNS'		=> array(
						'author_id'				=> array('UINT', NULL, 'auto_increment'),
						'user_id'				=> array('UINT', 0),
						'phpbb_user_id'			=> array('UINT', 0),
						'author_realname'		=> array('VCHAR_CI', ''),
						'author_website'		=> array('VCHAR_UNI:200', ''),
						'author_rating'			=> array('DECIMAL', 0),
						'author_rating_count'	=> array('UINT', 0),
						'author_contribs'		=> array('UINT', 0), // Total # of contribs
						'author_mods'			=> array('UINT', 0), // Number of mods
						'author_styles'			=> array('UINT', 0), // Number of styles
						'author_visible'		=> array('BOOL', 1),
						'author_desc'			=> array('MTEXT_UNI', ''),
						'author_desc_bitfield'	=> array('VCHAR:255', ''),
						'author_desc_uid'		=> array('VCHAR:8', ''),
						'author_desc_options'	=> array('UINT:11', 7),
					),
					'PRIMARY_KEY'	=> 'author_id',
					'KEYS'			=> array(
						'uid'			=> array('UNIQUE', 'user_id'),
						'a_rat'			=> array('INDEX', 'author_rating'),
						'a_con'			=> array('INDEX', 'author_contribs'),
						'a_mod'			=> array('INDEX', 'author_mods'),
						'a_sty'			=> array('INDEX', 'author_styles'),
						'a_vis'			=> array('INDEX', 'author_visible'),
					),
				),
				$table_prefix . 'categories' => array(
					'COLUMNS'		=> array(
						'category_id'				=> array('UINT', NULL, 'auto_increment'),
						'parent_id'					=> array('UINT', 0),
						'left_id'					=> array('UINT', 0),
						'right_id'					=> array('UINT', 0),
						'category_type'				=> array('TINT:1', 0),
						'category_contribs'			=> array('UINT', 0), // Number of items
						'category_visible'			=> array('BOOL', 1),
						'category_name'				=> array('STEXT_UNI', '', 'true_sort'),
						'category_name_clean'		=> array('VCHAR_CI', ''),
						'category_desc'				=> array('MTEXT_UNI', ''),
						'category_desc_bitfield'	=> array('VCHAR:255', ''),
						'category_desc_uid'			=> array('VCHAR:8', ''),
						'category_desc_options'		=> array('UINT:11', 7),
						'category_options'			=> array('TINT:4', 0),
					),
					'PRIMARY_KEY'	=> 'category_id',
					'KEYS'			=> array(
						'p_id'			=> array('INDEX', 'parent_id'),
						'lr_id'			=> array('INDEX', array('left_id', 'right_id')),
						'c_typ'			=> array('INDEX', 'category_type'),
						'c_vis'			=> array('INDEX', 'category_visible'),
					),
				),
				$table_prefix . 'contribs' => array(
					'COLUMNS'		=> array(
						'contrib_id'					=> array('UINT', NULL, 'auto_increment'),
						'contrib_user_id'				=> array('UINT', 0),
						'contrib_type'					=> array('TINT:1', 0),
						'contrib_name'					=> array('STEXT_UNI', '', 'true_sort'),
						'contrib_name_clean'			=> array('VCHAR_CI', ''),
						'contrib_desc'					=> array('MTEXT_UNI', ''),
						'contrib_desc_bitfield'			=> array('VCHAR:255', ''),
						'contrib_desc_uid'				=> array('VCHAR:8', ''),
						'contrib_desc_options'			=> array('UINT:11', 7),
						'contrib_status'				=> array('TINT:2', 0),
						'contrib_downloads'				=> array('UINT', 0),
						'contrib_views'					=> array('UINT', 0),
						'contrib_rating'				=> array('DECIMAL', 0),
						'contrib_rating_count'			=> array('UINT', 0),
						'contrib_visible'				=> array('BOOL', 1),
						'contrib_last_update'			=> array('TIMESTAMP', 0),
						'contrib_demo'					=> array('VCHAR_UNI:200', ''),
						'contrib_release_topic_id'		=> array('UINT', 0),
						'contrib_faq_count'				=> array('VCHAR', ''),
						'contrib_iso_code'				=> array('VCHAR', ''),
						'contrib_local_name'			=> array('VCHAR', ''),
						'contrib_clr_colors'			=> array('VCHAR:255', ''),
						'contrib_limited_support'		=> array('TINT:1', 0),
						'contrib_categories'			=> array('VCHAR:255', ''),
						'contrib_creation_time'			=> array('TIMESTAMP', 0),
					),
					'PRIMARY_KEY'	=> 'contrib_id',
					'KEYS'			=> array(
						'c_uid'			=> array('INDEX', 'contrib_user_id'),
						'c_type'		=> array('INDEX', 'contrib_type'),
						'c_cle'			=> array('INDEX', 'contrib_name_clean'),
						'c_sta'			=> array('INDEX', 'contrib_status'),
						'c_dls'			=> array('INDEX', 'contrib_downloads'),
						'c_rat'			=> array('INDEX', 'contrib_rating'),
						'c_vis'			=> array('INDEX', 'contrib_visible'),
						'c_upd'			=> array('INDEX', 'contrib_last_update'),
					),
				),
				$table_prefix . 'contrib_coauthors' => array(
					'COLUMNS'		=> array(
						'contrib_id'			=> array('UINT', 0),
						'user_id'				=> array('UINT', 0),
						'active'				=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> array('contrib_id', 'user_id'),
					'KEYS'			=> array(
						'act'		=> array('INDEX', 'active'),
					),
				),
				$table_prefix . 'contrib_faq'  => array(
					'COLUMNS'		=> array(
						'faq_id'				=> array('UINT', NULL, 'auto_increment'),
						'contrib_id'			=> array('UINT', 0),
						'left_id'				=> array('UINT', 0),
						'right_id'				=> array('UINT', 0),
						'faq_subject'			=> array('STEXT_UNI', '', 'true_sort'),
						'faq_text'				=> array('MTEXT_UNI', ''),
						'faq_text_bitfield'		=> array('VCHAR:255', ''),
						'faq_text_uid'			=> array('VCHAR:8', ''),
						'faq_text_options'		=> array('UINT:11', 7),
						'faq_views'				=> array('UINT', 0),
						'faq_access'			=> array('TINT:1', 2),
					),
					'PRIMARY_KEY'	=> 'faq_id',
					'KEYS'			=> array(
						'c_id'			=> array('INDEX', 'contrib_id'),
						'faq'			=> array('INDEX', 'faq_access'),
						'lr_id'			=> array('INDEX', array('left_id', 'right_id')),
					),
				),
				$table_prefix . 'contrib_in_categories' => array(
					'COLUMNS'		=> array(
						'contrib_id'			=> array('UINT', 0),
						'category_id'			=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> array('contrib_id', 'category_id'),
				),
				$table_prefix . 'posts' => array(
					'COLUMNS'		=> array(
						'post_id'				=> array('UINT', NULL, 'auto_increment'),
						'topic_id'				=> array('UINT', 0),
						'post_url'				=> array('VCHAR_CI', ''),
						'post_type'				=> array('TINT:1', 0), // Post Type, Main TITANIA_ constants
						'post_access'			=> array('TINT:1', 0), // Access level, TITANIA_ACCESS_ constants
						'post_locked'			=> array('BOOL', 0),
						'post_approved'			=> array('BOOL', 1),
						'post_reported'			=> array('BOOL', 0),
						'post_attachment'		=> array('BOOL', 0),
						'post_user_id'			=> array('UINT', 0),
						'post_ip'				=> array('VCHAR:40', ''),
						'post_time'				=> array('UINT:11', 0),
						'post_edited'			=> array('UINT:11', 0), // Post edited; 0 for not edited, timestamp if (when) last edited
						'post_deleted'			=> array('UINT:11', 0), // Post deleted; 0 for not edited, timestamp if (when) last edited
						'post_delete_user'		=> array('UINT', 0), // The last user to delete the post
						'post_edit_user'		=> array('UINT', 0), // The last user to edit the post
						'post_edit_reason'		=> array('STEXT_UNI', ''), // Reason for deleting/editing
						'post_subject'			=> array('STEXT_UNI', '', 'true_sort'),
						'post_text'				=> array('MTEXT_UNI', '', 'true_sort'),
						'post_text_bitfield'	=> array('VCHAR:255', ''),
						'post_text_uid'			=> array('VCHAR:8', ''),
						'post_text_options'		=> array('UINT:11', 7),
						'post_edit_time'		=> array('UINT:11', 0),
					),
					'PRIMARY_KEY'	=> 'post_id',
					'KEYS'			=> array(
						't_id'				=> array('INDEX', 'topic_id'),
						'p_type'			=> array('INDEX', 'post_type'),
						'p_acce'			=> array('INDEX', 'post_access'),
						'p_appr'			=> array('INDEX', 'post_approved'),
						'p_repo'			=> array('INDEX', 'post_reported'),
						'p_uid'				=> array('INDEX', 'post_user_id'),
						'p_dele'			=> array('INDEX', 'post_deleted'),
					),
				),
				$table_prefix . 'queue' => array(
					'COLUMNS'		=> array(
						'queue_id'				=> array('UINT', NULL, 'auto_increment'),
						'revision_id'			=> array('UINT', 0),
						'contrib_id'			=> array('UINT', 0),
						'queue_type'			=> array('TINT:1', 0),
						'queue_status'			=> array('TINT:1', 0),
						'submitter_user_id'		=> array('UINT', 0),
						'queue_allow_repack'	=> array('BOOL', 1),
						'queue_notes'			=> array('MTEXT_UNI', ''),
						'queue_notes_bitfield'	=> array('VCHAR:255', ''),
						'queue_notes_uid'		=> array('VCHAR:8', ''),
						'queue_notes_options'	=> array('UINT:11', 7),
						'validation_notes'			=> array('MTEXT_UNI', ''),
						'validation_notes_bitfield'	=> array('VCHAR:255', ''),
						'validation_notes_uid'		=> array('VCHAR:8', ''),
						'validation_notes_options'	=> array('UINT:11', 7),
						'queue_submit_time'		=> array('UINT:11', 0),
						'queue_progress'		=> array('UINT', 0), // user_id
						'queue_progress_time'	=> array('UINT:11', 0),
						'queue_close_time'		=> array('UINT:11', 0),
						'queue_close_user'		=> array('UINT', 0),
						'queue_topic_id'		=> array('UINT', 0),
						'mpv_results'			=> array('MTEXT_UNI', ''),
						'mpv_results_bitfield'	=> array('VCHAR:255', ''),
						'mpv_results_uid'		=> array('VCHAR:8', ''),
						'automod_results'		=> array('MTEXT_UNI', ''),
						'allow_author_repack'	=> array('BOOL', 0),
						'queue_tested'			=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'queue_id',
					'KEYS'			=> array(
						'r_id'			=> array('INDEX', 'revision_id'),
						'c_id'			=> array('INDEX', 'contrib_id'),
						'q_type'		=> array('INDEX', 'queue_type'),
						'q_status'		=> array('INDEX', 'queue_status'),
						's_uid'			=> array('INDEX', 'submitter_user_id'),
						'q_time'		=> array('INDEX', 'queue_submit_time'),
					),
				),
				$table_prefix . 'ratings' => array(
					'COLUMNS'		=> array(
						'rating_id'				=> array('UINT', NULL, 'auto_increment'),
						'rating_type_id'		=> array('UINT', 0),
						'rating_user_id'		=> array('UINT', 0),
						'rating_object_id'		=> array('UINT', 0),
						'rating_value'			=> array('DECIMAL', 0), // Not sure if we should allow partial ratings (like 4.5/5) or just integer ratings...
					),
					'PRIMARY_KEY'	=> 'rating_id',
					'KEYS'			=> array(
						't_u_o'			=> array('UNIQUE', array('rating_type_id', 'rating_user_id', 'rating_object_id')),
					),
				),
				$table_prefix . 'revisions' => array(
					'COLUMNS'		=> array(
						'revision_id'				=> array('UINT', NULL, 'auto_increment'),
						'contrib_id'				=> array('UINT', 0),
						'attachment_id'				=> array('UINT', 0),
						'revision_version'			=> array('VCHAR', ''),
						'revision_name'				=> array('STEXT_UNI', '', 'true_sort'),
						'revision_time'				=> array('UINT:11', 0),
						'validation_date'			=> array('UINT:11', 0),
						'install_time'				=> array('USINT', 0),
						'install_level'				=> array('TINT:1', 0),
						'revision_submitted'		=> array('BOOL', 0), // So we can hide the revision while we are creating it, false means someone is working on creating it (or did not finish creating it)
						'revision_queue_id'			=> array('UINT', 0),
						'revision_status'			=> array('TINT:2', 0),
						'revision_license'			=> array('VCHAR', ''),
						'revision_clr_options'		=> array('TEXT_UNI', 0),
						'revision_bbc_bbcode_usage'	=> array('MTEXT_UNI', ''),
						'revision_bbc_html_replace'	=> array('MTEXT_UNI', ''),
						'revision_bbc_help_line'	=> array('VCHAR:255', ''),
						'revision_bbc_demo'			=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'revision_id',
					'KEYS'			=> array(
						'c_id'			=> array('INDEX', 'contrib_id'),
						'r_time'		=> array('INDEX', 'revision_time'),
						'v_date'		=> array('INDEX', 'validation_date'),
						'r_subm'		=> array('INDEX', 'revision_submitted'),
						'r_qid'			=> array('INDEX', 'revision_queue_id'),
						'r_stat'		=> array('INDEX', 'revision_status'),
					),
				),
				$table_prefix . 'revisions_phpbb' => array(
					'COLUMNS'		=> array(
						'row_id'					=> array('UINT', NULL, 'auto_increment'),
						'revision_id'				=> array('UINT', 0),
						'contrib_id'				=> array('UINT', 0),
						'phpbb_version_branch'		=> array('TINT:1', 0),
						'phpbb_version_revision'	=> array('VCHAR', ''),
						'revision_validated'		=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'row_id',
					'KEYS'			=> array(
						'r_id'			=> array('INDEX', 'revision_id'),
						'c_id'			=> array('INDEX', 'contrib_id'),
						'pv_bra'		=> array('INDEX', 'phpbb_version_branch'),
						'pv_rev'		=> array('INDEX', 'phpbb_version_revision'),
						'r_val'			=> array('INDEX', 'revision_validated'),
					),
				),
				$table_prefix . 'tag_applied' => array(
					'COLUMNS'		=> array(
						'object_type'			=> array('UINT', 0),
						'object_id'				=> array('UINT', 0),
						'tag_id'				=> array('UINT', 0),
						'tag_value'				=> array('STEXT_UNI', '', 'true_sort'),
					),
					'PRIMARY_KEY'	=> array('object_type', 'object_id', 'tag_id'),
				),
				$table_prefix . 'tag_fields' => array(
					'COLUMNS'		=> array(
						'tag_id'				=> array('UINT', NULL, 'auto_increment'),
						'tag_type_id'			=> array('UINT', 0),
						'tag_field_name'		=> array('XSTEXT_UNI', '', 'true_sort'),
						'tag_clean_name'		=> array('XSTEXT_UNI', '', 'true_sort'),
						'tag_field_desc'		=> array('STEXT_UNI', '', 'true_sort'),
						'no_delete'				=> array('BOOL', 0), // A few tags we have to hard-code (like new status for a queue item)
					),
					'PRIMARY_KEY'	=> 'tag_id',
					'KEYS'			=> array(
						't_id'			=> array('INDEX', 'tag_type_id'),
					),
				),
				$table_prefix . 'tag_types' => array(
					'COLUMNS'		=> array(
						'tag_type_id'			=> array('UINT', NULL, 'auto_increment'),
						'tag_type_name'			=> array('STEXT_UNI', '', 'true_sort'),
					),
					'PRIMARY_KEY'	=> 'tag_type_id',
				),
				$table_prefix . 'topics' => array(
					'COLUMNS'		=> array(
						'topic_id'						=> array('UINT', NULL, 'auto_increment'),
						'parent_id'						=> array('UINT', 0),
						'topic_url'						=> array('VCHAR_CI', ''),
						'topic_type'					=> array('TINT:1', 0), // Post Type, Main TITANIA_ constants
						'topic_access'					=> array('TINT:1', 0), // Access level, TITANIA_ACCESS_ constants
						'topic_category'				=> array('UINT', 0), // Category for the topic. For the Tracker
						'topic_status'					=> array('UINT', 0), // Topic Status, use tags from the DB
						'topic_assigned'				=> array('VCHAR:255', ''), // Topic assigned status; u- for user, g- for group (followed by the id).  For the tracker
						'topic_time'					=> array('UINT:11', 0),
						'topic_sticky'					=> array('BOOL', 0),
						'topic_locked'					=> array('BOOL', 0),
						'topic_approved'				=> array('BOOL', 1),
						'topic_reported'				=> array('BOOL', 0), // True if any posts in the topic are reported
						'topic_views'					=> array('UINT', 0),
						'topic_posts'					=> array('VCHAR', ''), // Post count; separated by : between access levels ('10:9:8' = 10 team; 9 Mod Author; 8 Public)
						'topic_subject'					=> array('STEXT_UNI', ''),
						'topic_subject_clean'			=> array('STEXT_UNI', ''), // used for building the url
						'topic_first_post_id'			=> array('UINT', 0),
						'topic_first_post_user_id'		=> array('UINT', 0),
						'topic_first_post_username'		=> array('VCHAR_UNI', ''),
						'topic_first_post_user_colour'	=> array('VCHAR:6', ''),
						'topic_first_post_time'			=> array('UINT:11', 0),
						'topic_last_post_id'			=> array('UINT', 0),
						'topic_last_post_user_id'		=> array('UINT', 0),
						'topic_last_post_username'		=> array('VCHAR_UNI', ''),
						'topic_last_post_user_colour'	=> array('VCHAR:6', ''),
						'topic_last_post_time'			=> array('UINT:11', 0),
						'topic_last_post_subject'		=> array('STEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'topic_id',
					'KEYS'			=> array(
						'p_id'			=> array('INDEX', 'parent_id'),
						't_type'		=> array('INDEX', 'topic_type'),
						't_acc'			=> array('INDEX', 'topic_access'),
						't_cat'			=> array('INDEX', 'topic_category'),
						't_sta'			=> array('INDEX', 'topic_status'),
						't_ass'			=> array('INDEX', 'topic_assigned'),
						't_sti'			=> array('INDEX', 'topic_sticky'),
						't_appr'		=> array('INDEX', 'topic_approved'),
						't_repo'		=> array('INDEX', 'topic_reported'),
						't_time'		=> array('INDEX', 'topic_time'),
						't_lpt'			=> array('INDEX', 'topic_last_post_time'),
					),
				),
				$table_prefix . 'topics_posted' => array(
					'COLUMNS'		=> array(
						'user_id'			=> array('UINT', 0),
						'topic_id'			=> array('UINT', 0),
						'topic_posted'		=> array('TINT:1', 0),
					),
					'PRIMARY_KEY'	=> array('user_id', 'topic_id'),
				),
				$table_prefix . 'track' => array(
					'COLUMNS'		=> array(
						'track_type'			=> array('UINT', 0),
						'track_id'				=> array('UINT', 0),
						'track_user_id'			=> array('UINT', 0),
						'track_time'			=> array('UINT:11', 0),
					),
					'PRIMARY_KEY'	=> array('track_type', 'track_id', 'track_user_id'),
				),
				$table_prefix . 'watch' => array(
					'COLUMNS'		=> array(
						'watch_type'			=> array('TINT:1', 0),
						'watch_object_type'		=> array('UINT', 0),
						'watch_object_id'		=> array('UINT', 0),
						'watch_user_id'			=> array('UINT', 0),
						'watch_mark_time'		=> array('UINT:11', 0),
					),
					'PRIMARY_KEY'	=> array('watch_object_type', 'watch_object_id', 'watch_user_id', 'watch_type'),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('permission.add', array('u_titania_admin')),				// Can administrate titania

			array('permission.add', array('u_titania_mod_author_mod')),		// Can moderate author profiles
			array('permission.add', array('u_titania_mod_contrib_mod')),	// Can moderate all contrib items
			array('permission.add', array('u_titania_mod_rate_reset')),		// Can reset the rating on items
			array('permission.add', array('u_titania_mod_faq_mod')),		// Can moderate FAQ entries
			array('permission.add', array('u_titania_mod_post_mod')),		// Can moderate topics
			array('permission.add', array('u_titania_mod_style_clr')),		// Can edit style colorizeit settings

			array('permission.add', array('u_titania_contrib_submit')),		// Can submit contrib items
			array('permission.add', array('u_titania_rate')),				// Can rate items
			array('permission.add', array('u_titania_faq_create')),			// Can create FAQ entries
			array('permission.add', array('u_titania_faq_edit')),			// Can edit own FAQ entries
			array('permission.add', array('u_titania_faq_delete')),			// Can delete own FAQ entries
			array('permission.add', array('u_titania_topic')),				// Can create new topics
			array('permission.add', array('u_titania_bbcode')),				// Can post bbcode
			array('permission.add', array('u_titania_smilies')),			// Can post smilies
			array('permission.add', array('u_titania_post')),				// Can create new posts
			array('permission.add', array('u_titania_post_approved')),		// Posts are approved?
			array('permission.add', array('u_titania_post_edit_own')),		// Can edit own posts
			array('permission.add', array('u_titania_post_delete_own')),	// Can delete own posts
			array('permission.add', array('u_titania_post_mod_own')),		// Can moderate own contrib topics
			array('permission.add', array('u_titania_post_attach')),		// Can attach files to posts
			array('permission.add', array('u_titania_post_hard_delete')),	// Can hard delete

			array('permission.add', array('u_titania_mod_bbcode_queue_discussion')),
			array('permission.add', array('u_titania_mod_bbcode_queue')),
			array('permission.add', array('u_titania_mod_bbcode_validate')),
			array('permission.add', array('u_titania_mod_bbcode_moderate')),

			array('permission.add', array('u_titania_mod_bridge_queue_discussion')),
			array('permission.add', array('u_titania_mod_bridge_queue')),
			array('permission.add', array('u_titania_mod_bridge_validate')),
			array('permission.add', array('u_titania_mod_bridge_moderate')),

			array('permission.add', array('u_titania_mod_converter_queue_discussion')),
			array('permission.add', array('u_titania_mod_converter_queue')),
			array('permission.add', array('u_titania_mod_converter_validate')),
			array('permission.add', array('u_titania_mod_converter_moderate')),

			array('permission.add', array('u_titania_mod_modification_queue_discussion')),
			array('permission.add', array('u_titania_mod_modification_queue')),
			array('permission.add', array('u_titania_mod_modification_validate')),
			array('permission.add', array('u_titania_mod_modification_moderate')),

			array('permission.add', array('u_titania_mod_official_tool_moderate')),

			array('permission.add', array('u_titania_mod_style_queue_discussion')),
			array('permission.add', array('u_titania_mod_style_queue')),
			array('permission.add', array('u_titania_mod_style_validate')),
			array('permission.add', array('u_titania_mod_style_moderate')),

			array('permission.add', array('u_titania_mod_translation_queue_discussion')),
			array('permission.add', array('u_titania_mod_translation_queue')),
			array('permission.add', array('u_titania_mod_translation_validate')),
			array('permission.add', array('u_titania_mod_translation_moderate')),

			array('permission.add', array('u_titania_mod_extension_queue_discussion')),
			array('permission.add', array('u_titania_mod_extension_queue')),
			array('permission.add', array('u_titania_mod_extension_validate')),
			array('permission.add', array('u_titania_mod_extension_moderate')),

			array('permission.role_add', array('ROLE_TITANIA_MODIFICATION_TEAM', 'u_', '')),
			array('permission.role_add', array('ROLE_TITANIA_STYLE_TEAM', 'u_', '')),
			array('permission.role_add', array('ROLE_TITANIA_MODERATOR_TEAM', 'u_', '')),
			array('permission.role_add', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_', '')),

			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_admin')),					// Can administrate titania
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_mod_author_mod')),			// Can moderate author profiles
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_mod_contrib_mod')),			// Can moderate all contrib items
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_mod_rate_reset')),			// Can reset the rating on items
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_mod_faq_mod')),				// Can moderate FAQ entries
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_mod_post_mod')),				// Can moderate topics
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_mod_modification_queue')),		// Can see the modifications queue
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_mod_modification_validate')),	// Can validate modifications
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_mod_modification_moderate')),	// Can moderate modifications
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_mod_style_queue')),			// Can see the styles queue
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_mod_style_validate')),		// Can validate styles
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'u_titania_mod_style_moderate')),		// Can moderate styles

			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_admin')),				// Can administrate titania
			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_mod_author_mod')),		// Can moderate author profiles
			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_mod_contrib_mod')),		// Can moderate all contrib items
			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_mod_rate_reset')),		// Can reset the rating on items
			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_mod_faq_mod')),			// Can moderate FAQ entries
			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_mod_post_mod')),			// Can moderate topics
			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_mod_modification_queue')),			// Can see the modifications queue
			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_mod_modification_validate')),		// Can validate modifications
			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_mod_modification_moderate')),		// Can moderate modifications
			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_mod_style_queue')),		// Can see the styles queue
			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_mod_style_validate')),	// Can validate styles
			array('permission.permission_set', array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_titania_mod_style_moderate')),	// Can moderate styles

			array('permission.permission_set', array('ROLE_TITANIA_MODIFICATION_TEAM', 'u_titania_mod_author_mod')),		// Can moderate author profiles
			array('permission.permission_set', array('ROLE_TITANIA_MODIFICATION_TEAM', 'u_titania_mod_faq_mod')),			// Can moderate FAQ entries
			array('permission.permission_set', array('ROLE_TITANIA_MODIFICATION_TEAM', 'u_titania_mod_post_mod')),			// Can moderate topics
			array('permission.permission_set', array('ROLE_TITANIA_MODIFICATION_TEAM', 'u_titania_mod_modification_queue')),		// Can see the modifications queue
			array('permission.permission_set', array('ROLE_TITANIA_MODIFICATION_TEAM', 'u_titania_mod_modification_validate')),		// Can validate modifications
			array('permission.permission_set', array('ROLE_TITANIA_MODIFICATION_TEAM', 'u_titania_mod_modification_moderate')),		// Can moderate modifications

			array('permission.permission_set', array('ROLE_TITANIA_STYLE_TEAM', 'u_titania_mod_author_mod')),		// Can moderate author profiles
			array('permission.permission_set', array('ROLE_TITANIA_STYLE_TEAM', 'u_titania_mod_faq_mod')),			// Can moderate FAQ entries
			array('permission.permission_set', array('ROLE_TITANIA_STYLE_TEAM', 'u_titania_mod_post_mod')),			// Can moderate topics
			array('permission.permission_set', array('ROLE_TITANIA_STYLE_TEAM', 'u_titania_mod_style_queue')),		// Can see the styles queue
			array('permission.permission_set', array('ROLE_TITANIA_STYLE_TEAM', 'u_titania_mod_style_validate')),	// Can validate styles
			array('permission.permission_set', array('ROLE_TITANIA_STYLE_TEAM', 'u_titania_mod_style_moderate')),	// Can moderate styles

			array('permission.permission_set', array('ROLE_TITANIA_MODERATOR_TEAM', 'u_titania_mod_author_mod')),	// Can moderate author profiles
			array('permission.permission_set', array('ROLE_TITANIA_MODERATOR_TEAM', 'u_titania_mod_faq_mod')),		// Can moderate FAQ entries
			array('permission.permission_set', array('ROLE_TITANIA_MODERATOR_TEAM', 'u_titania_mod_post_mod')),		// Can moderate topics

			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_contrib_submit')),	// Can submit contrib items
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_rate')),				// Can rate items
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_faq_create')),		// Can create FAQ entries
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_faq_edit')),			// Can edit own FAQ entries
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_faq_delete')),		// Can delete own FAQ entries
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_topic')),				// Can create new topics
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_bbcode')),			// Can post bbcode
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_smilies')),			// Can post smilies
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_post')),				// Can create new posts
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_post_approved')),		// Posts are approved?
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_post_edit_own')),		// Can edit own posts
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_post_delete_own')),	// Can delete own posts
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_titania_post_attach')),		// Can attach files to posts

			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_contrib_submit')),		// Can submit contrib items
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_rate')),					// Can rate items
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_faq_create')),			// Can create FAQ entries
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_faq_edit')),				// Can edit own FAQ entries
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_faq_delete')),			// Can delete own FAQ entries
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_topic')),					// Can create new topics
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_bbcode')),				// Can post bbcode
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_smilies')),				// Can post smilies
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_post')),					// Can create new posts
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_post_approved')),			// Posts are approved?
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_post_edit_own')),			// Can edit own posts
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_post_delete_own')),		// Can delete own posts
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_titania_post_attach')),			// Can attach files to posts

			array('custom', array(
					array($this, 'add_tags'),
				),
			),
			array('custom', array(
					array($this, 'add_categories'),
				),
			),

			array('config.add', array('titania_last_cleanup', time(), true)),
			array('config.add', array('titania_version', '1.1.0', false)),
		);
	}

	public function revert_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'drop_tables'	=> array(
				$table_prefix . 'attachments',
				$table_prefix . 'attention',
				$table_prefix . 'automod_queue',
				$table_prefix . 'authors',
				$table_prefix . 'categories',
				$table_prefix . 'contribs',
				$table_prefix . 'contrib_coauthors',
				$table_prefix . 'contrib_faq',
				$table_prefix . 'contrib_in_categories',
				$table_prefix . 'posts',
				$table_prefix . 'queue',
				$table_prefix . 'ratings',
				$table_prefix . 'revisions',
				$table_prefix . 'revisions_phpbb',
				$table_prefix . 'tag_applied',
				$table_prefix . 'tag_fields',
				$table_prefix . 'tag_types',
				$table_prefix . 'topics',
				$table_prefix . 'topics_posted',
				$table_prefix . 'track',
				$table_prefix . 'watch',
			),
		);
	}

	public function add_tags()
	{
		$table_prefix = $this->get_titania_table_prefix();

		$tag_types = array(
			array(
				'tag_type_id'	=> ext::TITANIA_QUEUE,
				'tag_type_name'	=> 'QUEUE_TAGS',
			)
		);

		$this->db->sql_multi_insert($table_prefix . 'tag_types', $tag_types);

		$tags = array(
			array(
				'tag_id'			=> 1,
				'tag_type_id'		=> ext::TITANIA_QUEUE,
				'tag_field_name'	=> 'QUEUE_NEW',
				'tag_clean_name'	=> 'new',
				'no_delete'			=> true,
			),
			// Leave space for others if we need to hard-code any
			array(
				'tag_id'			=> 15,
				'tag_type_id'		=> ext::TITANIA_QUEUE,
				'tag_field_name'	=> 'QUEUE_ATTENTION',
				'tag_clean_name'	=> 'attention',
				'no_delete'			=> false,
			),
			array(
				'tag_id'			=> 16,
				'tag_type_id'		=> ext::TITANIA_QUEUE,
				'tag_field_name'	=> 'QUEUE_REPACK',
				'tag_clean_name'	=> 'repack',
				'no_delete'			=> false,
			),
			array(
				'tag_id'			=> 17,
				'tag_type_id'		=> ext::TITANIA_QUEUE,
				'tag_field_name'	=> 'QUEUE_VALIDATING',
				'tag_clean_name'	=> 'validating',
				'no_delete'			=> false,
			),
			array(
				'tag_id'			=> 18,
				'tag_type_id'		=> ext::TITANIA_QUEUE,
				'tag_field_name'	=> 'QUEUE_TESTING',
				'tag_clean_name'	=> 'testing',
				'no_delete'			=> false,
			),
			array(
				'tag_id'			=> 19,
				'tag_type_id'		=> ext::TITANIA_QUEUE,
				'tag_field_name'	=> 'QUEUE_APPROVE',
				'tag_clean_name'	=> 'approve',
				'no_delete'			=> false,
			),
			array(
				'tag_id'			=> 20,
				'tag_type_id'		=> ext::TITANIA_QUEUE,
				'tag_field_name'	=> 'QUEUE_DENY',
				'tag_clean_name'	=> 'deny',
				'no_delete'			=> false,
			),
		);

		$this->db->sql_multi_insert($table_prefix . 'tag_fields', $tags);
	}

	public function add_categories()
	{
		$table_prefix = $this->get_titania_table_prefix();

		$categories = array(
			array(
				'category_id'	=> 1,
				'parent_id'		=> 0,
				'left_id'		=> 1,
				'right_id'		=> 20,
				'category_type'	=> 0,
				'category_name'	=> 'CAT_MODIFICATIONS',
				'category_name_clean'	=> 'modifications',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 2,
				'parent_id'		=> 0,
				'left_id'		=> 21,
				'right_id'		=> 32,
				'category_type'	=> 0,
				'category_name'	=> 'CAT_STYLES',
				'category_name_clean'	=> 'styles',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 3,
				'parent_id'		=> 1,
				'left_id'		=> 2,
				'right_id'		=> 3,
				'category_type'	=> 1,
				'category_name'	=> 'CAT_COSMETIC',
				'category_name_clean'	=> 'cosmetic',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 4,
				'parent_id'		=> 1,
				'left_id'		=> 4,
				'right_id'		=> 5,
				'category_type'	=> 1,
				'category_name'	=> 'CAT_TOOLS',
				'category_name_clean'	=> 'tools',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 5,
				'parent_id'		=> 1,
				'left_id'		=> 6,
				'right_id'		=> 7,
				'category_type'	=> 1,
				'category_name'	=> 'CAT_SECURITY',
				'category_name_clean'	=> 'security',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 6,
				'parent_id'		=> 1,
				'left_id'		=> 8,
				'right_id'		=> 9,
				'category_type'	=> 1,
				'category_name'	=> 'CAT_COMMUNICATION',
				'category_name_clean'	=> 'communication',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 7,
				'parent_id'		=> 1,
				'left_id'		=> 10,
				'right_id'		=> 11,
				'category_type'	=> 1,
				'category_name'	=> 'CAT_PROFILE_UCP',
				'category_name_clean'	=> 'profile',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 8,
				'parent_id'		=> 1,
				'left_id'		=> 12,
				'right_id'		=> 13,
				'category_type'	=> 1,
				'category_name'	=> 'CAT_ADDONS',
				'category_name_clean'	=> 'addons',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 9,
				'parent_id'		=> 1,
				'left_id'		=> 14,
				'right_id'		=> 15,
				'category_type'	=> 1,
				'category_name'	=> 'CAT_ANTI_SPAM',
				'category_name_clean'	=> 'anti-spam',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 10,
				'parent_id'		=> 1,
				'left_id'		=> 16,
				'right_id'		=> 17,
				'category_type'	=> 1,
				'category_name'	=> 'CAT_ENTERTAINMENT',
				'category_name_clean'	=> 'entertainment',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 11,
				'parent_id'		=> 1,
				'left_id'		=> 18,
				'right_id'		=> 19,
				'category_type'	=> 1,
				'category_name'	=> 'CAT_MISC',
				'category_name_clean'	=> 'misc',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 12,
				'parent_id'		=> 2,
				'left_id'		=> 22,
				'right_id'		=> 23,
				'category_type'	=> 2,
				'category_name'	=> 'CAT_BOARD_STYLES',
				'category_name_clean'	=> 'board_styles',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 13,
				'parent_id'		=> 2,
				'left_id'		=> 24,
				'right_id'		=> 25,
				'category_type'	=> 2,
				'category_name'	=> 'CAT_SMILIES',
				'category_name_clean'	=> 'smilies',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 14,
				'parent_id'		=> 2,
				'left_id'		=> 26,
				'right_id'		=> 27,
				'category_type'	=> 2,
				'category_name'	=> 'CAT_AVATARS',
				'category_name_clean'	=> 'avatars',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 15,
				'parent_id'		=> 2,
				'left_id'		=> 28,
				'right_id'		=> 29,
				'category_type'	=> 2,
				'category_name'	=> 'CAT_RANKS',
				'category_name_clean'	=> 'ranks',
				'category_desc'			=> '',
			),
			array(
				'category_id'	=> 16,
				'parent_id'		=> 2,
				'left_id'		=> 30,
				'right_id'		=> 31,
				'category_type'	=> 2,
				'category_name'	=> 'CAT_MISC',
				'category_name_clean'	=> 'misc',
				'category_desc'			=> '',
			),
		);

		$this->db->sql_multi_insert($table_prefix . 'categories', $categories);
	}
}

