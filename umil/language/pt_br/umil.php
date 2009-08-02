<?php
/**
*
* umil.php [Brazilian Portuguese]
*
* @author Nathan Guse (EXreaction) http://lithiumstudios.org
* @package phpBB3 UMIL - Unified MOD Install File
* @version $Id$
* @copyright (c) 2009 Suporte phpBB
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @Traduzido por: Seiken <seiken@suportephpbb.org>
*                        http://www.suportephpbb.org/
*
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
	'ACTION'						=> 'Ação',
	'ADVANCED'						=> 'Avançado',
	'AUTH_CACHE_PURGE'				=> 'Limpando o cache de permissões',

	'CACHE_PURGE'					=> 'Limpando o cache de seus fóruns',
	'CONFIGURE'						=> 'Configurar',
	'CONFIG_ADD'					=> 'Adicionando nova variável de configuração: %s',
	'CONFIG_ALREADY_EXISTS'			=> 'ERRO: A variável de configuração %s já existe.',
	'CONFIG_NOT_EXIST'				=> 'ERRO: A variável de configuração %s não existe.',
	'CONFIG_REMOVE'					=> 'Removendo variável de configuração: %s',
	'CONFIG_UPDATE'					=> 'Atualizando variável de configuração: %s',

	'DISPLAY_RESULTS'				=> 'Exibir resultados completos',
	'DISPLAY_RESULTS_EXPLAIN'		=> 'Selecione sim para exibir todas as ações e resultados durante a ação solicitada.',

	'ERROR_NOTICE'					=> 'Um ou mais erros ocorreram durante a ação solicitada.  Por favor, baixe <a href="%1$s">este arquivo</a> com os erros listados e solicite assistência ao autor da mod.<br /><br />Caso ocorra qualquer problema ao baixar o arquivo, você pode acessá-lo diretamente com um navegador FTP através do seguinte endereço: %2$s',
	'ERROR_NOTICE_NO_FILE'			=> 'Um ou mais erros ocorreram durante a ação solicitada.  Por favor, faça um registro completo de quaisquer erros que ocorram e solicite assistência ao autor da mod.',

	'FAIL'							=> 'Falha',
	'FILE_COULD_NOT_READ'			=> 'ERRO: Não foi possível abrir o arquivo %s para leitura.',
	'FOUNDERS_ONLY'					=> 'Você deve estar logado como um fundador para acessar esta página.',

	'GROUP_NOT_EXIST'				=> 'O grupo não existe',

	'IGNORE'						=> 'Ignorar',
	'IMAGESET_CACHE_PURGE'			=> 'Atualizando o imageset %s',
	'INSTALL'						=> 'Instalar',
	'INSTALL_MOD'					=> 'Instalar %s',
	'INSTALL_MOD_CONFIRM'			=> 'Você deseja realmente instalar %s?',

	'MODULE_ADD'					=> 'Adicionando %1$s módulo: %2$s',
	'MODULE_ALREADY_EXIST'			=> 'ERRO: O módulo selecionado já existe.',
	'MODULE_NOT_EXIST'				=> 'ERRO: O módulo selecionado não existe.',
	'MODULE_REMOVE'					=> 'Removendo %1$s módulo: %2$s',

	'NONE'							=> 'Nenhum',
	'NO_TABLE_DATA'					=> 'ERRO: Nenhuma tabela foi especificada',

	'PARENT_NOT_EXIST'				=> 'ERRO: A categoria pai especificada para este módulo não existe.',
	'PERMISSIONS_WARNING'			=> 'Novas permissões foram adicionadas.  Cheque suas permissões e veja se elas estão de acordo como gostaria que estivessem.',
	'PERMISSION_ADD'				=> 'Adicionando nova opção de permissão: %s',
	'PERMISSION_ALREADY_EXISTS'		=> 'ERRO: A opção de permissão %s já existe.',
	'PERMISSION_NOT_EXIST'			=> 'ERRO: A opção de permissão %s não existe.',
	'PERMISSION_REMOVE'				=> 'Removendo opção de permissão: %s',
	'PERMISSION_SET_GROUP'			=> 'Permissões configuradas para o grupo %s.',
	'PERMISSION_SET_ROLE'			=> 'Permissões configuradas para a tarefa %s.',
	'PERMISSION_UNSET_GROUP'		=> 'Permissões não configuradas para o grupo %s.',
	'PERMISSION_UNSET_ROLE'			=> 'Permissões não configuradas para a tarefa %s.',

	'ROLE_NOT_EXIST'				=> 'Tarefa não existente',

	'SUCCESS'						=> 'Sucesso',

	'TABLE_ADD'						=> 'Adicionando nova tabela no banco de dados: %s',
	'TABLE_ALREADY_EXISTS'			=> 'ERRO: A tabela %s já existe no banco de dados.',
	'TABLE_COLUMN_ADD'				=> 'Adicionando uma nova coluna nomeada %2$s na tabela %1$s',
	'TABLE_COLUMN_ALREADY_EXISTS'	=> 'ERRO: A coluna %2$s já existe na tabela %1$s.',
	'TABLE_COLUMN_NOT_EXIST'		=> 'ERRO: A coluna %2$s não existe na tabela %1$s.',
	'TABLE_COLUMN_REMOVE'			=> 'Removendo a coluna nomeada %2$s da tabela %1$s',
	'TABLE_COLUMN_UPDATE'			=> 'Atualizando a coluna nomeada %2$s da tabela %1$s',
	'TABLE_ROW_INSERT_DATA'			=> 'Inserindo linhas na tabela %s do banco de dados.',
	'TABLE_ROW_REMOVE_DATA'			=> 'Removendo linhas na tabela %s do banco de dados.',
	'TABLE_ROW_UPDATE_DATA'			=> 'Atualizando linhas na tabela %s do banco de dados.',
	'TABLE_KEY_ADD'					=> 'Adicionando uma chave nomeada %2$s na tabela %1$s',
	'TABLE_KEY_ALREADY_EXIST'		=> 'ERRO: O índice %2$s já existe na tabela %1$s.',
	'TABLE_KEY_NOT_EXIST'			=> 'ERRO: O índice %2$s não existe na tabela %1$s.',
	'TABLE_KEY_REMOVE'				=> 'Removendo uma chave nomeada %2$s da tabela %1$s',
	'TABLE_NOT_EXIST'				=> 'ERRO: A tabela %s não existe no banco de dados.',
	'TABLE_REMOVE'					=> 'Removendo tabela do banco de dados: %s',
	'TEMPLATE_CACHE_PURGE'			=> 'Atualizando o template %s',
	'THEME_CACHE_PURGE'				=> 'Atualizando o tema %s',

	'UNINSTALL'						=> 'Desinstalar',
	'UNINSTALL_MOD'					=> 'Desinstalar %s',
	'UNINSTALL_MOD_CONFIRM'			=> 'Você deseja realmente desinstalar %s?  Todos os dados e configurações salvos por este mod serão removidos!',
	'UNKNOWN'						=> 'Desconhecido',
	'UPDATE_MOD'					=> 'Atualizar %s',
	'UPDATE_MOD_CONFIRM'			=> 'Você deseja realmente atualizar %s?',
	'UPDATE_UMIL'					=> 'Esta versão do UMIL está desatualizada.<br /><br />Por favor, baixe a mais nova versão do UMIL (Unified MOD Install Library) a seguir: <a href="%1$s">%1$s</a>',

	'VERSIONS'						=> 'Versão da Mod: <strong>%1$s</strong><br />Versão atualmente instalada: <strong>%2$s</strong>',
	'VERSION_SELECT'				=> 'Seleção de Versão',
	'VERSION_SELECT_EXPLAIN'		=> 'Não modifique a opção "Ignorar" a menos que você saiba o que está fazendo ou o que lhe foi dito.',
));

?>