<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**#@+
* Enum constants for separating contrib types
*/
define('CONTRIB_MODS', 1);
define('CONTRIB_STYLES', 2);
/**#@-*/

/**#@+
* Enum constants for separating contrib status
*/
define('CONTRIB_REJECTED', -1);				// Not suitable for the database (spam, troll, wrong contrib, or N/A)
define('CONTRIB_NEW', 0);					// Submitted by Author for approval
define('CONTRIB_DEVELOPMENT', 1);			// Accepted into the database but not approved for release 
define('CONTRIB_RELEASED', 2);				// Released
define('CONTRIB_PULLED', 3);				// Removed from the database for whatever reason (usually security concerns)
define('CONTRIB_CLEANED', 4);				// Contrib is waiting for MOD/STYLE db cleanup.
/**#@-*/

/**#@+
* Enum constants for contrib roles
*/
define('CONTRIB_OWNER', 1);					// Creator/founder/project manager
define('CONTRIB_DEVELOPER', 2);				// can update & submit bug fixes, etc
define('CONTRIB_DOCUMENTOR', 3);			// can update documentation, etc
/**#@-*/

/**#@+
* Enum constants for queue_statuses.
* Negative numbers denote closed status.
* Positive Numbers denote open status.
*/
define('QUEUE_NEW', 1);						// Item is new in queue
define('QUEUE_SPECIAL', 2);					// Item is undergoing validation (fallback status != new)
define('QUEUE_APPROVE', 3);					// Item is ready for approval
define('QUEUE_DENY', 4);					// Item is ready to be denied

define('QUEUE_CLOSED', -1);					// Item approved
define('QUEUE_DENIED', -2);					// Item denied
define('QUEUE_DEPRECATED', -3);				// Item outdated by a replacement submission from author
define('QUEUE_CANCELLED', -4);				// Item cancelled by author
define('QUEUE_REPLACED', -5);				// Item outdated by a replacement submission from validator (Not used?)
define('QUEUE_CANNED', -6);                 // Item canned by teammember
/**#@-*/

/**#@+
* Enum constants for queue statuses.
* Negative numbers denote closed status.
* Positive Numbers denote open status.
*/
define('CONTRIB_TOPIC_ANNOUNCEMENTS', 1);		// Announcements thread
define('CONTRIB_TOPIC_SUPPORT', 2);				// Support/release thread
define('CONTRIB_TOPIC_DEVELOPMENT', 3);			// Development thread
define('CONTRIB_TOPIC_QUEUE', 4);				// Validation thread
define('CONTRIB_TOPIC_DISCUSS', 5);				// Author/Team discussion thread
/**#@-*/

/**#@+
* Queue types ("queue_action")
*/
define('QUEUE_CREATE', 1);
define('QUEUE_UPDATE', 2);
define('QUEUE_DESCRIPTION', 3);
define('QUEUE_TAGS', 4);
define('QUEUE_AUTHOR', 5);
define('QUEUE_NAME', 6);
/**#@-*/

$site_prefix = $table_prefix . 'site_';

/**#@+
* Database table constants
*/
define('CONTRIB_DB_TABLE',			$site_prefix . 'contrib_db');
define('SITE_CONTRIBS_TABLE',			$site_prefix . 'contribs');
define('SITE_CONTRIB_USER_TABLE',	$site_prefix . 'contributers');
define('SITE_CONTRIB_TAGS_TABLE',	$site_prefix . 'contrib_tags');
define('SITE_EMAILS_TABLE',				$site_prefix . 'emails');
define('SITE_QUEUE_TABLE',				$site_prefix . 'queue');
define('SITE_REVISIONS_TABLE',		$site_prefix . 'contrib_revisions');
define('SITE_TAGS_TABLE',					$site_prefix . 'tags');
define('SITE_TOPICS_TABLE',				$site_prefix . 'contrib_topics');
define('SITE_CHANGE_OWNER_TABLE', $site_prefix . 'change_owner');
/**#@-*/

/**#@+
* Styles demo
*/
define('STYLE_DEMO_PATH_20', $root_path . 'styles/demo/2.0/board/');
define('STYLE_DEMO_PATH_30', $phpbb_root_path);

define('TABLE_PREFIX_20', 'stylesdemo_');
/**#@-*/

?>