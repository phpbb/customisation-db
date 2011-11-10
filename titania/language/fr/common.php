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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACCESS_LIMIT_AUTHORS'		=> 'Accès limité aux auteurs',
	'ACCESS_LIMIT_TEAMS'		=> 'Accès limité aux membres de l’équipe',
	'ADD_FIELD'					=> 'Ajouter un champ',
	'AGREE'						=> 'J’accepte ces conditions',
	'AGREEMENT'					=> 'Conditions d’utilisation',
	'ALL'						=> 'Tous',
	'ALL_CONTRIBUTIONS'			=> 'Toutes les contributions',
	'ALL_SUPPORT'				=> 'Tous les sujets de support',
	'AUTHOR_BY'					=> 'Par %s',

	'BAD_RATING'				=> 'La tentative de notation a echoué.',
	'BY'						=> 'par',

	'CACHE_PURGED'				=> 'Le cache a été vidé avec succès',
	'CATEGORY'					=> 'Catégorie',
	'CATEGORY_CHILD_AS_PARENT'	=> 'La catégorie parente ne peut pas être selectionnée car il s’agit d’une sous-catégorie.',
	'CATEGORY_DELETED'			=> 'La catégorie a été supprimée',
	'CATEGORY_DESC'				=> 'Description de la catégorie',
	'CATEGORY_DUPLICATE_PARENT'	=> 'La catégorie ne peut pas être son propre parent.',
	'CATEGORY_HAS_CHILDREN'		=> 'La catégorie ne peut pas être supprimée car elle contient des sous-catégories.',
	'CATEGORY_INFORMATION'		=> 'Informations sur la catégorie',
	'CATEGORY_NAME'				=> 'Nom de la catégorie',
	'CATEGORY_TYPE'				=> 'Type de catégorie',
	'CATEGORY_TYPE_EXPLAIN'		=> 'Le type de contribution de cette catégorie sera retenue. Laissez ce champ vide afin de ne pas accepter de contributions.',
	'CAT_ADDONS'				=> 'Add-ons',
	'CAT_ANTI_SPAM'				=> 'Anti-pourriel',
	'CAT_AVATARS'				=> 'Avatars',
	'CAT_BOARD_STYLES'			=> 'Styles de forum',
	'CAT_COMMUNICATION'			=> 'Communication',
	'CAT_COSMETIC'				=> 'Cosmétique',
	'CAT_ENTERTAINMENT'			=> 'Divertissement',
	'CAT_LANGUAGE_PACKS'		=> 'Archives de langue',
	'CAT_MISC'					=> 'Divers',
	'CAT_MODIFICATIONS'			=> 'Modifications',
	'CAT_PROFILE_UCP'			=> 'Profil / Panneau de contrôle de l’utilisateur',
	'CAT_RANKS'					=> 'Rangs',
	'CAT_SECURITY'				=> 'Securité',
	'CAT_SMILIES'				=> 'Émoticônes',
	'CAT_SNIPPETS'				=> 'Snippets',
	'CAT_STYLES'				=> 'Styles',
	'CAT_TOOLS'					=> 'Outils',
	'CLOSED_BY'					=> 'Clôturé par',
	'CLOSED_ITEMS'				=> 'Éléments clôturés',
	'COLORIZEIT_COLORS'         => 'Palette de couleurs',
	'COLORIZEIT_DOWNLOAD'       => 'Modifier la palette de couleurs.',
	'COLORIZEIT_DOWNLOAD_STYLE' => 'Modifier la palette de couleurs puis télécharger',
	'COLORIZEIT_MANAGE'         => 'Configuration de ColorizeIt',
	'COLORIZEIT_MANAGE_EXPLAIN' => 'Avant d’activer ColorizeIt sur ce style, vous devez télécharger une image d’exemple et modifier la palette de couleurs par défaut. L’image d’exemple doit être au format GIF, ne doit pas être animée et la taille doit être comprise entre 200x300 et 500x600 pixels. L’image d’exemple ne doit pas être redimensionnée, ne doit pas inclure de couleurs qui ne sont pas présentes dans le style et les textes ne doivent pas bénéficier d’un anticrénelage. <a href="http://www.colorizeit.com/advanced.html?do=tutorial_sample">Cliquez sur ce lien</a> afin de consulter une documentation détaillée.',
	'COLORIZEIT_SAMPLE'         => 'Afficher l’éditeur de la palette de couleurs',
	'COLORIZEIT_SAMPLE_EXPLAIN' => 'Ajoutez des couleurs à l’éditeur en les sélectionnant à partir d’une image d’exemple, copiez la chaîne de la palette de couleurs à partir du champ de texte situé sous l’éditeur au champ de texte situé sous ce texte, puis cliquez sur « Envoyer » afin d’enregistrer les modifications.',
	'CONFIRM_PURGE_CACHE'		=> 'Êtes-vous sûr de vouloir vider le cache ?',
	'CONTINUE'					=> 'Continuer',
	'CONTRIBUTION'				=> 'Contribution',
	'CONTRIBUTIONS'				=> 'Contributions',
	'CONTRIB_FAQ'				=> 'FAQ',
	'CONTRIB_MANAGE'			=> 'Gérer la contribution',
	'CONTRIB_SUPPORT'			=> 'Discussion / Support',
	'CREATE_CATEGORY'			=> 'Créer une catégorie',
	'CREATE_CONTRIBUTION'		=> 'Créer une contribution',
	'CUSTOMISATION_DATABASE'	=> 'Base de données des contributions',

	'DATE_CLOSED'				=> 'Date de clôture',
	'DELETED_MESSAGE'			=> 'Dernière suppression par %1$s le %2$s - <a href="%3$s">Cliquez ici afin de restaurer ce message</a>',
	'DELETE_ALL_CONTRIBS'		=> 'Supprimer toutes les contributions',
	'DELETE_CATEGORY'			=> 'Supprimer la catégorie',
	'DELETE_SUBCATS'			=> 'Supprimer les sous-catégories',
	'DESCRIPTION'				=> 'Description',
	'DESTINATION_CAT_INVALID'	=> 'La catégorie de destination ne peut pas accepter de contributions.',
	'DETAILS'					=> 'Informations',
	'DOWNLOAD'					=> 'Télécharger',
	'DOWNLOADS'					=> 'Téléchargements',
	'DOWNLOAD_ACCESS_DENIED'	=> 'Vous n’êtes pas autorisé à télécharger le fichier demandé.',
	'DOWNLOAD_NOT_FOUND'		=> 'Le fichier demandé est introuvable.',

	'EDIT'						=> 'Éditer',
	'EDITED_MESSAGE'			=> 'Dernière édition par %1$s le %2$s',
	'EDIT_CATEGORY'				=> 'Éditer la catégorie',
	'ERROR'						=> 'Erreur',

	'FILE_NOT_EXIST'			=> 'Le fichier n’existe pas : %s',
	'FIND_CONTRIBUTION'			=> 'Rechercher une contribution',

	'HARD_DELETE'				=> 'Suppression permanente',
	'HARD_DELETE_EXPLAIN'		=> 'Cochez cette case afin de supprimer définitivement cet élément.',
	'HARD_DELETE_TOPIC'			=> 'Suppression permanente du sujet',

	'LANGUAGE_PACK'				=> 'Archive de langue',
	'LIST'						=> 'Liste',

	'MAKE_CATEGORY_VISIBLE'		=> 'Afficher la catégorie',
	'MANAGE'					=> 'Gérer',
	'MARK_CONTRIBS_READ'		=> 'Marquer toutes les contributions comme lues',
	'MOVE_CONTRIBS_TO'			=> 'Déplacer les contributions vers',
	'MOVE_DOWN'					=> 'Descendre',
	'MOVE_SUBCATS_TO'			=> 'Déplacer les sous-catégories vers',
	'MOVE_UP'					=> 'Monter',
	'MULTI_SELECT_EXPLAIN'		=> 'Maintenez la touche CTRL enfoncée puis cliquez afin de sélectionner plusieurs éléments.',
	'MY_CONTRIBUTIONS'			=> 'Mes contributions',

	'NAME'						=> 'Nom',
	'NEW_REVISION'				=> 'Nouvelle révision',
	'NOT_AGREE'					=> 'Je refuse ces conditions',
	'NO_AUTH'					=> 'Vous n’êtes pas autorisé à consulter cette page.',
	'NO_CATEGORY'				=> 'La catégorie demandée n’existe pas.',
	'NO_CATEGORY_NAME'			=> 'Saisissez le nom de la catégorie',
	'NO_CONTRIB'				=> 'La contribution demandée n’existe pas.',
	'NO_CONTRIBS'				=> 'Aucune contribution n’a été trouvée',
	'NO_DESC'					=> 'Vous devez saisir une description.',
	'NO_DESTINATION_CATEGORY'	=> 'Aucun catégorie de destination n’a été trouvée.',
	'NO_POST'					=> 'Le message demandé n’existe pas.',
	'NO_REVISION_NAME'			=> 'Aucun nom de révision n’a été renseigné',
	'NO_TOPIC'					=> 'Le sujet demandé n’existe pas.',

	'ORDER'						=> 'Trier',

	'PARENT_CATEGORY'			=> 'Catégorie parente',
	'PARENT_NOT_EXIST'			=> 'Le parent n’existe pas.',
	'POST_IP'					=> 'Adresse IP du message',
	'PURGE_CACHE'				=> 'Vider le cache',

	'QUEUE'						=> 'File d’attente',
	'QUEUE_DISCUSSION'			=> 'Discussion de la file d’attente',
	'QUICK_ACTIONS'				=> 'Actions rapides',

	'RATING'					=> 'Note',
	'REMOVE_RATING'				=> 'Supprimer la note',
	'REPORT'					=> 'Rapporter',
	'RETURN_LAST_PAGE'			=> 'Retourner à la page précédente',
	'ROOT'						=> 'Racine',

	'SEARCH_UNAVAILABLE'		=> 'Le système de recherche est actuellement indisponible.  Veuillez réessayer dans quelques minutes.',
	'SELECT_CATEGORY'			=> '-- Sélectionner une catégorie --',
	'SELECT_CATEGORY_TYPE'		=> '-- Sélectionner un type de catégorie --',
	'SELECT_SORT_METHOD'		=> 'Trier par',
	'SHOW_ALL_REVISIONS'		=> 'Afficher toutes les révisions',
	'SITE_INDEX'				=> 'Index du site',
	'SNIPPET'					=> 'Snippet',
	'SOFT_DELETE_TOPIC'			=> 'Archiver le sujet',
	'SORT_CONTRIB_NAME'			=> 'Nom de la contribution',
	'STICKIES'					=> 'Notes',
	'SUBSCRIBE'					=> 'S’abonner',
	'SUBSCRIPTION_NOTIFICATION'	=> 'Notification d’abonnement',
	'SUCCESSBOX_TITLE'     		=> 'Succès',
	'SYNC_SUCCESS'      	  	=> 'Synchronisé avec succès',

	'TITANIA_DISABLED'			=> 'La base de données des contributions est temporairement désactivée, veuillez réessayer dans quelques minutes.',
	'TITANIA_INDEX'				=> 'Base de données des contributions',
	'TOTAL_CONTRIBS'			=> '%d contributions',
	'TOTAL_CONTRIBS_ONE'		=> '1 contribution',
	'TOTAL_POSTS'				=> '%d messages',
	'TOTAL_POSTS_ONE'			=> '1 message',
	'TOTAL_RESULTS'				=> '%d résultats',
	'TOTAL_RESULTS_ONE'			=> '1 résultat',
	'TOTAL_TOPICS'				=> '%d sujets',
	'TOTAL_TOPICS_ONE'			=> '1 sujet',
	'TRANSLATION'				=> 'Archive de langue',
	'TRANSLATIONS'				=> 'Archives de langue',
	'TYPE'						=> 'Type',

	'UNDELETE_TOPIC'			=> 'Restaurer le sujet',
	'UNKNOWN'					=> 'Inconnu',
	'UNSUBSCRIBE'				=> 'Se désabonner',
	'UPDATE_TIME'				=> 'Mis à jour',

	'VERSION'					=> 'Version',
	'VIEW'						=> 'Consulter',
));
