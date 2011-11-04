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
	'AUTHOR_CONVERTORS'						=> '%d convertisseurs',
	'AUTHOR_CONVERTORS_ONE'					=> '1 convertisseur',
	'CONVERTER'								=> 'Convertisseur',
	'CONVERTERS'							=> 'Convertisseurs',
	'CONVERTER_VALIDATION'					=> '[Validation de convertisseur pour phpBB] %1$s %2$s',
	'CONVERTER_VALIDATION_MESSAGE_APPROVE'	=> 'Nous vous remercions d’avoir soumis votre convertisseur dans la base de données de phpBB.com. Après un examen approfondi, votre convertisseur a été approuvé et publié dans notre base de données des contributions.

Nous espérons que vous fournirez un minimum de support concernant ce convertisseur et que vous le maintiendrez à jour avec les futures versions de phpBB. Nous sommes reconnaissants de votre travail et de votre contribution à la communauté. Vous participez à faire de phpBB.com un endroit plus riche.

[b]Notes de l’équipe concernant votre convertisseur :[/b]
[quote]%s[/quote]

Cordialement,
Les équipes de phpBB.com.',
	'CONVERTER_VALIDATION_MESSAGE_DENY'		=> 'Bonjour,

Comme vous le savez, tous les convertisseurs soumis à notre base de données doivent être validés et approuvés par des membres de notre équipe.

Malheureusement, nous tenons à vous informer que suite à la validation de votre convertisseur, nous avons décidés de le refuser.

Pour corriger le(s) problème(s) rencontré(s) avec votre convertisseur, veuillez suivre les instructions ci-dessous :
[list=1][*]Effectuez les modifications nécessaires afin de corriger le(s) problème(s) responsable(s) du refus de votre convertisseur (veuillez consulter le rapport de validation ci-dessous).
[*]Transférez à nouveau votre convertisseur dans notre base de données des contributions.[/list]
Assurez-vous d’avoir testé votre convertisseur sur la dernière version de phpBB avant de le soumettre à nouveau (veuillez consulter la [url=http://www.phpbb.com/downloads/]page des téléchargements[/url]).

Voici le rapport de validation qui indique les raisons du refus de votre convertisseur :
[quote]%s[/quote]

En vous remerciant,
Les équipes de phpBB.com.',
));
