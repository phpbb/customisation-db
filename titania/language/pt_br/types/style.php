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
	'STYLE'								=> 'Estilo',
	'STYLES'							=> 'Estilos',
	'STYLE_CREATE_PUBLIC'				=> '[b]Nome do estilo[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descrição do estilo[/b]: %4$s
[b]Versão do estilo[/b]: %5$s
[b]Testado na versão do phpBB[/b]: Veja abaixo

[b]Download do arquivo[/b]: [url=%6$s]%7$s[/url]
[b]Tamanho do arquivo:[/b] %8$s Bytes

[b]Página de resumo do estilo:[/b] [url=%9$s]View[/url]

[color=blue][b]A equipe do phpBB não é responsável nem obrigada a fornecer suporte a este estilo. Ao instalar esse estilo, você reconhece que a equipe de suporte do phpBB ou a equipe de estilos do phpBB podem não serem capazes de fornecer ajuda.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Suporte ao estilo[/b]&lt;--[/url][/size]',
	'STYLE_DEMO_INSTALL'				=> 'Instalar no fórum de demonstração de estilos',
	'STYLE_QUEUE_TOPIC'					=> '[b]Nome do estilo[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descrição do estilo[/b]: %4$s
[b]Versão do estilo[/b]: %5$s

[b]Download do arquivo[/b]: [url=%6$s]%7$s[/url]
[b]Tamanho do arquivo:[/b] %8$s Bytes',
	'STYLE_REPLY_PUBLIC'				=> '[b][color=darkred]Estilo validado/lançado[/color][/b]',
	'STYLE_REPLY_PUBLIC_NOTES'			=> '

[b]Notas: %s[/b]',
	'STYLE_UPDATE_PUBLIC'				=> '[b][color=darkred]Estilo atualizado para a versão %1$s
Veja a primeira mensagem para encontrar o link de download[/color][/b]',
	'STYLE_UPDATE_PUBLIC_NOTES'			=> '

[b]Notas:[/b] %1$s',
	'STYLE_UPLOAD_AGREEMENT'			=> '// ATENÇÃO DESENVOLVEDORES
//
// Todos os arquivos de idioma devem usar UTF-8 como codificação e os arquivos não devem conter BOM.
//
// Placeholders agora podem conter informações de ordem, ex.: ao invés de
// \'Página %s de %s\' você pode (e deve) escrever \'Página %1$s de %2$s\', isto permite
// aos tradutores reordenar a saída de dados, assegurando que permaneçam corretos',
	'STYLE_VALIDATION'					=> '[Validação de estilos do phpBB] %1$s %2$s',
	'STYLE_VALIDATION_MESSAGE_APPROVE'	=> 'Obrigado por enviar seu estilo para a phpBB.com Customisation Database. Após uma cuidadosa inspeção seu estilo foi aprovado e lançado em nossa Customisation Database.

Temos esperança de que você fornecerá um nível básico de suporte para este estilo e irá mantê-lo atualizado com futuras versões do phpBB. Apreciamos seu trabalho e sua contribuição para a comunidade. Autores como você fazem o phpBB.com um melhor lugar para todos.

[b]Notas da equipe de estilos sobre seu estilo:[/b]
[quote]%s[/quote]

Atenciosamente,
A equipe de estilos',
	'STYLE_VALIDATION_MESSAGE_DENY'		=> 'Olá,

Como você deve saber, todos os estilos enviados a base de estilos do phpBB devem ser validados e aprovados por um membro da equipe do phpBB.

Após a validação de seu estilo, a equipe de estilos do phpBB lamenta informar que tivemos de recusá-lo. As razões para isto são descritas abaixo:
[quote]%s[/quote]

Se você desejar enviar novamente este estilo para a base de estilos, por favor, certifique-se de que tenha corrigido os problemas identificados e que ele cumpra a [url=http://www.phpbb.com/community/viewtopic.php?t=988545]política de envio de estilos[/url].

Se você acha que esta recusa não possui justificativa, por favor contate o líder da equipe de estilos.

Atenciosamente,
A equipe de estilos',
));
