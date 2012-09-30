<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* Traducción hecha y revisada por nextgen <http://www.melvingarcia.com>
* Traductores anteriores angelismo y sof-teo
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
	'ACCESS_LIMIT_AUTHORS'		=> 'Nivel de acceso limitado al autor',
	'ACCESS_LIMIT_TEAMS'		=> 'Nivel de acceso limitado al equipo',
	'ADD_FIELD'					=> 'Agregar campo',
	'AGREE'						=> 'Estoy de acuerdo',
	'AGREEMENT'					=> 'Acuerdo',
	'ALL'						=> 'Todos',
	'ALL_CONTRIBUTIONS'			=> 'Todas las contribuciones',
	'ALL_SUPPORT'				=> 'Todos los temas de soporte',
	'AUTHOR_BY'					=> 'Por %s',

	'BAD_RATING'				=> 'Intento de evaluación fallida',
	'BY'						=> 'por',

	'CACHE_PURGED'				=> 'Cache purgado correctamente',
	'CATEGORY'					=> 'Categoría',
	'CATEGORIES'				=> 'Categorías',
	'CATEGORY_CHILD_AS_PARENT'	=> 'La categoría principal elegida no puede ser seleccionada porque es un hijo de esta categoría.',
	'CATEGORY_DELETED'			=> 'Categoría eliminada',
	'CATEGORY_DESC'				=> 'Descripción de la categoria',
	'CATEGORY_DUPLICATE_PARENT'	=> 'La categoría no puede ser su propia padre',
	'CATEGORY_HAS_CHILDREN'		=> 'Esta categoría no se pueden eliminar, ya que contiene categorías menores.',
	'CATEGORY_INFORMATION'		=> 'Información de la categoría',
	'CATEGORY_NAME'				=> 'Nombre de categoría',
	'CATEGORY_OPTIONS'			=> 'Opciones de categoría',	
	'CATEGORY_TYPE'				=> 'Tipo de categoría',
	'CATEGORY_TYPE_EXPLAIN'		=> 'El tipo de contribuciones en esta categoría se llevara a cabo. Deja sin definir para no aceptar contribuciones.',
	'CAT_ADDONS'				=> 'Accesorios',
	'CAT_ANTI_SPAM'				=> 'Anti-Spam',
	'CAT_AVATARS'				=> 'Avatares',
	'CAT_BOARD_STYLES'			=> 'Estilos',
	'CAT_COMMUNICATION'			=> 'Comunicación',
	'CAT_COSMETIC'				=> 'Cosmético',
	'CAT_ENTERTAINMENT'			=> 'Entretenimiento',
	'CAT_LANGUAGE_PACKS'		=> 'Paquetes de idiomas',
	'CAT_MISC'					=> 'Varios',
	'CAT_MODIFICATIONS'			=> 'Modificaciones',
	'CAT_PROFILE_UCP'			=> 'Perfil/Panel de control del usuario',
	'CAT_RANKS'					=> 'Rangos',
	'CAT_SECURITY'				=> 'Seguridad',
	'CAT_SMILIES'				=> 'Smilies',
	'CAT_SNIPPETS'				=> 'Fragmentos',
	'CAT_STYLES'				=> 'Estilos',
	'CAT_TOOLS'					=> 'Herramientas',
	'CLOSED_BY'					=> 'Cerrado por',
	'CLOSED_ITEMS'				=> 'Preguntas cerradas',
	'COLORIZEIT_COLORS'         => 'Esquema de color',
	'COLORIZEIT_DOWNLOAD'       => 'Cambiar esquema de color.',
	'COLORIZEIT_DOWNLOAD_STYLE' => 'Cambiar el esquema de color y descarga',
	'COLORIZEIT_MANAGE'         => 'Configuración de ColorizeIt',
	'COLORIZEIT_MANAGE_EXPLAIN' => 'Para activar ColorizeIt para este estilo, es necesario que cargue por defecto la imagen de la muestra y cambiar el esquema de color por defecto. La imagen de la muestra debe estar en formato GIF, no debe ser animado y el tamaño debe estar entre 200x300 y 500X600 píxeles. La muestra no debe ser escalada, no debe incluir los colores que no están disponibles en el estilo, no debe incluir texto suavizado. <a href="http://www.colorizeit.com/advanced.html?do=tutorial_sample">Sigue esta URL</a> para leer el tutorial completo.',
	'COLORIZEIT_SAMPLE'         => 'Mostrar el editor de esquemas de colores',
	'COLORIZEIT_SAMPLE_EXPLAIN' => 'Añadir colores a la editor para recogerlos a partir de una imagen de muestra, a continuación copiar la cadena esquema de colores del campo de texto debajo de editor para el campo de texto debajo de este texto y haga clic en "Enviar" para guardar los cambios.',	
	'CONFIRM_PURGE_CACHE'		=> '¿Estás seguro de que desea purgar el caché?',
	'CONTINUE'					=> 'Continuar',
	'CONTRIBUTION'				=> 'Contribución',
	'CONTRIBUTIONS'				=> 'Contribuciones',
	'CONTRIB_FAQ'				=> 'FAQ',
	'CONTRIB_MANAGE'			=> 'Administrar contribución',
	'CONTRIB_SUPPORT'			=> 'Discusión/Soporte',
	'CREATE_CATEGORY'			=> 'Crear categoría',
	'CREATE_CONTRIBUTION'		=> 'Crear contribución',
	'CUSTOMISATION_DATABASE'	=> 'Base de descargas',

	'DATE_CLOSED'				=> 'Fecha de cierre',
	'DELETED_MESSAGE'			=> 'Eliminado por %1$s en %2$s - <a href="%3$s">Haga clic aquí para recuperar este mensaje</a>',
	'DELETE_ALL_CONTRIBS'		=> 'Eliminar todas las contribuciones',
	'DELETE_CATEGORY'			=> 'Eliminar categoría',
	'DELETE_SUBCATS'			=> 'Eliminar subcategorías',
	'DESCRIPTION'				=> 'Descripción',
	'DESTINATION_CAT_INVALID'	=> 'La categoría de destino no es capaz de aceptar contribuciones.',
	'DETAILS'					=> 'Detalles',
	'DOWNLOAD'					=> 'Descarga',
	'DOWNLOADS'					=> 'Descargas',
	'DOWNLOAD_ACCESS_DENIED'	=> 'No se  permite descargar el archivo solicitado.',
	'DOWNLOAD_NOT_FOUND'		=> 'El archivo solicitado no se encuentra',

	'EDIT'						=> 'Editar',
	'EDITED_MESSAGE'			=> 'Última edición por %1$ s en %2$s',
	'EDIT_CATEGORY'				=> 'Editar categoría',
	'ERROR'						=> 'Error',

	'FILE_NOT_EXIST'			=> 'El archivo no existe %s',
	'FIND_CONTRIBUTION'			=> 'Encontrar contribución',

	'HARD_DELETE'				=> 'Eliminación definitiva',
	'HARD_DELETE_EXPLAIN'		=> 'Seleccione esta opción para eliminar de forma permanente este tema.',
	'HARD_DELETE_TOPIC'			=> 'Eliminar tema definitivamente',

	'LANGUAGE_PACK'				=> 'Paquete de traducción',
	'LIST'						=> 'Lista',

	'MAKE_CATEGORY_VISIBLE'		=> 'Hacer visible la categoría',
	'MANAGE'					=> 'Administrar',
	'MARK_CONTRIBS_READ'		=> 'Marcar contribuciones como leídas',
	'MOVE_CONTRIBS_TO'			=> 'Mover las contribuciones a',
	'MOVE_DOWN'					=> 'Mover abajo',
	'MOVE_SUBCATS_TO'			=> 'Mover subcategorías a',
	'MOVE_UP'					=> 'Mover arriba',
	'MULTI_SELECT_EXPLAIN'		=> 'Mantenga presionada la tecla CTRL y haga clic para seleccionar varios elementos.',
	'MY_CONTRIBUTIONS'			=> 'Mis Contribuciones',

	'NAME'						=> 'Nombre',
	'NEW_REVISION'				=> 'Nueva revisión',
	'NOT_AGREE'					=> 'No estoy de acuerdo',
	'NO_AUTH'					=> 'Usted no está autorizado a ver esta página.',
	'NO_CATEGORY'				=> 'La categoría solicitada no existe',
	'NO_CATEGORY_NAME'			=> 'Escriba el nombre de la categoría',
	'NO_CONTRIB'				=> 'La contribución solicitada no existe',
	'NO_CONTRIBS'				=> 'Contribuciones no encontradas',
	'NO_DESC'					=> 'Tienes que escribir una descripción',
	'NO_DESTINATION_CATEGORY'	=> 'No se puede encontrar la categoría de destino.',
	'NO_POST'					=> 'El mensaje solicitado no existe',
	'NO_REVISION_NAME'			=> 'Ningún nombre de revisión ha sido previsto',
	'NO_TOPIC'					=> 'El tema solicitado no existe',

	'ORDER'						=> 'Ordenar',

	'PARENT_CATEGORY'			=> 'Categoría padre',
	'PARENT_NOT_EXIST'			=> 'Padre no existe',
	'POST_IP'					=> 'Post IP',
	'PURGE_CACHE'				=> 'Purgar cache',

	'QUEUE'						=> 'Cola',
	'QUEUE_DISCUSSION'			=> 'Cola de discusión',
	'QUICK_ACTIONS'				=> 'Acciones rápidas',

	'RATING'					=> 'Valoración',
	'REMOVE_RATING'				=> 'Eliminar valoración',
	'REPORT'					=> 'Reportar',
	'RETURN_LAST_PAGE'			=> 'Volver a la pagina anterior',
	'ROOT'						=> 'Raíz',

	'SEARCH_UNAVAILABLE'		=> 'El sistema de búsqueda no está disponible actualmente. Por favor, inténtelo de nuevo en unos minutos.',
	'SELECT_CATEGORY'			=> '-- Seleccionar categoría --',
	'SELECT_CATEGORY_TYPE'		=> '-- Seleccionar tipo de categoría --',
	'SELECT_SORT_METHOD'		=> 'Ordenar por',
	'SHOW_ALL_REVISIONS'		=> 'Mostrar todas las revisiones',
	'SITE_INDEX'				=> 'Índice general',
	'SNIPPET'					=> 'Fragmento',
	'SOFT_DELETE_TOPIC'			=> 'Eliminar tema',
	'SORT_CONTRIB_NAME'			=> 'Nombre de contribución',
	'STICKIES'					=> 'Fijos',
	'SUBSCRIBE'					=> 'Subscribir',
	'SUBSCRIPTION_NOTIFICATION'	=> 'Notificación de subscripción',	
	'SUCCESSBOX_TITLE'			=> 'Exitoso',
	'SYNC_SUCCESS'				=> 'Sincronización éxitosa',	

	'TITANIA_DISABLED'			=> 'La base de descargas está temporalmente desactivada, por favor, inténtelo de nuevo en unos minutos.',
	'TITANIA_INDEX'				=> 'Base de descargas',
	'TOTAL_CONTRIBS'			=> '%d Contribuciones',
	'TOTAL_CONTRIBS_ONE'		=> '1 Contribución',
	'TOTAL_POSTS'				=> '%d mensajes',
	'TOTAL_POSTS_ONE'			=> '1 mensaje',
	'TOTAL_RESULTS'				=> '%d resultados',
	'TOTAL_RESULTS_ONE'			=> '1 resultado',
	'TOTAL_TOPICS'				=> '%d temas',
	'TOTAL_TOPICS_ONE'			=> '1 tema',
	'TRANSLATION'				=> 'Traducción',
	'TRANSLATIONS'				=> 'Paquetes de traducciones',	
	'TYPE'						=> 'Tipo',

	'UNDELETE_TOPIC'			=> 'Recuperar tema',
	'UNKNOWN'					=> 'Desconocido',
	'UNSUBSCRIBE'				=> 'Cancelar subscripción',
	'UPDATE_TIME'				=> 'Actualizado',

	'VERSION'					=> 'Versión',
	'VIEW'						=> 'Ver',
));
