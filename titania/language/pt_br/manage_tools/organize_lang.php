<?php
/**
*
* @package Support Tool Kit - Organize Language Files
* @version $Id$
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
* Tradução feita e revisada pela Equipe phpBB Brasil <http://www.phpbbrasil.com.br>!
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
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
	'NO_FILE'						=> 'O arquivo requisitado não existe.',

	'ORGANIZE_LANG'					=> 'Organizar arquivos de idioma',
	'ORGANIZE_LANG_EXPLAIN'			=> 'Isto lhe permite organizar um arquivo de idioma ou um diretório. Para mais informações <a href="http://www.lithiumstudios.org/forum/viewtopic.php?f=9&t=841">leia este tópico</a>.',
	'ORGANIZE_LANG_FILE'			=> 'Arquivo',
	'ORGANIZE_LANG_FILE_EXPLAIN'	=> 'Digite o nome do arquivo ou do diretório que você gostaria de organizar.<br />Exemplo: en/mods/ para language/en/mods/, ou en/common para language/en/common.php',
	'ORGANIZE_LANG_SUCCESS'			=> 'O arquivo de idioma ou o diretório foi organizado com sucesso.',
));
