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
	'STYLE'								=> 'Style',
	'STYLES'							=> 'Styles',
	'STYLE_CREATE_PUBLIC'				=> '[b]Nom du style[/b] : %1$s
[b]Auteur[/b] : [url=%2$s]%3$s[/url]
[b]Description[/b] : %4$s
[b]Version[/b] : %5$s
[b]Testé sur la version de phpBB[/b] : %11$s

[b]Télécharger le fichier[/b] : [url=%6$s]%7$s[/url]
[b]Taille du fichier[/b] : %8$s octets

[b]Page de présentation du style[/b] : [url=%9$s]consulter[/url]

[color=blue][b]L’équipe de phpBB.com n’est ni responsable, ni dans l’obligation de fournir du support pour ce style. En installant ce style, vous acceptez que l’équipe de support ou l’équipe des styles n’est pas dans l’obligation de vous fournir du support.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Support du style[/b]&lt;--[/url][/size]',
	'STYLE_DEMO_INSTALL'				=> 'Installer sur le forum de démonstration des styles',
	'STYLE_QUEUE_TOPIC'					=> '[b]Nom du style[/b] : %1$s
[b]Auteur[/b] : [url=%2$s]%3$s[/url]
[b]Description[/b] : %4$s
[b]Version[/b] : %5$s

[b]Télécharger le fichier[/b] : [url=%6$s]%7$s[/url]
[b]Taille du fichier[/b] : %8$s octets',
	'STYLE_REPLY_PUBLIC'				=> '[b][color=darkred]Style approuvé[/color][/b]',
	'STYLE_REPLY_PUBLIC_NOTES'			=> '

[b]Notes: %s[/b]',
	'STYLE_UPDATE_PUBLIC'				=> '[b][color=darkred]Le style a été mis à jour à la version %1$s
Veuillez consulter le premier message afin d’obtenir le lien de téléchargement[/color][/b]',
	'STYLE_UPDATE_PUBLIC_NOTES'			=> '

[b]Notes[/b] : %1$s',
	'STYLE_UPLOAD_AGREEMENT'			=> '// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// \'Page %s of %s\' you can (and should) write \'Page %1$s of %2$s\', this allows
// translators to re-order the output of data while ensuring it remains correct',
	'STYLE_VALIDATION'					=> '[Validation de style pour phpBB] %1$s %2$s',
	'STYLE_VALIDATION_MESSAGE_APPROVE'	=> 'Nous vous remercions d’avoir soumis votre style dans la base de données de phpBB.com. Après un examen approfondi, votre style a été approuvé et publié dans notre base de données des contributions.

Nous espérons que vous fournirez un minimum de support concernant ce style et que vous le maintiendrez à jour avec les futures versions de phpBB. Nous sommes reconnaissants de votre travail et de votre contribution à la communauté. Vous participez à faire de phpBB.com un endroit plus riche.

[b]Notes de l’équipe concernant votre style :[/b]
[quote]%s[/quote]

Cordialement,
L’équipe des styles de phpBB.com.',
	'STYLE_VALIDATION_MESSAGE_DENY'		=> 'Bonjour,

Comme vous le savez, tous les styles soumis à notre base de données doivent être validés et approuvés par des membres de notre équipe.

Malheureusement, nous tenons à vous informer que suite à la validation de votre style, nous avons décidés de le refuser.

Si vous souhaitez soumettre à nouveau ce style à notre base de données des styles, veuillez vous assurer de corriger toutes les erreurs identifiées et de respecter notre [url=http://www.phpbb.com/community/viewtopic.php?t=988545]politique de soumission des styles[/url].

Si vous souhaitez contester ce refus, veuillez contacter le responsable de l’équipe des styles.

En vous remerciant,
L’équipe des styles de phpBB.com.',
));
