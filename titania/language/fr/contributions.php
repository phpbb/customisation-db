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
'CUSTOM_LICENSE' => 'Custom',
	'ANNOUNCEMENT_TOPIC'					=> 'Sujet d’annonce',
	'ANNOUNCEMENT_TOPIC_SUPPORT'			=> 'Sujet de support',
	'ANNOUNCEMENT_TOPIC_VIEW'				=> '%sConsulter%s',
	'ATTENTION_CONTRIB_CATEGORIES_CHANGED'	=> '<strong>Les catégories de la contribution ont été modifiées de :</strong><br />%1$s<br /><br /><strong>à :</strong><br />%2$s',
	'ATTENTION_CONTRIB_DESC_CHANGED'		=> '<strong>La description de la contribution a été modifiée de :</strong><br />%1$s<br /><br /><strong>à :</strong><br />%2$s',
	'AUTOMOD_RESULTS'						=> '<strong>Veuillez vérifier les résultats de l’installation d’AutoMOD et vous assurer que rien ne doit être corrigé.<br /><br />Si une erreur survient mais que vous êtes certain que cette dernière peut être ignorée, appuyez sur le bouton « Continuer » ci-dessous.</strong>',
	'AUTOMOD_TEST'							=> 'Le MOD sera testé au travers d’AutoMOD et les résultats seront affichés (cela peut prendre un certain temps, soyez patient).<br /><br />Appuyez sur le bouton « Continuer » lorsque vous êtes prêt.',

	'BAD_VERSION_SELECTED'					=> '%s n’est pas une version de phpBB reconnue.',

	'CANNOT_ADD_SELF_COAUTHOR'				=> 'Vous êtes l’auteur principal, vous ne pouvez pas vous ajouter vous-même à la liste des co-auteurs.',
	'CLEANED_CONTRIB'						=> 'Contribution nettoyée',
	'CONTRIB'								=> 'Contribution',
	'CONTRIBUTIONS'							=> 'Contributions',
	'CONTRIB_ACTIVE_AUTHORS'				=> 'Co-auteurs actifs',
	'CONTRIB_ACTIVE_AUTHORS_EXPLAIN'		=> 'Les co-auteurs actifs peuvent gérer certaines parties de la contribution.',
	'CONTRIB_APPROVED'						=> 'Approuvé',
	'CONTRIB_AUTHOR'						=> 'Auteur de la contribution',
	'CONTRIB_AUTHORS_EXPLAIN'				=> 'Saisissez les noms des co-auteurs, séparés par une nouvelle ligne.',
	'CONTRIB_CATEGORY'						=> 'Catégorie de la contribution',
	'CONTRIB_CHANGE_OWNER'					=> 'Modifier le propriétaire',
	'CONTRIB_CHANGE_OWNER_EXPLAIN'			=> 'Saisissez ici un nom d’utilisateur afin de définir cet utilisateur comme le propriétaire. En modifiant cela, vous serez considéré comme un ancien contributeur.',
	'CONTRIB_CHANGE_OWNER_NOT_FOUND'		=> 'L’utilisateur, %s, que vous souhaitez définir comme propriétaire, est introuvable.',
	'CONTRIB_CLEANED'						=> 'Nettoyé',
	'CONTRIB_CONFIRM_OWNER_CHANGE'			=> 'Êtes-vous sûr de vouloir définir la propriété à %s ? Vous ne pourrez plus gérer le projet et cette action est irréversible.',
	'CONTRIB_CREATED'						=> 'La contribution a été créée avec succès',
	'CONTRIB_DESCRIPTION'					=> 'Description de la contribution',
	'CONTRIB_DETAILS'						=> 'Informations sur la contribution',
	'CONTRIB_DISABLED'						=> 'Invisible + désactivé',
	'CONTRIB_DOWNLOAD_DISABLED'				=> 'Téléchargements désactivés',
	'CONTRIB_EDITED'						=> 'La contribution a été éditée avec succès.',
	'CONTRIB_HIDDEN'						=> 'Invisible',
	'CONTRIB_ISO_CODE'						=> 'Code ISO',
	'CONTRIB_ISO_CODE_EXPLAIN'				=> 'Le code ISO est défini selon les <a href="http://area51.phpbb.com/docs/coding-guidelines.html#translation">directives de codage des traductions</a>.',
	'CONTRIB_LOCAL_NAME'					=> 'Nom local',
	'CONTRIB_LOCAL_NAME_EXPLAIN'			=> 'Le nom national de la langue, comme <em>English</em>.',
	'CONTRIB_NAME'							=> 'Nom de la contribution',
	'CONTRIB_NAME_EXISTS'					=> 'Ce nom a déjà été reservé.',
	'CONTRIB_NEW'							=> 'Nouveau',
	'CONTRIB_NONACTIVE_AUTHORS'				=> 'Anciens contributeurs',
	'CONTRIB_NONACTIVE_AUTHORS_EXPLAIN'		=> 'Les anciens contributeurs ne peuvent rien gérer et sont uniquement listés afin de les remercier de leurs contributions passées.',
	'CONTRIB_NOT_FOUND'						=> 'La contribution demandée est introuvable.',
	'CONTRIB_OWNER_UPDATED'					=> 'Le propriétaire a été modifié.',
	'CONTRIB_PERMALINK'						=> 'Permalien de la contribution',
	'CONTRIB_PERMALINK_EXPLAIN'				=> 'Correspond à la version nettoyée du nom de contribution, qui est utilisée afin de construire construire l’adresse de la contribution.<br /><strong>Laissez ce champ vide afin d’obtenir une adresse automatique, basée sur le nom de la contribution.</strong>',
	'CONTRIB_RELEASE_DATE'					=> 'Date de sortie',
	'CONTRIB_STATUS'						=> 'Statut de la contribution',
	'CONTRIB_STATUS_EXPLAIN'				=> 'Modifier le statut de la contribution',
	'CONTRIB_TYPE'							=> 'Type de contribution',
	'CONTRIB_UPDATED'						=> 'La contribution a été mise à jour avec succès.',
	'CONTRIB_UPDATE_DATE'					=> 'Dernière mise à jour',
	'COULD_NOT_FIND_ROOT'					=> 'Le répertoire principal est introuvable. Veuillez vérifier si le fichier « install.xml » est présent dans le contenu de votre archive compressée.',
	'COULD_NOT_FIND_USERS'					=> 'Les utilisateurs suivants sont introuvables : %s',
	'COULD_NOT_OPEN_MODX'					=> 'Impossible d’ouvrir le fichier MODX.',
	'CO_AUTHORS'							=> 'Co-auteurs',

	'DELETE_CONTRIBUTION'					=> 'Supprimer la contribution',
	'DELETE_CONTRIBUTION_EXPLAIN'			=> 'Supprime définitivement cette contribution (utilisez le champ du statut de la contribution si vous souhaitez seulement la masquer).',
	'DELETE_REVISION'						=> 'Supprimer la révision',
	'DELETE_REVISION_EXPLAIN'				=> 'Supprime définitivement cette révision (utilisez le champ du statut de la révision si vous souhaitez seulement la masquer).',
	'DEMO_URL'								=> 'Lien vers la démonstration',
	'DEMO_URL_EXPLAIN'						=> 'Emplacement de la démonstration',
	'DOWNLOADS_PER_DAY'						=> '%.2f téléchargement(s) par jour',
	'DOWNLOADS_TOTAL'						=> 'Téléchargements',
	'DOWNLOADS_VERSION'						=> 'Nombre de téléchargements de la version actuelle',
	'DOWNLOAD_CHECKSUM'						=> 'Empreinte MD5',
	'DUPLICATE_AUTHORS'						=> 'Les auteurs suivants sont listés à la fois comme actifs et inactifs (cela doit être modifié) : %s',

	'EDIT_REVISION'							=> 'Éditer la révision',
	'EMPTY_CATEGORY'						=> 'Sélectionnez au moins une catégorie',
	'EMPTY_CONTRIB_DESC'					=> 'Saisissez la description de la contribution',
	'EMPTY_CONTRIB_ISO_CODE'				=> 'Saisissez le code ISO',
	'EMPTY_CONTRIB_LOCAL_NAME'				=> 'Saisissez le nom local',
	'EMPTY_CONTRIB_NAME'					=> 'Saisissez le nom de la contribution',
	'EMPTY_CONTRIB_PERMALINK'				=> 'Saisissez votre proposition concernant le permalien de la contribution',
	'EMPTY_CONTRIB_TYPE'					=> 'Sélectionnez au moins un type de contribution',
	'ERROR_CONTRIB_EMAIL_FRIEND'			=> 'Vous n’êtes pas autorisé à recommander cette contribution à quelqu’un d’autre.',

	'INSTALL_LESS_THAN_1_MINUTE'			=> 'Il y a moins d’une minute',
	'INSTALL_LEVEL'							=> 'Niveau d’installation',
	'INSTALL_LEVEL_1'						=> 'Facile',
	'INSTALL_LEVEL_2'						=> 'Intermédiaire',
	'INSTALL_LEVEL_3'						=> 'Avancé',
	'INSTALL_MINUTES'						=> 'Environ %s minute(s)',
	'INSTALL_TIME'							=> 'Durée d’installation',
	'INVALID_LICENSE'						=> 'La licence est incorrecte',
	'INVALID_PERMALINK'						=> 'Vous devez saisir un permalien correct, comme %s',

	'LICENSE'								=> 'Licence',
	'LICENSE_EXPLAIN'						=> 'La licence sous laquelle cette contribution est soumise.',
	'LICENSE_FILE_MISSING'					=> 'L’archive doit contenir un fichier « license.txt » contenant les termes de la licence qui doit être situé dans le répertoire principal ou dans un sous-répertoire du répertoire principal.',
	'LOGIN_EXPLAIN_CONTRIB'					=> 'Vous devez être inscrit et connecté afin de créer une nouvelle contribution',

	'MANAGE_CONTRIBUTION'					=> 'Gérer la contribution',
	'MPV_RESULTS'							=> '<strong>Veuillez vérifier les résultats de MPV et vous assurer que rien ne doit être corrigé.<br /><br />Si une erreur survient mais que vous êtes certain que cette dernière peut être ignorée, appuyez sur le bouton « Continuer » ci-dessous.</strong>',
	'MPV_TEST'								=> 'Le MOD sera testé au travers de MPV et les résultats seront affichés (cela peut prendre un certain temps, soyez patient).<br /><br />Appuyez sur le bouton « Continuer » lorsque vous êtes prêt.',
	'MPV_TEST_FAILED'						=> 'Le test automatisé de MPV a échoué et les résultats de votre test sont indiposnibles.  Veuillez nous excuser, puis continuer.',
	'MPV_TEST_FAILED_QUEUE_MSG'				=> 'Le test automatisé de MPV a échoué.  [url=%s]Cliquez ici afin de lancer de nouveau le test automatisé de MPV[/url]',
	'MUST_SELECT_ONE_VERSION'				=> 'Vous devez sélectionner au moins une version de phpBB.',

	'NEW_CONTRIBUTION'						=> 'Nouvelle contribution',
	'NEW_REVISION'							=> 'Nouvelle révision',
	'NEW_REVISION_SUBMITTED'				=> 'La nouvelle révision a été soumise avec succès.',
	'NEW_TOPIC'								=> 'Nouveau sujet',
	'NOT_VALIDATED'							=> 'Non validé',
	'NO_CATEGORY'							=> 'La catégorie que vous avez sélectionnée n’existe pas',
	'NO_PHPBB_BRANCH'						=> 'Vous devez sélectionner une branche de phpBB.',
	'NO_QUEUE_DISCUSSION_TOPIC'				=> 'Aucun sujet de discussion de la file d’attente n’a été trouvé.  Avez-vous déjà soumis une révision concernant cette contribution ?  La discussion de la file d’attente sera automatiquement créée lors de cette opération.',
	'NO_REVISIONS'							=> 'Aucune révision',
	'NO_REVISION_ATTACHMENT'				=> 'Veuillez sélectionner un fichier à transférer',
	'NO_REVISION_VERSION'					=> 'Veuillez saisir la version de cette révision',
	'NO_SCREENSHOT'							=> 'Aucune capture d’écran',
	'NO_TRANSLATION'						=> 'L’archive de langue semble incorrecte. Veuillez vous assurer qu’elle ne contient que les fichiers présents dans l’archive de langue anglaise par défaut.',

	'PHPBB_BRANCH'							=> 'Branche de phpBB',
	'PHPBB_BRANCH_EXPLAIN'					=> 'Sélectionnez la branche de phpBB qui est compatible avec cette révision.',
	'PHPBB_VERSION'							=> 'Version(s) de phpBB',

	'QUEUE_ALLOW_REPACK'					=> 'Autoriser le repaquetage',
	'QUEUE_ALLOW_REPACK_EXPLAIN'			=> 'Autorisez-vous à repaqueter cette contribution en cas d’erreurs mineures ?',
	'QUEUE_NOTES'							=> 'Notes de validation',
	'QUEUE_NOTES_EXPLAIN'					=> 'Message adressé à l’équipe.',

	'REPORT_CONTRIBUTION'					=> 'Rapporter la contribution',
	'REPORT_CONTRIBUTION_CONFIRM'			=> 'Utilisez ce formulaire afin de rapporter la contribution concernée aux modérateurs et aux administrateurs. Vous ne devriez utiliser cette fonctionnalité que lorsque la contribution ne respecte pas les règles du forum.',
	'REVISION'								=> 'Révision',
	'REVISIONS'								=> 'Révisions',
	'REVISION_APPROVED'						=> 'Approuvé',
	'REVISION_DENIED'						=> 'Refusé',
	'REVISION_IN_QUEUE'						=> 'Une révision est déjà présente dans la file d’attente de validation.  Vous devez attendre que la précédente révision soit approuvée ou refusée afin d’en envoyer une nouvelle.',
	'REVISION_NAME'							=> 'Nom de la révision',
	'REVISION_NAME_EXPLAIN'					=> 'Saisissez un nom optionnel concernant cette version (comme « Édition Peluche »)',
	'REVISION_NEW'							=> 'Nouvelle',
	'REVISION_PENDING'						=> 'En attente',
	'REVISION_PULLED_FOR_OTHER'				=> 'Retiré',
	'REVISION_PULLED_FOR_SECURITY'			=> 'Retiré pour des raisons de securité',
	'REVISION_REPACKED'						=> 'Repaqueté',
	'REVISION_RESUBMITTED'					=> 'Resoumis',
	'REVISION_STATUS'						=> 'Statut de la révision',
	'REVISION_STATUS_EXPLAIN'				=> 'Modifier le statut de la révision',
	'REVISION_SUBMITTED'					=> 'La révision a été soumise avec succès.',
	'REVISION_VERSION'						=> 'Version de la révision',
	'REVISION_VERSION_EXPLAIN'				=> 'Le numéro de version de cette archive',

	'SCREENSHOTS'							=> 'Captures d’écran',
	'SELECT_CONTRIB_TYPE'					=> '-- Sélectionner un type de contribution --',
	'SELECT_PHPBB_BRANCH'					=> 'Sélectionner une branche de phpBB',
	'SUBDIRECTORY_LIMIT'					=> 'Les archives ne doivent pas contenir plus de 50 sous-répertoires.',
	'SUBMIT_NEW_REVISION'					=> 'Soumettre et ajouter une nouvelle révision',

	'TOO_MANY_TRANSLATOR_LINKS'				=> 'Vous utilisez actuellement %d liens externes dans ligne TRANSLATION/TRANSLATION_INFO. Vous êtes limité à <strong>un lien</strong>. Il est possible d’insérer deux liens au cas par cas.  Si vous souhaitez avoir cette possibilité, veuillez publier un sujet dans le forum des traductions en indiquant le plus clairement et honnêtement possible les raisons de votre requête.',

	'VALIDATION_TIME'						=> 'Date de validation',
	'VIEW_DEMO'								=> 'Consulter la démonstration',
	'VIEW_INSTALL_FILE'						=> 'Consulter le fichier d’installation',

	'WRONG_CATEGORY'						=> 'Vous ne pouvez mettre cette contribution que dans une catégorie de même type.',
));
