<?php
/*
Plugin Name: HTML5 Responsive FAQ
Author: Aman Verma
Author URI: http://www.indatos.com/?ref=faq
Plugin URI: http://www.indatos.com/?ref=faq
Description: HTML5 Responsive FAQ plugin makes it easy for you to FAQs on your site. Fully compatible with all responsive themes.
Version: 2.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/



add_action('init', 'register_hrf_faq');

function register_hrf_faq() {

   register_post_type('hrf_faq', array(
         'label'           => 'FAQs',
         'description'     => '',
         'public'          => true,
         'show_ui'         => true,
         'show_in_menu'    => true,
         'capability_type' => 'post',
         'map_meta_cap'    => true,
         'hierarchical'    => false,
         'rewrite'         => array(
                                 'slug'       => 'hrf_faq',
                                 'with_front' => true
                              ),
         'query_var'       => true,
         'exclude_from_search' => true,
         'menu_position'   => 5,
         'supports'        => array('title','editor', 'page-attributes'),
         'taxonomies'      => array('category'),
         'labels'          => array (
                                'name'               => 'FAQs',
                                'singular_name'      => 'FAQ',
                                'menu_name'          => 'FAQs',
                                'add_new'            => 'Add FAQ',
                                'add_new_item'       => 'Add New FAQ',
                                'edit'               => 'Edit',
                                'edit_item'          => 'Edit FAQ',
                                'new_item'           => 'New FAQ',
                                'view'               => 'View FAQ',
                                'view_item'          => 'View FAQ',
                                'search_items'       => 'Search FAQs',
                                'not_found'          => 'No FAQs Found',
                                'not_found_in_trash' => 'No FAQs Found in Trash',
                                'parent'             => 'Parent FAQ',
                              )) );
}

require plugin_dir_path(__FILE__) . 'include/hrf-options.php';
require plugin_dir_path(__FILE__) . 'include/hrf-faq.php';
require plugin_dir_path(__FILE__) . 'include/hrf-style.php';

// Register style sheet.
add_action( 'wp_enqueue_scripts', 'fn_hrf_scripts' );

function fn_hrf_scripts() 
{
   wp_enqueue_script( 'html5-responsive-faq', plugins_url( 'html5-responsive-faq/js/hrf-script.js' ) , array('jquery') );
}


add_action( 'admin_enqueue_scripts', 'fn_hrf_color_picker' );

function fn_hrf_color_picker( $hook ) 
{

    if( is_admin() ) {  
        // Add the color picker css file       
        wp_enqueue_style( 'wp-color-picker' ); 
        // Include our custom jQuery file with WordPress Color Picker dependency
        wp_enqueue_script( 'html5-responsive-faq', plugins_url( 'html5-responsive-faq/js/hrf-options.js' ), array( 'wp-color-picker' ), false, true ); 
    }
}
