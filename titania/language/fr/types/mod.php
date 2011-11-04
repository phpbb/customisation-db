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
	'MODIFICATION'						=> 'Modification',
	'MODIFICATIONS'						=> 'Modifications',
	'MOD_CREATE_PUBLIC'					=> '[b]Nom de la modification[/b] : %1$s
[b]Auteur[/b] : [url=%2$s]%3$s[/url]
[b]Description[/b] : %4$s
[b]Version[/b] : %5$s
[b]Testé sur la version de phpBB[/b] : %11$s

[b]Télécharger le fichier[/b] : [url=%6$s]%7$s[/url]
[b]Taille du fichier[/b] : %8$s octets

[b]Page de présentation de la modification[/b] : [url=%9$s]consulter[/url]

[color=blue][b]L’équipe de phpBB.com n’est ni responsable, ni dans l’obligation de fournir du support pour ce MOD. En installant ce MOD, vous acceptez que l’équipe de support ou l’équipe des modifications n’est pas dans l’obligation de vous fournir du support.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Support de la modification[/b]&lt;--[/url][/size]',
	'MOD_QUEUE_TOPIC'					=> '[b]Nom de la modification[/b] : %1$s
[b]Auteur[/b] : [url=%2$s]%3$s[/url]
[b]Description[/b] : %4$s
[b]Version[/b] : %5$s

[b]Télécharger le fichier[/b] : [url=%6$s]%7$s[/url]
[b]Taille du fichier[/b] : %8$s octets',
	'MOD_REPLY_PUBLIC'					=> '[b][color=darkred]Modification approuvée[/color][/b]',
	'MOD_REPLY_PUBLIC_NOTES'			=> '

[b]Notes[/b] : %s',
	'MOD_UPDATE_PUBLIC'					=> '[b][color=darkred]La modification a été mise à jour à la version %1$s
Veuillez consulter le premier message afin d’obtenir le lien de téléchargement[/color][/b]',
	'MOD_UPDATE_PUBLIC_NOTES'			=> '

[b]Notes[/b] : %1$s',
	'MOD_UPLOAD_AGREEMENT'				=> '<span style="font-size: 1.5em;">En soumettant cette révision, vous vous engagez à respecter notre <a href="http://www.phpbb.com/mods/policies/">politique concernant la base de données des modifications</a> et à ce que votre modification soit conforme aux <a href="http://code.phpbb.com/svn/phpbb/branches/phpBB-3_0_0/phpBB/docs/coding-guidelines.html">directives de codage de phpBB3</a>.

Vous vous engagez également à ce que la licence de votre modification et de tous ses composants soient compatibles avec la <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU GPLv2</a> et que votre modification soit redistribué indéfiniement à travers de ce site. Si vous souhaitez obtenir la liste des licences disponibles et compatibles avec la GNU GPLv2, veuillez <a href="http://en.wikipedia.org/wiki/List_of_FSF_approved_software_licenses">consulter cette page</a>.</span>',
	'MOD_VALIDATION'					=> '[Validation de MOD pour phpBB] %1$s %2$s',
	'MOD_VALIDATION_MESSAGE_APPROVE'	=> 'Nous vous remercions d’avoir soumis votre modification dans la base de données de phpBB.com. Après un examen approfondi, votre modification a été approuvée et publiée dans notre base de données des contributions.

Nous espérons que vous fournirez un minimum de support concernant cette modification et que vous la maintiendrez à jour avec les futures versions de phpBB. Nous sommes reconnaissants de votre travail et de votre contribution à la communauté. Vous participez à faire de phpBB.com un endroit plus riche.

[b]Notes de l’équipe concernant votre modification :[/b]
[quote]%s[/quote]

Cordialement,
L’équipe des MODs de phpBB.com.',
	'MOD_VALIDATION_MESSAGE_DENY'		=> 'Bonjour,

Comme vous le savez, toutes les modifications soumises à notre base de données doivent être validées et approuvées par des membres de notre équipe.

Malheureusement, nous tenons à vous informer que suite à la validation de votre modification, nous avons décidés de la refuser.

Pour corriger le(s) problème(s) rencontré(s) avec votre modification, veuillez suivre les instructions ci-dessous :
[list=1][*]Effectuez les modifications nécessaires afin de corriger le(s) problème(s) responsable(s) du refus de votre modification (veuillez consulter le rapport de validation ci-dessous).
[*]Testez votre MOD, le fichier XML et son installation.
[*]Transférez à nouveau votre modification dans notre base de données des contributions.[/list]
Assurez-vous d’avoir testé votre modification sur la dernière version de phpBB avant de la soumettre à nouveau (veuillez consulter la [url=http://www.phpbb.com/downloads/]page des téléchargements[/url]).

Voici le rapport de validation qui indique les raisons du refus de votre modification :
[quote]%s[/quote]

Veuillez vous référer aux liens suivants avant de transférer à nouveau votre modification :
[list]
[*][url=http://www.phpbb.com/mods/modx/]Informations sur MODX[/url]
[*][b]Sécurisation des MODs :[/b]
[url=http://blog.phpbb.com/2009/02/12/injection-vulnerabilities/]Prévention des vulnérabilités d’injection[/url]
[url=http://blog.phpbb.com/2009/09/10/how-not-to-use-request_var/]Comment (ne pas) utiliser request_var[/url]
[/list]

Pour plus de renseignements, vous pouvez être intéressé par les liens suivants :
[list][*][url=http://www.phpbb.com/mods/faq/]Foire aux questions des modifications[/url]
[*][url=http://www.phpbb.com/kb/3.0/modifications/]Catégorie des modifications de la base de connaissances de phpBB3[/url][/list]

Si vous souhaitez obtenir une aide dans le codage de MODs pour phpBB, les ressources suivantes peuvent vous intéresser :
[list][*][url=http://www.phpbb.com/community/viewforum.php?f=71]Forum d’assistance des auteurs de MODs[/url]
[*]Support IRC - [url=irc://irc.freenode.net/phpBB-coding]#phpBB-coding[/url] est enregistré sur le réseau IRC FreeNode ([url=irc://irc.freenode.net/]irc.freenode.net[/url])[/list]

[b]Si vous souhaitez donner votre avis sur quoi que ce soit dans ce message privé, veuillez envoyer un message en utilisant l’onglet de discussion dans la base de données des MODs.[/b]

Si vous souhaitez contester ce refus, veuillez contacter le responsable de l’équipe des MODs.
Si vous avez des questions ou que vous souhaitez vous entretenir avec les validateurs, veuillez utiliser le sujet de discussion de la file d’attente.

En vous remerciant,
L’équipe des MODs de phpBB.com.',
));
