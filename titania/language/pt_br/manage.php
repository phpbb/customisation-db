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
	'ADMINISTRATION'			=> 'Administração',
	'ALTER_NOTES'				=> 'Alterar notas de validação',
	'APPROVE'					=> 'Aprovar',
	'APPROVE_QUEUE'				=> 'Aprovar',
	'APPROVE_QUEUE_CONFIRM'		=> 'Você realmente deseja <strong>aprovar</strong> este item?',
	'ATTENTION'					=> 'Atenção',

	'CATEGORY_NAME_CLEAN'		=> 'URL da categoria',
	'CHANGE_STATUS'				=> 'Alterar estado/Mover',
	'CLOSED_ITEMS'				=> 'Itens trancados',

	'DELETE_QUEUE'				=> 'Remover entrada da fila',
	'DELETE_QUEUE_CONFIRM'		=> 'Você realmente deseja remover esta entrada da fila? Todas as mensagens da fila serão perdidas e a revisão será definida como pulled se for nova.',
	'DENY'						=> 'Rejeitar',
	'DENY_QUEUE'				=> 'Rejeitar',
	'DENY_QUEUE_CONFIRM'		=> 'Você realmente deseja <strong>rejeitar</strong> este item?',

	'EDIT_VALIDATION_NOTES'		=> 'Editar notas de validação',

	'MANAGE_CATEGORIES'			=> 'Gerenciar categorias',
	'MARK_IN_PROGRESS'			=> 'Marcar como "Em progresso"',
	'MARK_NO_PROGRESS'			=> 'Desmarcar como "Em progresso"',
	'MOVE_QUEUE'				=> 'Mover fila',
	'MOVE_QUEUE_CONFIRM'		=> 'Selecione o novo local da fila e confirme.',

	'NO_ATTENTION'				=> 'Nenhum item necessita de atenção.',
	'NO_ATTENTION_ITEM'			=> 'Não há itens que requeiram sua atenção.',
	'NO_ATTENTION_TYPE'			=> 'Tipo de atenção inadequado.',
	'NO_NOTES'					=> 'Sem notas',
	'NO_QUEUE_ITEM'				=> 'Item da fila não existe.',

	'OLD_VALIDATION_AUTOMOD'	=> 'Teste do AutoMOD de pré-reempacotamento',
	'OLD_VALIDATION_MPV'		=> 'Notas do PVM de pré-reempacotamento',
	'OPEN_ITEMS'				=> 'Abrir itens',

	'PUBLIC_NOTES'				=> 'Notas de lançamento público',

	'QUEUE_APPROVE'				=> 'Aguardando aprovação',
	'QUEUE_ATTENTION'			=> 'Atenção',
	'QUEUE_DENY'				=> 'Aguardando rejeição',
	'QUEUE_DISCUSSION_TOPIC'	=> 'Tópico de discussão da fila',
	'QUEUE_NEW'					=> 'Novo',
	'QUEUE_REPACK'				=> 'Reempacotar',
	'QUEUE_REPACK_ALLOWED'		=> 'Reempacotamento permitido',
	'QUEUE_REPACK_NOT_ALLOWED'	=> 'Reempacotamento <strong>não</strong> permitido',
	'QUEUE_REPLY_APPROVED'		=> 'Revisão %1$s [b]aprovada[/b] pela seguinte razão:<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_DENIED'		=> 'Revisão %1$s [b]rejeitada[/b] pela seguinte razão:<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_IN_PROGRESS'	=> 'Marcado como em progresso',
	'QUEUE_REPLY_MOVE'			=> 'Movido de %1$s para %2$s',
	'QUEUE_REPLY_NO_PROGRESS'	=> 'Desmarcado como em progresso',
	'QUEUE_REVIEW'				=> 'Revisão da fila',
	'QUEUE_STATUS'				=> 'Estado da fila',
	'QUEUE_TESTING'				=> 'Testando',
	'QUEUE_VALIDATING'			=> 'Validando',

	'REBUILD_FIRST_POST'		=> 'Refazer primeira mensagem',
	'REPACK'					=> 'Reempacotar',
	'REPORTED'					=> 'Reportado',
	'RETEST_AUTOMOD'			=> 'Re-testar com o AutoMOD',
	'RETEST_MPV'				=> 'Re-testar com o PVM',
	'REVISION_REPACKED'			=> 'Esta revisão foi reempacotada.',

	'SUBMIT_TIME'				=> 'Data de envio',

	'UNAPPROVED'				=> 'Não aprovada',
	'UNKNOWN'					=> 'Desconhecido',

	'VALIDATION'				=> 'Validação',
	'VALIDATION_AUTOMOD'		=> 'Teste com AutoMOD',
	'VALIDATION_MESSAGE'		=> 'Mensagem/razão de validação',
	'VALIDATION_MPV'			=> 'Notas do PVM',
	'VALIDATION_NOTES'			=> 'Notas de validação',
	'VALIDATION_QUEUE'			=> 'Fila de validação',
	'VALIDATION_SUBMISSION'		=> 'Envio de validação',
));
