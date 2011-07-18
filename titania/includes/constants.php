<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
 * @ignore
 */
if (!defined('IN_TITANIA'))
{
	exit;
}

// Without this we cannot include phpBB 3.0.x scripts.
if (!defined('IN_PHPBB'))
{
	define('IN_PHPBB', true);
}

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

// Header status codes
define('HEADER_OK',						200);
define('HEADER_CREATED',				201);
define('HEADER_ACCEPTED',				202);
define('HEADER_NO_CONTENT', 			204);
define('HEADER_RESET_CONTENT',			205);
define('HEADER_MULTIPLE_CHOICES',		300);
define('HEADER_MOVED_PERMANENTLY',		301);
define('HEADER_FOUND',					302); // Moved Temporarily
define('HEADER_SEE_OTHER',				303);
define('HEADER_NOT_MODIFIED',			304);
define('HEADER_TEMPORARY_REDIRECT',		307);
define('HEADER_BAD_REQUEST',			400);
define('HEADER_UNAUTHORIZED',			401);
define('HEADER_FORBIDDEN',				403);
define('HEADER_NOT_FOUND',				404);
define('HEADER_NOT_ACCEPTABLE',			406);
define('HEADER_CONFLICT',				409);
define('HEADER_GONE',					410);
define('HEADER_INTERNAL_SERVER_ERROR',	500);
define('HEADER_NOT_IMPLEMENTED',		501);
define('HEADER_BAD_GATEWAY',			502);
define('HEADER_SERVICE_UNAVAILABLE',	503);