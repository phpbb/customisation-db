<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team, (c) 2011 phpBB.fr
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License 2.0
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
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

$lang = array_merge($lang, array(
	'ACCESS'							=> 'Niveau d’accés',
	'ACCESS_AUTHORS'					=> 'Accés aux auteurs',
	'ACCESS_PUBLIC'						=> 'Accés public',
	'ACCESS_TEAMS'						=> 'Accés à l’équipe',
	'ATTACH'							=> 'Insérer',

	'FILE_DELETED'						=> 'Ce fichier sera supprimé lors de l’envoi',

	'HARD_DELETE_TOPIC_CONFIRM'			=> 'Êtes-vous sûr de vouloir supprimer <strong>définitivement</strong> ce sujet ?<br /><br />Ce sujet ne pourra pas être restauré !',

	'QUEUE_DISCUSSION_TOPIC_MESSAGE'	=> 'Ce sujet est un espace de discussion concernant la validation entre les auteurs de la contribution et les validateurs.

Tout ce qui sera publié dans ce sujet sera lu par les personnes qui valident votre contribution. Veuillez donc utiliser ce sujet et éviter d’envoyer des messages privés aux validateurs.

L’équipe de validation peut également poser quelques questions ou publier des commentaires. Veuillez essayer de leur répondre le plus clairement et le plus rapidement possible afin qu’ils puissent poursuivre la procédure de validation dans des délais convenables.

Veuillez noter que par défaut, ce sujet est privé entre les auteurs de la contribution et les validateurs. Le contenu n’est donc pas public.',
	'QUEUE_DISCUSSION_TOPIC_TITLE'		=> 'Discussion de validation - %s',

	'REPORT_POST_CONFIRM'				=> 'Utilisez ce formulaire afin de rapporter le message que vous avez sélectionné aux modérateurs et aux administrateurs du forum. Veuillez ne rapporter que les messages qui ne respectent pas les règles du forum.',

	'SET_PREVIEW_FILE'					=> 'Définir comme prévisualisation',
	'SOFT_DELETE_TOPIC_CONFIRM'			=> 'Êtes-vous sûr de vouloir <strong>archiver</strong> ce sujet ?',
	'STICKIES'							=> 'Notes',
	'STICKY_TOPIC'						=> 'Sujet annoté',

	'UNDELETE_FILE'						=> 'Annuler la suppression',
	'UNDELETE_POST'						=> 'Restaurer le message',
	'UNDELETE_POST_CONFIRM'				=> 'Êtes-vous sûr de vouloir restaurer ce message ?',
	'UNDELETE_TOPIC_CONFIRM'			=> 'Êtes-vous sûr de vouloir restaurer ce sujet ?',
));
