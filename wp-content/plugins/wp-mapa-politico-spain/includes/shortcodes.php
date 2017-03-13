<?php 
	//[wp-political-map-spain] por compatibilidad con versiones anteriores
	add_shortcode( 'wp-political-map-spain', 'wpmps_show_map' );
	add_shortcode( 'wpmps-map', 'wpmps_show_map' );
	function wpmps_show_map( $atts ){
		
		
		$html_mapa = '<div class="wpim-wrap-mapa wp-border-img-mapa" style="background-color:'.get_option('wpmps_background_color').'">'
					.'<img id="wp-img-mapa"  src="'.plugins_url( 'wp-mapa-politico-spain/images/mapa_base_azul_claro.png' ).'" >'
					.'</div>'
					;		
		return $html_mapa;
		
	}
	
	
	
	add_action( 'wp_ajax_generar_mapa_coordenadas', 'generar_mapa_coordenadas' );
	add_action( 'wp_ajax_nopriv_generar_mapa_coordenadas', 'generar_mapa_coordenadas' );
	function generar_mapa_coordenadas() {	
		
		$width_referencia = 601;
		$height_referencia = 477;
		
		$wpms_ancho = intval( $_POST['wpms_ancho_imagen'] );
		$wpms_alto = intval( $_POST['wpms_alto_imagen'] );
				
		// Calculamos los factores de correccion
		$factorx = $wpms_ancho / $width_referencia;
		$factory = $wpms_alto  / $height_referencia;
				
		$wpmps_mapas =  get_option( 'wpmps_plugin_mapas' );
		$mapa = $wpmps_mapas[0];
			
		$nombre_mapa = 'wp-img-mapa'.$wpms_ancho.'x';
		
		$mapa_coordenadas = '';
		$mapa_coordenadas .= '<map name="'.$nombre_mapa.'" id="'.$nombre_mapa.'" class="wpms-map">';
	
		foreach ($mapa['areas'] as $cod_area => $value){
						
			// Recorremos las areas de cada provincia para recalcular su mapa
			$coordenadas = "";
			foreach ($value['area'] as $i => $crd){
				$valor = $value['area'][$i];
			
				if (($valor%2)==0){
					$coordenadas .= round($valor * $factorx);
				}	else {
					$coordenadas .= round($valor * $factory);
				}
				$coordenadas .= ",";
					
			}
				
			// Fuera a la última coma ...
			$coordenadas = trim($coordenadas, ',');

			// ... y vuelta a casita
			$value['area'] = array($coordenadas);
						
			$mapa_coordenadas .= '<area shape="poly" '
										.' id ="prv-'.$cod_area.'"'
										.' class="provincia" '
										.' coords="'.implode(",", $value['area']).'"'
										.' href="'.esc_url($value['href']).'"'
										.' alt="'.$value['alt'].'"'
										.' title="'.$value['title'].'"'
										.' target="'.$value['target'].'"'
										.'/>';
		}
		$mapa_coordenadas .= '</map>';
	
		echo $mapa_coordenadas;
	
		wp_die(); // this is required to terminate immediately and return a proper response
		
	}	