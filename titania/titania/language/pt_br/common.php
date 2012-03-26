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
	'ACCESS_LIMIT_AUTHORS'		=> 'Restrições de acesso, nível de autor',
	'ACCESS_LIMIT_TEAMS'		=> 'Restrições de acesso, nível de equipe',
	'ADD_FIELD'					=> 'Adicionar campo',
	'AGREE'						=> 'Concordo',
	'AGREEMENT'					=> 'Acordo',
	'ALL'						=> 'Tudo',
	'ALL_CONTRIBUTIONS'			=> 'Todas as contribuições',
	'ALL_SUPPORT'				=> 'Todos os tópicos de suporte',
	'AUTHOR_BY'					=> 'Por %s',

	'BAD_RATING'				=> 'Tentativa de classificar falhou.',
	'BY'						=> 'por',

	'CACHE_PURGED'				=> 'O cache foi removido com sucesso',
	'CATEGORY'					=> 'Categoria',
	'CATEGORY_CHILD_AS_PARENT'	=> 'A categoria-mãe escolhida não pode ser selecionada por ser uma subcategoria desta.',
	'CATEGORY_DELETED'			=> 'Categoria removida',
	'CATEGORY_DESC'				=> 'Descrição da categoria',
	'CATEGORY_DUPLICATE_PARENT'	=> 'A categoria não pode ser sua própria mãe.',
	'CATEGORY_HAS_CHILDREN'		=> 'Esta categoria não pode ser removida porque possui subcategorias.',
	'CATEGORY_INFORMATION'		=> 'Informação da categoria',
	'CATEGORY_NAME'				=> 'Nome da categoria',
	'CATEGORY_TYPE'				=> 'Tipo da categoria',
	'CATEGORY_TYPE_EXPLAIN'		=> 'Os tipos de contribuições que esta categoria vai aceitar. Deixe em branco para não aceitar contribuições.',
	'CAT_ADDONS'				=> 'Add-ons',
	'CAT_ANTI_SPAM'				=> 'Anti-Spam',
	'CAT_AVATARS'				=> 'Avatares',
	'CAT_BOARD_STYLES'			=> 'Estilos de fórum',
	'CAT_COMMUNICATION'			=> 'Comunicação',
	'CAT_COSMETIC'				=> 'Cosméticos',
	'CAT_ENTERTAINMENT'			=> 'Entretenimento',
	'CAT_LANGUAGE_PACKS'		=> 'Pacotes de idioma',
	'CAT_MISC'					=> 'Diversos',
	'CAT_MODIFICATIONS'			=> 'Modificações',
	'CAT_PROFILE_UCP'			=> 'Perfil/Painel de Controle do Usuário',
	'CAT_RANKS'					=> 'Ranks',
	'CAT_SECURITY'				=> 'Segurança',
	'CAT_SMILIES'				=> 'Emoções',
	'CAT_SNIPPETS'				=> 'Trechos de códigos',
	'CAT_STYLES'				=> 'Estilos',
	'CAT_TOOLS'					=> 'Ferramentas',
	'CLOSED_BY'					=> 'Trancado por',
	'CLOSED_ITEMS'				=> 'Itens trancados',
	'CONFIRM_PURGE_CACHE'		=> 'Você realmente deseja remover o cache?',
	'CONTINUE'					=> 'Continuar',
	'CONTRIBUTION'				=> 'Contribuição',
	'CONTRIBUTIONS'				=> 'Contribuições',
	'CONTRIB_FAQ'				=> 'FAQ',
	'CONTRIB_MANAGE'			=> 'Gerenciar contribuição',
	'CONTRIB_SUPPORT'			=> 'Discussão/Suporte',
	'CREATE_CATEGORY'			=> 'Criar categoria',
	'CREATE_CONTRIBUTION'		=> 'Criar contribuição',
	'CUSTOMISATION_DATABASE'	=> 'Base de customizações',

	'DATE_CLOSED'				=> 'Data em que foi trancado',
	'DELETED_MESSAGE'			=> 'Última remoção por %1$s em %2$s - <a href="%3$s">Clique aqui para restaurar esta mensagem</a>',
	'DELETE_ALL_CONTRIBS'		=> 'Remover todas as contribuições',
	'DELETE_CATEGORY'			=> 'Remover categoria',
	'DELETE_SUBCATS'			=> 'Remover sub-categorias',
	'DESCRIPTION'				=> 'Descrição',
	'DESTINATION_CAT_INVALID'	=> 'A categoria de destino não pode aceitar contribuições.',
	'DETAILS'					=> 'Detalhes',
	'DOWNLOAD'					=> 'Download',
	'DOWNLOADS'					=> 'Downloads',
	'DOWNLOAD_ACCESS_DENIED'	=> 'Você não tem permissão para baixar o arquivo requisitado.',
	'DOWNLOAD_NOT_FOUND'		=> 'O arquivo requisitado não pôde ser encontrado.',

	'EDIT'						=> 'Editar',
	'EDITED_MESSAGE'			=> 'Última edição por %1$s em %2$s',
	'EDIT_CATEGORY'				=> 'Editar categoria',
	'ERROR'						=> 'Erro',

	'FILE_NOT_EXIST'			=> 'O arquivo não existe: %s',
	'FIND_CONTRIBUTION'			=> 'Procurar contribuição',

	'HARD_DELETE'				=> 'Remoção permanente',
	'HARD_DELETE_EXPLAIN'		=> 'Selecione para remover permanentemente este item.',
	'HARD_DELETE_TOPIC'			=> 'Remover tópico permanentemente',

	'LANGUAGE_PACK'				=> 'Pacote de idioma',
	'LIST'						=> 'Lista',

	'MAKE_CATEGORY_VISIBLE'		=> 'Deixar a categoria visível',
	'MANAGE'					=> 'Gerenciar',
	'MARK_CONTRIBS_READ'		=> 'Marcar contribuições como lidas',
	'MOVE_CONTRIBS_TO'			=> 'Mover contribuições para',
	'MOVE_DOWN'					=> 'Mover para baixo',
	'MOVE_SUBCATS_TO'			=> 'Mover sub-categorias para',
	'MOVE_UP'					=> 'Mover para cima',
	'MULTI_SELECT_EXPLAIN'		=> 'Pressione CTRL e clique para selecionar múltiplos Itens.',
	'MY_CONTRIBUTIONS'			=> 'Minhas contribuições',

	'NAME'						=> 'Nome',
	'NEW_REVISION'				=> 'Nova revisão',
	'NOT_AGREE'					=> 'Eu não concordo',
	'NO_AUTH'					=> 'Você não está autorizado a ver esta página.',
	'NO_CATEGORY'				=> 'A categoria requisitada não existe.',
	'NO_CATEGORY_NAME'			=> 'Digite o nome da categoria',
	'NO_CONTRIB'				=> 'A contribuição requisitada não existe.',
	'NO_CONTRIBS'				=> 'Nenhuma contribuição foi encontrada',
	'NO_DESC'					=> 'Você deve digitar uma descrição.',
	'NO_DESTINATION_CATEGORY'	=> 'Nenhuma categoria de destino pôde ser encontrado.',
	'NO_POST'					=> 'A mensagem requisitada não existe.',
	'NO_REVISION_NAME'			=> 'Nenhum nome para a revisão foi informado',
	'NO_TOPIC'					=> 'O tópico requisitado não existe.',

	'ORDER'						=> 'Ordenar',

	'PARENT_CATEGORY'			=> 'Categoria-mãe',
	'PARENT_NOT_EXIST'			=> 'A categoria-mãe não existe.',
	'POST_IP'					=> 'IP da mensagem',
	'PURGE_CACHE'				=> 'Remover cache',

	'QUEUE'						=> 'Fila',
	'QUEUE_DISCUSSION'			=> 'Fila de discussão',
	'QUICK_ACTIONS'				=> 'Ações rápidas',

	'RATING'					=> 'Classificação',
	'REMOVE_RATING'				=> 'Remover classificação',
	'REPORT'					=> 'Reportar',
	'RETURN_LAST_PAGE'			=> 'Voltar à página anterior',
	'ROOT'						=> 'Raiz',

	'SEARCH_UNAVAILABLE'		=> 'O sistema de busca está indisponível no momento. Por favor, tente novamente em alguns minutos.',
	'SELECT_CATEGORY'			=> '-- Selecionar categoria --',
	'SELECT_CATEGORY_TYPE'		=> '-- Selecionar o tipo de categoria --',
	'SELECT_SORT_METHOD'		=> 'Classificar por',
	'SHOW_ALL_REVISIONS'		=> 'Exibir todas as revisões',
	'SITE_INDEX'				=> 'Índice do site',
	'SNIPPET'					=> 'Trecho de código',
	'SOFT_DELETE_TOPIC'			=> 'Remoção parcial do tópico',
	'SORT_CONTRIB_NAME'			=> 'Nome da contribuição',
	'STICKIES'					=> 'Fixos',
	'SUBSCRIBE'					=> 'Inscrever-se',
	'SUBSCRIPTION_NOTIFICATION'	=> 'Inscrever-se para ser notificado',

	'TITANIA_DISABLED'			=> 'A base de customizações está temporariamente desativada, por favor, tente novamente em alguns minutos.',
	'TITANIA_INDEX'				=> 'Base de customizações',
	'TOTAL_CONTRIBS'			=> '%d contribuições',
	'TOTAL_CONTRIBS_ONE'		=> '1 contribuição',
	'TOTAL_POSTS'				=> '%d mensagens',
	'TOTAL_POSTS_ONE'			=> '1 mensagem',
	'TOTAL_RESULTS'				=> '%d resultados',
	'TOTAL_RESULTS_ONE'			=> '1 resultado',
	'TOTAL_TOPICS'				=> '%d tópicos',
	'TOTAL_TOPICS_ONE'			=> '1 tópico',
	'TRANSLATION'				=> 'Tradução',
	'TRANSLATIONS'				=> 'Traduções',
	'TYPE'						=> 'Tipo',

	'UNDELETE_TOPIC'			=> 'Restaurar tópico',
	'UNKNOWN'					=> 'Desconhecido',
	'UNSUBSCRIBE'				=> 'Desinscrever-se',
	'UPDATE_TIME'				=> 'Atualizado',

	'VERSION'					=> 'Versão',
	'VIEW'						=> 'Ver',
));
