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
	'AUTHOR_LANGUAGE_PACKS'						=> '%d archives de langue',
	'AUTHOR_LANGUAGE_PACKS_ONE'					=> '1 archive de langue',
	'COULD_NOT_FIND_TRANSLATION_ROOT'			=> 'Impossible de localiser le répertoire racine de votre archive de langue. Assurez-vous que le répertoire <code>language/</code> et, éventuellement, le répertoire <code>styles/</code>, soient présents à la racine.',

	'MISSING_FILE'								=> 'Le fichier <code>%s</code> est manquant dans votre archive de langue',
	'MISSING_KEYS'								=> 'Les clés de langue suivantes sont manquantes dans <code>%1$s</code> :<br />%2$s',

	'PASSED_VALIDATION'							=> 'Votre archive de langue a passé le processus de validation qui vérifie les clés manquantes, le fichier de licence et qui effectue un repaquetage de votre traduction. Veuillez à présent continuer.',

	'TRANSLATION'								=> 'Traduction',
	'TRANSLATION_VALIDATION'					=> '[Validation de traduction pour phpBB] %1$s %2$s',
	'TRANSLATION_VALIDATION_MESSAGE_APPROVE'	=> 'Nous vous remercions d’avoir soumis votre traduction dans la base de données de phpBB.com. Après un examen approfondi, votre traduction a été approuvée et publiée dans notre base de données des contributions.

Nous espérons que vous fournirez un minimum de support concernant cette traduction et que vous la maintiendrez à jour avec les futures versions de phpBB. Nous sommes reconnaissants de votre travail et de votre contribution à la communauté. Vous participez à faire de phpBB.com un endroit plus riche.

[b]Notes du responsable des traductions et des communautés internationales de support concernant votre traduction :[/b]
[quote]%s[/quote]

Cordialement,
Les équipes de phpBB.com.',
	'TRANSLATION_VALIDATION_MESSAGE_DENY'		=> 'Bonjour,

Comme vous le savez, toutes les traductions soumises à notre base de données doivent être validées et approuvées par le responsable des traductions et des communautés internationales de support.

Malheureusement, je tiens à vous informer que suite à la validation de votre traduction, j’ai décidé de la refuser.

Pour corriger le(s) problème(s) rencontré(s) avec votre traduction, veuillez suivre les instructions ci-dessous :
[list=1][*]Effectuez les modifications nécessaires afin de corriger le(s) problème(s) responsable(s) du refus de votre traduction (veuillez consulter le rapport de validation ci-dessous).
[*]Transférez à nouveau votre traduction dans notre base de données des contributions.[/list]
Assurez-vous d’avoir testé votre traduction sur la dernière version de phpBB avant de la soumettre à nouveau (veuillez consulter la [url=http://www.phpbb.com/downloads/]page des téléchargements[/url]).

Si vous souhaitez contester ce refus, veuillez contacter le responsable des traductions et des communautés internationales de support.

Voici le rapport de validation qui indique les raisons du refus de votre traduction :
[quote]%s[/quote]

En vous remerciant,
Le responsable des traductions et des communautés internationales de support de phpBB.com.',
));
