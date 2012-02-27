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
	'ADMINISTRATION'			=> 'Administration',
	'ALLOW_AUTHOR_REPACK'		=> 'Autoriser l’auteur à repaqueter',
	'ALTER_NOTES'				=> 'Modifier les notes de validation',
	'APPROVE'					=> 'Approuver',
	'APPROVE_QUEUE'				=> 'Approuver',
	'APPROVE_QUEUE_CONFIRM'		=> 'Êtes-vous sûr de vouloir <strong>approuver</strong> cet élément ?',
	'ATTENTION'					=> 'Attention',
	'AUTHOR_REPACK_LINK'		=> 'Cliquez ici afin de repaqueter la révision',

	'CATEGORY_NAME_CLEAN'		=> 'Lien vers la catégorie',
	'CHANGE_STATUS'				=> 'Modifier le statut ou Déplacer',
	'CLOSED_ITEMS'				=> 'Éléments clôturés',

	'DELETE_QUEUE'				=> 'Supprimer les éléments de la file d’attente',
	'DELETE_QUEUE_CONFIRM'		=> 'Êtes-vous sûr de vouloir supprimer les éléments de cette file d’attente ? Tous les messages de cette file d’attente seront perdus et la révision sera retirée.',
	'DENY'						=> 'Refuser',
	'DENY_QUEUE'				=> 'Refuser',
	'DENY_QUEUE_CONFIRM'		=> 'Êtes-vous sûr de vouloir <strong>refuser</strong> cet élément ?',
	'DISCUSSION_REPLY_MESSAGE'	=> 'Réponse à la discussion de la file d’attente',

	'EDIT_VALIDATION_NOTES'		=> 'Éditer les notes de validation',

	'MANAGE_CATEGORIES'			=> 'Gérer les catégories',
	'MARK_IN_PROGRESS'			=> 'Marquer « en cours »',
	'MARK_NO_PROGRESS'			=> 'Ne plus marquer « en cours »',
	'MOVE_QUEUE'				=> 'Déplacer la file d’attente',
	'MOVE_QUEUE_CONFIRM'		=> 'Sélectionner la nouvelle localisation de la file d’attente, puis confirmer.',

	'NO_ATTENTION'				=> 'Aucun élément ne demande votre attention.',
	'NO_ATTENTION_ITEM'			=> 'L’élement n’existe pas.',
	'NO_ATTENTION_TYPE'			=> 'Le type d’attention est inapproprié.',
	'NO_NOTES'					=> 'Aucune note',
	'NO_QUEUE_ITEM'				=> 'L’élément de la file d’attente n’existe pas.',

	'OLD_VALIDATION_AUTOMOD'	=> 'Test d’AutoMOD depuis un pré-repaquetage',
	'OLD_VALIDATION_MPV'		=> 'Notes de MPV depuis un pré-repaquetage',
	'OPEN_ITEMS'				=> 'Éléments ouverts',

	'PUBLIC_NOTES'				=> 'Notes publiques de sortie',

	'QUEUE_APPROVE'				=> 'En attente d’approbation',
	'QUEUE_ATTENTION'			=> 'Attention',
	'QUEUE_DENY'				=> 'En attente de refus',
	'QUEUE_DISCUSSION_TOPIC'	=> 'Sujet de discussion de la file d’attente',
	'QUEUE_NEW'					=> 'Nouveau',
	'QUEUE_REPACK'				=> 'Repaquetage',
	'QUEUE_REPACK_ALLOWED'		=> 'Repaquetage autorisé',
	'QUEUE_REPACK_NOT_ALLOWED'	=> 'Repaquetage <strong>non</strong> autorisé',
	'QUEUE_REPLY_ALLOW_REPACK'	=> 'Autoriser l’auteur à repaqueter',
	'QUEUE_REPLY_APPROVED'		=> 'La révision %1$s a été [b]approuvée[/b] avec la raison suivante :<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_DENIED'		=> 'La révision %1$s a été [b]refusée[/b] avec la raison suivante :<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_IN_PROGRESS'	=> 'Marqué comme en cours',
	'QUEUE_REPLY_MOVE'			=> 'Déplacé de %1$s vers %2$s',
	'QUEUE_REPLY_NO_PROGRESS'	=> 'Démarqué comme en cours',
	'QUEUE_REVIEW'				=> 'Revue de la file d’attente',
	'QUEUE_STATUS'				=> 'Statut de la file d’attente',
	'QUEUE_TESTING'				=> 'En cours de test',
	'QUEUE_VALIDATING'			=> 'En cours de validation',

	'REBUILD_FIRST_POST'		=> 'Refondre le premier message',
	'REPACK'					=> 'Repaqueter',
	'REPORTED'					=> 'Reporté',
	'RETEST_AUTOMOD'			=> 'Tester à nouveau avec AutoMOD',
	'RETEST_MPV'				=> 'Tester à nouveau avec MPV',
	'REVISION_REPACKED'			=> 'Cette révision a été repaquetée.',

	'SUBMIT_TIME'				=> 'Date de soumission',

	'UNAPPROVED'				=> 'Non approuvé',
	'UNKNOWN'					=> 'Inconnu',

	'VALIDATION'				=> 'Validation',
	'VALIDATION_AUTOMOD'		=> 'Test d’AutoMOD',
	'VALIDATION_MESSAGE'		=> 'Message ou raison de validation',
	'VALIDATION_MPV'			=> 'Notes de MPV',
	'VALIDATION_NOTES'			=> 'Notes de validation',
	'VALIDATION_QUEUE'			=> 'File d’attente de validation',
	'VALIDATION_SUBMISSION'		=> 'Soumission d’une validation',
));
