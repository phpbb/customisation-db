<?php
/**
*
* @package Support Toolkit - Fix Left/Right ID's
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
	'FIX_LEFT_RIGHT_IDS'			=> 'Corrigir ID’s das colunas a direita e a esquerda',
	'FIX_LEFT_RIGHT_IDS_CONFIRM'	=> 'Você realmente deseja corrigir as ID’s das colunas a direita e a esquerda?<br /><br /><strong>Faça um backup de sua base de dados antes de executar esta ferramenta!</strong>',

	'LEFT_RIGHT_IDS_FIX_SUCCESS'	=> 'As ID’s das colunas a direita e a esquerda foram corrigidas com sucesso.',
	'LEFT_RIGHT_IDS_NO_CHANGE'		=> 'A ferramenta terminou de analisar todas as id’s das colunas a direita e a esquerda e todas as linhas já estão corretas, portanto nenhuma alteração foi efetuada.',
));
