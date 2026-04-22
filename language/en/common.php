<?php
/**
*
* @package Customisation DB
* @copyright (c) 2025 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = [];
}

$lang = array_merge($lang, [

    // Customisation types
    'CUSTDB_CONTRIBUTION_TYPE'          => 'Contribution Type',
    'CUSTDB_TYPE_EXTENSIONS'            => 'Extensions',
    'CUSTDB_TYPE_STYLES'                => 'Styles',
    'CUSTDB_TYPE_TRANSLATIONS'          => 'Translations',
    'CUSTDB_TYPE_BBCODES'               => 'Custom bbCodes',
    'CUSTDB_TYPE_TOOLS'                 => 'Tools',
    'CUSTDB_TYPE_ARCHIVE'               => 'Archive',
    
    'CUSTDB_INDEX'                      => 'Customisation DB Index',
    'CUSTDB_NOT_ENABLED'                => 'Customisation DB not enabled',
    'CUSTDB_NEW'                        => 'New Customisation',
    'CUSTDB_SEARCH'                     => 'Search',
    'CUSTDB_TYPE_TO_SEARCH'             => 'Type to search...',

    // Sort by...
    'CUSTDB_SORT_BY'                    => 'Sort by...',
    'CUSTDB_SORT_BY_DATE'               => 'Sort by date',
    'CUSTDB_SORT_BY_NAME'               => 'Sort by name',

    // Status
    'CUSTDB_STATUS'                             => 'Status',
    'CUSTDB_QUEUE_STATUS'                       => 'Queue Status',
    'CUSTDB_STATUS_UNVALIDATED'                 => 'Unvalidated',
    'CUSTDB_STATUS_APPROVED'                    => 'Approved',
    'CUSTDB_STATUS_DENIED'                      => 'Denied',

    // Internal status
    'CUSTDB_INTERNAL_STATUS_UNVALIDATED'                => 'Unvalidated',
    'CUSTDB_INTERNAL_STATUS_AWAITING_AI_VALIDATION'     => 'Awaiting AI Validation',
    'CUSTDB_INTERNAL_STATUS_COMPLETED_AI_VALIDATION'    => 'Completed AI Validation',
    'CUSTDB_INTERNAL_STATUS_AWAITING_TESTING'           => 'Awaiting Testing',
    'CUSTDB_INTERNAL_STATUS_COMPLETED_TESTING'          => 'Completed Testing',
    'CUSTDB_INTERNAL_STATUS_DENIED'                     => 'Denied',
    'CUSTDB_INTERNAL_STATUS_APPROVED'                   => 'Approved',

    // Add revision
    'CUSTDB_ADD_REVISION'                       => 'Add Revision',

    // Add contribution
    'CUSTDB_ADD_CONTRIBUTION'                   => 'Add Contribution',
    'CUSTDB_CONTRIBUTION_NAME'                  => 'Contribution Name',
    'CUSTDB_ENTER_CONTRIBUTION_NAME'            => 'Enter contribution name...',
    'CUSTDB_REVISION_NAME'                      => 'Revision name',
    'CUSTDB_ENTER_REVISION_NAME'                => 'Enter revision name...',
    'CUSTDB_AUTHORS'                            => 'Authors',
    'CUSTDB_ENTER_AUTHORS'                      => 'Enter authors...',
    'CUSTDB_VERSION_NUMBER'                     => 'Version Number',
    'CUSTDB_ENTER_VERSION_NUMBER'               => 'e.g., 1.0.0',
    'CUSTDB_PHPBB_VERSION'                      => 'phpBB Version',
    'CUSTDB_CONTRIBUTION_TYPE'                  => 'Contribution Type',
    'CUSTDB_ENTER_CONTRIBUTION_TYPE'            => 'Enter contribution type...',
    'CUSTDB_CONTRIBUTION_DESCRIPTION'           => 'Contribution Description',
    'CUSTDB_REVISION_DESCRIPTION'               => 'Revision Description',
    'CUSTDB_ENTER_DESCRIPTION'                  => 'Enter description...',
    'CUSTDB_UPLOAD_CONTRIBUTION'                => 'Upload Contribution',
    'CUSTDB_UPLOAD_SCREENSHOTS'                 => 'Upload Screenshots',
    'CUSTDB_DEMO_LINK'                          => 'Demo Link',
    'CUSTDB_ENTER_DEMO_LINK'                    => 'Enter demo link...',
    'CUSTDB_CONTRIBUTION_ADDED_SUCCESSFULLY'    => 'Contribution has been successfully submitted',

    // View contribution
    'CUSTDB_VIEW_CONTRIBUTION'                  => 'View Contribution',
    'CUSTDB_EDIT_CONTRIBUTION'                  => 'Edit Contribution',
    'CUSTDB_NEW_REVISION'                       => 'New Revision',
    'CUSTDB_NO_IMAGE'                           => 'No image',
    'CUSTDB_VALIDATION_COMMENT'                 => 'Validation Comment',
    'CUSTDB_VALIDATION_OPTIONAL_COMMENT'        => 'Enter an optional validation comment...',

    // View revision
    'CUSTDB_VIEW_REVISION'                      => 'View Revision',
    'CUSTDB_BACK_TO_CONTRIBUTION'               => 'Back to Contribution',
    'CUSTDB_ATTACHMENT'                         => 'Attachment',
    'CUSTDB_DOWNLOAD_ATTACHMENT'                => 'Download attachment',
    'CUSTDB_REVISION_STATUS'                    => 'Revision Status',
    'CUSTDB_REVISION_DATE'                      => 'Submission Date',

    // Revision list
    'CUSTDB_REVISIONS'                          => 'Revisions',

    // Posts
    'CUSTDB_CONTRIBUTION_APPROVED'              => "%1\$s has been approved.\n\n[b]Download:[/b]\n[url=%2\$s]%1\$s[/url]\n\n[b]Validation Notes:[/b]\n%3\$s",
    'CUSTDB_STATUS_MANUAL_CHANGE'               => '[b]Status manually changed to %s[/b]',
    'CUSTDB_STATUS_AUTOMATIC_CHANGE'            => '[b]Status automatically changed from %s to %s[/b]',

    // Errors
    'CUSTDB_NO_ACCESS'                          => 'You do not have permission to access this page.',
    'CUSTDB_MISSING_REQUIRED_FIELDS'            => 'Go back to the previous page and ensure all of the required fields have been filled.',
    'CUSTDB_FILE_UPLOAD_FAILED'                 => 'There was an error uploading the contribution file.',
    'CUSTDB_SCREENSHOT_UPLOAD_FAILED'           => 'There was an error uploading the screenshot file(s).',
    'CUSTDB_CONTRIBUTION_NOT_FOUND'             => 'This contribution could not be found.',
    'CUSTDB_FILE_NOT_FOUND'                     => 'The requested file could not be found.',
]);