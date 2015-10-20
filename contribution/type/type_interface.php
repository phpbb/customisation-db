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

namespace phpbb\titania\contribution\type;

use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\titania\attachment\attachment;

interface type_interface
{
	/**
	 * Check if the user is authorized for the given action.
	 *
	 * @param string $action E.g. view, test, validate, etc.
	 * @return bool
	 */
	public function acl_get($action);

	/**
	 * Perform checks on uploaded revision for this contribution type.
	 *
	 * @param attachment $attachment
	 * @return array Returns array containing any errors found.
	 */
	public function upload_check(attachment $attachment);

	/**
	 * Fix package name to ensure naming convention is followed.
	 *
	 * @param \titania_contribution $contrib Contribution object
	 * @param \titania_revision $revision Revision object
	 * @param attachment $attachment Attachment object
	 * @param string $root_dir Package root directory
	 *
	 * @return mixed New root dir name
	 */
	public function fix_package_name(\titania_contribution $contrib, \titania_revision $revision, attachment $attachment, $root_dir = null);

	/**
	 * Run custom action after revision has been denied.
	 *
	 * @param \titania_contribution $contrib
	 * @param \titania_queue $queue
	 * @param request_interface $request
	 *
	 * @return null
	 */
	public function deny(\titania_contribution $contrib, \titania_queue $queue, request_interface $request);

	/**
	 * Run custom action after revision has been approved.
	 *
	 * @param \titania_contribution $contrib
	 * @param \titania_queue $queue
	 * @param request_interface $request
	 *
	 * @return null
	 */
	public function approve(\titania_contribution $contrib, \titania_queue $queue, request_interface $request);

	/**
	 * Install demo for the contribution type.
	 *
	 * @param \titania_contribution $contrib
	 * @param \titania_revision $revision
	 * @return string Demo url
	 */
	public function install_demo(\titania_contribution $contrib, \titania_revision $revision);

	/**
	 * Display additional options when approving/denying a revision.
	 *
	 * @param string $action				Either approve or deny
	 * @param request_interface $request
	 * @param template $template
	 * @return null
	 */
	public function display_validation_options($action, request_interface $request, template $template);

	/**
	 * Validate contribution fields.
	 *
	 * @param array $fields
	 * @return array Returns array containing any errors found.
	 */
	public function validate_contrib_fields(array $fields);

	/**
	 * Validate revision fields.
	 *
	 * @param array $fields
	 * @return array Returns array containing any errors found.
	 */
	public function validate_revision_fields(array $fields);

	/**
	 * Get allowed branches.
	 *
	 * @param bool $name_only			Only return branch names.
	 * @param bool $check_allow_upload	Only include branch if it allows uploads.
	 *
	 * @return array
	 */
	public function get_allowed_branches($name_only = false, $check_allow_upload = true);

	/**
	 * Get instance of type demo class.
	 *
	 * @return mixed
	 */
	public function get_demo();

	/**
	 * Get instance of type prevalidator class.
	 *
	 * @return mixed
	 */
	public function get_prevalidator();
}
