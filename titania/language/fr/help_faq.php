<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team, (c) 2011 phpBB.fr
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License 2.0
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
		1 => 'Qu’est ce que Titania (alias la base de données des contributions)'
	),
	array(
		0 => 'Qu’est ce que Titania ?',
		1 => 'Titania (alias la base de données des contributions) est une base de données où les utilisateurs peuvent télécharger des modifications et des styles pour un forum phpBB.  Vous avez également l’assurance que la modification ou le style que vous avez téléchargé a rempli les conditions nécessaires fixées par l’équipe de validation de phpBB.'
	),
	array(
		0 => 'Une validation ? Qu’est-ce ?',
		1 => 'Chaque modification ou style téléchargé sur Titania a été soumis à une validation.  La validation signifie qu’une modification, ou un style, a été vérifié scrupuleusement afin de détecter d’éventuelles failles de sécurité qui peuvent être présentes dans le code et afin de s’assurer que l’installation et le fonctionnement de la modification ou du style soit correct et compatible avec une version particulière d’un forum phpBB.  La validation vous apporte la sérénité de savoir que vous n’êtes pas en train de télécharger ou d’installer une modification ou un style susceptible de rendre vulnérable votre forum.'
	),
	array(
		0 => '--',
		1 => 'Comment utiliser Titania',
	),
	array(
		0 => 'Rechercher une contribution',
		1 => 'Il existe différentes manières de trouver une contribution.  Sur la page principale de la base de données des contributions, vous pouvez aussi bien consulter les catégories qui sont actuellement disponibles que les modifications et les styles qui ont été récemment approuvés dans la base de données.'
	),
	array(
		0 => 'Rechercher une modification',
		1 => 'Vous pouvez soit vous rendre directement au type de contribution désiré en parcourant la catégorie correspondante (outils, communication, sécurité, divertissement, etc.) ou soit utiliser la fonction de recherche qui est disponible en haut de la page.  Si vous utilisez la fonction de recherche, vous pouvez utiliser des jokers et aussi bien rechercher selon le nom de la contribution (ou une partie du nom) que selon l’auteur de la contribution.  Une fois que vous avez trouvé la contribution qui vous intéressait, vous serez redirigé vers la page des « Informations sur la contribution » sur laquelle vous pourrez télécharger aussi bien la version actuelle de la contribution que ses versions précédentes par l’intermédiaire de la section « Révisions ».'
	),
	array(
		0 => 'Rechercher un style',
		1 => 'De la même manière que vous recherchez une modification, Titania vous permet également de localiser des styles, des archives d’émoticônes, des images de rang et d’autres éléments.  La fonction de recherche vous permet également d’utiliser des jokers ou de ne rechercher que selon le nom des auteurs.  Une fois que vous avez trouvé l’élément qui vous intéressait, vous serez redirigé vers la page des « Informations sur la contribution » sur laquelle vous pourrez télécharger aussi bien la version actuelle de l’élément que ses versions précédentes par l’intermédiaire de la section « Révisions ».' 
	),
	array(
		0 => '--',
		1 => 'Support des contributions'
	),
	array(
		0 => 'Règles',
		1 => 'Avec l’arrivée de Titania, les règles qui ont évoluées afin de correspondre à son utilisation sont très simples.  Dans le passé, il était question de rechercher à obtenir du support dans le sujet correspondant à la modification ou au style où vous aviez téléchargé la contribution.  Pendant que l’équipe de support de phpBB.com faisait de son mieux afin de vous assister dans l’utilisation de votre forum, ils n’avaient ni le temps, ni le rôle, de vous aider en ce qui concerne l’utilisation des contributions.  À présent, nous incitons les auteurs des contributions à aider leurs utilisateurs.  Cependant, gardez en tête que tous les auteurs sont des bénévoles qui ont contribués à améliorer le logiciel phpBB.  Le proverbe « on attire plus de mouches avec du miel qu’avec du vinaigre » est donc de mise, veuillez donc prendre cela en considération lorsque vous demandez de l’aide pour une contribution (soyez reconnaissant).'
	),
	array(
		0 => 'Comment obtenir de l’aide ?',
		1 => 'Chaque contribution vous met à disposition au moins une méthode afin que vous puissiez obtenir du support.  L’auteur a, par exemple, la possibilité de publier une foire aux questions ou de vous répondre au cas par cas dans un espace dedié au support. Le support peut varier, allant de vous assister dans l’installation de la contribution jusqu’à même prendre le temps de vous fournir des add-ons pour améliorer cette dernière.  Pour accéder à cet espace de support, cliquez tout simplement sur la contribution, puis sur l’onglet « Discussion / Support » que vous devriez apercevoir. Une fois dans cet espace, vous pouvez poser une question à l’auteur ou lui publier des commentaires.  N’oubliez pas que les auteurs n’ont aucune obligation à vous aider tout comme ils n’ont aucune obligation à vous distribuer leurs contributions.  Si vous constatez qu’un message ou un commentaire n’est pas dans l’intêret de la communauté, n’hésitez pas à utiliser le bouton « Rapporter ce message » afin d’alerter un modérateur qui prendra toutes les mesures nécessaires.'
	),
	// This block will switch the FAQ-Questions to the second template column
	// Authors corner!!
	array(
		0 => '--',
		1 => '--'
	),
	array(
		0 => '--',
		1 => 'Création et gestion de contributions'
	),
	array(
		0 => 'Création d’une contribution',
		1 => 'Pour toute contribution, les auteurs sont invités à suivre certaines directives quand ils soumettent leur contribution.  Les <a href="http://area51.phpbb.com/docs/coding-guidelines.html">directives de codage</a>, même si elles peuvent être intimidantes au premier abord, sont là pour vous aider.  Elles doivent être respectées autant que possible afin de maximiser vos chances pour que votre contribution doit proposée à la communauté.  Dans le cas d’un MOD, le <a href="http://www.phpbb.com/mods/mpv/">pré-validateur de MODs de phpBB</a> (alias « MPV ») sera exécuté afin de vérifier dans votre contribution certaines informations telles que la licence, la version actuelle de phpBB et la version actuelle de <a href="http://www.phpbb.com/mods/modx/">MODX</a>.'
	),
	array(
		0 => 'Soumettre une contribution',
		1 => 'Vous avez donc créé une contribution. Faisons en sorte qu’elle soit maintenant publiée !<br /><br />Pour soumettre une contribution, allez dans la base de données des contributions où vous trouverez un bouton « Nouvelle contribution ».  Après avoir cliqué dessus, vous pourrez indiquer le nom de la contribution, sélectionner le type de contribution, spécifier quelques lignes afin de décrire la contribution (les émoticônes et les BBCodes sont autorisés), sélectionner un ou plusieurs catégorie(s) qui correspondent à la contribution, ajouter des co-auteurs (s’il y en a) et des captures d’écran.  Sachez que lorsque vous soumettez une contribution, cette dernière sera identifiée sous votre nom.'
	),
	array(
		0 => 'Gestion de contributions',
		1 => 'Une fois que vous avez transféré avec succès votre contribution à Titania, vous pouvez gérer cette dernière.  Après avoir sélectionné votre contribution en cliquant sur le lien « Mes contributions » situé en haut de la page, vous pouvez ajouter des informations complémentaires par l’intermédiaire de l’onglet « Gérer la contribution ».  Vous pouvez modifier la description de la contribution, transférer des captures d’écran, modifier le propriétaire de la contribution (notez que cette action est irréversible, assurez-vous donc de vouloir réellement transférer à un autre utilisateur la propriété de votre contribution), modifier les catégories qui correspondent à la contribution et indiquer un lien vers une démonstration où les utilisateurs peuvent découvrir l’aspect et les fonctionnalités de votre contribution.'
	),
	array(
		0 => 'Soumettre une nouvelle révision',
		1 => 'Vous pouvez transférer de nouvelles révisions sur la page principale, la section « Informations sur la contribution », de votre contribution.  Aprés avoir cliqué sur le lien « Nouvelle révision », une nouvelle page vous permettra de transférer une nouvelle révision, de lui assigner un numéro de version et de laisser une note à l’équipe de validation (les émoticônes et les BBCodes sont autorisés).  Vous pouvez également donner l’autorisation à l’équipe de validation de « repaqueter » la contribution.  Le repaquetage permet de corriger des erreurs mineures présentes dans la contribution.  Cela peut impliquer des corrections du fichier d’installation MODX ou des modifications mineures du code.  Le repaquetage ne consiste <strong>pas</strong> à ce que l’équipe de validation ré-écrive la majeure partie du code de votre contribution, ce qui censé être votre « travail ».<br /><br />Les règles qui s’appliquent lors de la création d’une contribution sont toujours de rigueur lorsque vous soumettez une nouvelle révision de votre contribution.  De même, le <a href="http://www.phpbb.com/mods/mpv/">pré-validateur de MODs de phpBB</a> (alias « MPV ») sera exécuté afin de vérifier dans votre contribution certaines informations telles que la licence, la version actuelle de phpBB et la version actuelle de <a href="http://www.phpbb.com/mods/modx/">MODX</a>.'
	),
	array(
		0 => '--',
		1 => 'Distribution de support'
	),
	array(
		0 => 'FAQ',
		1 => 'Chaque contribution permet à l’auteur de publier des sujets dans la FAQ.  Ces sujets doivent être rédigés le plus clairement possible afin que les utilisateurs puissent les comprendre et les appliquer.  Il est donc conseillé de rédiger un guide d’installation, d’utilisation, etc.  Veuillez noter que seuls les auteurs de la contribution peuvent créer de nouveaux éléments.  Les utilisateurs ne peuvent pas éditer ou répondre aux éléments de la FAQ.'
	),
	array(
		0 => 'Forum de support',
		1 => 'Gardez en tête que les utilisateurs pourront poser des questions ou rédiger des commentaires concernant votre contribution.  Nous apprécieront que vous vous occupiez du support de votre contribution autant que vous le pouvez.  Nous sommes bien conscients que vous avez déjà passé beaucoup de votre temps de libre à créer votre contribution et que vous préfereriez à présent l’utiliser à d’autres fins.  Nous demandons seulement à ce que les auteurs tels que vous puissent aider leurs utilisateurs.  Si vous constatez qu’un message ou un commentaire n’est pas dans l’intêret de la communauté, n’hésitez pas à utiliser le bouton « Rapporter ce message » afin d’alerter un modérateur qui prendra toutes les mesures nécessaires.'
	),
	array(
		0 => '--',
		1 => 'Validation'
	),
	array(
		0 => 'Ma contribution ne passe pas l’étape de pré-validation',
		1 => 'Souvenez-vous que chaque contribution DOIT être proposée sous la bonne licence (actuellement, la GNU GPL version 2) et que la version du logiciel phpBB et de MODx doit être la plus récente.  Si votre contribution ne respecte pas ces pré-requis, elle ne pourra pas être acceptée dans notre base de données.  Certaines erreurs sont de simples avertissements et ne nécessitent pas forcément de corrections.  Si vous n’êtes pas certain qu’il s’agisse réellement d’un problème, n’hésitez pas à continuer, un validateur s’occupera de vérifier cela pour vous.'
	),
	array(
		0 => 'Ma contribution a passé l’étape de pré-validation, que se passe t-il ensuite ?',
		1 => 'Une fois votre contribution a été acceptée dans la base de données, une des équipes de validation se chargera de valider votre contribution.  Vous pouvez vous retrouver à recevoir un message vous indiquant que votre contribution a été refusée.  Ne vous offusquez pas.  Le message contiendra les éléments vous aidant à identifier et à corriger les problèmes.  Ces éléments peuvent vous suggérer de modifier une partie de votre code ou certaines de vos images, ou encore de vous suggérer d’améliorer l’ergonomie de votre contribution.  Bien souvent, les suggestions concernant l’ergonomie ne sont que des conseils.  La partie la plus critique d’une contribution concerne la sécurité.<br /><br />Si rien n’a été trouvé durant la procédure de validation de votre contribution, vous recevrez un message privé vous indiquant que votre contribution a été approuvée dans la base de données.  Il sera alors temps de vous décontracter et de vous rendre compte que vous avez participé à l’enrichissement de la communauté.<br /><br />Peu importe le résultat de la validation, nous vous sommes reconnaissants du temps que vous avez passé et de l’effort que vous avez fourni afin de partager votre contribution.'
	),
	array(
		0 => 'Qui s’occupe de la validation de ma contribution ?',
		1 => 'S’il s’agit d’une modification, elle sera validée par l’équipe des modifications, l’équipe des validateurs juniors des modifications et, occasionnellement, par un membre de l’équipe de développement.  S’il s’agit d’un style, il sera validé par l’équipe des styles et l’équipe des validateurs juniors des styles.  S’il s’agit d’un convertisseur, il sera validé par l’équipe de support et l’équipe de développement.  S’il s’agit d’un bridge, il sera validé par l’équipe des modifications et l’équipe de développement.  S’il s’agit d’une archive de langue, elle sera validée par le responsable international.  S’il s’agit d’un outil officiel, il est créé puis validé par les différentes équipes de phpBB.com.'
	),
);

?>