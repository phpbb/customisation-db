<?php
/**
*
* captcha_qa [Spanish]

* @package language
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* Traducción hecha y revisada por nextgen <http://www.melvingarcia.com>
* Traductores anteriores angelismo y sof-teo
*/

/**
* DO NOT CHANGE
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
	'ADDITIONAL_CHANGES'					=> 'Cambios disponibles',
	'AM_MANUAL_INSTRUCTIONS'				=> 'Automod está enviando un archivo comprimido a su ordenador. Debido a la configuración Automod, los archivos no se pueden escribir en su sitio automáticamente. Usted tendrá que extraer el archivo y subir los archivos a su servidor de forma manual, usando un cliente FTP o método similar. Si usted no recibe este archivo automáticamente, haga clic %saqui%s',
	'AM_MOD_ALREADY_INSTALLED'				=> 'Automod ha detectado este MOD ya está instalado y no puede continuar.',
	'APPLY_TEMPLATESET'						=> 'A esta plantilla',
	'APPLY_THESE_CHANGES'					=> 'Aplicar estos cambios',
	'AUTHOR_EMAIL'							=> 'Correo electrónico del autor',
	'AUTHOR_INFORMATION'					=> 'Información del autor',
	'AUTHOR_NAME'							=> 'Nombre del autor',
	'AUTHOR_NOTES'							=> 'Notas del autor',
	'AUTHOR_URL'							=> 'URL de autor',
	'AUTOMOD'								=> 'Automod',
	'AUTOMOD_CANNOT_INSTALL_OLD_VERSION'	=> 'La versión de Automod que está intentando instalar ya se ha instalado. Por favor, eliminar esta instalación/directorio.',
	'AUTOMOD_INSTALLATION'					=> 'Automod instalación',
	'AUTOMOD_INSTALLATION_EXPLAIN'			=> 'Bienvenido a la instalación Automod. Usted necesitará los datos de su FTP si Automod detecta que es la mejor manera de escribir los archivos. Los requisitos resultado del test están por debajo.',
	'AUTOMOD_UNKNOWN_VERSION'				=> 'Automod no fue capaz de ponerlo al día porque no se pudo determinar la versión actualmente instalada. La versión de la lista para su instalación es %s.',
	'AUTOMOD_VERSION'						=> 'Versión Automod',

	'CAT_INSTALL_AUTOMOD'					=> 'Automod',
	'CHANGES'								=> 'Cambios',
	'CHANGE_DATE'							=> 'Fecha de lanzamiento',
	'CHANGE_VERSION'						=> 'Número de versión',
	'CHECK_AGAIN'							=> 'Comprobar nuevamente',
	'COMMENT'								=> 'Comentario',
	'CREATE_TABLE'							=> 'Alteraciones de base de datos',
	'CREATE_TABLE_EXPLAIN'					=> 'Automod ha realizado con éxito su base de datos de alteraciones, incluyendo un permiso que se le ha asignado al "Administrador total".',

	'DELETE'								=> 'Borrar',
	'DELETE_CONFIRM'						=> '¿Está seguro que desea eliminar este MOD?',
	'DELETE_ERROR'							=> 'Se ha producido un error al eliminar el MOD seleccionado.',
	'DELETE_SUCCESS'						=> 'MOD ha sido eliminado correctamente.',
	'DEPENDENCY_INSTRUCTIONS'				=> 'El MOD que está tratando de instalar depende de otro MOD. Automod no puede detectar si el MOD  se ha instalado.  Por favor, compruebe que tiene  instalado <strong><a href="%1$s">%2$s</a></strong> antes de instalar su MOD.',
	'DESCRIPTION'							=> 'Descripción',
	'DETAILS'								=> 'Detalles',
	'DIR_PERMS'								=> 'Permisos de directorio',
	'DIR_PERMS_EXPLAIN'						=> 'Algunos sistemas requieren directorios tener ciertos permisos para trabajar correctamente. Normalmente el valor predeterminado 0755 es correcta. Este ajuste no tiene impacto en los sistemas Windows.',
	'DIY_INSTRUCTIONS'						=> 'Hágalo usted mismo instrucciones',

	'EDITED_ROOT_CREATE_FAIL'				=> 'Automod no pudo crear el directorio donde los archivos editados seran almacenados.',
	'ERROR'									=> 'Error',

	'FILESYSTEM_NOT_WRITABLE'				=> 'Automod ha determinado que el sistema de archivos no se puede escribir, por lo que el método de escritura directa no se puede utilizar',
	'FILE_EDITS'							=> 'Edición de archivo',
	'FILE_EMPTY'							=> 'Archivo vacío',
	'FILE_MISSING'							=> 'No se puede encontrar el archivo',
	'FILE_PERMS'							=> 'Permisos de archivo',
	'FILE_PERMS_EXPLAIN'					=> 'Algunos sistemas de archivos requieren ciertos permisos para funcionar correctamente. Normalmente el valor predeterminado 0644 es correcto. Este ajuste no tiene impacto en los sistemas Windows.',
	'FILE_TYPE'								=> 'Tipo de archivo comprimido',
	'FILE_TYPE_EXPLAIN'						=> 'Esto sólo es válido con la " descarga de archivos comprimidos" método de escritura',
	'FIND'									=> 'Buscar',
	'FIND_MISSING'							=> 'La Búsqueda especificada por el MOD no se pudo encontrar',
	'FORCE_CONFIRM'							=> 'La función de la instalación a la Fuerza: índica que el MOD no está completamente instalado. Usted tendrá que hacer algunos arreglos manualmente para finalizar la instalación.¿Desea continuar? ',
	'FORCE_INSTALL'							=> 'Instalar a la Fuerza',
	'FORCE_UNINSTALL'						=> 'Desinstalar a la Fuerza',
	'FTP_INFORMATION'						=> 'Informacion FTP',
	'FTP_METHOD_ERROR'						=> 'No hay ningún método FTP que se encuentre, por favor, revise debajo Automod configuración si no se establece un correcto método de FTP',
	'FTP_METHOD_EXPLAIN'					=> 'Si tiene problemas con el valor por defecto "ftp", puede intentar "Simple Socket" como una forma alternativa de conectarse al servidor FTP',
	'FTP_METHOD_FSOCK'						=> 'Simple Socket',
	'FTP_METHOD_FTP'						=> 'FTP',
	'FTP_NOT_USABLE'						=> 'La función de FTP no puede ser utilizado porque ha sido deshabilitado por su alojamiento.',

	'GO_PHP_INSTALLER'						=> 'El MOD requiere un instalador externo para finalizar la instalación. Haga clic aquí para continuar con ese paso',

	'INHERIT_NO_CHANGE'						=> 'No se pueden realizar cambios a este archivo porque la plantilla %1$s depende de %2$s.',
	'INLINE_EDIT_ERROR'						=> 'Error, en una línea de edición en el archivo de instalación MODX faltan todos los elementos necesarios',
	'INLINE_FIND_MISSING'					=> 'La búsqueda especificada en el MOD no pudo ser encontrado.',
	'INSTALLATION_SUCCESSFUL'				=> 'Automod instalado con éxito. Ahora puede administrar Modificaciones phpBB a través de la ficha Automod en el Panel de Control de Administración.',
	'INSTALLED'								=> 'MOD instalado',
	'INSTALLED_EXPLAIN'						=> 'Su MOD  se ha instalado! Aquí puede ver algunos de los resultados de la instalación. Por favor, tenga en cuenta los errores, y busque apoyo en <a href="http://www.phpbb.com">phpBB.com</a>',
	'INSTALLED_MODS'						=> 'MODs instalados',
	'INSTALL_AUTOMOD'						=> 'Instalación Automod',
	'INSTALL_AUTOMOD_CONFIRM'				=> 'Está seguro de que desea instalar Automod?',
	'INSTALL_ERROR'							=> 'Una o mas acciones han fallado.  Por favor revise las siguientes acciones, haga los ajustes y vuelva a intentarlo. Usted puede continuar con la instalación a pesar de que algunas de las acciones no se hayan hecho.<strong>Esto no es recomendable y puede hacer que su foro no funcione correctamente.</strong>',
	'INSTALL_FORCED'						=> 'Se forzó la instalación de este MOD a pesar de que hubieron errores en su instalación. Su foro puede estar dañado. Por favor tenga en cuenta las acciones que fallaron a continuación y corríjalos.',
	'INSTALL_MOD'							=> 'Instalar este MOD',
	'INSTALL_TIME'							=> 'Tiempo de instalación',
	'INVALID_MOD_INSTRUCTION'				=> 'Este MOD tiene una instrucción no valida, o una búsqueda en línea ha fallado.',
	'INVALID_MOD_NO_ACTION'					=> 'En el MOD falta una acción que se ponga en relación con el hallazgo ‘%s’',
	'INVALID_MOD_NO_FIND'					=> 'En el MOD falta una búsqueda en relación con la acción ‘%s’',

	'LANGUAGE_NAME'							=> 'Nombre de idioma',

	'MANUAL_COPY'							=> 'No se intentó copiar',
	'MODS_CONFIG_EXPLAIN'					=> 'Puede seleccionar cómo Automod ajusta sus archivos aquí. El método más básico es Descargar archivo comprimido. Los otros requieren permisos adicionales en el servidor.',
	'MODS_COPY_FAILURE'						=> 'El archivo %s no se puede copiar en su lugar. Por favor, revise sus permisos o autorizaciones de uso de su método de escritura.',
	'MODS_EXPLAIN'							=> 'Aquí puede administrar la MODs disponibles en su foro. AutoMODs le permite para personalizar el foro, la instalación automática de modificaciones producido por la comunidad de phpBB. Para más información sobre MODs y Automod por favor, visite el <a href="http://www.phpbb.com/mods">Apartado de MODs</a>. Para agregar un MOD a esta lista, utilice el formulario al final de esta página. Alternativamente, puede descomprimir y cargar los archivos a la / Store / mods / directorio en el servidor.',
	'MODS_FTP_CONNECT_FAILURE'				=> 'Automod no pudo conectarse a su servidor FTP. El error fue %s',
	'MODS_FTP_FAILURE'						=> 'Automod no podía FTP el archivo %s en su lugar',
	'MODS_MKDIR_FAILED'						=> 'El directorio %s no pudo ser creado',
	'MODS_SETUP_INCOMPLETE'					=> 'Un problema se encontró con su configuración, y Automod no puede funcionar. Esto sólo debería ocurrir cuando la configuración (por ejemplo nombre de usuario FTP) han cambiado, y puede ser corregido en la página de configuración Automod.',
	'MOD_CONFIG'							=> 'Automod Configuración',
	'MOD_CONFIG_UPDATED'					=> 'Configuración Automod ha sido actualizada.',
	'MOD_DETAILS'							=> 'Detalles del MOD',
	'MOD_DETAILS_EXPLAIN'					=> 'Aquí puede ver toda la información conocida sobre el MOD seleccionado.',
	'MOD_MANAGER'							=> 'Automod',
	'MOD_NAME'								=> 'Nombre del MOD',
	'MOD_OPEN_FILE_FAIL'					=> 'Automod no ha podido abrir %s.',
	'MOD_UPLOAD'							=> 'Subir MOD',
	'MOD_UPLOAD_EXPLAIN'					=> 'Aquí puede subir un paquete MOD comprimido que contiene los archivos necesarios de MODX para realizar la instalación. Automod entonces intentará descomprimir el archivo y tenerlo listo para la instalación.',
	'MOD_UPLOAD_INIT_FAIL'					=> 'Error de inicializaciones el proceso de carga del MOD.',
	'MOD_UPLOAD_SUCCESS'					=> 'MOD cargado y preparado para la instalación.',

	'NAME'									=> 'Nombre',
	'NEW_FILES'								=> 'Nuevos archivos',
	'NO_ATTEMPT'							=> 'No se ha intentado',
	'NO_INSTALLED_MODS'						=> 'MODs instalados no detectados',
	'NO_MOD'								=> 'El MOD seleccionado no pudo ser encontrado.',
	'NO_UNINSTALLED_MODS'					=> 'MODs desinstalados  no detectados',
	'NO_UPLOAD_FILE'						=> 'No existe el fichero especificado.',

	'ORIGINAL'								=> 'Original',

	'PATH'									=> 'Ruta de acceso',
	'PREVIEW_CHANGES'						=> 'vista previa de cambios',
	'PREVIEW_CHANGES_EXPLAIN'				=> 'Muestra los cambios que deben realizarse antes de ejecutarlas.',
	'PRE_INSTALL'							=> 'Preparación para la instalación',
	'PRE_INSTALL_EXPLAIN'					=> 'Aquí puedes ver todas las modificaciones que deben introducirse en la tabla, antes de que se lleven a cabo. <strong>ADVERTENCIA!</strong>, una vez aceptados, los archivos de base de phpBB se ha editado y alteraciones de base de datos puede ocurrir. Sin embargo, si la instalación no se realiza correctamente, suponiendo que usted puede tener acceso a AutoMOD, se le dará la opción de restaurar a este punto.',
	'PRE_UNINSTALL'							=> 'Preparación para la desinstalación',
	'PRE_UNINSTALL_EXPLAIN'					=> 'Aquí puedes ver todas las modificaciones que deben introducirse en la tabla, con el fin de desinstalar el MOD. <strong>ADVERTENCIA!</strong>, una vez aceptados, los archivos base de phpBB se ha editado y alteraciones de base de datos puede ocurrir. Además, este proceso utiliza las técnicas de inversión que no puede ser fiable al 100%. Sin embargo, si la desinstalación no se realiza correctamente, suponiendo que usted puede tener acceso a Automod, se le dará la opción de restaurar a este punto.',

	'REMOVING_FILES'						=> 'Archivos a eliminar',
	'RETRY'									=> 'Volver a intentar ',
	'RETURN_MODS'							=> 'Volver a Automod',
	'REVERSE'								=> 'Revertir',
	'ROOT_IS_READABLE'						=> 'El directorio raíz de phpBB es legible.',
	'ROOT_NOT_READABLE'						=> 'Automod no era capaz de abrir phpBB\'s index.php para lectura. Esto probablemente significa que los permisos son demasiado restrictivos en el directorio raíz de phpBB, lo que impedirá a Automod trabajar. Por favor, modifique los permisos e intente de nuevo.',

	'SOURCE'								=> 'Fuente',
	'SQL_QUERIES'							=> 'Consultas SQL',
	'STATUS'								=> 'Estado',
	'STORE_IS_WRITABLE'						=> 'El directorio store/ directorio tiene permisos de escritura.',
	'STORE_NOT_WRITABLE'					=> 'El directorio store/ no tiene permisos de escritura.',
	'STORE_NOT_WRITABLE_INST'				=> 'Automod instalación ha detectado que la tienda / directorio no tiene permisos de escritura. Esto es necesario para Automod para funcionar correctamente. Por favor, modifica los permisos y vuelva a intentarlo.',
	'STYLE_NAME'							=> 'Nombre del estilo',
	'SUCCESS'								=> 'Éxito',

	'TARGET'								=> 'Objetivo',

	'UNINSTALL'								=> 'Desinstalar',
	'UNINSTALLED'							=> 'MOD desinstalado',
	'UNINSTALLED_EXPLAIN'					=> 'Su MOD se ha desinstalado! Aquí puede ver algunos de los resultados de la desinstalación. Por favor, tenga en cuenta los errores, y busque apoyo en <a href="http://www.phpbb.com">phpBB.com</a>.',
	'UNINSTALLED_MODS'						=> 'MODs desinstalados',
	'UNINSTALL_AUTOMOD'						=> 'Desinstalar Automod',
	'UNINSTALL_AUTOMOD_CONFIRM'				=> '¿Está seguro que desea desinstalar Automod? Esto no eliminará ningún MOD que se han instalado con Automod.',
	'UNKNOWN_MOD_AUTHOR-NOTES'				=> 'No hay notas de autor especificadas.',
	'UNKNOWN_MOD_COMMENT'					=> '',
	'UNKNOWN_MOD_DESCRIPTION'				=> '',
	'UNKNOWN_MOD_DIY-INSTRUCTIONS'			=> '',
	'UNKNOWN_MOD_INLINE-COMMENT'			=> '',
	'UNKNOWN_QUERY_REVERSE'					=> 'consulta inversa desconocida',
	'UNRECOGNISED_COMMAND'					=> 'Error, comando no reconocido %s',
	'UPDATE_AUTOMOD'						=> 'Actualizar Automod',
	'UPDATE_AUTOMOD_CONFIRM'				=> 'Por favor, confirme que desea actualizar Automod.',
	'UPLOAD'								=> 'Subir',

	'VERSION'								=> 'Versión',

	'WRITE_DIRECT_FAIL'						=> 'Automod no puede copiar el archivo %s en su lugar utilizando el método directo. Por favor, use otro método de escritura y vuelva a intentarlo.',
	'WRITE_DIRECT_TOO_SHORT'				=> 'Automod no pudo terminar de escribir el archivo %s. A menudo, esto puede ser resuelto con el botón Reintentar. Si esto no funciona, pruebe con otro método de escritura.',
	'WRITE_MANUAL_FAIL'						=> 'No se puede agregar el archivo %s para un archivo comprimido. Por favor, pruebe con otro método de escritura.',
	'WRITE_METHOD'							=> 'Método de escritura',
	'WRITE_METHOD_DIRECT'					=> 'Directo',
	'WRITE_METHOD_EXPLAIN'					=> 'Usted puede establecer un método preferido para escribir archivos. La opción más compatible "descarga de archivos comprimidos".',
	'WRITE_METHOD_FTP'						=> 'FTP',
	'WRITE_METHOD_MANUAL'					=> 'Descargar archivo comprimido',

	'after add'								=> 'Agregar Despues',

	'before add'							=> 'Agregar Antes',

	'find'									=> 'Buscar',

	'in-line-after-add'						=> 'En-línea Después, Añadir,',
	'in-line-before-add'					=> 'En-Línea Antes, Añadir,',
	'in-line-edit'							=> 'En-Línea Buscar',
	'in-line-operation'						=> 'En-Línea incrementar',
	'in-line-replace'						=> 'En-Línea reemplazar',
	'in-line-replace-with'					=> 'En-Línea reemplazar',

	'operation'								=> 'Incrementar',

	'replace'								=> 'Reemplazar',
	'replace with'							=> 'Reemplazar con',
));
