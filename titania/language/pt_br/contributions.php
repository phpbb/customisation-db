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
'CUSTOM_LICENSE' => 'Custom',
	'ANNOUNCEMENT_TOPIC'					=> 'Tópico de anúncio',
	'ANNOUNCEMENT_TOPIC_SUPPORT'			=> 'Tópico de suporte',
	'ANNOUNCEMENT_TOPIC_VIEW'				=> '%sVer%s',
	'ATTENTION_CONTRIB_CATEGORIES_CHANGED'	=> '<strong>Categorias da contribuição alteradas de:</strong><br />%1$s<br /><br /><strong>para:</strong><br />%2$s',
	'ATTENTION_CONTRIB_DESC_CHANGED'		=> '<strong>Descrição da contribuição alterada de:</strong><br />%1$s<br /><br /><strong>para:</strong><br />%2$s',
	'AUTOMOD_RESULTS'						=> '<strong>Por favor, verifique os resultados de instalação do AutoMOD e tenha certeza de que nada precisa ser corrigido.<br /><br />Se algum erro ocorrer e você tiver certeza de que seja um engano, basta clicar em continuar abaixo.</strong>',
	'AUTOMOD_TEST'							=> 'A MOD será testada com o AutoMOD e os resultados serão exibidos (isso pode levar alguns minutos, portanto, seja paciente).<br /><br />Por favor, clique em continuar quando estiver pronto.',

	'BAD_VERSION_SELECTED'					=> '%s não é uma versão correta do phpBB.',

	'CANNOT_ADD_SELF_COAUTHOR'				=> 'Você é o autor principal, portanto você não pode adicionar-se à lista de co-autores.',
	'CLEANED_CONTRIB'						=> 'Contribuição limpa',
	'CONTRIB'								=> 'Contribuição',
	'CONTRIBUTIONS'							=> 'Contribuições',
	'CONTRIB_ACTIVE_AUTHORS'				=> 'Co-autores ativos',
	'CONTRIB_ACTIVE_AUTHORS_EXPLAIN'		=> 'Co-autores ativos podem gerenciar a maior parte dos itens da contribuição.',
	'CONTRIB_APPROVED'						=> 'Aprovado',
	'CONTRIB_AUTHOR'						=> 'Autor da contribuição',
	'CONTRIB_AUTHORS_EXPLAIN'				=> 'Digite os nomes dos co-autores, com um nome de co-autor por linha.',
	'CONTRIB_CATEGORY'						=> 'Categoria da contribuição',
	'CONTRIB_CHANGE_OWNER'					=> 'Alterar proprietário',
	'CONTRIB_CHANGE_OWNER_EXPLAIN'			=> 'Introduza um nome aqui para definir este usuário como proprietário. Mudando isto, você será definido como autor não-contribuinte.',
	'CONTRIB_CHANGE_OWNER_NOT_FOUND'		=> 'O usuário que você tentou definir como proprietário, %s, não foi encontrado.',
	'CONTRIB_CLEANED'						=> 'Limpo',
	'CONTRIB_CONFIRM_OWNER_CHANGE'			=> 'Você realmente deseja atribuir a propriedade para %s? Isto irá impedi-lo de gerenciar o projeto e não pode ser desfeito.',
	'CONTRIB_CREATED'						=> 'A contribuição foi criada com sucesso',
	'CONTRIB_DESCRIPTION'					=> 'Descrição da contribuição',
	'CONTRIB_DETAILS'						=> 'Detalhes da contribuição',
	'CONTRIB_DISABLED'						=> 'Oculto + Desabilitado',
	'CONTRIB_DOWNLOAD_DISABLED'				=> 'Downloads desabilitados',
	'CONTRIB_EDITED'						=> 'A contribuição foi editada com sucesso.',
	'CONTRIB_HIDDEN'						=> 'Oculto',
	'CONTRIB_ISO_CODE'						=> 'Código ISO',
	'CONTRIB_ISO_CODE_EXPLAIN'				=> 'O código ISO de acordo com as <a href="http://area51.phpbb.com/docs/coding-guidelines.html#translation">Diretrizes para Codificação de Traduções</a>.',
	'CONTRIB_LOCAL_NAME'					=> 'Nome local',
	'CONTRIB_LOCAL_NAME_EXPLAIN'			=> 'O nome localizado do idioma, ex.: <em>Français</em>.',
	'CONTRIB_NAME'							=> 'Nome da contribuição',
	'CONTRIB_NAME_EXISTS'					=> 'O nome único já foi reservado.',
	'CONTRIB_NEW'							=> 'Novo',
	'CONTRIB_NONACTIVE_AUTHORS'				=> 'Co-autores não-ativos (contribuintes anteriores)',
	'CONTRIB_NONACTIVE_AUTHORS_EXPLAIN'		=> 'Co-autores não-ativos não podem gerenciar nada da contribuição e são listados apenas como autores anteriores.',
	'CONTRIB_NOT_FOUND'						=> 'A contribuição requisitada não pôde ser encontrada.',
	'CONTRIB_OWNER_UPDATED'					=> 'O proprietário foi alterado.',
	'CONTRIB_PERMALINK'						=> 'Link permanente da contribuição',
	'CONTRIB_PERMALINK_EXPLAIN'				=> 'Versão limpa do nome da contribuição, usado para criar a url da contribuição.<br /><strong>Deixe em branco para ter um link criado automaticamente com base no nome da contribuição.</strong>',
	'CONTRIB_RELEASE_DATE'					=> 'Data de lançamento',
	'CONTRIB_STATUS'						=> 'Estado da contribuição',
	'CONTRIB_STATUS_EXPLAIN'				=> 'Alterar o estado da contribuição',
	'CONTRIB_TYPE'							=> 'Tipo de contribuição',
	'CONTRIB_UPDATED'						=> 'A contribuição foi atualizada com sucesso.',
	'CONTRIB_UPDATE_DATE'					=> 'Última atualização',
	'COULD_NOT_FIND_ROOT'					=> 'Não foi possível localizar o diretório principal. Certifique-se de que há um arquivo xml com o nome install em algum lugar do pacote zip.',
	'COULD_NOT_FIND_USERS'					=> 'Não foi possível encontrar os seguintes usuários: %s',
	'COULD_NOT_OPEN_MODX'					=> 'Não foi possível abrir o arquivo MODX.',
	'CO_AUTHORS'							=> 'Co-autores',

	'DELETE_CONTRIBUTION'					=> 'Remover contribuição',
	'DELETE_CONTRIBUTION_EXPLAIN'			=> 'Remover permanentemente esta contribuição (use o campo de estado da contribuição, se você precisa ocultá-la).',
	'DELETE_REVISION'						=> 'Remover revisão',
	'DELETE_REVISION_EXPLAIN'				=> 'Remover permanentemente esta revisão (use o campo de estado da revisão se você precisa ocultá-la).',
	'DEMO_URL'								=> 'URL de demonstração',
	'DEMO_URL_EXPLAIN'						=> 'Localização da demonstração',
	'DOWNLOADS_PER_DAY'						=> '%.2f downloads por dia',
	'DOWNLOADS_TOTAL'						=> 'Total de downloads',
	'DOWNLOADS_VERSION'						=> 'Versão dos downloads',
	'DOWNLOAD_CHECKSUM'						=> 'MD5 checksum',
	'DUPLICATE_AUTHORS'						=> 'Você tem os seguintes autores listados como ativos e não-ativos (eles não podem ser ambos): %s',

	'EDIT_REVISION'							=> 'Editar revisão',
	'EMPTY_CATEGORY'						=> 'Selecione pelo menos uma categoria',
	'EMPTY_CONTRIB_DESC'					=> 'Digite a descrição da contribuição',
	'EMPTY_CONTRIB_ISO_CODE'				=> 'Digite o código ISO',
	'EMPTY_CONTRIB_LOCAL_NAME'				=> 'Digite o nome local',
	'EMPTY_CONTRIB_NAME'					=> 'Digite o nome da contribuição',
	'EMPTY_CONTRIB_PERMALINK'				=> 'Digite sua proposta para o link permanente da contribuição',
	'EMPTY_CONTRIB_TYPE'					=> 'Selecione pelo menos um tipo para a contribuição',
	'ERROR_CONTRIB_EMAIL_FRIEND'			=> 'Você não possui permissão para recomendar esta contribuição para alguém.',

	'INVALID_LICENSE'						=> 'Licença inválida',
	'INVALID_PERMALINK'						=> 'É necessário digitar um link permanente válido, por exemplo: %s',

	'LICENSE'								=> 'Licença',
	'LICENSE_EXPLAIN'						=> 'Licença com a qual o trabalho será liberado.',
	'LOGIN_EXPLAIN_CONTRIB'					=> 'Para criar uma nova contribuição você deve estar registrado',

	'MANAGE_CONTRIBUTION'					=> 'Gerenciar contribuição',
	'MPV_RESULTS'							=> '<strong>Por favor, verifique os resultados do PVM (pré-validador de MODs) e certifique-se de que nada precisa ser corrigido.<br /><br />Se você acha que nada precisa ser corrigido ou não tem certeza, apenas clique abaixo em continuar.</strong>',
	'MPV_TEST'								=> 'A MOD será testada com o PVM (pré-validador de MODs) e os resultados serão exibidos (isto pode levar alguns minutos, portanto, seja paciente).<br /><br />Por favor, clique em continuar quando estiver pronto.',
	'MPV_TEST_FAILED'						=> 'Desculpe, o teste automático PVM (pré-validador de MODs) falhou e os resultados do seu teste não estão disponíveis. Por favor, continue.',
	'MPV_TEST_FAILED_QUEUE_MSG'				=> 'Teste automático do PVM (pré-validador de MODs) falhou. [url=%s]Clique aqui para tentar executar automaticamente o PVM (pré-validador de MODs) novamente[/url]',
	'MUST_SELECT_ONE_VERSION'				=> 'Você deve selecionar pelo menos uma versão do phpBB.',

	'NEW_CONTRIBUTION'						=> 'Nova contribuição',
	'NEW_REVISION'							=> 'Nova revisão',
	'NEW_REVISION_SUBMITTED'				=> 'A nova revisão foi enviada com sucesso!',
	'NEW_TOPIC'								=> 'Novo tópico',
	'NOT_VALIDATED'							=> 'Não validado',
	'NO_CATEGORY'							=> 'A categoria selecionada não existe',
	'NO_PHPBB_BRANCH'						=> 'Você deve selecionar uma versão do phpBB.',
	'NO_QUEUE_DISCUSSION_TOPIC'				=> 'Nenhuma fila de tópicos de discussões pôde ser encontrada. Você já enviou uma revisão para esta contribuição (ele será criado quando você fizer isto)?',
	'NO_REVISIONS'							=> 'Nenhuma revisão',
	'NO_REVISION_ATTACHMENT'				=> 'Por favor, selecione um arquivo para enviar',
	'NO_REVISION_VERSION'					=> 'Por favor, digite uma versão para a revisão',
	'NO_SCREENSHOT'							=> 'Nenhum imagem.',
	'NO_TRANSLATION'						=> 'O arquivo não parece ser um pacote de idioma válido. Verifique se ele contem todos os arquivos encontrados no diretório do idioma Inglês',

	'PHPBB_BRANCH'							=> 'Versões do phpBB',
	'PHPBB_BRANCH_EXPLAIN'					=> 'Selecione a versão do phpBB que esta revisão suporta.',
	'PHPBB_VERSION'							=> 'Versão do phpBB',

	'QUEUE_ALLOW_REPACK'					=> 'Permitir reempacotamento',
	'QUEUE_ALLOW_REPACK_EXPLAIN'			=> 'Permitir que esta contribuição seja reempacotada devido a pequenos erros?',
	'QUEUE_NOTES'							=> 'Notas de validação',
	'QUEUE_NOTES_EXPLAIN'					=> 'Mensagem para a equipe.',

	'REPORT_CONTRIBUTION'					=> 'Reportar contribuição',
	'REPORT_CONTRIBUTION_CONFIRM'			=> 'Utilize este formulário para reportar a contribuição selecionada para os moderadores e administradores. Reporte apenas se a contribuição infringe alguma regra do fórum.',
	'REVISION'								=> 'Revisão',
	'REVISIONS'								=> 'Revisões',
	'REVISION_APPROVED'						=> 'Aprovado',
	'REVISION_DENIED'						=> 'Rejeitado',
	'REVISION_IN_QUEUE'						=> 'Você já possui uma revisão na fila de validação. Você deve aguardar até que a revisão anterior seja aprovada ou rejeitada para enviar uma nova.',
	'REVISION_NAME'							=> 'Nome da revisão',
	'REVISION_NAME_EXPLAIN'					=> 'Digite um nome opcional para esta versão (ex.: Edição de Natal)',
	'REVISION_NEW'							=> 'Novo',
	'REVISION_PENDING'						=> 'Pendente',
	'REVISION_PULLED_FOR_OTHER'				=> 'Puxado',
	'REVISION_PULLED_FOR_SECURITY'			=> 'Puxado - Segurança',
	'REVISION_REPACKED'						=> 'Reempacotado',
	'REVISION_RESUBMITTED'					=> 'Reenviado',
	'REVISION_STATUS'						=> 'Estado da revisão',
	'REVISION_STATUS_EXPLAIN'				=> 'Alterar o estado da revisão',
	'REVISION_SUBMITTED'					=> 'A revisão foi enviada com sucesso.',
	'REVISION_VERSION'						=> 'Versão da revisão',
	'REVISION_VERSION_EXPLAIN'				=> 'O número da versão deste pacote',

	'SCREENSHOTS'							=> 'Imagens',
	'SELECT_CONTRIB_TYPE'					=> '-- Selecione o tipo da contribuição --',
	'SELECT_PHPBB_BRANCH'					=> 'Selecione a versão do phpBB',
	'SUBDIRECTORY_LIMIT'					=> 'Os pacotes não podem ter mais de 50 sub-diretórios em qualquer ponto.',
	'SUBMIT_NEW_REVISION'					=> 'Envie e adicione uma nova revisão',

	'TOO_MANY_TRANSLATOR_LINKS'				=> 'Você está utilizando %d links externos dentro da linha TRANSLATION/TRANSLATION_INFO. Por favor inclua apenas <strong>um link</strong>. Incluir dois links é permitido APENAS após análise caso-a-caso - por favor, poste no fórum de traduções explicando suas razões para inserir mais links externos na linha.',

	'VALIDATION_TIME'						=> 'Data da validação',
	'VIEW_DEMO'								=> 'Ver demonstração',
	'VIEW_INSTALL_FILE'						=> 'Ver arquivo de instalação',

	'WRONG_CATEGORY'						=> 'Você pode colocar esta contribuição apenas no mesmo tipo de categoria que o tipo de contribuição.',
));
