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

namespace phpbb\titania;

class ext extends \phpbb\extension\base
{
	// Contribution types
	const TITANIA_TYPE_MOD = 1;
	const TITANIA_TYPE_STYLE = 2;
	const TITANIA_TYPE_CONVERTER = 3;
	const TITANIA_TYPE_OFFICIAL_TOOL = 4;
	const TITANIA_TYPE_BRIDGE = 5;
	const TITANIA_TYPE_TRANSLATION = 6;
	const TITANIA_TYPE_BBCODE = 7;
	const TITANIA_TYPE_EXTENSION = 8;

	// Category option flags
	const TITANIA_CAT_FLAG_DEMO = 1; // Integrated styles demo
	const TITANIA_CAT_FLAG_ALL_VERSIONS = 2; // Contributions support all phpBB versions

	// Contrib status
	const TITANIA_CONTRIB_NEW = 1; // Does not have any validated revisions; Hidden from category listing, shown if directly linked to
	const TITANIA_CONTRIB_APPROVED = 2; // Has at least one validated revision
	const TITANIA_CONTRIB_CLEANED = 3; // "Cleaned" contributions, such as those for really old versions of phpBB and no longer supported by anyone; Hidden from category listing, shown if directly linked to, not editable by authors
	const TITANIA_CONTRIB_DOWNLOAD_DISABLED = 4; // Downloads disabled (while under review for security audit or some similar reason); Shown the same as Approved, but with downloads disabled for non-team/author
	const TITANIA_CONTRIB_HIDDEN = 5; // Hidden from category listing, shown to author/teams if directly linked to
	const TITANIA_CONTRIB_DISABLED = 6; // Hidden from category listing, shown to author/teams if directly linked to, not editable by authors

	// Revision status
	const TITANIA_REVISION_NEW = 1; // Is not approved yet
	const TITANIA_REVISION_APPROVED = 2; // Is approved (this is the only status shown to the public unless approval is not required)
	const TITANIA_REVISION_DENIED = 3; // Is denied
	const TITANIA_REVISION_PULLED_SECURITY = 4; // Has been pulled for a security vulnerability
	const TITANIA_REVISION_PULLED_OTHER = 5; // Has been pulled for an other non-security reason
	const TITANIA_REVISION_REPACKED = 6; // Has been repacked
	const TITANIA_REVISION_RESUBMITTED = 7; // Has been resubmitted
	const TITANIA_REVISION_ON_HOLD = 8; // Aimed for next phpBB release

	// Queue status
	const TITANIA_QUEUE_CLOSED = -3; // Special case to hide closed revisions from the queue
	const TITANIA_QUEUE_DENIED = -2; // Special case to hide denied revisions from the queue
	const TITANIA_QUEUE_APPROVED = -1; // Special case to hide approved revisions from the queue
	const TITANIA_QUEUE_HIDE = 0; // Special case to hide an unfinished submission
	const TITANIA_QUEUE_NEW = 1; // Same as QUEUE_NEW in the Tag Fields table

	// Main TYPE constants (use whenever possible)
	const TITANIA_CONTRIB = 1;
	const TITANIA_FAQ = 2;
	const TITANIA_QUEUE = 3;
	const TITANIA_SUPPORT = 4;
	const TITANIA_TRACKER = 5;
	const TITANIA_TOPIC = 6;
	const TITANIA_AUTHOR = 7;
	const TITANIA_CATEGORY = 8;
	const TITANIA_QUEUE_DISCUSSION = 9;
	const TITANIA_POST = 10;
	const TITANIA_SCREENSHOT = 11;
	const TITANIA_ATTENTION = 12;
	const TITANIA_TRANSLATION = 13;
	const TITANIA_CLR_SCREENSHOT = 14; // ColorizeIt sample image
	const TITANIA_ALL_SUPPORT = 15;
	const TITANIA_QUEUE_TAG = 16;

	// Errorbox types
	const TITANIA_ERROR = 1;
	const TITANIA_SUCCESS = 2;
	const TITANIA_DEBUG = 3;

	// Author constants
	const TITANIA_AUTHOR_HIDDEN = 0;
	const TITANIA_AUTHOR_VISIBLE = 1;

	// Access Levels
	const TITANIA_ACCESS_TEAMS = 0;
	const TITANIA_ACCESS_AUTHORS = 1;
	const TITANIA_ACCESS_PUBLIC = 2;

	// Attention stuff
	const TITANIA_ATTENTION_REPORTED = 1;
	const TITANIA_ATTENTION_UNAPPROVED = 2;
	const TITANIA_ATTENTION_CATS_CHANGED = 3;
	const TITANIA_ATTENTION_DESC_CHANGED = 4;
	const TITANIA_ATTENTION_NAME_CHANGED = 5;

	// Misc
	const TITANIA_CONFIG_PREFIX = 'titania_';
}
