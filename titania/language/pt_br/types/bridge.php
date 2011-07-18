<?php
/**
*
* @package Titania
* @version $Id: converter.php 1556 2010-06-15 00:25:31Z exreaction $
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'integração'							=> 'Integração',
	'integraçãoS'							=> 'Integrações',
	'integração_VALIDATION'					=> '[Validação de integrações do phpBB] %1$s %2$s',
	'integração_VALIDATION_MESSAGE_APPROVE'	=> 'Obrigado por enviar a sua integração para a Base de Customizações do phpBBrasil. Após uma cuidadosa inspeção sua integração foi aprovada e lançada em nossa Base de Customizações.

Temos esperança de que você fornecerá um nível básico de suporte para esta integração e irá mantê-la atualizada com futuras versões do phpBB. Apreciamos seu trabalho e sua contribuição para a comunidade. Autores como você fazem do phpBBrasil.com.br um melhor lugar para todos.

[b]Notas da equipe sobre sua integração:[/b]
[quote]%s[/quote]

Atenciosamente,
Equipe phpBBrasil',
	'integração_VALIDATION_MESSAGE_DENY'	=> 'Olá,

Como você deve saber, todas as integrações enviadas para a Base de Customizações do phpBBrasil devem ser validadas e aprovadas por membros da equipe do site.

Após a validação de sua integração, a equipe do phpBB lamenta em informar que tivemos de recusá-la.

Para corrigir o(s) problema(s) com sua integração, por favor siga as instruções abaixo:
[list=1][*]Faça as alterações necessárias para corrigir quaisquer problemas (listados abaixo) que resultaram na recusa de sua integração.
[*]Envie novamente sua integração para nossa Base de Customizações.[/list]
Por favor, certifique-se de que tenha testado a integração na versão mais recente do phpBB (veja a página de [url=http://www.phpbb.com/downloads/]Downloads[/url]) antes de enviar novamente a sua integração.

Se você acha que esta recusa não possui justificativa, por favor, contate o líder de desenvolvimento.

Aqui está um relatório sobre o porquê de sua integração ter sido recusada:
[quote]%s[/quote]

Obrigado,
Equipe phpBB',
));
