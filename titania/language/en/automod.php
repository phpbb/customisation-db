<?php
/**
*
* captcha_qa [English]
*
* @package language
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine


$lang = array_merge($lang, array(
	'ADDITIONAL_CHANGES'					=> 'Available Changes',
	'AM_MANUAL_INSTRUCTIONS'				=> 'AutoMOD is sending a compressed file to your computer.  Because of the AutoMOD configuration, files cannot be written to your site automatically.  You will need to extract the file and upload the files to your server manually, using an FTP client or similar method.  If you did not receive this file automatically, click %shere%s.',
	'AM_MOD_ALREADY_INSTALLED'				=> 'AutoMOD has detected this MOD is already installed and cannot proceed.',
	'APPLY_TEMPLATESET'						=> 'to this template',
	'APPLY_THESE_CHANGES'					=> 'Apply these changes',
	'AUTHOR_EMAIL'							=> 'Author Email',
	'AUTHOR_INFORMATION'					=> 'Author Information',
	'AUTHOR_NAME'							=> 'Author Name',
	'AUTHOR_NOTES'							=> 'Author Notes',
	'AUTHOR_URL'							=> 'Author URL',
	'AUTOMOD'								=> 'AutoMOD',
	'AUTOMOD_CANNOT_INSTALL_OLD_VERSION'	=> 'The version of AutoMOD you are trying to install has already been installed.  Please delete this install/ directory.',
	'AUTOMOD_INSTALLATION'					=> 'AutoMOD Installation',
	'AUTOMOD_INSTALLATION_EXPLAIN'			=> 'Welcome to the AutoMOD Installation.  You will need your FTP details if AutoMOD detects that is the best way to write files.  The requirements test results are below.',
	'AUTOMOD_UNKNOWN_VERSION'				=> 'AutoMOD was not able to update because it could not determine the version currently installed.  The version listed for your installation is %s.',
	'AUTOMOD_VERSION'						=> 'AutoMOD Version',

	'CAT_INSTALL_AUTOMOD'					=> 'AutoMOD',
	'CHANGES'								=> 'Changes',
	'CHANGE_DATE'							=> 'Release Date',
	'CHANGE_VERSION'						=> 'Version Number',
	'CHECK_AGAIN'							=> 'Check again',
	'COMMENT'								=> 'Comment',
	'CREATE_TABLE'							=> 'Database Alterations',
	'CREATE_TABLE_EXPLAIN'					=> 'AutoMOD has successfully made its database alterations, including a permission which has been assigned to the “Full Administrator” role.',

	'DELETE'								=> 'Delete',
	'DELETE_CONFIRM'						=> 'Are you sure you want to delete this MOD?',
	'DELETE_ERROR'							=> 'There was an error deleting the selected MOD.',
	'DELETE_SUCCESS'						=> 'MOD has been successfully deleted.',
	'DEPENDENCY_INSTRUCTIONS'				=> 'The MOD you are trying to install depends on another MOD.  AutoMOD cannot detect if this MOD has been installed.  Please verify that you have installed <strong><a href="%1$s">%2$s</a></strong> before installing your MOD.',
	'DESCRIPTION'							=> 'Description',
	'DETAILS'								=> 'Details',
	'DIR_PERMS'								=> 'Directory Permissions',
	'DIR_PERMS_EXPLAIN'						=> 'Some systems require directories to have certain permissions to work properly.  Normally the default 0755 is correct.  This setting has no impact on Windows systems.',
	'DIY_INSTRUCTIONS'						=> 'Do It Yourself Instructions',

	'EDITED_ROOT_CREATE_FAIL'				=> 'AutoMOD was unable to create the directory where the edited files will be stored.',
	'ERROR'									=> 'Error',

	'FILESYSTEM_NOT_WRITABLE'				=> 'AutoMOD has determined the filesystem is not writable, so the direct write method cannot be used.',
	'FILE_EDITS'							=> 'File edits',
	'FILE_EMPTY'							=> 'File empty',
	'FILE_MISSING'							=> 'Cannot locate file',
	'FILE_PERMS'							=> 'File Permissions',
	'FILE_PERMS_EXPLAIN'					=> 'Some systems require files to have certain permissions to work properly.  Normally the default 0644 is correct.  This setting has no impact on Windows systems.',
	'FILE_TYPE'								=> 'Compressed File Type',
	'FILE_TYPE_EXPLAIN'						=> 'This is only valid with the “Compressed File Download” write method',
	'FIND'									=> 'Find',
	'FIND_MISSING'							=> 'The Find specified by the MOD could not be found',
	'FORCE_CONFIRM'							=> 'The Force Install feature means the MOD is not fully installed.  You will need to make some manual fixes to your board to finish installation.  Continue?',
	'FORCE_INSTALL'							=> 'Force Install',
	'FORCE_UNINSTALL'						=> 'Force Uninstall',
	'FTP_INFORMATION'						=> 'FTP Information',
	'FTP_METHOD_ERROR'						=> 'There is no FTP method found, please check under autoMOD configuration if there is set a correct FTP method.',
	'FTP_METHOD_EXPLAIN'					=> 'If you experience problems with the default "FTP", you may try "Simple Socket" as an alternate way to connect to the FTP server.',
	'FTP_METHOD_FSOCK'						=> 'Simple Socket',
	'FTP_METHOD_FTP'						=> 'FTP',
	'FTP_NOT_USABLE'						=> 'The FTP function can\'t be used as this has been disabled by your hosting.',

	'GO_PHP_INSTALLER'						=> 'The MOD requires an external installer to finish installation.  Click here to continue to that step.',

	'INHERIT_NO_CHANGE'						=> 'No changes can be made to this file because the template %1$s depends on %2$s.',
	'INLINE_EDIT_ERROR'						=> 'Error, an inline edit in the MODX install file is missing all the required elements',
	'INLINE_FIND_MISSING'					=> 'The In-Line Find specified by the MOD could not be found.',
	'INSTALLATION_SUCCESSFUL'				=> 'AutoMOD installed successfully.  You can now manage phpBB MODifications through the AutoMOD tab in the Administration Control Panel.',
	'INSTALLED'								=> 'MOD installed',
	'INSTALLED_EXPLAIN'						=> 'Your MOD has been installed! Here you can view some of the results from the installation. Please note any errors, and seek support at <a href="http://www.phpbb.com">phpBB.com</a>',
	'INSTALLED_MODS'						=> 'Installed MODs',
	'INSTALL_AUTOMOD'						=> 'AutoMOD Installation',
	'INSTALL_AUTOMOD_CONFIRM'				=> 'Are you sure you want to install AutoMOD?',
	'INSTALL_ERROR'							=> 'One or more install actions failed. Please review the actions below, make any adjustments and retry. You may continue with the installation even though some of the actions failed. <strong>This is not recommended and may cause your board to not function correctly.</strong>',
	'INSTALL_FORCED'						=> 'You forced the installation of this MOD even though there were errors installing the MOD. Your board may be broken. Please note the actions that failed below and correct them.',
	'INSTALL_MOD'							=> 'Install this MOD',
	'INSTALL_TIME'							=> 'Installation time',
	'INVALID_MOD_INSTRUCTION'				=> 'This MOD has an invalid instruction, or an in-line find operation failed.',
	'INVALID_MOD_NO_ACTION'					=> 'The MOD is missing an action matching the find ‘%s’',
	'INVALID_MOD_NO_FIND'					=> 'The MOD is missing a find matching the action ‘%s’',

	'LANGUAGE_NAME'							=> 'Language Name',

	'MANUAL_COPY'							=> 'Copy not attempted',
	'MODS_CONFIG_EXPLAIN'					=> 'You can select how AutoMOD adjusts your files here.  The most basic method is Compressed File Download.  The others require additional permissions on the server.',
	'MODS_COPY_FAILURE'						=> 'The file %s could not be copied into place.  Please check your permissions or use an alternate write method.',
	'MODS_EXPLAIN'							=> 'Here you can manage the available MODs on your board. AutoMODs allows you to customize your board by automatically installing modifications produced by the phpBB community. For further information on MODs and AutoMOD please visit the <a href="http://www.phpbb.com/mods">phpBB website</a>.  To add a MOD to this list, use the form at the bottom of this page.  Alternatively, you may unzip it and upload the files to the /store/mods/ directory on your server.',
	'MODS_FTP_CONNECT_FAILURE'				=> 'AutoMOD was unable to connect to your FTP server.  The error was %s',
	'MODS_FTP_FAILURE'						=> 'AutoMOD could not FTP the file %s into place',
	'MODS_MKDIR_FAILED'						=> 'The directory %s could not be created',
	'MODS_SETUP_INCOMPLETE'					=> 'A problem was found with your configuration, and AutoMOD cannot operate.  This should only occur when settings (e.g. FTP username) have changed, and can be corrected in the AutoMOD configuration page.',
	'MOD_CONFIG'							=> 'AutoMOD Configuration',
	'MOD_CONFIG_UPDATED'					=> 'AutoMOD configuration has been updated.',
	'MOD_DETAILS'							=> 'MOD Details',
	'MOD_DETAILS_EXPLAIN'					=> 'Here you can view all known information about the MOD you selected.',
	'MOD_MANAGER'							=> 'AutoMOD',
	'MOD_NAME'								=> 'MOD Name',
	'MOD_OPEN_FILE_FAIL'					=> 'AutoMOD was unable to open %s.',
	'MOD_UPLOAD'							=> 'Upload MOD',
	'MOD_UPLOAD_EXPLAIN'					=> 'Here you can upload a zipped MOD package containing the necessary MODX files to perform installation.  AutoMOD will then attempt to unzip the file and have it ready for installation.',
	'MOD_UPLOAD_INIT_FAIL'					=> 'There was an error initialising the MOD upload process.',
	'MOD_UPLOAD_SUCCESS'					=> 'MOD uploaded and prepared for installation.',

	'NAME'									=> 'Name',
	'NEW_FILES'								=> 'New Files',
	'NO_ATTEMPT'							=> 'Not Attempted',
	'NO_INSTALLED_MODS'						=> 'No installed MODs detected',
	'NO_MOD'								=> 'The selected MOD could not be found.',
	'NO_UNINSTALLED_MODS'					=> 'No uninstalled MODs detected',
	'NO_UPLOAD_FILE'						=> 'No file specified.',

	'ORIGINAL'								=> 'Original',

	'PATH'									=> 'Path',
	'PREVIEW_CHANGES'						=> 'Preview Changes',
	'PREVIEW_CHANGES_EXPLAIN'				=> 'Displays the changes to be performed before executing them.',
	'PRE_INSTALL'							=> 'Preparing to Install',
	'PRE_INSTALL_EXPLAIN'					=> 'Here you can preview all the modifications to be made to your board, before they are carried out. <strong>WARNING!</strong>, once accepted, your phpBB base files will be edited and database alterations may occur. However, if the install is unsuccessful, assuming you can access AutoMOD, you will be given the option to restore to this point.',
	'PRE_UNINSTALL'							=> 'Preparing to Uninstall',
	'PRE_UNINSTALL_EXPLAIN'					=> 'Here you can preview all the modifications to be made to your board, in order to uninstall the MOD. <strong>WARNING!</strong>, once accepted, your phpBB base files will be edited and database alterations may occur. Also, this process uses reversing techniques that may not be 100% accurate. However, if the uninstall is unsuccessful, assuming you can access AutoMOD, you will be given the option to restore to this point.',

	'REMOVING_FILES'						=> 'Files to be removed',
	'RETRY'									=> 'Retry',
	'RETURN_MODS'							=> 'Return to AutoMOD',
	'REVERSE'								=> 'Reverse',
	'ROOT_IS_READABLE'						=> 'The phpBB root directory is readable.',
	'ROOT_NOT_READABLE'						=> 'AutoMOD was not able to open phpBB\'s index.php for reading.  This probably means that permissions are too restrictive on your phpBB root directory, which will prevent AutoMOD from working.  Please adjust your permissions and try the check again.',

	'SOURCE'								=> 'Source',
	'SQL_QUERIES'							=> 'SQL Queries',
	'STATUS'								=> 'Status',
	'STORE_IS_WRITABLE'						=> 'The store/ directory is writable.',
	'STORE_NOT_WRITABLE'					=> 'The store/ directory is not writable.',
	'STORE_NOT_WRITABLE_INST'				=> 'AutoMOD installation has detected that the store/ directory is not writable.  This is required for AutoMOD to work properly.  Please adjust your permissions and try again.',
	'STYLE_NAME'							=> 'Style name',
	'SUCCESS'								=> 'Success',

	'TARGET'								=> 'Target',

	'UNINSTALL'								=> 'Uninstall',
	'UNINSTALLED'							=> 'MOD uninstalled',
	'UNINSTALLED_EXPLAIN'					=> 'Your MOD has been uninstalled! Here you can view some of the results from the uninstallation. Please note any errors, and seek support at <a href="http://www.phpbb.com">phpBB.com</a>.',
	'UNINSTALLED_MODS'						=> 'Uninstalled MODs',
	'UNINSTALL_AUTOMOD'						=> 'AutoMOD Uninstallation',
	'UNINSTALL_AUTOMOD_CONFIRM'				=> 'Are you sure you wish to uninstall AutoMOD?  This will NOT remove any MODs which have been installed with AutoMOD.',
	'UNKNOWN_MOD_AUTHOR-NOTES'				=> 'No Author Notes were specified.',
	'UNKNOWN_MOD_COMMENT'					=> '',
	'UNKNOWN_MOD_DESCRIPTION'				=> '',
	'UNKNOWN_MOD_DIY-INSTRUCTIONS'			=> '',
	'UNKNOWN_MOD_INLINE-COMMENT'			=> '',
	'UNKNOWN_QUERY_REVERSE'					=> 'Unknown reverse query',
	'UNRECOGNISED_COMMAND'					=> 'Error, unrecognised command %s',
	'UPDATE_AUTOMOD'						=> 'Update AutoMOD',
	'UPDATE_AUTOMOD_CONFIRM'				=> 'Please confirm you want to update AutoMOD.',
	'UPLOAD'								=> 'Upload',

	'VERSION'								=> 'Version',

	'WRITE_DIRECT_FAIL'						=> 'AutoMOD could not copy the file %s into place using the direct method.  Please use another write method and try again.',
	'WRITE_DIRECT_TOO_SHORT'				=> 'AutoMOD was unable to finish writing the file %s.  This can often be solved with the Retry button.  If this does not work, try another write method.',
	'WRITE_MANUAL_FAIL'						=> 'AutoMOD could not add the file %s to a compressed archive.  Please try another write method.',
	'WRITE_METHOD'							=> 'Write Method',
	'WRITE_METHOD_DIRECT'					=> 'Direct',
	'WRITE_METHOD_EXPLAIN'					=> 'You can set a preferred method to write files.  The most compatible option is “Compressed File Download”.',
	'WRITE_METHOD_FTP'						=> 'FTP',
	'WRITE_METHOD_MANUAL'					=> 'Compressed File Download',

	'after add'								=> 'Add After',

	'before add'							=> 'Add Before',

	'find'									=> 'Find',

	'in-line-after-add'						=> 'In-Line After, Add',
	'in-line-before-add'					=> 'In-Line Before, Add',
	'in-line-edit'							=> 'In-Line Find',
	'in-line-operation'						=> 'In-Line Increment',
	'in-line-replace'						=> 'In-Line Replace',
	'in-line-replace-with'					=> 'In-Line Replace',

	'operation'								=> 'Increment',

	'replace'								=> 'Replace With',
	'replace with'							=> 'Replace With',
));
