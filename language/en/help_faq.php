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
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'HELP_FAQ_BLOCK_TITANIA'       => 'What is Titania (aka The Customisation Database)',
	'HELP_FAQ_TITANIA_QUESTION'    => 'What is Titania?',
	'HELP_FAQ_TITANIA_ANSWER'      => 'Titania (aka The Customisation Database) is a database where users are be able to download extensions and styles for a phpBB forum. You also are ensured that the extensions or style that you have downloaded has passed the phpBB validation requirements.',
	'HELP_FAQ_VALIDATION_QUESTION' => 'Validation? What’s that?',
	'HELP_FAQ_VALIDATION_ANSWER'   => 'Each and every extension, or style, that is downloaded by you within Titania has undergone validation. Validation means that an extension, or style, has undergone scrutiny as to the security of the code involved as well as testing to ensure the extension or style installs and works properly within a particular version of a phpBB forum. Validation provides to you a level of comfort in knowing that you are not downloading/installing a extension or style that may subject your forum to being hacked.',

	'HELP_FAQ_BLOCK_USE_TITANIA'     => 'How to use Titania',
	'HELP_FAQ_FIND_CONTRIB_QUESTION' => 'Finding a contribution',
	'HELP_FAQ_FIND_CONTRIB_ANSWER'   => 'There are several ways to find a contribution. On the main Customisation Database page you can see the categories that are currently available as well as recent extensions/styles that have been approved within the database.',
	'HELP_FAQ_FIND_EXT_QUESTION'     => 'Finding an extension',
	'HELP_FAQ_FIND_EXT_ANSWER'       => 'You can either go directly to the type of extension wanted based on what category the contribution is based on (Tools, Communication, Security, Entertainment, etc) or by using the search feature toward the top of the page. If using the search feature you can use wildcards and search on either the contribution name (or part of the name) as well as the contribution’s author. Once you find the customisation you are interested in you will be brought to the “Contribution Details” page where you can find a download of the current version of the customisation as well as past versions of the customisation within the “Revisions” section.',
	'HELP_FAQ_FIND_STYLE_QUESTION'   => 'Finding a style',
	'HELP_FAQ_FIND_STYLE_ANSWER'     => 'Similar to finding an extension, Titania also allows you to locate styles, smilie packs, rank images and other items. The search feature will also allow you to use wild cards and to search on just an authors name as well. Once you find the item you are interested in you will be brought to the “Contribution Details” page where you can find a download of the current version of the item as well as past versions of the that item within the “Revisions” section.',

	'HELP_FAQ_BLOCK_SUPPORT'        => 'Customisation Support',
	'HELP_FAQ_RULES_QUESTION'       => 'Rules',
	'HELP_FAQ_RULES_ANSWER'         => 'With the introduction of Titania, the rules involved to use it are very simple. As in the past, the saying “You should seek support within the extension/style topic where you got the customisation” pertains. While the support Team of phpBB.com does their best to assist you in running and using your forum they can not, and are not expected to, provide support for any customisation/contribution. It is our hope that the author of a contribution provides end user support. Please remember that all authors are volunteers who have spent their time in providing an enhancement to the phpBB software. The saying “You get more flies with honey than you do with vinegar” applies, so please keep this in mind when requesting support for a customisation (eg, be nice in the way you ask).',
	'HELP_FAQ_GET_SUPPORT_QUESTION' => 'How to get support',
	'HELP_FAQ_GET_SUPPORT_ANSWER'   => 'Each customisation is provided a method to provide support to you. Within each is the ability for the author to post FAQ(s) concerning the customisation as well as a discussion/support area for a type of one-on-one support. This support can range anywhere from assisting you to getting the customisation installed and may even provide to you additional add-ons to enhance the customisation. To access this area you simply click on the customisation and a tab will be displayed stating “Discussion/Support”. Once you access this area you may post a question or comment to the author. Please remember that authors are under no obligation to provide support just as they are under no obligation to provide to you the customisation. If you run across a post or comment that you feel is not in the best interests of the community, please feel free to use the “Report this post” button and a moderator will take the appropriate action necessary.',

	'HELP_FAQ_BLOCK_MANAGING'            => 'Creating and Managing Contributions',
	'HELP_FAQ_CREATING_CONTRIB_QUESTION' => 'Creating a Contribution',
	'HELP_FAQ_CREATING_CONTRIB_ANSWER'   => 'As with any contribution, authors are requested to follow certain guidelines when submitting their own contribution. The <a href="http://area51.phpbb.com/docs/coding-guidelines.html">Coding Guidelines</a>, though seemingly daunting at first, are actually your friend. They should be followed as closely as possible to assist you in getting your contribution to the community published. In the case of an extension, the <a href="http://www.phpbb.com/extensions/epv/">phpBB extension pre-validator</a> (aka “EPV”) will be run against the submitted revision and will check for things such as correct licensing, current phpBB version and correct packaging and version requirements.',
	'HELP_FAQ_SUBMIT_CONTRIB_QUESTION'   => 'Submitting a Contribution',
	'HELP_FAQ_SUBMIT_CONTRIB_ANSWER'     => 'So you’ve made a contribution. Let’s get that puppy published!!<br /><br />To submit a contribution, go to the Customisation Database and within that page you will find an image link that states “New Contribution”. Once clicked on you will be able to enter in the contribution name, select the contribution type, add some wording to describe the contribution (smilies and bbcode is allowed), select the category(ies) that the contribution fits into, add co-authors (if any) and screenshots as well. Please keep in mind that as you are submitting the contribution, it is your name the contribution will be aligned with.',
	'HELP_FAQ_MANAGING_CONTRIB_QUESTION' => 'Managing Contributions',
	'HELP_FAQ_MANAGING_CONTRIB_ANSWER'   => 'Once your contribution is uploaded successfully into Titania, you are able to manage it. After selecting your contribution by clicking on "My Contributions" at the top of the page, you may add additional information to it via the "Manage Contribution" tab. You are able to amend the description of the contribution, upload screen shots, change ownership of the contribution (please note this is irreversible so ensure you really want to give another user ownership of your contribution), change the categories the contribution fits under as well as input a demo url so users can see firsthand what the contribution looks like and how it works.',
	'HELP_FAQ_SUBMIT_REVISION_QUESTION'  => 'Submitting a new Revision',
	'HELP_FAQ_SUBMIT_REVISION_ANSWER'    => 'You can upload new revisions on the main page, the “Contribution Details” section, of your customisation. Once you click on the “New Revision” link, you are presented a page where in you upload the revision, assign it a version and input notes to the validation team (bbcode and smilies are allowed). You can also choose to have the validation team “repack” the extension. Repacking involves making minor fixes to the customisation. This may involve corrections to the composer.json file or even minor code changes. Repacking is <strong>not</strong> having the validation team re-write major snippets of the code you supplied, that would be your “job”.<br /><br />The rules, as they apply concerning creation of a customisation, still apply when submitting revisions to your customisation. That is, the <a href="http://www.phpbb.com/extensions/epv/">phpBB extension pre-validator</a> (aka “EPV”) will be run against the revision of the customisation and will check for things such as correct licensing, current phpBB version and correct packaging and version requirements.',

	'HELP_FAQ_BLOCK_SUPPORTING'       => 'Giving Support',
	'HELP_FAQ_SUPPORT_FAQ_QUESTION'   => 'FAQ',
	'HELP_FAQ_SUPPORT_FAQ_ANSWER'     => 'Each customisation provides to the author the ability to submit FAQ type of topics. These topics that you create should be written in a way that a user can understand and apply the topic to the customisation, whether the topic be concerning how to get the customisation installed, accessing features of the customisation, etc. It should be noted that this area is just for you. Users can not edit or reply to FAQ entries.',
	'HELP_FAQ_SUPPORT_FORUM_QUESTION' => 'Support Forum',
	'HELP_FAQ_SUPPORT_FORUM_ANSWER'   => 'Please keep in mind that users will ask questions or make comments concerning your contribution. We ask that you support your contribution as much as you can. We realize that you spent your free time in creating your contribution and that real life can, sometimes, get in the way of fun. We just ask that you as the author(s) provide as much support as is possible. If you run across a post or comment that you feel is not in the best interests of the community, please feel free to use the “Report this post” button and a moderator will take the appropriate action necessary.',

	'HELP_FAQ_BLOCK_VALIDATION'         => 'Validation',
	'HELP_FAQ_VALIDATION_FAIL_QUESTION' => 'My Customisation didn’t pass the pre validator check',
	'HELP_FAQ_VALIDATION_FAIL_ANSWER'   => 'Remember, every customisation MUST have the correct licensing (currenty GNU GPL version 2), the correct version of phpBB software and the correct packaging. If your customisation does not have these rudimentary items then it cannot be accepted into the database. Some errors are simply warnings and may not need fixing, if you are unsure of the problem feel free to continue with the submission and a validator will handle it.',
	'HELP_FAQ_VALIDATION_PASS_QUESTION' => 'My Customisation passed pre validation, now what?',
	'HELP_FAQ_VALIDATION_PASS_ANSWER'   => 'Once a customisation is accepted into the database, if it is an extension is then up to the relevant team who will validate your contribution. You may find that you get a message stating your customisation was denied. Please don’t fret. We know things get overlooked or just simply missed. Not to worry. The message you receive will contain items that were found. These items may suggest changes to the code or images and may even suggest changes on “user friendliness”. Generally speaking, “user friendliness” suggestions are just that...suggestions. The most important part of any customisation is security, not in what it looks like to the end user.<br /><br />If no items were found during validation of your contribution you will get a PM stating that your contribution has been accepted into the database. It is now time to relax a bit and revel in the knowledge that you have made a contribution to the open source community.<br /><br />No matter the outcome of the validation, we appreciate the time and effort you have exerted in sharing your contribution.',
	'HELP_FAQ_VALIDATORS_QUESTION'      => 'Who will be validating my contribution?',
	'HELP_FAQ_VALIDATORS_ANSWER'        => 'If it is an extension it will be validated by the Extension Customisations Team and Junior Validators or occassionally a Development Team Member. For a style it will be validated by the Style Customisations Team and Junior Style Validators. For a convertor it will be validated by a Support or Development Team Member. For a bridge it will be validated by an Extension or Development Team Member. Language Packs are all checked by the International Manager. Official Tools are tested and created by the phpBB.com Teams.',
));
