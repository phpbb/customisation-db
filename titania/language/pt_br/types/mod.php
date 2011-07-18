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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'MODIFICATION'						=> 'Modificação',
	'MODIFICATIONS'						=> 'Modificações',
	'MOD_CREATE_PUBLIC'					=> '[b]Nome da modificação[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descrição da modificação[/b]: %4$s
[b]Versão da modificação[/b]: %5$s
[b]Testado na versão do phpBB[/b]: Veja abaixo

[b]Download do arquivo[/b]: [url=%6$s]%7$s[/url]
[b]Tamanho do arquivo:[/b] %8$s Bytes

[b]Página de resumo da modificação:[/b] [url=%9$s]Ver[/url]

[color=blue][b]A equipe do phpBBrasil não é responsável nem obrigada a fornecer suporte para esta modificação. Ao instalar esta MOD, você reconhece que a equipe de suporte do phpBBrasil ou a equipe de modificações do phpBBrasil podem não ser capazes de fornecer ajuda.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Suporte à modificação[/b]&lt;--[/url][/size]',
	'MOD_QUEUE_TOPIC'					=> '[b]Nome da modificação[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descrição da modificação[/b]: %4$s
[b]Versão da modificação[/b]: %5$s

[b]Download do arquivo[/b]: [url=%6$s]%7$s[/url]
[b]Tamanho do arquivo:[/b] %8$s Bytes',
	'MOD_REPLY_PUBLIC'					=> '[b][color=darkred]Lançamento/validade da modificação[/color][/b]',
	'MOD_REPLY_PUBLIC_NOTES'			=> '

[b]Notas:[/b] %s',
	'MOD_UPDATE_PUBLIC'					=> '[b][color=darkred]MOD atualizada para a versão %1$s
Veja a primeira mensagem para encontrar o link de download[/color][/b]',
	'MOD_UPDATE_PUBLIC_NOTES'			=> '

[b]Notas:[/b] %1$s',
	'MOD_UPLOAD_AGREEMENT'				=> '<span style="font-size: 1.5em;">Ao enviar esta revisão você concorda em cumprir as  <a href="http://www.phpbb.com/mods/policies/">políticas da base de dados de MODificações</a> e que sua MOD obedece e segue as <a href="http://code.phpbb.com/svn/phpbb/branches/phpBB-3_0_0/phpBB/docs/coding-guidelines.html">diretrizes de codificação do phpBB3</a>.

Você também concorda e aceita que a licença desta MODificação e a licença de quaisquer componentes incluídos são compatíveis com a <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU GPLv2</a> e que você também permite a re-distribuição de sua MODificação através deste site indefinidamente. Para obter uma lista de licenças disponíveis e compatíveis com a GNU GPLv2, por favor, referencie-se pela <a href="http://en.wikipedia.org/wiki/List_of_FSF_approved_software_licenses">lista de licenças de softwares aprovadas pela FSF</a>.</span>',
	'MOD_VALIDATION'					=> '[Validação de MOD do phpBB] %1$s %2$s',
	'MOD_VALIDATION_MESSAGE_APPROVE'	=> 'Obrigado por enviar sua modificação para a Base de Customizações do phpBBrasil. Após uma cuidadosa inspeção sua modificação foi aprovada e lançada em nossa Base de Customizações.

Temos esperança de que você fornecerá um nível básico de suporte para esta modificação e irá mantê-la atualizada com futuras versões do phpBB. Apreciamos seu trabalho e sua contribuição para a comunidade. Autores como você fazem do phpBBrasil.com.br um melhor lugar para todos.

[b]Notas da equipe de modificações sobre sua modificação:[/b]
[quote]%s[/quote]

Atenciosamente,
Equipe de modificações do phpBB',
	'MOD_VALIDATION_MESSAGE_DENY'		=> 'Olá,

Como você deve saber, todas as modificações enviadas para a base de modificações do phpBBrasil devem ser validadas e aprovadas por membros da equipe do site.

Após a validação de sua modificação, a equipe de modificações do phpBBrasil lamenta informar que tivemos de recusá-la.

Para corrigir o(s) problema(s) com sua modificação, por favor siga as instruções abaixo:
[list=1][*]Faça as alterações necessárias para corrigir quaisquer problemas (listados abaixo) que resultaram na recusa de sua modificação.
[*]Teste sua MOD, o arquivo XML e a instalação dela.
[*]Envie novamente sua MOD para nossa base de modificações.[/list]
Por favor, certifique-se de que tenha testado a modificação na versão mais recente do phpBB (veja a página de [url=http://www.phpbb.com/downloads/]Downloads[/url]) antes de enviar novamente sua modificação.

Se você acha que esta recusa não possui justificativa, por favor, contate o líder de validação de MODs.

Aqui está um relatório sobre o porquê de sua modificação ter sido recusada:
[quote]%s[/quote]

Por favor, consulte os links a seguir antes de enviar novamente a sua modificação (em inglês):
[list]
[*][url=http://www.phpbb.com/mods/modx/]Padrões phpBB MODX[/url]
[*][b]Protegendo modificações:[/b]
[url=http://blog.phpbb.com/2009/02/12/injection-vulnerabilities/]Prevenindo vulnerabilidades[/url]
[url=http://blog.phpbb.com/2009/09/10/how-not-to-use-request_var/]Como (não) usar request_var[/url]
[/list]

Para leitura auxiliar, talvez você queira rever o seguinte (em inglês):
[list][*][url=http://www.phpbb.com/mods/faq/]FAQ de Modificações[/url]
[*][url=http://www.phpbb.com/kb/3.0/modifications/]Categoria de Modificações na base de conhecimentos do phpBB3[/url][/list]

Para auxílio ao escrever MODs para o phpBB, os seguintes recursos existem:
[list][*][url=http://www.phpbb.com/community/viewforum.php?f=71]Forum de ajuda para autores de MODs[/url]
[*]Suporte no IRC - [url=irc://irc.freenode.net/phpBB-coding]#phpBB-coding[/url] deve estar registrado na rede IRC FreeNode ([url=irc://irc.freenode.net/]irc.freenode.net[/url])[/list]

[b]Se desejar discutir qualquer coisa nesta MP, por favor, envie uma mensagem usando a aba discussão na Base de modificações, Minhas modificações, Gerenciar esta MOD.[/b] Se você acha que esta recusa não possui justificativa, por favor, contate o líder de validação de MODs.

Obrigado,
Equipe de modificações do phpBB',
));
