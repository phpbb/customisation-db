<?php
/**
*
* @package language
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
* Tradução feita e revisada pela Equipe phpBB Brasil <http://www.phpbbrasil.com.br>!
*
*/
/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine


$lang = array_merge($lang, array(
	'ADDITIONAL_CHANGES'					=> 'Alterações disponíveis',
	'AM_MANUAL_INSTRUCTIONS'				=> 'O AutoMOD está enviando um arquivo compactado para o seu computador. Devido à configuração do AutoMOD, os arquivos não podem ser escritos em seu site automaticamente. Você precisará extrair o arquivo e enviá-los para o seu servidor manualmente, usando um cliente FTP ou um método similar. Se você não receber este arquivo automaticamente, clique %saqui%s.',
	'AM_MOD_ALREADY_INSTALLED'				=> 'O AutoMOD detectou que esta MOD já está instalada e não pôde continuar.',
	'APPLY_TEMPLATESET'						=> 'para este estilo',
	'APPLY_THESE_CHANGES'					=> 'Aplicar estas alterações',
	'AUTHOR_EMAIL'							=> 'E-mail do autor',
	'AUTHOR_INFORMATION'					=> 'Informações do autor',
	'AUTHOR_NAME'							=> 'Nome do autor',
	'AUTHOR_NOTES'							=> 'Notas do autor',
	'AUTHOR_URL'							=> 'URL do autor',
	'AUTOMOD'								=> 'AutoMOD',
	'AUTOMOD_CANNOT_INSTALL_OLD_VERSION'	=> 'A versão do AutoMOD que você está tentando instalar já está instalada. Por favor, remova o diretório install/.',
	'AUTOMOD_INSTALLATION'					=> 'Instalação do AutoMOD',
	'AUTOMOD_INSTALLATION_EXPLAIN'			=> 'Bem-vindo a instalação do AutoMOD. Você vai precisar de seus dados FTP se o AutoMOD detectar que é a melhor maneira de gravar arquivos. Os resultados do teste de requisitos estão abaixo.',
	'AUTOMOD_UNKNOWN_VERSION'				=> 'O AutoMOD não conseguiu atualizar porque não foi possível determinar a versão instalada atualmente. A versão indicada para a sua instalação é %s.',
	'AUTOMOD_VERSION'						=> 'Versão do AutoMOD',

	'CAT_INSTALL_AUTOMOD'					=> 'AutoMOD',
	'CHANGES'								=> 'Alterações',
	'CHANGE_DATE'							=> 'Data de lançamento',
	'CHANGE_VERSION'						=> 'Número da versão',
	'CHECK_AGAIN'							=> 'Verificar novamente',
	'COMMENT'								=> 'Comentário',
	'CREATE_TABLE'							=> 'Alterações na base de dados',
	'CREATE_TABLE_EXPLAIN'					=> 'O AutoMOD conseguiu realizar as alterações na sua base de dados, incluindo uma permissão que foi atribuída a regra “Administração Completa”.',

	'DELETE'								=> 'Remover',
	'DELETE_CONFIRM'						=> 'Você realmente deseja remover esta MOD?',
	'DELETE_ERROR'							=> 'Houve um erro ao remover a MOD selecionada.',
	'DELETE_SUCCESS'						=> 'A MOD foi removida com sucesso.',
	'DEPENDENCY_INSTRUCTIONS'				=> 'A MOD que você está tentando instalar depende de outra MOD. O AutoMOD não conseguiu detectar se esta MOD está instalada. Por favor, verifique se você possui instalada a <strong><a href="%1$s">%2$s</a></strong> antes de instalar sua MOD.',
	'DESCRIPTION'							=> 'Descrição',
	'DETAILS'								=> 'Detalhes',
	'DIR_PERMS'								=> 'Permissões de diretório',
	'DIR_PERMS_EXPLAIN'						=> 'Alguns sistemas exigem que diretórios possuam certas permissões para funcionar corretamente. Normalmente o padrão de 0755 é o correto. Esta configuração não possui impacto em sistemas Windows.',
	'DIY_INSTRUCTIONS'						=> 'Instruções “Faça Você Mesmo” (Do It Yourself)',

	'EDITED_ROOT_CREATE_FAIL'				=> 'O AutoMOD não conseguiu criar o diretório onde os arquivos editados serão armazenados.',
	'ERROR'									=> 'Erro',

	'FILESYSTEM_NOT_WRITABLE'				=> 'O AutoMOD determinou que o sistema de arquivos não é gravável, portanto, o método de gravação direta não pode ser utilizado.',
	'FILE_EDITS'							=> 'Edição de arquivos',
	'FILE_EMPTY'							=> 'Arquivo vazio',
	'FILE_MISSING'							=> 'Não é possível localizar o arquivo',
	'FILE_PERMS'							=> 'Permissões do arquivo',
	'FILE_PERMS_EXPLAIN'					=> 'Alguns sistemas exigem que arquivos possuam certas permissões para funcionar corretamente. Normalmente o padrão de 0644 é o correto. Esta configuração não possui impacto em sistemas Windows.',
	'FILE_TYPE'								=> 'Tipo de arquivo compactado',
	'FILE_TYPE_EXPLAIN'						=> 'Isso só é válido com o método de gravação “Baixar arquivo comprimido”',
	'FIND'									=> 'Busca',
	'FIND_MISSING'							=> 'A busca especificada pela MOD não pôde ser encontrada',
	'FORCE_CONFIRM'							=> 'O recurso de forçar a instalação significa que a MOD não está totalmente instalada. Você vai precisar realizar algumas correções manuais no seu fórum para terminar a instalação. Continuar?',
	'FORCE_INSTALL'							=> 'Forçar instalação',
	'FORCE_UNINSTALL'						=> 'Forçar desinstalação',
	'FTP_INFORMATION'						=> 'Informações FTP',
	'FTP_METHOD_ERROR'						=> 'Nenhum método FTP encontrado, por favor, verifique abaixo de configurações do autoMOD se foi definido corretamente um método FTP.',
	'FTP_METHOD_EXPLAIN'					=> 'Se você experimentar problemas com o padrão "FTP", você pode tentar o "Simple Socket" como uma alternativa de se conectar ao servidor FTP.',
	'FTP_METHOD_FSOCK'						=> 'Simple Socket',
	'FTP_METHOD_FTP'						=> 'FTP',
	'FTP_NOT_USABLE'						=> 'A função FTP não pode ser usada pois foi desabilitada por sua hospedagem.',

	'GO_PHP_INSTALLER'						=> 'A MOD requer um instalador externo para concluir a instalação. Clique aqui para continuar esta etapa.',

	'INHERIT_NO_CHANGE'						=> 'Nenhuma alteração pôde ser efetuada neste arquivo porque a template %1$s depende da %2$s.',
	'INLINE_EDIT_ERROR'						=> 'Erro: uma edição na linha do arquivo de instalação MODX não possui nenhum dos elementos necessários',
	'INLINE_FIND_MISSING'					=> 'A busca na linha especificada pela MOD não pôde ser encontrada.',
	'INSTALLATION_SUCCESSFUL'				=> 'O AutoMOD foi instalado com sucesso. Agora você pode gerenciar MODificações do phpBB através da aba AutoMOD no Painel de Controle da Administração.',
	'INSTALLED'								=> 'MOD instalada',
	'INSTALLED_EXPLAIN'						=> 'Sua MOD foi instalada! Aqui você pode ver alguns resultados da instalação. Ao notar quaisquer erros, procure suporte no <a href="http://www.phpbbrasil.com.br">phpBBrasil</a>',
	'INSTALLED_MODS'						=> 'MODs instaladas',
	'INSTALL_AUTOMOD'						=> 'Instalação do AutoMOD',
	'INSTALL_AUTOMOD_CONFIRM'				=> 'Você realmente deseja instalar o AutoMOD?',
	'INSTALL_ERROR'							=> 'Uma ou mais ações de instalação falharam. Reveja as ações abaixo, faça alguns ajustes e tente novamente. Você pode continuar com a instalação mesmo que alguma das ações falhe. <strong>Isso não é recomendado e pode fazer seu fórum não funcionar corretamente.</strong>',
	'INSTALL_FORCED'						=> 'Você forçou a instalação desta MOD embora houvessem erros ao instalá-la. Seu fórum pode apresentar erros. Observe as ações em que houve falhas abaixo e tente corrigí-las.',
	'INSTALL_MOD'							=> 'Instalar esta MOD',
	'INSTALL_TIME'							=> 'Tempo de instalação',
	'INVALID_MOD_INSTRUCTION'				=> 'Esta MOD tem uma instrução inválida, ou uma operação de busca na linha falhou.',
	'INVALID_MOD_NO_ACTION'					=> 'A MOD não possui uma ação correspondente a busca ‘%s’',
	'INVALID_MOD_NO_FIND'					=> 'A MOD não possui uma busca correspondente a ação ‘%s’',

	'LANGUAGE_NAME'							=> 'Nome do idioma',

	'MANUAL_COPY'							=> 'Cópia não efetuada',
	'MODS_CONFIG_EXPLAIN'					=> 'Você pode escolher como o AutoMOD ajusta seus arquivos aqui. O método mais básico é baixar o arquivo comprimido. Os outros necessitam de permissões adicionais no servidor.',
	'MODS_COPY_FAILURE'						=> 'O arquivo %s não pôde ser copiado para seu lugar. Verifique suas permissões ou use um método de gravação alternativo.',
	'MODS_EXPLAIN'							=> 'Aqui você pode gerenciar as MODs disponíveis em seu fórum. O AutoMOD permite a você personalizar seu fórum, instalando automaticamente modificações produzidas pela comunidade phpBB. Para mais informaçõs sobre MODs e o AutoMOD, visite o <a href="http://www.phpbb.com/mods">website do phpBB</a>. Para adicionar uma MOD nesta lista, use o formulário na parte inferior desta página. Alternativamente, você pode descompactá-la e enviar os arquivos para o diretório /store/mods/ de seu servidor.',
	'MODS_FTP_CONNECT_FAILURE'				=> 'O AutoMOD não conseguiu se conectar ao seu servidor FTP. O erro foi %s',
	'MODS_FTP_FAILURE'						=> 'O AutoMOD não pôde enviar o arquivo %s para seu lugar através do FTP',
	'MODS_MKDIR_FAILED'						=> 'O diretório %s não pôde ser criado',
	'MODS_SETUP_INCOMPLETE'					=> 'Um problema foi encontrado com sua configuração, e o AutoMOD não pode operar. Isso só ocorre quando as configurações (ex.: nome de usuário do FTP) foram alteradas, e pode ser corrigido na página de configurações do AutoMOD.',
	'MOD_CONFIG'							=> 'Configuração do AutoMOD',
	'MOD_CONFIG_UPDATED'					=> 'A configuração do AutoMOD foi atualizada.',
	'MOD_DETAILS'							=> 'Detalhes da MOD',
	'MOD_DETAILS_EXPLAIN'					=> 'Aqui você pode ver todas as informações conhecidas sobre a MOD selecionada.',
	'MOD_MANAGER'							=> 'AutoMOD',
	'MOD_NAME'								=> 'Nome da MOD',
	'MOD_OPEN_FILE_FAIL'					=> 'O AutoMOD não conseguiu abrir %s',
	'MOD_UPLOAD'							=> 'Enviar MOD',
	'MOD_UPLOAD_EXPLAIN'					=> 'Aqui você pode enviar um pacote de MOD compactado contendo os arquivos MODX necessários para executar a instalação. O AutoMOD tentará então descompactar o arquivo e prepará-lo para instalação.',
	'MOD_UPLOAD_INIT_FAIL'					=> 'Houve um erro ao inicializar o processo de envio da MOD.',
	'MOD_UPLOAD_SUCCESS'					=> 'MOD enviada e preparada para instalação.',

	'NAME'									=> 'Nome',
	'NEW_FILES'								=> 'Novos arquivos',
	'NO_ATTEMPT'							=> 'Nenhuma tentativa',
	'NO_INSTALLED_MODS'						=> 'Nenhuma instalação de MODs detectada',
	'NO_MOD'								=> 'A MOD selecionada não pôde ser encontrada.',
	'NO_UNINSTALLED_MODS'					=> 'Nenhuma desinstalação de MODs detectada',
	'NO_UPLOAD_FILE'						=> 'Nenhum arquivo especificado.',

	'ORIGINAL'								=> 'Original',

	'PATH'									=> 'Caminho',
	'PREVIEW_CHANGES'						=> 'Prever alterações',
	'PREVIEW_CHANGES_EXPLAIN'				=> 'Exibir as alterações a serem realizadas antes de executá-las.',
	'PRE_INSTALL'							=> 'Preparando para instalar',
	'PRE_INSTALL_EXPLAIN'					=> 'Aqui você pode prever todas as modificações a serem feitas em seu fórum, antes de serem aplicadas. <strong>ATENÇÃO!</strong>, uma vez aceito, seus arquivos padrões do phpBB serão editados e alterações na sua base de dados podem ocorrer. No entanto, se a instalação não for bem sucedida, supondo que você possa acessar o AutoMOD, você terá a opção de restaurar o fórum para este ponto.',
	'PRE_UNINSTALL'							=> 'Preparando para desinstalar',
	'PRE_UNINSTALL_EXPLAIN'					=> 'Aqui você pode prever todas as modificações que serão realizadas em seu fórum para desinstalar a MOD. <strong>ATENÇÃO!</strong>, uma vez aceito, os arquivos base do seu phpBB serão editados e alterações em sua base de dados podem ocorrer. Além disso, este processo utiliza técnicas de reversão que podem não ser 100% exatas. No entanto, se a desinstalação não for bem sucedida, supondo que você possa acessar o AutoMOD, você terá a opção de restaurar a este ponto.',

	'REMOVING_FILES'						=> 'Arquivos a serem removidos',
	'RETRY'									=> 'Tentar novamente',
	'RETURN_MODS'							=> 'Retornar ao AutoMOD',
	'REVERSE'								=> 'Reverter',
	'ROOT_IS_READABLE'						=> 'O diretório raiz do phpBB é legível.',
	'ROOT_NOT_READABLE'						=> 'O AutoMOD não pôde abrir o arquivo config.php do phpBB para leitura. Isto provavelmente significa que as permissões são muito restritivas no diretório raiz do phpBB, o que impedirá o AutoMOD de funcionar. Por favor ajuste as permissões e verifique novamente.',

	'SOURCE'								=> 'Fonte',
	'SQL_QUERIES'							=> 'Consultas SQL',
	'STATUS'								=> 'Estado',
	'STORE_IS_WRITABLE'						=> 'O diretório store/ é gravável.',
	'STORE_NOT_WRITABLE'					=> 'O diretório store/ não é gravável.',
	'STORE_NOT_WRITABLE_INST'				=> 'A instalação do AutoMOD detectou que o diretório store/ não é gravável. Isto é necessário para o AutoMOD funcionar corretamente. Por favor ajuste as permissões e tente novamente.',
	'STYLE_NAME'							=> 'Nome do estilo',
	'SUCCESS'								=> 'Sucesso',

	'TARGET'								=> 'Alvo',

	'UNINSTALL'								=> 'Desinstalar',
	'UNINSTALLED'							=> 'MOD desinstalada',
	'UNINSTALLED_EXPLAIN'					=> 'Sua MOD foi desinstalada! Aqui você pode ver alguns resultados da desinstalação. Se observar quaisquer erros, procure suporte em <a href="http://www.phpbbrasil.com.br/">phpBBrasil</a>.',
	'UNINSTALLED_MODS'						=> 'MODs desinstaladas',
	'UNINSTALL_AUTOMOD'						=> 'Desinstalação do AutoMOD',
	'UNINSTALL_AUTOMOD_CONFIRM'				=> 'Você realmente deseja desinstalar o AutoMOD? Isto NÃO removerá quaisquer MODs que tenham sido instaladas com o AutoMOD.',
	'UNKNOWN_MOD_AUTHOR-NOTES'				=> 'Nenhuma nota do autor foi especificada.',
	'UNKNOWN_MOD_COMMENT'					=> '',
	'UNKNOWN_MOD_DESCRIPTION'				=> '',
	'UNKNOWN_MOD_DIY-INSTRUCTIONS'			=> '',
	'UNKNOWN_MOD_INLINE-COMMENT'			=> '',
	'UNKNOWN_QUERY_REVERSE'					=> 'Busca reversa desconhecida',
	'UNRECOGNISED_COMMAND'					=> 'Erro, comando %s não reconhecido',
	'UPDATE_AUTOMOD'						=> 'Atualizar AutoMOD',
	'UPDATE_AUTOMOD_CONFIRM'				=> 'Por favor, confirme que você realmente deseja atualizar o AutoMOD.',
	'UPLOAD'								=> 'Enviar',

	'VERSION'								=> 'Versão',

	'WRITE_DIRECT_FAIL'						=> 'O AutoMOD não pôde copiar o arquivo %s para o local usando o método direto. Por favor, utilize outro método de tente novamente.',
	'WRITE_DIRECT_TOO_SHORT'				=> 'O AutoMOD não pôde terminar de gravar o arquivo %s. Isso muitas vezes pode ser resolvido com o botão tentar novamente. Se isso não funcionar, tente outro método de gravação.',
	'WRITE_MANUAL_FAIL'						=> 'O AutoMOD não pôde adicionar o arquivo %s em um arquivo comprimido. Por favor, tente outro método de gravação.',
	'WRITE_METHOD'							=> 'Método de gravação',
	'WRITE_METHOD_DIRECT'					=> 'Direto',
	'WRITE_METHOD_EXPLAIN'					=> 'Você pode definir seu método preferido para gravar arquivos. A opção mais compatível é “Baixar arquivo comprimido”.',
	'WRITE_METHOD_FTP'						=> 'FTP',
	'WRITE_METHOD_MANUAL'					=> 'Baixar arquivo comprimido',

	'after add'								=> 'Em seguida, adicione',

	'before add'							=> 'Antes, adicione',

	'find'									=> 'Procure',

	'in-line-after-add'						=> 'Na linha, em seguida, adicione',
	'in-line-before-add'					=> 'Na linha, antes, adicione',
	'in-line-edit'							=> 'Procure na linha',
	'in-line-operation'						=> 'Na linha, Incremente',
	'in-line-replace'						=> 'Na linha, substitua',
	'in-line-replace-with'					=> 'Na linha, substitua com',

	'operation'								=> 'Incrementar',

	'replace'								=> 'Substituir com',
	'replace with'							=> 'Substituir com',
));
