<?php
/**
* titania ucp language [English]
*
* @package language
* @version $Id: info_ucp_titania.php 1071 2010-04-17 05:10:36Z exreaction $
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
	'NO_SECTIONS'					=> 'No está subscrito a cualquiera de las secciones.',
	'NO_ITEMS'						=> 'Usted no se ha subscrito a ningún artículo.',
	'NO_SUBSCRIPTIONS_SELECTED'		=> 'No hay subscripciones seleccionadas.',
	'NO_TYPES_SELECTED'				=> 'Ningún tipo de subscripciones seleccionadas.',
	
	'SUBSCRIPTION_ATTENTION'			=> 'Subscripción en cola',
	'SUBSCRIPTION_CONTRIB'				=> 'Colaboraciones',
	'SUBSCRIPTION_ITEMS_MANAGE'			=> 'Administrar subscripciones de temas',
	'SUBSCRIPTION_SECTIONS_MANAGE'		=> 'Administrar secciones de subscripciones',
	'SUBSCRIPTION_ITEMS_MANAGE_EXPLAIN'	=> 'A continuación se muestra una lista de artículos a los que usted está subscrito a la Base de Descargas. Se le notificará de nuevos temas en cualquiera. <br />Para darse de baja marca los elementos y pulse el botón No subscribirse marcados.',
	'SUBSCRIPTION_SECTIONS_MANAGE_EXPLAIN'	=> 'A continuación se muestra una lista de secciones que están suscritos a la base de modificaciones. Se le notificará de nuevos puestos en cualquiera. <br />Para darse de baja marcar las secciones  y a continuación, pulse el botón dejar de observar marcados.',
	'SUBSCRIPTION_QUEUE'			=> 'Cola de validación',
	'SUBSCRIPTION_QUEUE_VALIDATION'	=> 'Validación de discusión',
	'SUBSCRIPTION_SUPPORT_TOPIC'	=> 'Tema de soporte',
	'SUBSCRIPTION_TARGET'			=> 'objetivo',
	'SUBSCRIPTION_TITANIA'			=> 'Subscripciones a la Base de Descargas',
	'SUBSCRIPTION_TOPIC'			=> 'Tema',
	'SUBSCRIPTION_SUPPORT'			=> 'Tema de discusión/soporte',
	
	'UNWATCH_SUBSCRIPTION_MARKED'	=> 'Dejar de observar marcados',
	'UNWATCHED_SUBSCRIPTIONS'		=> 'Usted ya no está suscrito a las suscripciones seleccionadas.',
	
	'WATCHED_SECTIONS'				=> 'Secciones vistas',
	'WATCHED_SINCE'					=> 'Vistas desde',
	'WATCHED_ITEMS'					=> 'Vistos los artículos',
));

?>