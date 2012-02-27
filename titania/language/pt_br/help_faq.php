<?php
/**
*
* help_faq [English]
*
* @package Titania language
* @version $Id: help_faq.php
* @author: RMcGirr83
* @copyright (c) 2010 phpBB Group
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
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$help = array(
	array(
		0 => '--',
		1 => 'O que é a Titania (aka The Customisation Database)'
	),
	array(
		0 => 'O que é a Titania?',
		1 => 'Titania (aka The Customisation Database) é uma base de dados onde os usuários poderão baixar modificações e estilos para um fórum phpBB. Você também é assegurado de que a modificação ou o estilo que baixou passou nos requisitos de validação do phpBBrasil.'
	),
	array(
		0 => 'Validação? O que é isso?',
		1 => 'Toda e qualquer modificação ou estilo que você tenha baixado na Titania foi submetida à validação. Validação significa que uma modificação ou estilo passou por um exame de segurança do código envolvido, bem como testes para assegurar que a modificação ou estilo é instalado e funciona corretamente em uma determinada versão de um fórum phpBB. A validação lhe fornece um nível de conforto ao saber que você não está baixando/instalando uma modificação ou estilo que pode deixar seu fórum vulnerável e ser atacado.'
	),
	array(
		0 => '--',
		1 => 'Como usar a Titania',
	),
	array(
		0 => 'Encontrando uma contribuição',
		1 => 'Há várias maneiras de encontrar uma contribuição. Na página principal da base de dados de customizações você pode ver as categorias que estão disponíveis atualmente, bem como as modificações/estilos recentemente aprovados na base de dados.'
	),
	array(
		0 => 'Procurando uma modificação',
		1 => 'Você pode ir diretamente ao tipo de modificação desejada baseado em qual categoria a contribuição se encaixa (ferramentas, comunicação, segurança, entretenimento, etc) ou usando o recurso de busca no topo da página. Se estiver usando o recurso de busca, você pode usar coringas e buscar pelo nome da contribuição (ou parte do nome), bem como o autor da contribuição. Após encontrar a customização em que está interessado você será levado para a página “Detalhes da customização”, onde irá encontrar o download da versão atual da customização bem como as versões anteriores dela dentro da seção “Revisões”.'
	),
	array(
		0 => 'Encontrando um estilo',
		1 => 'Semelhante a procura de modificações, a Titania também permite que você localize estilos, pacotes de smilies, imagens de ranks e outros Itens. O recurso de busca também permite que você use coringas e busque apenas por nomes de autores. Após encontrar o Item que desejado, você será levado a página “Detalhes da contribuição” onde irá encontrar o download da versão atual do item, bem como as versões anteriores do item na seção “Revisões”.'
	),
	array(
		0 => '--',
		1 => 'Suporte à customização'
	),
	array(
		0 => 'Regras',
		1 => 'Com a introdução da Titania, as regras envolvidas para utilizá-la são muito simples. Assim como no passado, o ditado “Você deve buscar suporte em relação a modificação/estilo no tópico onde você pegou a customização”. Enquanto a equipe de suporte phpBBrasil faz o seu melhor para ajudá-lo em executar e usar seu fórum, eles não podem, e nem espera-se que forneçam suporte para nenhuma customização/contribuição. A esperança do phpBBrasil é que o autor da contribuição forneça a você, usuário final, o suporte na utilização de sua customização. Lembre-se que todos os autores são voluntários que passaram seu tempo fornecendo um aprimoramento ao software phpBB. O ditado “Você pega mais moscas com mel do que com vinagre” aplica-se aqui, portanto, mantenha isso em mente quando requisitar suporte para uma customização (ex.: ser educado na maneira de perguntar).'
	),
	array(
		0 => 'Como obter suporte',
		1 => 'Cada customização fornece um método para fornecer suporte para você. Dentro de cada um o autor pode postar FAQ(s) relacionadas a customização, bem como uma área de discussão/suporte para um tipo de suporte um-a-um. Este suporte pode pode variar entre ajudá-lo a ter a instalação instalada e podem até dar a você mais recursos adicionais (add-ons) para melhorar a customização. Para acessar esta área basta clicar sobre a customização e uma aba será exibida dizendo “Discussão/suporte”. Uma vez que você acesse esta área você pode postar uma pergunta ou um comentário para o autor. Lembre-se que os autores não são obrigados a prestar suporte assim como eles não são obrigados a fornecer-lhe esta customização. Se você encontrar uma mensagem ou um comentário e sentir que não é de interesse da comunidade, sinta-se livre para usar o botão “Reportar esta mensagem” e um moderador tomará as medidas cabíveis.'
	),
	// This block will switch the FAQ-Questions to the second template column
	// Authors corner!!
	array(
		0 => '--',
		1 => '--'
	),
	array(
		0 => '--',
		1 => 'Criando e gerenciando contribuições'
	),
	array(
		0 => 'Criando uma contribuição',
		1 => 'Assim como qualquer contribuição, os autores são convidados a seguir algumas orientações quando enviam suas contribuições. As <a href="http://area51.phpbb.com/docs/coding-guidelines.html">Diretrizes de Codificação</a>, apesar de aparentemente difíceis no início, são na verdade suas amigas. Elas devem ser seguidas o máximo possível, para ajudá-lo a ter a sua contribuição para a comunidade publicada. No caso de MODs, o <a href="http://www.phpbb.com/mods/mpv/">pré-validador de MODs do phpBB</a> (aka “MPV”) será executado com a revisão enviada e checará por coisas como licenciamento correto, versão atual do phpBB e a versão atual do <a href="http://www.phpbb.com/mods/modx/">MODX</a>.'
	),
	array(
		0 => 'Enviando uma contribuição',
		1 => 'Então você criou uma contribuição. Vamos publicá-la!!<br /><br />Para enviar uma contribuição, vá até a Base de Contribuições e nesta página você encontrará uma imagem com os dizeres “Nova Contribuição”. Uma vez clicado, você poderá digitar o nome da contribuição, selecionar o tipo de contribuição, adicionar algumas palavras para descrever a contribuição (smilies e bbcode são permitidos), selecionar alguma(s) categoria(s) em que a contribuição se encaixe, adicionar co-autores (se houver) e imagens também. Tenha em mente que, como você está enviando a contribuição, ela será atrelada ao seu nome.'
	),
	array(
		0 => 'Gerenciando contribuições',
		1 => 'Uma vez que sua contribuição foi enviada com sucesso a Titania, você pode gerenciá-la. Depois de selecionar a sua contribuição clicando em "Minhas Contribuições" no topo da página, você pode adicionar informações adicionais a ela através da aba "Gerenciar Contribuição". Você pode alterar alterar a descrição da contribuição, enviar imagens, alterar a posse da contribuição (note que isto é irreversível, portanto, tenha certeza de que você deseja dar a posse de sua contribuição), alterar as categorias em que a contribuição se encaixa, bem como inserir uma url de demonstração para que os usuários possam ver em primeira mão como é a contribuição e como ela funciona.'
	),
	array(
		0 => 'Enviando uma nova revisão',
		1 => 'Você pode enviar novas revisões na página principal, na seção “Detalhes da contribuição”, de sua contribuição. Uma vez que você clique no link “Nova revisão”, será exibida uma página onde você envia a revisão, atribui uma versão e insere notas para a equipe de validação (bbcode e smilies são permitidos). Você também pode optar por ter a modificação “reempacotada” pela equipe de validação. Reempacotamento envolve realizar pequenas correções na contribuição. Isto pode envolver correções no arquivo de instalação MODX ou mesmo mudanças menores no código. O reempacotamento <strong>não</strong> significa que a equipe de validação irá reescrever grandes trechos de código que você forneceu, pois isto seria o seu “trabalho”.<br /><br />As regras que se aplicam a respeito da criação de uma customização, ainda se aplicam ao enviar revisões da customização. Ou seja, o <a href="http://www.phpbb.com/mods/mpv/">pré-validador de MODs do phpBB</a> (aka “PVM”) será executado na revisão da customização e checará por coisas como licenciamento correto, versão atual do phpBB e a versão correta do <a href="http://www.phpbb.com/mods/modx/">MODX</a>.'
	),
	array(
		0 => '--',
		1 => 'Fornecendo suporte'
	),
	array(
		0 => 'FAQ',
		1 => 'Cada customização fornece ao autor a possibilidade de enviar tipos de temas ao FAQ. Esses temas que você criará devem ser escritos de uma maneira que o usuário possa entender e aplicar o tema à customização, se o tema for sobre como ter a customizaçao instalada, acessar os recursos da customização, etc. Note que esta área é somente para você. Os usuários não podem editar ou responder entradas no FAQ.'
	),
	array(
		0 => 'Fórum de suporte',
		1 => 'Tenha em mente que os usuários irão fazer perguntas ou comentários sobre sua contribuição. Nós pedimos que você forneça suporte a sua contribuição o máximo possível. Sabemos que você gasta seu tempo livre na criação de sua contribuição, e que a vida real pode, às vezes, ficar no caminho da diversão. Nós só pedimos que você(s), como o(s) autor(es) forneça(m) o máximo de suporte possível. Se você encontrar uma mensagem ou comentário e achar que não é do interesse da comunidade, sinta-se livre para usar o botão “Reportar esta mensagem” e um moderador tomará as medidas cabíveis.'
	),
	array(
		0 => '--',
		1 => 'Validação'
	),
	array(
		0 => 'Minha customização não passou na verificação pré-validação',
		1 => 'Lembre-se, cada customização DEVE ter a licença correta (atualmente GNU GPL versão 2), a versão correta do software phpBB e a versão correta do MODX. Se sua customização não possuir esses itens essenciais, então ela não pode ser aceita na base de dados. Alguns erros são simples avisos e podem não necessitar de correção, se você não tiver certeza do problema, sinta-se livre para continuar com o envio e um validador lidará com isso.'
	),
	array(
		0 => 'Minha customização passou na pré-validação, e agora?',
		1 => 'Uma vez que uma customização é aceita na base de dados, ela é encaminhada a equipe relevante que validará sua contribuição. Você pode receber uma mensagem informando que a sua customização foi rejeitada. Por favor, não se preocupe. Sabemos que algumas coisas passam despercebidas, não há nada com o que se preocupar. A mensagem que você recebe conterá itens que foram encontrados. Estes itens podem sugerir alterações no código ou em imagens e podem até mesmo sugerir alterações na “interatividade com o usuário”. De modo geral, sugestões de “interatividade com o usuário” são apenas... sugestões. A parte mais importante de qualquer customização é a segurança, e não como ela exibida ao usuário final.<br /><br />Se nenhum item foi encontrado durante a validação de sua contribuição, você receberá uma MP dizendo que sua contribuição foi aceita na base de dados. Agora é hora de relaxar um pouco e deleitar-se com o conhecimento de que você fez uma contribuição para a comunidade aberta (open source).<br /><br />Não importa o resultado da validação, agradecemos o tempo e o esforço que você exerceu em compartilhar sua contribuição.'
	),
	array(
		0 => 'Quem irá validar a minha contribuição?',
		1 => 'Se for uma modificação será validada pela equipe de MODs e os validadores junior de MODs ou, ocasionalmente, um membro da equipe de desenvolvimento. Se for um estilo, será validado pela equipe de estilos e pelos validadores junior de estilos. Se for um conversor, será validado por um membro da equipe de suporte ou da equipe de desenvolvimento. Se for uma integração, será validado por um membro da equipe de MODs ou da equipe de desenvolvimento. Traduções são verificadas pelo gerenciador de traduções e pelo IST. Ferramentas oficiais são testadas e criadas pelas equipes do phpBBrasil.com.br e phpBB.com.'
	),
);

?>