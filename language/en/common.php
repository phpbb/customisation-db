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
    'CUSTDB_STATUS_UNVALIDATED'                 => 'Unvalidated',
    'CUSTDB_STATUS_APPROVED'                    => 'Approved',
    'CUSTDB_STATUS_DENIED'                      => 'Denied',

    // Add revision
    'CUSTDB_ADD_REVISION'                       => 'Add Revision',

    // Add contribution
    'CUSTDB_ADD_CONTRIBUTION'                   => 'Add Contribution',
    'CUSTDB_CONTRIBUTION_NAME'                  => 'Contribution Name',
    'CUSTDB_ENTER_CONTRIBUTION_NAME'            => 'Enter contribution name...',
    'CUSTDB_AUTHORS'                            => 'Authors',
    'CUSTDB_ENTER_AUTHORS'                      => 'Enter authors...',
    'CUSTDB_VERSION_NUMBER'                     => 'Version Number',
    'CUSTDB_ENTER_VERSION_NUMBER'               => 'e.g., 1.0.0',
    'CUSTDB_PHPBB_VERSION'                      => 'phpBB Version',
    'CUSTDB_CONTRIBUTION_TYPE'                  => 'Contribution Type',
    'CUSTDB_ENTER_CONTRIBUTION_TYPE'            => 'Enter contribution type...',
    'CUSTDB_DESCRIPTION'                        => 'Description',
    'CUSTDB_ENTER_DESCRIPTION'                  => 'Enter description...',
    'CUSTDB_UPLOAD_CONTRIBUTION'                => 'Upload Contribution',
    'CUSTDB_UPLOAD_SCREENSHOTS'                 => 'Upload Screenshots',
    'CUSTDB_DEMO_LINK'                          => 'Demo Link',
    'CUSTDB_ENTER_DEMO_LINK'                    => 'Enter demo link...',
    'CUSTDB_CONTRIBUTION_ADDED_SUCCESSFULLY'    => 'Contribution has been successfully submitted',

    // View contribution
    'CUSTDB_EDIT_CONTRIBUTION'                  => 'Edit Contribution',
    'CUSTDB_NEW_REVISION'                       => 'New Revision',

    // Errors
    'CUSTDB_MISSING_REQUIRED_FIELDS'            => 'Go back to the previous page and ensure all of the required fields have been filled.',
    'CUSTDB_FILE_UPLOAD_FAILED'                 => 'There was an error uploading the contribution file.',
    'CUSTDB_SCREENSHOT_UPLOAD_FAILED'           => 'There was an error uploading the screenshot file(s).',
    'CUSTDB_CONTRIBUTION_NOT_FOUND'             => 'This contribution could not be found.',
]);