<?php
/*
Plugin Name: Google Analytics y la ley de Cookies
Plugin URI: http://obturecode.com
Description: Obturecode te intenta ayudar con la ley española de cookies, integra analitycs facilmente en tu sitio
Author: Obture Code
Version: 1.6.1
Author URI: http://obturecode.com

*/  
define('OBTURECODE_GA_NAME','Google Analytics y la ley de Cookies');
define('OBTURECODE_GA_NAME_PATH','google-analytics-y-la-ley-de-cookies');
define('OBTURECODE_GA_TITLE','Uso de Cookies');
define('OBTURECODE_GA_ACCEPT','Acepto');
define('OBTURECODE_GA_TEXT','Utilizamos cookies propias y de terceros para mejorar nuestros servicios. Si continúa navegando, consideramos que acepta su uso. Más información en la %button%');
define('OBTURECODE_GA_INTRUSIVE_MODE','0');

function widget_obtcookies_control(){
    require dirname(__FILE__).'/obturecode_ga_admin.php';
}


function widget_obtcookies_menu() {
    add_options_page( 'Ajustes del plugin "'.OBTURECODE_GA_NAME.'"', OBTURECODE_GA_NAME, 'manage_options', "Widget_Obturecode_GA", 'widget_obtcookies_control' );

    register_setting( 'obt_cookies', 'obtga_idanalitycs' );
    register_setting( 'obt_cookies', 'obtga_texto' );
    register_setting( 'obt_cookies', 'obtga_titulo' );
    register_setting( 'obt_cookies', 'obtga_acepto' );
    register_setting( 'obt_cookies', 'obtga_intrusive_mode');
    if(function_exists('pll_register_string')){
        pll_register_string("Titulo aviso cookies", OBTURECODE_GA_TITLE, "obtga", false);
        pll_register_string("Texto aviso cookies",OBTURECODE_GA_TEXT , "obtga", true);
        pll_register_string("Acepto",OBTURECODE_GA_ACCEPT , "obtga", false);
    }      
}
  
// This registers the (optional!) widget control form.
wp_register_widget_control('Obturecode_GA','ObturecodeGA', 'widget_obtcookies_control');

add_action( 'admin_menu', 'widget_obtcookies_menu' );

function obtcookies_add_script(){
    wp_enqueue_script( 'obtga-script', WP_PLUGIN_URL . '/'.OBTURECODE_GA_NAME_PATH.'/main.js', array(), null, true );
    $id_pagina = get_option('obtga_id_pagina');
    $params = array(
        'idGA' => get_option('obtga_idanalitycs'),
    );
    if(function_exists('pll__')){
        $params['texto']=pll__(OBTURECODE_GA_TEXT);
        $params['titulo']=pll__(OBTURECODE_GA_TITLE);
        $params['acepto']=pll__(OBTURECODE_GA_ACCEPT);
    }else{
        $params['texto']=get_option('obtga_texto',OBTURECODE_GA_TEXT);
        $params['titulo']=get_option('obtga_titulo',OBTURECODE_GA_TITLE);
        $params['acepto']=get_option('obtga_acepto',OBTURECODE_GA_ACCEPT);
    }

    $params['intrusivo'] = get_option('obtga_intrusive_mode',OBTURECODE_GA_INTRUSIVE_MODE);

    if(function_exists('pll_get_post')){
        $id_pagina=pll_get_post($id_pagina);
    }
        
    $params['url']= esc_url( get_permalink( $id_pagina ));
    $params['titulo_pagina'] = get_the_title($id_pagina);

    wp_enqueue_style('obtga-style',WP_PLUGIN_URL . '/'.OBTURECODE_GA_NAME_PATH.'/obturecode_ga.css');
    wp_localize_script( 'obtga-script', 'ObtGAParams', $params );
}

add_action( 'wp_enqueue_scripts', 'obtcookies_add_script' );

