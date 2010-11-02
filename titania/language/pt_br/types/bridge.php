<?php
/**
*
* @package Titania
* @version $Id: converter.php 1556 2010-06-15 00:25:31Z exreaction $
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	'BRIDGE'							=> 'Bridge',
	'BRIDGES'							=> 'Bridges',
	'BRIDGE_VALIDATION'					=> '[Validação de bridges do phpBB] %1$s %2$s',
	'BRIDGE_VALIDATION_MESSAGE_APPROVE'	=> 'Obrigado por enviar a sua bridge para o phpBB.com Customisation Database. Após uma cuidadosa inspeção sua bridge foi aprovada e lançada em nossa Customisation Database.

Temos esperança de que você fornecerá um nível básico de suporte para esta bridge e irá mantê-la atualizada com futuras versões do phpBB. Apreciamos seu trabalho e sua contribuição para a comunidade. Autores como você fazem o phpBB.com um melhor lugar para todos.

[b]Notas da equipe sobre sua bridge:[/b]
[quote]%s[/quote]

Atenciosamente,
Equipe phpBB',
	'BRIDGE_VALIDATION_MESSAGE_DENY'	=> 'Olá,

Como você deve saber, todas as bridges enviadas para a phpBB Customisation Database devem ser validadas e aprovadas por membros da equipe do phpBB.

Após a validação de sua bridge, a equipe do phpBB lamenta em informar que tivemos de recusá-la.

Para corrigir o(s) problema(s) com sua birdge, por favor siga as instruções abaixo:
[list=1][*]Faça as alterações necessárias para corrigir quaisquer problemas (listados abaixo) que resultaram na recusa de sua bridge.
[*]Envie novamente sua bridge para nossa Customisation Database.[/list]
Por favor, certifique-se de que tenha testado a bridge na versão mais recente do phpBB (veja a página de [url=http://www.phpbb.com/downloads/]Downloads[/url]) antes de enviar novamente a sua bridge.

Se você acha que esta recusa não possui justificativa, por favor contate o líder de desenvolvimento.

Aqui está um relatório sobre o porquê de sua bridge ter sido recusada:
[quote]%s[/quote]

Obrigado,
Equipe phpBB',
));
