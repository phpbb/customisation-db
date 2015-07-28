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

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

// Category option flags
define('TITANIA_CAT_FLAG_DEMO', 1); // Integrated styles demo
define('TITANIA_CAT_FLAG_ALL_VERSIONS', 2); // Contributions support all phpBB versions

// Contrib status
define('TITANIA_CONTRIB_NEW', 1); // Does not have any validated revisions; Hidden from category listing, shown if directly linked to
define('TITANIA_CONTRIB_APPROVED', 2); // Has at least one validated revision
define('TITANIA_CONTRIB_CLEANED', 3); // "Cleaned" contributions, such as those for really old versions of phpBB and no longer supported by anyone; Hidden from category listing, shown if directly linked to, not editable by authors
define('TITANIA_CONTRIB_DOWNLOAD_DISABLED', 4); // Downloads disabled (while under review for security audit or some similar reason); Shown the same as Approved, but with downloads disabled for non-team/author
define('TITANIA_CONTRIB_HIDDEN', 5); // Hidden from category listing, shown to author/teams if directly linked to
define('TITANIA_CONTRIB_DISABLED', 6); // Hidden from category listing, shown to author/teams if directly linked to, not editable by authors

// Revision status
define('TITANIA_REVISION_NEW', 1); // Is not approved yet
define('TITANIA_REVISION_APPROVED', 2); // Is approved (this is the only status shown to the public unless approval is not required)
define('TITANIA_REVISION_DENIED', 3); // Is denied
define('TITANIA_REVISION_PULLED_SECURITY', 4); // Has been pulled for a security vulnerability
define('TITANIA_REVISION_PULLED_OTHER', 5); // Has been pulled for an other non-security reason
define('TITANIA_REVISION_REPACKED', 6); // Has been repacked
define('TITANIA_REVISION_RESUBMITTED', 7); // Has been resubmitted
define('TITANIA_REVISION_ON_HOLD', 8); // Aimed for next phpBB release

// Queue status
define('TITANIA_QUEUE_CLOSED', -3); // Special case to hide closed revisions from the queue
define('TITANIA_QUEUE_DENIED', -2); // Special case to hide denied revisions from the queue
define('TITANIA_QUEUE_APPROVED', -1); // Special case to hide approved revisions from the queue
define('TITANIA_QUEUE_HIDE', 0); // Special case to hide an unfinished submission
define('TITANIA_QUEUE_NEW', 1); // Same as QUEUE_NEW in the Tag Fields table

// Main TYPE constants (use whenever possible)
define('TITANIA_CONTRIB', 1);
define('TITANIA_FAQ', 2);
define('TITANIA_QUEUE', 3);
define('TITANIA_SUPPORT', 4);
define('TITANIA_TRACKER', 5);
define('TITANIA_TOPIC', 6);
define('TITANIA_AUTHOR', 7);
define('TITANIA_CATEGORY', 8);
define('TITANIA_QUEUE_DISCUSSION', 9);
define('TITANIA_POST', 10);
define('TITANIA_SCREENSHOT', 11);
define('TITANIA_ATTENTION', 12);
define('TITANIA_TRANSLATION', 13);
define('TITANIA_CLR_SCREENSHOT', 14); // ColorizeIt sample image
define('TITANIA_ALL_SUPPORT', 15);
define('TITANIA_QUEUE_TAG', 16);

// Errorbox types
define('TITANIA_ERROR', 1);
define('TITANIA_SUCCESS', 2);
define('TITANIA_DEBUG', 3);

// Author constants
define('TITANIA_AUTHOR_HIDDEN', 0);
define('TITANIA_AUTHOR_VISIBLE', 1);

// Access Levels
define('TITANIA_ACCESS_TEAMS', 0);
define('TITANIA_ACCESS_AUTHORS', 1);
define('TITANIA_ACCESS_PUBLIC', 2);

// Attention stuff
define('TITANIA_ATTENTION_REPORTED', 1);
define('TITANIA_ATTENTION_UNAPPROVED', 2);
define('TITANIA_ATTENTION_CATS_CHANGED', 3);
define('TITANIA_ATTENTION_DESC_CHANGED', 4);
define('TITANIA_ATTENTION_NAME_CHANGED', 5);
