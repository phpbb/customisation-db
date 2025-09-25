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
    'CUSTDB_TYPE_EXTENSIONS' => 'Extensions',
    'CUSTDB_TYPE_STYLES' => 'Styles',
    'CUSTDB_TYPE_TRANSLATIONS' => 'Translations',
    'CUSTDB_TYPE_BBCODES' => 'Custom bbCodes',
    'CUSTDB_TYPE_TOOLS' => 'Tools',
    'CUSTDB_TYPE_ARCHIVE' => 'Archive',
    
    'CUSTDB_INDEX' => 'Customisation DB Index',
    'CUSTDB_NOT_ENABLED' => 'Customisation DB not enabled',
    'CUSTDB_NEW' => 'New Customisation',
    'CUSTDB_SEARCH' => 'Search',
    'CUSTDB_TYPE_TO_SEARCH' => 'Type to search...',

    // Sort by...
    'CUSTDB_SORT_BY'        => 'Sort by...',
    'CUSTDB_SORT_BY_DATE'   => 'Sort by date',
    'CUSTDB_SORT_BY_NAME'   => 'Sort by name',

    // Status
    'CUSTDB_STATUS'             => 'Status',
    'CUSTDB_STATUS_UNVALIDATED'   => 'Unvalidated',
    'CUSTDB_STATUS_APPROVED'    => 'Approved',
    'CUSTDB_STATUS_DENIED'    => 'Denied',
]);