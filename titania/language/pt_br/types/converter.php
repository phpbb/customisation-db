<?php
/**
*
* @package Titania
* @version $Id$
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
	'CONVERTER'								=> 'Conversor',
	'CONVERTERS'							=> 'Conversores',
	'CONVERTER_VALIDATION'					=> '[Validação de conversores do phpBB] %1$s %2$s',
	'CONVERTER_VALIDATION_MESSAGE_APPROVE'	=> 'Obrigado por enviar seu conversor para a phpBB.com Customisation Database. Após uma cuidadosa inspeção seu conversor foi aprovado e lançado em nossa Customisation Database.

Temos esperança de que você fornecerá um nível básico de suporte para este conversor e irá mantê-lo atualizado com futuras versões do phpBB. Apreciamos seu trabalho e sua contribuição para a comunidade. Autores como você fazem o phpBB.com um melhor lugar para todos.

[b]Notas da equipe sobre seu conversor:[/b]
[quote]%s[/quote]

Atenciosamente,
Equipe phpBB',
	'CONVERTER_VALIDATION_MESSAGE_DENY'		=> 'Olá,

Como você deve saber, todos os conversores enviados para a phpBB Customisation Database devem ser validadas e aprovadas por membros da equipe do phpBB.

Após a validação de seu conversor, a equipe do phpBB lamenta em informar que tivemos de recusá-la.

Para corrigir o(s) problema(s) com seu conversor, por favor siga as instruções abaixo:
[list=1][*]Faça as alterações necessárias para corrigir quaisquer problemas (listados abaixo) que resultaram na recusa de seu conversor.
[*]Envie novamente seu conversor para nossa Customisation Database.[/list]
Por favor, certifique-se de que tenha testado o conversor na versão mais recente do phpBB (veja a página de [url=http://www.phpbb.com/downloads/]Downloads[/url]) antes de enviar novamente o seu conversor.

Se você acha que esta recusa não possui justificativa, por favor contate o líder de desenvolvimento.

Aqui está um relatório sobre o porquê de seu conversor ter sido recusado:
[quote]%s[/quote]

Obrigado,
Equipe phpBB',
));
