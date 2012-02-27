<?php
/**
*
* acp_mods [French]
*
* @package language
* @version 1.0.1-dev
* @copyright (c) 2008 phpBB Group, (c) 2011 phpBB.fr
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License 2.0
*
*/
/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE 
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine


$lang = array_merge($lang, array(
	'ADDITIONAL_CHANGES'	=> 'Modifications disponibles',

	'AM_MOD_ALREADY_INSTALLED'	=> 'AutoMOD a détecté que ce MOD est déjà installé et ne peut donc pas effectuer cette opération.',
	'AM_MANUAL_INSTRUCTIONS'	=> 'AutoMOD est en train d’envoyer un fichier compressé à votre ordinateur. Du fait de la configuration d’AutoMOD, les fichiers ne peuvent pas être écrits automatiquement sur votre site. Vous devrez extraire le fichier et transférer manuellement les fichiers sur votre serveur, en utilisant un client FTP ou une méthode similaire. Si vous ne recevez pas ce fichier automatiquement, veuillez cliquer %sici%s.',

	'APPLY_THESE_CHANGES'	=> 'Appliquer ces modifications',
	'APPLY_TEMPLATESET'		=> 'à ce template',
	'AUTHOR_EMAIL'			=> 'Adresse de courrier électronique de l’auteur',
	'AUTHOR_INFORMATION'	=> 'Informations sur l’auteur',
	'AUTHOR_NAME'			=> 'Nom de l’auteur',
	'AUTHOR_NOTES'			=> 'Notes de l’auteur',
	'AUTHOR_URL'			=> 'Lien de l’auteur',
	'AUTOMOD'				=> 'AutoMOD',
	'AUTOMOD_CANNOT_INSTALL_OLD_VERSION'	=> 'La version d’AutoMOD que vous essayez d’installer a déjà été installée. Veuillez supprimer ce répertoire install/.',
	'AUTOMOD_UNKNOWN_VERSION'	=>	'AutoMOD n’a pas pu être mis à jour car il n’a pas été possible de déterminer la version qui est actuellement installée. La version listée de votre installation est la %s.',
	'AUTOMOD_VERSION'		=> 'Version d’AutoMOD',

	'CAT_INSTALL_AUTOMOD'	=> 'AutoMOD',
	'CHANGE_DATE'	=> 'Date de sortie',
	'CHANGE_VERSION'=> 'Numéro de version',
	'CHANGES'		=> 'Modifications',
	'CHECK_AGAIN'  => 'Vérifier de nouveau',
	'CLICK_HIDE_FILES' => 'Cliquez ici afin de masquer les fichiers ne comportant pas d’erreur',
	'CLICK_HIDE_EDITS' => 'Cliquez ici afin de masquer les modifications ne comportant pas d’erreur',
	'CLICK_SHOW_FILES' => 'Cliquez ici afin d’afficher tous les fichiers',
	'CLICK_SHOW_EDITS' => 'Cliquez ici afin d’afficher toutes les modifications',
	'COMMENT'		=> 'Commentaire',
	'CREATE_TABLE'	=> 'Modifications de la base de données',
	'CREATE_TABLE_EXPLAIN'	=> 'AutoMOD a réalisé avec succès ses modifications de la base de données, en incluant une permission qui a été assignée au rôle d’« administrateur aux pleins pouvoirs ».',
	'DELETE'			=> 'Supprimer',
	'DELETE_CONFIRM'	=> 'Êtes-vous sûr de vouloir supprimer ce MOD ?',
	'DELETE_ERROR'		=> 'Une erreur est survenue lors de la suppression du MOD que vous avez sélectionné.',
	'DELETE_SUCCESS'	=> 'Le MOD a été supprimé avec succès.',

	'DIR_PERMS'			=> 'Permissions du répertoire',
	'DIR_PERMS_EXPLAIN'	=> 'Certains systèmes demandent que des répertoires détiennent certaines permissions afin de fonctionner correctement. Normalement, la permission par défaut 0755 est correcte. Ce réglage n’a aucun impact sur les systèmes Windows.',
	'DIY_INSTRUCTIONS'	=> 'Instructions à effectuer manuellement (vous devez suivre ces instructions manuelles afin de terminer l’installation du MOD)',
	'DEPENDENCY_INSTRUCTIONS'	=>	'Le MOD que vous essayez d’installer dépend d’un autre MOD. AutoMOD ne peut pas détecter si ce MOD a déjà été installé. Veuillez vérifier que vous avez bien installé <strong><a href="%1$s">%2$s</a></strong> avant de commencer l’installation de votre MOD.',
	'DESCRIPTION'	=> 'Description',
	'DETAILS'		=> 'Informations',

	'EDITED_ROOT_CREATE_FAIL'	=> 'AutoMOD n’a pas réussi à installer le répertoire dans lequel les fichiers édités seront stockés.',
	'ERROR'			=> 'Erreur',

	'FILE_EDITS'		=> 'Modifications du fichier',
	'FILE_EMPTY'		=> 'Fichier vide',
	'FILE_MISSING'		=> 'Impossible de localiser le fichier',
	'FILE_PERMS'		=> 'Permissions du fichier',
	'FILE_PERMS_EXPLAIN'=> 'Certains systèmes demandent que des fichiers détiennent certaines permissions afin de fonctionner correctement. Normalement, la permission par défaut 0644 est correcte. Ce réglage n’a aucun impact sur les systèmes Windows.',
	'FILE_TYPE'			=> 'Type de fichier compressé',
	'FILE_TYPE_EXPLAIN'	=> 'Ceci n’est valide qu’avec la méthode d’écriture « téléchargement du fichier compressé »',
	'FILESYSTEM_NOT_WRITABLE'	=> 'AutoMOD a déterminé que le fichier système ne peut pas être écrit, la méthode d’écriture directe ne peut donc pas être utilisée.',
	'FIND'				=> 'Trouver',
	'FIND_MISSING'		=> 'L’opération « trouver », spécifiée par le MOD, est introuvable',
	'FORCE_INSTALL'		=> 'Forcer l’installation',
	'FORCE_UNINSTALL'	=> 'Forcer la désinstallation',
	'FORCE_CONFIRM'		=> 'La fonctionnalité permettant de forcer l’installation signifie que le MOD n’a pas été installé entièrement. Vous devrez réaliser manuellement quelques corrections à votre forum afin de terminer l’installation. Souhaitez-vous continuer ?',
	'FTP_INFORMATION'	=> 'Informations FTP',
	'FTP_NOT_USABLE'  => 'La fonction FTP ne peut pas être utilisée tant qu’elle est désactivée par votre hébergeur.',
	'FTP_METHOD_ERROR' => 'Aucune méthode FTP n’a été trouvée. Veuillez vérifier dans la configuration d’AutoMOD si une méthode FTP a bien été réglée correctement.',
	'FTP_METHOD_EXPLAIN'=> 'Si vous rencontrez des problèmes avec la méthode par défaut « FTP », vous devriez essayer « simple socket » comme solution alternative afin de vous connecter au serveur FTP.',
	'FTP_METHOD_FTP'	=> 'FTP',
	'FTP_METHOD_FSOCK'	=> 'Simple socket',

	'GO_PHP_INSTALLER'  => 'Le MOD a besoin d’un installeur externe afin de terminer l’installation. Cliquez ici afin de continuer vers cette étape.',

	'INHERIT_NO_CHANGE'	=> 'Aucune modification n’a pu être apportée à ce fichier car le template %1$s dépend de %2$s.',
	'INLINE_FIND_MISSING'=> 'L’opération « dans la ligne, trouver », spécifiée par le MOD, est introuvable.',
	'INLINE_EDIT_ERROR'	=> 'Erreur. Une modification « dans la ligne » présente dans le fichier d’installation MODX ne dispose pas de tous les éléments obligatoires.',
	'INSTALL_AUTOMOD'	=> 'Installation d’AutoMOD',
	'INSTALL_AUTOMOD_CONFIRM'	=> 'Êtes-vous sûr de vouloir installer AutoMOD ?',
	'INSTALL_TIME'		=> 'Durée d’installation',
	'INSTALL_MOD'		=> 'Installer ce MOD',
	'INSTALL_ERROR'		=> 'Une ou plusieurs actions d’installations ont échouées. Veuillez revoir les actions listées ci-dessous, apporter quelques ajustements, puis réessayer. Vous pouvez poursuivre l’installation même si certaines des actions ont échouées. <strong>Ceci n’est pas recommandé et peut provoquer un dysfonctionnement de votre forum.</strong>',
	'INSTALL_FORCED'	=> 'Vous avez forcé l’installation de ce MOD alors que certaines erreurs sont survenues lors de l’installation du MOD. Votre forum peut être défaillant. Veuillez prendre note des actions qui ont échouées ci-dessous et les corriger.',
	'INSTALLED'			=> 'MOD installé',
	'INSTALLED_EXPLAIN'	=> 'Votre MOD a été installé ! Vous pouvez consulter ici certains résultats de l’installation. Veuillez noter chaque erreur et rechercher du support sur <a href="http://www.phpbb.com">phpBB.com</a> ou sur <a href="http://www.phpbb.fr">phpBB.fr</a>',
	'INSTALLED_MODS'	=> 'MODs installés',
	'INSTALLATION_SUCCESSFUL'	=> 'AutoMOD a été installé avec succès. Vous pouvez à présent gérer les MODifications de phpBB par l’intermédiaire de l’onglet AutoMOD situé dans le panneau de contrôle d’administration.',
	'INVALID_MOD_INSTRUCTION'	=> 'Ce MOD détient une instruction invalide, ou une opération « dans la ligne, trouver » a échouée.',
	'INVALID_MOD_NO_FIND'       => 'Il manque au MOD un résultat de recherche concernant l’action ‘%s’',
	'INVALID_MOD_NO_ACTION'     => 'Il manque au MOD un résultat d’action concernant la recherche ‘%s’',

	'LANGUAGE_NAME'		=> 'Nom de la langue',

	'MANUAL_COPY'				=> 'Ne pas tenter de faire un copie',
	'MOD_CONFIG'				=> 'Configuration d’AutoMOD',
	'MOD_CONFIG_UPDATED'        => 'La configuration d’AutoMOD a été mise à jour.',
	'MOD_DETAILS'				=> 'Informations sur le MOD',
	'MOD_DETAILS_EXPLAIN'		=> 'Vous pouvez consulter ici toutes les informations connues sur le MOD que vous avez sélectionné.',
	'MOD_MANAGER'				=> 'AutoMOD',
	'MOD_NAME'					=> 'Nom du MOD',
	'MOD_OPEN_FILE_FAIL'		=> 'AutoMOD n’a pas été capable d’ouvrir %s.',
	'MOD_UPLOAD'				=> 'Transférer un MOD',
	'MOD_UPLOAD_EXPLAIN'		=> 'Vous pouvez transférer ici une archive compressée de MOD contenant les fichiers MODX nécessaires à l’installation. AutoMOD essaiera alors de décompresser le fichier et de le rendre installable.',
	'MOD_UPLOAD_INIT_FAIL'		=> 'Une erreur est survenue lors de l’initialisation du processus de transfert du MOD.',
	'MOD_UPLOAD_SUCCESS'		=> 'Le MOD a été transféré et préparé à l’installation.',
	'MOD_UPLOAD_UNRECOGNIZED'	=> 'La structure du répertoire de l’archive du MOD que vous avez transférée n’est pas reconnue.  Vérifiez si l’archive .zip que vous avez transférée est corrompue ou certains fichiers ou répertoires sont introuvables, ou contactez l’auteur du MOD.',
	'AUTOMOD_INSTALLATION'		=> 'Installation d’AutoMOD',
	'AUTOMOD_INSTALLATION_EXPLAIN'	=> 'Bienvenue sur l’installation d’AutoMOD. Vous allez avoir besoin de vos informations FTP si AutoMOD détecte que cette méthode est la meilleure afin d’écrire sur vos fichiers. Les résultats du test des obligations sont disponibles ci-dessous.',

	'MODS_CONFIG_EXPLAIN'		=> 'Vous pouvez sélectionner ici comment AutoMOD va ajuster vos fichiers. La méthode la plus fréquente est celle par téléchargement du fichier compressé. Les autres méthodes demandent des permissions supplémentaires sur le serveur.',
	'MODS_COPY_FAILURE'			=> 'Le fichier %s n’a pas pu être copié correctement. Veuillez vérifier vos permissions ou utiliser une méthode d’écriture alternative.',
	'MODS_EXPLAIN'				=> 'Vous pouvez gérer ici les MODs disponibles sur votre forum. AutoMOD vous permet de personnaliser votre forum en installant automatiquement les modifications créées par la communauté de phpBB. Pour plus d’informations concernant AutoMOD et les MODs, veuillez visiter le <a href="http://www.phpbb.com/mods">site officiel de phpBB</a> ou <a href="http://www.phpbb.fr/">sa communauté francophone</a>. Si vous souhaitez ajouter un MOD à cette liste, utilisez le formulaire disponible en bas de cette page. Autrement, vous pouvez décompresser et transférer les fichiers dans le répertoire /store/mods/ situé sur votre serveur.',
	'MODS_FTP_FAILURE'			=> 'AutoMOD n’a pas pu transférer correctement par FTP le fichier %s',
	'MODS_FTP_CONNECT_FAILURE'	=> 'AutoMOD n’a pas pu se connecter à votre serveur FTP. L’erreur survenue est : %s',
	'MODS_MKDIR_FAILED'			=> 'Le répertoire %s n’a pas pu être créé',
	'MODS_RMDIR_FAILURE'		=> 'Le répertoire %s n’a pas pu être supprimé',
	'MODS_RMFILE_FAILURE'		=> 'Le fichier %s n’a pas pu être supprimé',
	'MODS_NOT_WRITABLE'			=> 'Le répertoire store/mods/ n’est pas inscriptible.  Cela est obligatoire afin que le transfert de MOD puisse fonctionner correctement, à moins que vous ayez réglé la méthode d’écriture sur « FTP ».  Veuillez ajuster vos permissions ou vos réglages, puis réessayer.',
	'MODS_SETUP_INCOMPLETE'		=> 'Un problème est survenu avec votre configuration et AutoMOD n’a pas pu fonctionner. Cela ne peut se produire que si les réglages, comme le nom d’utilisateur FTP, ont été modifiés. Cela peut être corrigé à partir de la page de configuration d’AutoMOD.',

	'NAME'			=> 'Nom',
	'NEW_FILES'		=> 'Nouveaux fichiers',
	'NEED_READ_PERMISSIONS' => 'Permissions incorrectes : %s ne peut pas être lu.',
	'NO_ATTEMPT'	=> 'Ne pas tenter',
	'NO_INSTALLED_MODS'		=> 'Aucun MOD installé n’a été détecté',
	'NO_MOD'				=> 'Le MOD sélectionné n’a pas pu être trouvé.',
	'NO_UNINSTALLED_MODS'	=> 'Aucun MOD désinstallé n’a été détecté',
	'NO_UPLOAD_FILE'		=> 'Aucun fichier n’a été spécifié.',

	'ORIGINAL'	=> 'Original',

	'PATH'					=> 'Chemin',
	'PREVIEW_CHANGES'		=> 'Prévisualisation des modifications',
	'PREVIEW_CHANGES_EXPLAIN'	=> 'Affiche les modifications à réaliser avant de les exécuter.',
	'PRE_INSTALL'			=> 'Préparation à l’installation',
	'PRE_INSTALL_EXPLAIN'	=> 'Vous pouvez prévisualiser ici toutes les modifications à réaliser sur votre forum avant qu’elles ne soient exécutées. <strong>ATTENTION !</strong> Une fois acceptées, les fichiers originaux de phpBB seront édités et votre base de données peut subir des modifications. Cependant, si l’installation échoue mais qu’AutoMOD reste disponible, vous pourrez restaurer vos fichiers et votre base de données tels qu’ils l’étaient auparavant.',
	'PRE_UNINSTALL'			=> 'Préparation à la désinstallation',
	'PRE_UNINSTALL_EXPLAIN'	=> 'Vous pouvez prévisualiser ici toutes les modifications à réaliser sur votre forum afin de désinstaller le MOD. <strong>ATTENTION !</strong> Une fois acceptées, les fichiers originaux de phpBB seront édités et votre base de données peut subir des modifications. Ce processus utilise également des moyens de restauration qui ne fonctionnent pas à 100 %. Cependant, si la désinstallation échoue mais qu’AutoMOD reste disponible, vous pourrez restaurer vos fichiers et votre base de données tels qu’ils l’étaient auparavant.',

	'REMOVING_FILES'	=> 'Fichiers à supprimer',
	'RETRY'				=> 'Réessayer',
	'RETURN_MODS'		=> 'Retourner à AutoMOD',
	'REVERSE'			=> 'Restaurer',
	'ROOT_IS_READABLE'	=> 'La racine du répertoire de phpBB est lisible.',
	'ROOT_NOT_READABLE'	=> 'AutoMOD n’a pas pu ouvrir le fichier index.php de phpBB afin de le lire. Les permissions à la racine du répertoire de phpBB sont sans doute trop restrictives et ne permettent pas à AutoMOD de fonctionner correctement. Veuillez ajuster vos permissions, puis réessayer.',


	'SOURCE'		=> 'Source',
	'SQL_QUERIES'	=> 'Requêtes SQL',
	'STATUS'		=> 'Statut',
	'STORE_IS_WRITABLE'			=> 'Le répertoire store/ peut être écrit.',
	'STORE_NOT_WRITABLE_INST'	=> 'L’installation d’AutoMOD a détectée que le répertoire store/ ne peut pas être écrit. Ceci est obligatoire afin qu’AutoMOD fonctionne correctement. Veuillez ajuster vos permissions, puis réessayer.',
	'STORE_NOT_WRITABLE'		=> 'Le répertoire store/ ne peut pas être écrit.',
	'STYLE_NAME'	=> 'Nom du style',
	'SUCCESS'		=> 'Succès',

	'TARGET'		=> 'Cible',

	'UNKNOWN_MOD_AUTHOR-NOTES'	=> 'Aucun note de l’auteur n’a été spécifiée.',
	'UNKNOWN_MOD_DESCRIPTION'	=> '',
	'UNKNOWN_MOD_DIY-INSTRUCTIONS'=>'', // empty string hides this if not specified.
	'UNKNOWN_MOD_COMMENT'		=> '',
	'UNKNOWN_MOD_INLINE-COMMENT'=> '',
	'UNKNOWN_QUERY_REVERSE' => 'Requête de restauration inconnue',

	'UNINSTALL'				=> 'Désinstaller',
	'UNINSTALL_AUTOMOD'		=> 'Désinstallation d’AutoMOD',
	'UNINSTALL_AUTOMOD_CONFIRM' => 'Êtes-vous sûr de vouloir désinstaller AutoMOD ? Cela ne supprimera pas les MODs qui ont été installés avec AutoMOD.',
	'UNINSTALLED'			=> 'MOD désinstallé',
	'UNINSTALLED_MODS'		=> 'MODs désinstallés',
	'UNINSTALLED_EXPLAIN'	=> 'Votre MOD a été désinstallé ! Vous pouvez consulter ici certains résultats de la désinstallation. Veuillez noter chaque erreur et rechercher du support sur <a href="http://www.phpbb.com">phpBB.com</a> ou sur <a href="http://www.phpbb.fr">phpBB.fr</a>.',
	'UNRECOGNISED_COMMAND'	=> 'Erreur. Commande non reconnue : %s',
	'UPDATE_AUTOMOD'		=> 'Mettre à jour AutoMOD',
	'UPDATE_AUTOMOD_CONFIRM'=> 'Veuillez confirmer que vous souhaitez mettre à jour AutoMOD.',

	'UPLOAD'				=> 'Transférer',
	'VERSION'				=> 'Version',

	'WRITE_DIRECT_FAIL'		=> 'AutoMOD n’a pas pu copier le fichier %s en utilisant une méthode directe. Veuillez utiliser une méthode d’écriture alternative, puis réessayer.',
	'WRITE_DIRECT_TOO_SHORT'=> 'AutoMOD n’a pas pu terminer d’écrire sur le fichier %s. Cela peut se résoudre dans la plupart des cas en cliquant sur le bouton « réessayer ». Si cela ne fonctionne pas, veuillez essayer avec une méthode d’écriture alternative.',
	'WRITE_MANUAL_FAIL'		=> 'AutoMOD n’a pas pu ajouter le fichier %s à une archive compressée. Veuillez essayer avec une méthode d’écriture alternative.',
	'WRITE_METHOD'			=> 'Méthode d’écriture',
	'WRITE_METHOD_DIRECT'	=> 'Directe',
	'WRITE_METHOD_EXPLAIN'	=> 'Vous pouvez régler une méthode préférentielle afin d’écrire sur des fichiers. L’option la plus compatible est celle du « téléchargement du fichier compressé ».',
	'WRITE_METHOD_FTP'		=> 'FTP',
	'WRITE_METHOD_MANUAL'	=> 'Téléchargement du fichier compressé',

	// These keys for action names are purposely lower-cased and purposely contain spaces
	'after add'				=> 'Ajouter après',
	'before add'			=> 'Ajouter avant',
	'find'					=> 'Trouver',
	'in-line-after-add'		=> 'Dans la ligne, ajouter après',
	'in-line-before-add'	=> 'Dans la ligne, ajouter avant',
	'in-line-edit'			=> 'Dans la ligne, trouver',
	'in-line-operation'		=> 'Dans la ligne, incrémenter',
	'in-line-replace'		=> 'Dans la ligne, remplacer',
	'in-line-replace-with'	=> 'Dans la ligne, remplacer',
	'replace'				=> 'Remplacer par',
	'replace with'			=> 'Remplacer par',
	'operation'				=> 'Incrémenter',
));

?>