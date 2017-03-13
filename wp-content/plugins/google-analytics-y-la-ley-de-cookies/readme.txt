=== Google Analytics y la ley de Cookies ===
Contributors: obturecode
Tags: ley de cookies, google analytics, cookies, privacidad, usuario, analytics, analitica, legal
Stable tag: 1.6.1
Requires at least: 3.0.1
Tested up to: 4.0.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Si tu sitio web utiliza Google Analytics, te ayudamos a cumplir con la llamada Ley de Cookies española (artículo 22 de la LSSI) 

== Description ==

¿Utilizas Google Analytics y estás preocupado por la nueva ley de cookies? Si es tu caso, las cookies que utiliza Google Analytics están afectadas por esta ley y por lo tanto debes poner mecanismos para obtener el consentimiento informado del usuario para usarlas. Este plugin te ayuda a cumplir con la ley de manera rápida y sencilla.

¿Cómo conseguir esto?

1. Desactiva cualquier plugin de Google Analyitcs que tengas activo en WordPress

2. Instala el plugin

3. Personaliza los mensajes (esto es opcional)

4. Ya está. No hay paso 4


 

Resumiendo un poco la situación legal: a día de hoy cualquier sitio web que utilice cookies no técnicas necesita el consentimiento informado del usuario. Esto es, como administrador de un sitio web has de informar al usuario de las cookies no técnicas que utilizas y para qué fin y además obtener su consentimiento para poder  utilizarlas. Además, es necesario que el administrador del sitio web proporcionen una página donde se explique qué es una cookie y para qué se utilizan en tu sitio además de opciones para desactivarlas. 

¿Cómo funciona este plugin?

* Impedirá que se instalen de forma automática las cookies de Google Analytics

* Mostrará un aviso al usuario en la parte superior de la pantalla informando del uso de cookies (personalizable)

* El aviso permancerá mientras el usuario no pinche en Aceptar o navegue pulsando en algún enlace de la página web (a esto se le llama consentimiento informado)

* Creará una página html básica que podrás personalizar con tus estilos informando sobre la ley de cookies (como esta: http://obturecode.com/es/politica-de-cookies/)

* Una vez que el usuario acepte el mensaje, se instalarán de forma automática las cookies y se empezará a trackear las acciones y actividad del visitante


**OJO:**
Este plugin no se compromete a cumplir la ley y va sin ningún tipo de garantía. Su único fin es ayudarte a cumplir el artículo 22 de la LSSI (conocida como ley de cookies) si utilizas Google Analytics en tu sitio. Por lo tanto, corresponde al administrador del sitio la responsabilidad de implementar de forma correcta cualquier sistema que cumpla con la normativa vigente.
Si instalas este plugin, eximes a sus creadores de cualquier responsabilidad derivada del incumplimiento de la ley de cookies en tu sitio web.
Infórmate aquí: http://www.agpd.es/portalwebAGPD/canaldocumentacion/publicaciones/common/Guias/Guia_Cookies.pdf de cómo cumplir la ley y si tienes dudas consulta con un abogado

== Installation ==

Opción 1: desde el instalador de plugins de WordPress buscando Google Analytics y la ley de Cookies

Opción 2: descargando el .zip desde la web del plugin y cargarlo con tu adminstrador de plugins
== Changelog ==

= 1.5 =
* Añadido soporte de idiomas mediante el plugin Polylang.
* Corrección de varios errores.
* Probado en WordPress 3.9.1

= 1.6 =
* Añadido "modo intrusivo" de tal forma que si se activa, el aviso sobre cookies aparece como un cuadro modal que impide al usuario continuar navegando a menos que acepte el propio aviso
* Solucionados unos problemas de la versión 1.6
* Probado en WordPress 4.0

= 1.6.1 =
* Solucionados bugs de integración con plugin Polylang

== Configuración ==

Una vez tengas instalado el plugin aparecerá una opción dentro del menú de Ajustes llamada Google Analytics y la ley de Cookies.
Ahí deberás introducir tu id de Google Analytics y podrás configurar los textos que aparecen en el aviso (título y texto completo)
Para poder personalizar el estilo del aviso puedes sobreescribir estos estilos CSS:
#obt_ga_banner
#obt_ga_contenido h2
#obt_ga_contenido p
#obt_ga_contenido a
#obt_ga_contenido
#obt_ga_boton

En tu lista de Páginas verás una nueva página llamada Política de Cookies con información sobre cookies que podrás personalizar

== Frequently Asked Questions ==

= ¿Este plugin hará que mi sitio web cumpla con el artículo 22 de la LSSI (Ley de Cookies)? =

No. Este plugin te ayudará a cumplir con la ley si tu sitio web utiliza Google Analytics pero será tu responsabilidad, y no del plugin, cumplir con la ley. Infórmate aquí: http://bit.ly/J8x197 (Agencia de Protección de Datos) de cómo cumplir la ley y si tienes dudas consulta con un abogado

= ¿Puede colocar el aviso en la parte inferior? =

Actualmente no

= ¿Qué hay de otras cookies? =

Actualmente el plugin sólo impide que se instalen las cookies de Google Analytics. En futuras versiones estudiaremos añadir más.

= Tengo algo que sugerir ¿Cómo me pongo en contacto con vosotros? =

Puedes escribirnos a info[at]obturecode.com

== Screenshots ==

1. Aviso Ley de Cookies
2. Página con información de cookies
