<?php
/**
*
* @package Titania
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* Traducción hecha y revisada por nextgen <http://www.melvingarcia.com>
* Traductores anteriores angelismo y sof-teo
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
		1 => '¿Que es Titania (también conocido como Base de Descargas)?'
	),
	array(
		0 => '¿Que es Titania?',
		1 => 'Titania (también conocido como La base de descargas) es una base de datos donde los usuarios se podrán descargar las modificaciones y estilos para su foro phpBB. También se asegura que la modificación o el estilo que ha descargado ha superado los requisitos de validación de phpBB.'
	),
	array(
		0 => '¿Validación? ¿Qué es eso?',
		1 => 'Todos y cada modificación o estilo, que  usted descarga dentro de Titania han sido validados. Validación significa que una modificación o el estilo, ha sido objeto de control en cuanto a la seguridad del código involucrado, así como pruebas para asegurar que se instala la modificación o el estilo y funciona correctamente en una versión particular de un foro phpBB. La validación le proporciona un nivel de comodidad de saber que no se descarga la instalación de una modificación o estilo que puede suponer un problema de seguridad para su foro'
	),
	array(
		0 => '--',
		1 => 'Como utilizar Titania',
	),
	array(
		0 => 'Encontrar una contribución',
		1 => 'Hay varias maneras de encontrar una contribución. En la página principal de la base de datos usted puede ver las categorías que están actualmente disponibles, así como las modificaciones recientes y estilos que han sido aprobados dentro de la base de descargas.',
	),
	array(
		0 => 'Encontrar una modificación',
		1 => 'Usted puede ir directamente al tipo de modificación  que quiera en las distintas categorías de la base (Herramientas, Comunicación, Seguridad, Entretenimiento, etc.) o utilizando la función de búsqueda en la parte superior de la página. Si se utiliza la función de búsqueda que puede utilizar comodines y buscar ya sea en el nombre de la contribución (o parte del nombre), así como autor de la contribución. Una vez que encuentre la modificación que usted está interesado  será llevado a la pagina de información sobre la modificación donde puedes encontrar una descarga de la versión actual de la modificación, así como las versiones anteriores de la modificación en la sección de revisiones'
	),
	array(
		0 => 'Encontrar un estilo',
		1 => 'Al igual que en la búsqueda de una modificación, Titania también le permite localizar los estilos, los paquetes de smilies, imágenes de rango y otros artículos. La función de búsqueda también le permite usar comodines y buscar en sólo un nombre de los autores también. Una vez que encuentre el artículo que usted está interesado en que será llevado a la pagina de información del estilo donde puedes encontrar una descarga de la versión actual del tema, así como las versiones anteriores del estilo en la sección de revisiones.'
	),
	array(
		0 => '--',
		1 => 'Soporte de la modificación'
	),
	array(
		0 => 'Reglas',
		1 => 'Con la introducción de Titania, las normas implicadas a utilizarlo es muy sencillo. Al igual que en el pasado, el dicho "Usted debe buscar soporte en el tema de la modificación del estilo/donde tienes la modificación" se refiere. Si bien el soporte del Equipo de phpBB.com hace todo lo posible para ayudarle en la gestión y el uso de su foro no puede y no se espera que, prestar soporte a cualquier modificación/contribución. Es la esperanza de phpBB que el autor de la contribución pueda ofrecer, al usuario final,  el soporte en el uso de la modificación. Por favor, recuerde que todos los autores son voluntarios que han dedicado su tiempo en la prestación de una mejora en el software phpBB. El dicho "Usted obtiene más moscas con miel que con vinagre lo hace" se aplica, así que por favor tenga esto en cuenta cuando se solicite el soporte a una modificación (por ejemplo, ser amable en la manera de preguntar).'
	),
	array(
		0 => 'Como obtener soporte',
		1 => 'Cada modificación  proporciona un método para dar soporte. Dentro de cada uno es la capacidad del autor para publicar FAQ(s) relativa a la modificación, así como una discusión/área de soporte para un tipo de uno-a-uno de apoyo. Este apoyo puede ir desde ayudar a conseguir la modificación instalada e incluso pueden proporcionarle complementos adicionales para mejorar la modificación. Para acceder a esta zona simplemente haga clic en la modificación y una ficha se mostrará indicando “Discusión/Soporte técnico". En esa zona puede enviar un comentario o una pregunta al autor. Por favor, recuerde que los autores no están obligados a prestar soporte al igual que no está bajo ninguna obligación de proporcionar a la modificación. Si ve un mensaje no apropiado o que incumpla las normas, por favor no dude en utilizar el “Informar de este mensaje” y un moderador tomará las medidas apropiadas que sean necesarias.'
	),
	// This block will switch the FAQ-Questions to the second template column
	// Authors corner!!
	array(
		0 => '--',
		1 => '--'
	),
	array(
		0 => '--',
		1 => 'Creación y administración de las contribuciones'
	),
	array(
		0 => 'Crear una contribución',
		1 => 'Al igual que con cualquier contribución, los autores deberán seguir las instrucciones de seguridad al presentar su propia contribución. Las <a href="http://area51.phpbb.com/docs/coding-guidelines.html"> Instrucciones de codificación </ a>, aunque parezcan desalentadores al principio, son en realidad su amigo. Se debe seguir lo más cerca posible para ayudarle a conseguir que su contribución sea publicada en la comunidad. En el caso de un MOD, el <a href="http://www.phpbb.com/mods/mpv/"> phpBB MOD pre-validador </ a> (también conocido como "VP") se llevará a cabo en contra de la presentada revisión y verificación de las cosas tales como las licencias correctas, la versión de phpBB y corto plazo <a href="http://www.phpbb.com/mods/modx/"> MODX </ a> versión.'
	),
	array(
		0 => 'Enviar una contribución',
		1 => 'Así que usted ha hecho una contribución.<br /><br />Para enviar una contribución, ir a la base de descargas y dentro de esa página encontrará un enlace de imagen que dice "Nueva Contribución". Una vez hecho clic, será capaz de introducir el nombre de la contribución, seleccione el tipo de contribución, añada un poco de texto para describir la contribución (smilies y bbcode está permitido), seleccione la categoría (s) que la contribución se inscribe en, añadir co-autores (si procede) y capturas de pantalla también. Por favor, tenga en cuenta que a medida que se presenta la contribución, la contribución será presentada con su nombre.'
	),
	array(
		0 => 'Administrar contribuciones',
		1 => 'Una vez que su contribución se ha subido con éxito en Titania, se puede administrar. Después de seleccionar su contribución haciendo clic en "Mis contribuciones" en la parte superior de la página, puede añadir información adicional a la misma a través de la "Administrar contribución". Usted puede modificar la descripción de la contribución, subir capturas de pantalla, cambiar la propiedad de la contribución (por favor, tenga en cuenta esto es irreversible, así que asegúrese de que realmente quiere dar a otro usuario la propiedad de su contribución), cambiar las categorías de la contribución se ajuste bajo y como entrada una URL demo  que los usuarios puedan ver de primera mano como funciona la  contribución.'
	),
	array(
		0 => 'Presentación de una nueva revisión',
		1 => 'Usted puede subir nuevas revisiones en la página principal, en la pagina de detalles, la sección, de modificación. Una vez que haga clic en el vinculo "Nueva Versión" , se le presentará una página donde subir  la revisión, asignarle una versión y notas de entrada al equipo de validación (bbcode y emoticones están permitidos). También puede elegir que el equipo de validación "rehacer" la modificación. Reembalaje consiste en hacer correcciones menores a la modificación. Esto puede implicar correcciones en el archivo de instalación MODX o incluso pequeños cambios de código. Reembalaje es <strong> no </ strong> que tiene el nuevo equipo de validación y escritura fragmentos más importantes del código que se suministra, que sería su "trabajo". <br /> <br /> Las normas, que se aplican sobre la creación de una modificación, se siguen aplicando al presentar las revisiones de la personalización. Es decir, el <a href="http://www.phpbb.com/mods/mpv/">phpBB MOD pre-validador</a> (también conocido como "VP") se llevará a cabo en contra de la revisión de la modificación y comprobará si hay cosas tales como las licencias correctas, la versión de phpBB y corto plazo <a href="http://www.phpbb.com/mods/modx/">MODX</a> versión.   '
	),
	array(
		0 => '--',
		1 => 'Dar soporte'
	),
	array(
		0 => 'FAQ',
		1 => 'Cada modificación proporciona al autor la posibilidad de presentar todo tipo de preguntas más frecuentes de los temas. Estos temas que cree debe ser escrito de una manera que un usuario pueda entender y aplicar el tema de la modificación, si el tema será sobre cómo conseguir la modificación instalada, el acceso a las características de la modificación, etc. Cabe señalar que esta zona es sólo para ti. Los usuarios no pueden modificar o responder a las entradas de preguntas frecuentes.'
	),
	array(
		0 => 'Foro de soporte',
		1 => 'Por favor, tenga en cuenta que los usuarios pueden hacer preguntas o hacer comentarios con respecto a su contribución. Le pedimos que usted de soporte su contribución como todo lo que pueda. Nos damos cuenta de que usted pasó su tiempo libre en la creación de su contribución y que la vida real puede, a veces, en el camino de la diversión. Sólo pedimos que como autor que es, tanto soporte como sea posible. Si se ejecuta a través de un mensaje o comentario que usted siente que no está en los mejores intereses de la comunidad, por favor no dude en utilizar el “Informar de este mensaje” y un moderador tomará las medidas apropiadas que sean necesarias.'
	),
	array(
		0 => '--',
		1 => 'Validación'
	),
	array(
		0 => 'Mi personalización no pasa la comprobación de validación previa',
		1 => 'Recuerde, cada modificación debe tener la licencia correcta (Corriente Y GNU GPL versión 2), la versión correcta del software phpBB y la versión correcta de MODX. Si la modificación no tiene estos elementos rudimentarios, entonces no puede ser aceptada en la base de datos. Algunos errores son simplemente las advertencias y no sea necesario que se fijan, si no está seguro del problema no dude en continuar con la presentación y un validador va a tramitar.'
	),
	array(
		0 => 'Mi modificación ha pasado la validación previa, ¿ahora qué?',
		1 => 'Una vez que la modificación es aceptada en la base de datos, es entonces hasta el equipo de phpBB MOD y validadores Junior para validar su contribución. Puede encontrar que usted consigue un mensaje que indica la personalización se le negó. Por favor, no se preocupe. Sabemos que las cosas se pasan por alto o simplemente se perdió, simplemente. No se preocupe. En el mensaje recibido se contienen elementos que se encuentran. Estos elementos pueden sugerir cambios en el código e incluso puede sugerir cambios en la "facilidad de uso". En términos generales, "la facilidad de uso" sugerencias son sólo eso... sugerencias. La parte más importante de cualquier modificación es la seguridad, no en lo que parece para el usuario final.<br /><br />Si no hay elementos fueron encontrados durante la validación de su contribución obtendrá un PM que indica que su contribución ha sido aceptado en la base de datos. Ahora es el momento para relajarse un poco y se deleitan con el conocimiento que usted ha hecho una contribución a la comunidad de código abierto.<br /><br /> No importa el resultado de la validación, agradecemos el tiempo y el esfuerzo que han ejercido en compartir su contribución.'
	),
	array(
		0 => 'Quién valida mi contribución?',
		1 => 'Si se trata de una modificación que será validado por el Equipo de MOD y Validadores de MOD Junior o de vez en cuando por algún miembro del Equipo de Desarrollo. Para un estilo que será validado por el Equipo de Estilos y los Validadores de Estilo Junior. Para un conversor serán validados por un miembro del Equipo de Soporte o de Desarrollo. Para un puente será validado por un Miembro del Equipo de MODificaciones o Miembro del Equipo de Desarrollo. Para los paquetes de traducciones serán revisadas por el Gerente Internacional. Las Herramientas Oficiales han sido probadas y creadas por los Equipos de phpBB.com.'
	),
);

?>