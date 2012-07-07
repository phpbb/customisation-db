<?php
/**
*
* @package Titania
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

$lang = array_merge($lang, array(
'CUSTOM_LICENSE' => 'Personalizar',
	'ANNOUNCEMENT_TOPIC'					=> 'Tema de anuncio',
	'ANNOUNCEMENT_TOPIC_SUPPORT'			=> 'Tema de soporte',
	'ANNOUNCEMENT_TOPIC_VIEW'				=> '%sVer%s',
	'ATTENTION_CONTRIB_CATEGORIES_CHANGED'	=> '<strong>Categoría de contribución cambiada</strong><br />%1$s<br /><br /><strong>a:</strong><br />%2$s',
	'ATTENTION_CONTRIB_DESC_CHANGED'		=> '<strong>Cambiar descripción de la contribución:</strong><br />%1$s<br /><br /><strong>a:</strong><br />%2$s',
	'AUTOMOD_RESULTS'						=> '<strong>Por favor, consulte los resultados de la instalación con Automod y asegurarse de que no hay nada que arreglar.<br /><br />Si aparece un error y está seguro de que el error no es correcto, simplemente pulse siguiente a continuación.</strong>',
	'AUTOMOD_TEST'							=> 'El Mod se pondrá a prueba contra AutoMod y los resultados se mostraran (esto puede tardar unos minutos, así que por favor sea paciente).<br /><br />Por favor, pulse continuar cuando esté listo.',

	'BAD_VERSION_SELECTED'					=> '%s su versión de phpBB no esta actualizada.',

	'CANNOT_ADD_SELF_COAUTHOR'				=> 'Usted es el autor principal, no puede agregarse a la lista de CO-Autores.',
    'CEASE_REQUEST'							=> 'No volver a preguntar',
	'CLEANED_CONTRIB'						=> 'Limpiar contribución',
	'CONTRIB'								=> 'Contribución',
	'CONTRIBUTIONS'							=> 'Contribuciones',
	'CONTRIB_ACTIVE_AUTHORS'				=> 'Co-Autores Activos',
	'CONTRIB_ACTIVE_AUTHORS_EXPLAIN'		=> 'Co-Autores Activos, pueden activar la mayor parte de la contribución',
	'CONTRIB_APPROVED'						=> 'Aprobado',
	'CONTRIB_AUTHOR'						=> 'Autor de la contribución',
	'CONTRIB_AUTHORS_EXPLAIN'				=> 'Escriba los nombres de los CO-Autores, un nombre de usuario CO-Autor por línea.',
	'CONTRIB_CATEGORY'						=> 'Categoría de la contribución',
	'CONTRIB_CHANGE_OWNER'					=> 'Cambiar autor',
	'CONTRIB_CHANGE_OWNER_EXPLAIN'			=> 'Introduzca un nombre de usuario aquí para ponerlo como autor. Al cambiar esto, se establece como un autor para no contribuir.',
	'CONTRIB_CHANGE_OWNER_NOT_FOUND'		=> 'El usuario que ha intentado establecer como autor, %s, no se encontró.',
	'CONTRIB_CLEANED'						=> 'Limpiar',
	'CONTRIB_CONFIRM_OWNER_CHANGE'			=> '¿Está seguro que desea asignar como autor  a %s? Esto evitará que siga con la gestión del proyecto y no se puede revertir.',
	'CONTRIB_CREATED'						=> 'La contribución ha sido creada correctamnete',
	'CONTRIB_DEMO'							=> 'Demo',
	'CONTRIB_DESCRIPTION'					=> 'Descripción de la contribución',
	'CONTRIB_DETAILS'						=> 'Detalles de contribución',
	'CONTRIB_DISABLED'						=> 'Ocultar + Desactivar',
	'CONTRIB_DOWNLOAD_DISABLED'				=> 'Descargas desactivadas',
	'CONTRIB_EDITED'						=> 'La contribución ha sido editada correctamente',
	'CONTRIB_HIDDEN'						=> 'Oculto',
	'CONTRIB_ISO_CODE'						=> 'Código ISO',
	'CONTRIB_ISO_CODE_EXPLAIN'				=> 'Código ISO de acuerdo a <a href="http://area51.phpbb.com/docs/coding-guidelines.html#translation">Instrucciones de codificación de traducción</a>.',
	'CONTRIB_LOCAL_NAME'					=> 'Nombre local',
	'CONTRIB_LOCAL_NAME_EXPLAIN'			=> 'Nombre local traducido, ej. <em>Frances</em>.',
	'CONTRIB_NAME'							=> 'Nombre de la contribución',
	'CONTRIB_NAME_EXISTS'					=> 'El nombre único ya ha sido reservado',
	'CONTRIB_NEW'							=> 'Nuevo',
	'CONTRIB_NONACTIVE_AUTHORS'				=> 'No activar CO-Autores (autores anteriores)',
	'CONTRIB_NONACTIVE_AUTHORS_EXPLAIN'		=> 'No activar CO-Autores, no pueden administrar la contribución y sólo aparecen como autores anteriores.',
	'CONTRIB_NOT_FOUND'						=> 'No se encuentra la contribución solicitada',
	'CONTRIB_OWNER_UPDATED'					=> 'El autor ha sido modificado.',
	'CONTRIB_PERMALINK'						=> 'Enlace permanente de la contribución',
	'CONTRIB_PERMALINK_EXPLAIN'				=> 'Limpiar versión del nombre de la contribución, que se utiliza para construir la URL de la contribución.<br /><strong>Dejar en blanco para crear automáticamente un enlace basado en el nombre de la contribución.</strong>',
	'CONTRIB_RELEASE_DATE'					=> 'Fecha de publicación',
	'CONTRIB_STATUS'						=> 'Estado de contribución',
	'CONTRIB_STATUS_EXPLAIN'				=> 'Cambiar estado de contribución',
	'CONTRIB_TYPE'							=> 'Tipo de contribución',
	'CONTRIB_UPDATED'						=> 'La contribución ha sido actualizada correctamente.',
	'CONTRIB_UPDATE_DATE'					=> 'Ultima actualización',
	'COULD_NOT_FIND_ROOT'					=> 'No se pudo encontrar el directorio principal. Por favor, asegurarse de que existe un archivo install.xml en el archivo zip.',
	'COULD_NOT_FIND_USERS'					=> 'No se encontraron los siguientes usuarios: %s',
	'COULD_NOT_OPEN_MODX'					=> 'No se pudo abrir el archivo de MODx.',
	'CO_AUTHORS'							=> 'Co-Autores',

	'DELETE_CONTRIBUTION'					=> 'Eliminar contribución',
	'DELETE_CONTRIBUTION_EXPLAIN'			=> 'Elimine de forma permanente esta contribución (uso el campo de estado contribución si que hay que ocultar).',
	'DELETE_REVISION'						=> 'Eliminar revisión',
	'DELETE_REVISION_EXPLAIN'				=> 'Elimine de forma permanente esta revisión (usar el campo estado de revisión, si que hay que ocultar).',
	'DEMO_URL'								=> 'URL de la demo',
	'DEMO_URL_EXPLAIN'						=> 'Ubicación de la demo',
	'DOWNLOADS_PER_DAY'						=> '%.2f Descargas por día',
	'DOWNLOADS_TOTAL'						=> 'Descargas totales',
	'DOWNLOADS_VERSION'						=> 'Versión de la descargas',
	'DOWNLOAD_CHECKSUM'						=> 'MD5 checksum',
	'DUPLICATE_AUTHORS'						=> 'Usted tiene los siguientes autores que figuran como activos y no activos (no pueden ser a la vez): %s',
 
	'EDIT_REVISION'							=> 'Editar revisión',
	'EMPTY_CATEGORY'						=> 'Seleccionar una categoria menos',
	'EMPTY_CONTRIB_DESC'					=> 'Introduzca la descripción de la contribución',
	'EMPTY_CONTRIB_ISO_CODE'				=> 'Ingrese el código ISO ',
	'EMPTY_CONTRIB_LOCAL_NAME'				=> 'Introduzca el nombre local',
	'EMPTY_CONTRIB_NAME'					=> 'Introduzca el nombre de la contribución',
	'EMPTY_CONTRIB_PERMALINK'				=> 'Ingrese su propuesta de enlace permanente para la contribución',
	'EMPTY_CONTRIB_TYPE'					=> 'Seleccione al menos un tipo de contribución',
	'ERROR_CONTRIB_EMAIL_FRIEND'			=> 'No se permite recomendar esta contribución a otra persona',

	'INSTALL_LESS_THAN_1_MINUTE'			=> 'Menos de un minuto',
	'INSTALL_LEVEL'							=> 'Nivel de instalación',
	'INSTALL_LEVEL_1'						=> 'Fácil',
	'INSTALL_LEVEL_2'						=> 'Intermedio',
	'INSTALL_LEVEL_3'						=> 'Avanzado',
	'INSTALL_MINUTES'						=> 'Alrededor de %s minuto(s)',
	'INSTALL_TIME'							=> 'Tiempo de instalación',
	'INVALID_LICENSE'                       => 'Licencia no válida',
	'INVALID_PERMALINK'						=> 'Es necesario introducir un enlace permanente válido, por ejemplo:: %s',

	'LICENSE'								=> 'Licencia',
	'LICENSE_EXPLAIN'						=> 'Licencia para liberar su trabajo',
	'LICENSE_FILE_MISSING'					=> 'El paquete debe contener un archivo license.txt que contenga los términos de licencia, ya sea en el directorio principal o en un subdirectorio.',
	'LIMITED_SUPPORT'						=> 'No se da soporte',
	'LIMITED_SUPPORT_EXPLAIN'				=> 'Mostrar aviso de que el autor no va  dar más soporte',
	'LIMITED_SUPPORT_WARN'					=> '<strong>Aviso:</strong> El autor de esta contribución ya no da soporte',
	'LOGIN_EXPLAIN_CONTRIB'					=> 'Para crear una nueva contribución debe estar registrado',

	'MANAGE_CONTRIBUTION'					=> 'Administrar contribución',
	'MPV_RESULTS'							=> '<strong>Por favor, consulte sobre los resultados MPV y asegurarse de que no hay nada que arreglar.<br /><br />Si usted no cree que cualquier cosa requiere la fijación o no está seguro, simplemente pulse siguiente a continuación.</strong>',
	'MPV_TEST'								=> 'El Mod se pondrá a prueba contra VP y los resultados se mostraran (esto puede tardar unos minutos, así que por favor sea paciente). <br /> <br /> Por favor, pulsa continuar cuando esté listo.',
	'MPV_TEST_FAILED'						=> 'Lo sentimos, el VP no pruebas automáticas y resultados de la prueba VP no están disponibles. Por favor, continúe.',
	'MPV_TEST_FAILED_QUEUE_MSG'				=> 'Pruebas automatizadas MPV fallidas. [url=%s]Haga clic aquí para intentar ejecutar de nuevo MPV de forma automática [/url]',
	'MUST_SELECT_ONE_VERSION'				=> 'Debe seleccionar al menos una versión de phpBB',

	'NEW_CONTRIBUTION'						=> 'Nueva contribución',
	'NEW_REVISION'							=> 'Nueva revisión',
	'NEW_REVISION_SUBMITTED'				=> 'La nueva revisión ha sido enviada correctamente',
	'NEW_TOPIC'								=> 'Nuevo tema',
	'NOT_VALIDATED'							=> 'No ha sido validado',
	'NO_CATEGORY'							=> 'La categoría seleccionada no existe',
	'NO_PHPBB_BRANCH'						=> 'Debe seleccionar una rama de phpBB',
	'NO_QUEUE_DISCUSSION_TOPIC'				=> 'Ningún tema de discusión de cola se ha encontrado. ¿Ha presentado alguna revisión de esta contribución?',
	'NO_REVISIONS'							=> 'No hay revisiones',
	'NO_REVISION_ATTACHMENT'				=> 'Por favor seleccione un archivo para cargar',
	'NO_REVISION_VERSION'					=> 'Por favor introduzca la versión para la revisión',
	'NO_SCREENSHOT'							=> 'No hay capturas de pantalla',
	'NO_TRANSLATION'						=> 'El archivo no parece ser un paquete de idioma válido. Por favor, asegúrese de que contiene todos los archivos que se encuentran en el directorio del idioma Inglés',

	'PHPBB_BRANCH'							=> 'Rama phpBB',
	'PHPBB_BRANCH_EXPLAIN'					=> 'Seleccione la rama phpBB que esta revisión apoya.',
	'PHPBB_VERSION'							=> 'Versión phpBB',

	'QUEUE_ALLOW_REPACK'					=> 'Permitir reempaquetado',
	'QUEUE_ALLOW_REPACK_EXPLAIN'			=> '¿Permite que se corrijan los errores que se encuentren en esta contribución?',
	'QUEUE_NOTES'							=> 'Notas de validación',
	'QUEUE_NOTES_EXPLAIN'					=> 'Mensaje del equipo.',

	'REPORT_CONTRIBUTION'					=> 'Reportar Contribución',
	'REPORT_CONTRIBUTION_CONFIRM'			=> 'Utilice este formulario para informar  a los moderadores y administradores sobre la contribución seleccionada. Presentación de informes en general, se debe utilizar sólo si la contribución  rompe las reglas del foro.',
	'REVISION'								=> 'Revisión',
	'REVISIONS'								=> 'Revisiones',
	'REVISION_APPROVED'						=> 'Aprobado',
	'REVISION_DENIED'						=> 'Denegado',
	'REVISION_IN_QUEUE'						=> 'Usted ya tiene una revisión en la cola de validación. Usted debe esperar hasta que la revisión anterior sea aprobada o negada para presentar una nueva',
	'REVISION_NAME'							=> 'Nombre de la revisión',
	'REVISION_NAME_EXPLAIN'					=> 'Entre un nombre opcional para esta versión (por ejemplo: peludo Edition)',
	'REVISION_NEW'							=> 'Nuevo',
	'REVISION_PENDING'						=> 'Pendientes',
	'REVISION_PULLED_FOR_OTHER'				=> 'Extraer',
	'REVISION_PULLED_FOR_SECURITY'			=> 'Extraer - Seguridad',
	'REVISION_REPACKED'						=> 'Reempaquetar',
	'REVISION_RESUBMITTED'					=> 'Volver a presentar',
	'REVISION_STATUS'						=> 'Estado de la revisión',
	'REVISION_STATUS_EXPLAIN'				=> 'Cambiar estado de la revisión',
	'REVISION_SUBMITTED'					=> 'La revisión se ha enviado correctamente',
	'REVISION_VERSION'						=> 'Versión de la revisión',
	'REVISION_VERSION_EXPLAIN'				=> 'Numero de versión de este paquete',

	'SCREENSHOTS'							=> 'Capturas',
	'SELECT_CONTRIB_TYPE'					=> '-- Seleccionar tipo de contribución --',
	'SELECT_PHPBB_BRANCH'					=> 'Seleccionar rama de phpBB',
	'SUBDIRECTORY_LIMIT'					=> 'Los paquetes no pueden tener más de 50 subdirectorios en cualquier momento.',
	'SUBMIT_NEW_REVISION'					=> 'Enviar y presentar nueva revisión',
    'SUBSCRIBE_QUEUE_DISCUSSION'			=> 'Subscribirse al tema en la cola de discusión',
	'SUBSCRIPTION_REQUEST'					=> '¿Le gustaría suscribirse a la contribución a fin de recibir notificaciones por email sobre nuevas versiones y vulnerabilidades de seguridad?',

	'TOO_MANY_TRANSLATOR_LINKS'				=> 'En este momento está usando %d los enlaces externos dentro de la línea Traducción/TRANSLATION_INFO. Por favor, sólo incluya <strong>un enlace</strong>. Incluya dos enlaces sólo se permiten tras caso-a-caso - por favor, vaya al foro de traducciones y ponga sus razones para poner más enlaces externos dentro de la línea.',

	'VALIDATION_TIME'						=> 'Tiempo de validación',
	'VIEW_DEMO'								=> 'Ver demo',
	'VIEW_INSTALL_FILE'						=> 'Ver archivo de instalación',

	'WRONG_CATEGORY'						=> 'Sólo se puede poner esta contribución en la misma categoría que el tipo de contribución.',
));