function obtcookies_install(){
    global $wpdb;

    $titulo_es = 'Política de cookies';
    $titulo_en = 'Cookie Policy';
    $slug_es = 'politica-de-cookies';
    $slug_en = 'cookie-policy';
    
    $id_pagina = get_option('obtga_id_pagina');
    $the_page=null;
    if($id_pagina!==false)
        $the_page = get_post($id_pagina);


    if ($id_pagina!==false || !$the_page) {

        // Se crea el objeto del post
        $_p = array();
        $_p['post_title'] = $titulo_es;
        $_p['post_content'] = file_get_contents( plugins_url( 'politica.html', __FILE__ ) );
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1);

        // Se guarda en la base de datos
        $id_pagina = wp_insert_post( $_p );
        add_option('obtga_id_pagina', $id_pagina, '', 'yes');
        if(function_exists('pll_set_post_language')){
            pll_set_post_language($id_pagina, 'es');
        }

    }else {// significa que el plugin ya estaba activado antes
        
        $id_pagina = $the_page->ID;
        //Por si no esta publicada, la publicamos
        $the_page->post_status = 'publish';
        wp_update_post( $the_page );

    }

    if(function_exists('pll_get_post') && function_exists('pll_languages_list')){
        $idiomas = pll_languages_list();
        if(in_array('en',$idiomas) && pll_get_post($id_pagina, 'en')==null){
            
            // Se crea el objeto del post
            $_p = array();
            $_p['post_title'] = $titulo_en;
            $_p['post_content'] = file_get_contents( plugins_url( 'policy.html', __FILE__ ) );
            $_p['post_status'] = 'publish';
            $_p['post_type'] = 'page';
            $_p['comment_status'] = 'closed';
            $_p['ping_status'] = 'closed';
            $_p['post_category'] = array(1);

            // Se guarda en la base de datos
            $id_pagina_en = wp_insert_post( $_p );
            if(function_exists('pll_set_post_language')){
                pll_set_post_language($id_pagina_en, 'en');
            }
            if(function_exists('pll_save_post_translations')){
                pll_save_post_translations(array('es'=>$id_pagina,'en'=>$id_pagina_en));
            }
        }
    }

    //delete_option( 'obtga_id_pagina' );
    //add_option( 'obtga_id_pagina', $id_pagina,'','yes' );
}

function obtcookies_remove() {

    global $wpdb;

    //  obtenemos el id de la página para borrarlo
    $id_pagina = get_option( 'obtga_id_pagina' );
    if( $id_pagina ) {
        if(function_exists('pll_get_post')){
            $id_page_en = pll_get_post($id_pagina, 'en');
        }
        if($id_page_en!=null)
            wp_delete_post( $id_page_en );

        wp_delete_post( $id_pagina ); // this will trash, not delete

    }

    delete_option('obtga_id_pagina');
    delete_option( 'obtga_idanalitycs' );
    delete_option( 'obtga_texto' );
    delete_option( 'obtga_titulo' );
    delete_option( 'obtga_acepto' );
    delete_option( 'obtga_intrusive_mode' );

}
function showMessage($message, $errormsg = false)
{
    if ($errormsg) {
        echo '<div id="message" class="error">';
    }
    else {
        echo '<div id="message" class="updated fade">';
    }

    echo "<p><strong>$message</strong></p></div>";
}
function obtga_alerts() {
    if(get_option('obtga_idanalitycs')==null || get_option('obtga_idanalitycs')==""){
        showMessage('El plugin: "'.OBTURECODE_GA_NAME.'" necesita ser configurado, hasta que no añadas tu ID de Analitycs no va a funcionar. <a href="options-general.php?page=Widget_Obturecode_GA">Configúralo aquí</a>',true);
    }
    $error=false;
    if(function_exists('pll__')){
        //When pll__ accetp language
        //$i=0;
        //$idiomas = pll_languages_list(array('hide_empty'=>1));
        //while($i<count($idiomas) && pll__(OBTURECODE_GA_TEXT,))
        if(strstr(pll__(OBTURECODE_GA_TEXT),'%button%')===false)
            $error=true;
    }else{
        if(strstr(get_option('obtga_texto',OBTURECODE_GA_TEXT),'%button%')===false)
            $error=true;
    }
    if($error)
        showMessage('Pon "%button%" (sin las comillas) dentro del campo "Texto" y automáticamente se sustituirá por el enlace a la página de política de cookies. ¡Es obligatorio!');
    
    // showMessage('El plugin: "'.OBTURECODE_GA_NAME.'" ha detectado que el Texto de explicación que has personalizado no tiene la expresión %button% necesaria para sustituir por el botón a la pagina de polítitca de cookies. <a href="options-general.php?page=Widget_Obturecode_GA">Configúralo aquí</a>',true);


}
add_action( 'admin_notices', 'obtga_alerts' );

register_activation_hook(__FILE__,'obtcookies_install'); 

register_deactivation_hook( __FILE__, 'obtcookies_remove' );
