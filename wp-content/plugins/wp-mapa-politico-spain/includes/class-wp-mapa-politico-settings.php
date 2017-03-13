<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Mapa_Politico_Settings {

	/**
	 * The single instance of WP_Mapa_Politico_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'wpmps_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'WP Mapa Politico', 'wpmps-plugin' ) , __( 'WP Mapa Politico', 'wpmps-plugin' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Configuración', 'wpmps-plugin' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {
		
		
		$settings['inicio'] = array(
				'title'					=> __('Inicio', 'wpmps-plugin' ),
				'description'			=> __( 'Bienvenido al WP Mapa Politico España, plugin WordPress que te
											permitirá mostrar un mapa politico de España con areas seleccionables.', 'wpmps-plugin' ),
				'fields'				=> array(
						array(
								'id' 			=> 'inicio',
								'label'			=> '',
								'description'	=> '',
								'type'			=> 'inicio',
								'default'		=> '0',
		
						),
				)
		);
		
				
		$settings['mapa'] = array(
				'title'					=> __( 'Mapa', 'wpmps-plugin' ),
				'description'			=> __( 'Define los enlaces para cada una de las provincias. # es un enlace vacío, a usar sino se quiere definir enlace.', 'wpmps-plugin' ),
				'fields'				=> array(
												array(
														'id' 			=> 'mapa',
														'label'			=> '',
														'description'	=> '',
														'type'			=> 'mapa',
														'default'		=> '0',
														
												),
										)
								);
						
		$settings['configuracion'] = array(
				'title'					=> __( 'Configuración', 'wpmps-plugin' ),
				'description'			=> __( 'Configuración aspectos del plugins.', 'wpmps-plugin' ),
				'fields'				=> array(					
												array(
														'id' 			=> 'background_color',
														'label'			=> __( 'Color del fondo del mapa', 'wpmps-plugin' ),
														'description'	=> __( 'Seleccione el color del fondo del mapa.', 'wpmps-plugin' ),
														'type'			=> 'color',
														'default'		=> '#dde6da'
												),
						
											)
								);
		
								
								
		

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
		$html .= '<h2>' . __( 'WP Mapa Politico España' , 'wpmps-plugin' ) . '</h2>' . "\n";
		$html .= '<div>' .  "\n";

		$tab = '';
		if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
			$tab .= $_GET['tab'];
		}

		// Show page tabs
		if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

			$html .= '<h2 class="nav-tab-wrapper">' . "\n";

			$c = 0;
			foreach ( $this->settings as $section => $data ) {

				// Set tab class
				$class = 'nav-tab';
				if ( ! isset( $_GET['tab'] ) ) {
					if ( 0 == $c ) {
						$class .= ' nav-tab-active';
					}
				} else {
					if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
						$class .= ' nav-tab-active';
					}
				}

				// Set tab link
				$tab_link = add_query_arg( array( 'tab' => $section ) );
				if ( isset( $_GET['settings-updated'] ) ) {
					$tab_link = remove_query_arg( 'settings-updated', $tab_link );
				}

				// Output tab
				$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

				++$c;
			}

			$html .= '</h2>' . "\n";
		}
		
		if ($tab=='inicio' || $tab=='') {
			
			// Get settings fields
			ob_start();
			settings_fields( $this->parent->_token . '_settings' );
			$this->do_settings_sections_wmps( $this->parent->_token . '_settings' );
				
			$html .= ob_get_clean();
			
		
		} else if ($tab=='mapa') {
			
			if (isset($_POST['Submit'])) {
							
				$wpmps_mapas =  get_option( 'wpmps_plugin_mapas' );
				
				foreach ($_POST as $key => $valor){
				
					$v = explode( '-', $key);
				
					if (isset($wpmps_mapas[$v[0]][$v[1]][$v[2]][$v[3]])){
				
						$wpmps_mapas[$v[0]][$v[1]][$v[2]][$v[3]] = $valor;
				
					}
				
				}
				update_option( 'wpmps_plugin_mapas', $wpmps_mapas );
			
				echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">'
						.'<p>'. __( 'Datos guardados correctamente' , 'wpmps-plugin' ) .'</p>'
						.'<button type="button" class="notice-dismiss"><span class="screen-reader-text">Descartar este aviso.</span></button>'
					.'</div>';
			}
				
			// Formulario para capturar la informacion del mapa y guararlo como una única opcion
			// $bnn_action = '../wp-content/plugins/'.plugin_basename( __DIR__ ).'/lib/grabar_coordenadas.php';
			$bnn_action = "";
			$html .= '<form method="post" action="'.$bnn_action.'" enctype="multipart/form-data">' . "\n";
			
			
			
			// Get settings fields
			ob_start();
			settings_fields( $this->parent->_token . '_settings' );
			$this->do_settings_sections_wmps( $this->parent->_token . '_settings' );
			
			$html .= ob_get_clean();
			
			$html .= '<p class="submit">' . "\n";
			$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
			$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Guardar cambios' , 'wpmps-plugin' ) ) . '" />' . "\n";
			$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
			
			
			
		} else {

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

			// Get settings fields
			ob_start();
			settings_fields( $this->parent->_token . '_settings' );
			$this->do_settings_sections_wmps( $this->parent->_token . '_settings' );
			$html .= ob_get_clean();

			$html .= '<p class="submit">' . "\n";
			$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
			$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Guardar cambios' , 'wpmps-plugin' ) ) . '" />' . "\n";
			$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
			
		}
		
		$html .= '</div>' . "\n";

		echo $html;
	}
	
	
	function do_settings_sections_wmps( $page ) {
		global $wp_settings_sections, $wp_settings_fields;
	
		if ( ! isset( $wp_settings_sections[$page] ) )
			return;
	
		foreach ( (array) $wp_settings_sections[$page] as $section ) {
			if ( $section['title'] )
				echo "<h3>{$section['title']}</h3>\n";
	
			if ( $section['callback'] )
				call_user_func( $section['callback'], $section );
	
			if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
				continue;
			
			
			echo '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Guardar cambios' , 'wpmps-plugin' ) ) . '" />' . "\n";
			
			echo '<table class="form-table">';
			$this->do_settings_fields_wpms( $page, $section['id'] );
			echo '</table>';
		}
	}
	
	
	function do_settings_fields_wpms($page, $section) {
		global $wp_settings_fields;
	
		if ( ! isset( $wp_settings_fields[$page][$section] ) )
			return;
	
		
		
		foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
			$class = '';
	
			if ( ! empty( $field['args']['class'] ) ) {
				$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
			}
	
			
			echo "<tr{$class}>";
	
			if ( ! empty( $field['args']['label_for'] ) ) {
				echo '<th scope="col"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
				
			} else {
				echo '<th scope="col">' . $field['title'] . '</th>';
				
			}
	
			echo '</tr>';
			echo "<tr{$class}>";
			echo '<td>';
			call_user_func($field['callback'], $field['args']);
			echo '</td>';
			echo '</tr>';
		}
	}

	/**
	 * Main WP_Mapa_Politico_Settings Instance
	 *
	 * Ensures only one instance of WP_Mapa_Politico_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WP_Mapa_Politico()
	 * @return Main WP_Mapa_Politico_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}