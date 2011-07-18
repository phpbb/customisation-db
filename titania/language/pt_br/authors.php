<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
* Tradução feita e revisada pela Equipe phpBB Brasil <http://www.phpbbrasil.com.br>!
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
	'AUTHOR_CONTRIBS'			=> 'Contribuições',
	'AUTHOR_DATA_UPDATED'		=> 'As informações do autor foram atualizadas.',
	'AUTHOR_DESC'				=> 'Descrição do autor',
	'AUTHOR_DETAILS'			=> 'Detalhes do autor',
	'AUTHOR_MODS'				=> '%d Modificações',
	'AUTHOR_MODS_ONE'			=> '1 Modificação',
	'AUTHOR_NOT_FOUND'			=> 'Autor não encontrado',
	'AUTHOR_PROFILE'			=> 'Perfil do autor',
	'AUTHOR_RATING'				=> 'Avaliação do autor',
	'AUTHOR_REAL_NAME'			=> 'Nome real',
	'AUTHOR_SNIPPETS'			=> '%d fragmentos',
	'AUTHOR_SNIPPETS_ONE'		=> '1 fragmento',
	'AUTHOR_STATISTICS'			=> 'Estatísticas do autor',
	'AUTHOR_STYLES'				=> '%d Estilos',
	'AUTHOR_STYLES_ONE'			=> '1 Estilo',
	'AUTHOR_SUPPORT'			=> 'Suporte',

	'ENHANCED_EDITOR'			=> 'Editor aprimorado',
	'ENHANCED_EDITOR_EXPLAIN'	=> 'Ativa/desativa o editor aprimorado (capta abas e automaticamente expande áreas de texto).',

	'MANAGE_AUTHOR'				=> 'Gerenciar autor',

	'NO_AVATAR'					=> 'Sem avatar',

	'PHPBB_PROFILE'				=> 'Perfil no phpBBrasil',

	'USER_INFORMATION'			=> 'Informações do usuário',

	'VIEW_USER_PROFILE'			=> 'Ver perfil do usuário',
));
