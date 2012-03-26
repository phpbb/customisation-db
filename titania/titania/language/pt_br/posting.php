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
	'ACCESS'							=> 'Nível de acesso',
	'ACCESS_AUTHORS'					=> 'Acesso de autor',
	'ACCESS_PUBLIC'						=> 'Acesso público',
	'ACCESS_TEAMS'						=> 'Acesso de equipe',
	'ATTACH'							=> 'Anexar',

	'FILE_DELETED'						=> 'Este arquivo será removido quando você enviar',

	'HARD_DELETE_TOPIC_CONFIRM'			=> 'Você realmente deseja remover <strong>permanentemente</strong> este tópico?<br /><br />Este tópico será perdido para sempre!',

	'QUEUE_DISCUSSION_TOPIC_MESSAGE'	=> 'Este tópico é para discussão de validação entre os contribuidores e os validadores.

Qualquer coisa postada neste tópico será lida pelos responsáveis por validar sua contribuição, portanto, poste aqui ao invés de enviar mensagens privadas aos validadores.

A equipe de validação também pode enviar perguntas para os autores aqui, portanto, responda com informações úteis para eles, pois isto pode ser necessário para prosseguir com o processo de validação.

Note que por padrão este tópico é privado entre autores e validadores, e não pode ser visto pelo público.',
	'QUEUE_DISCUSSION_TOPIC_TITLE'		=> 'Discussão de validação - %s',

	'REPORT_POST_CONFIRM'				=> 'Utilize este formulário para reportar a mensagem selecionada para os moderadores e administradores do fórum. Reporte apenas se a mensagem infringir as regras do fórum.',

	'SOFT_DELETE_TOPIC_CONFIRM'			=> 'Você realmente deseja remover <strong>parcialmente</strong> este tópico?',
	'STICKIES'							=> 'Fixos',
	'STICKY_TOPIC'						=> 'Tópicos fixos',

	'UNDELETE_FILE'						=> 'Cancelar remoção',
	'UNDELETE_POST'						=> 'Restaurar mensagem',
	'UNDELETE_POST_CONFIRM'				=> 'Você realmente deseja restaurar esta mensagem?',
	'UNDELETE_TOPIC_CONFIRM'			=> 'Você realmente deseja restaurar este tópico?',
));
