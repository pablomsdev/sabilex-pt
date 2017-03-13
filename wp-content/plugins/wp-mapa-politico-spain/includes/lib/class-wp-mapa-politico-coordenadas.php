<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Mapa_Politico_Coordenadas {
	
	
	private static $wpmapas = array();
	
	public static function get_map_older_version(){
		
		$wpmapas = array();
	
		global $wpdb;
		
		
		$aux = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}wpmps'");

		if ($aux) {
			// Existe la table de areas de verisones atenriores
			$arrWPIMs = $wpdb->get_results("SELECT id, id_zona, desc_zona, href, title FROM {$wpdb->prefix}wpmps ", ARRAY_A);
			
			if ($arrWPIMs) {
				// Existen datos de una versión previa, los vamos a recuperar y borramos la tabla
				$wp_id_mapa = 0;
				$wpmapas[$wp_id_mapa]['mapa'] = array('title'=>'España', 'description'=>'Mapa político básico');
			
				// Las coordenadas tomamos las qeu tenemos por defcto
				$default_map = WP_Mapa_Politico_Coordenadas::get_default_map();			
				
				foreach ($arrWPIMs as $wpim) {
					
					
					$wpmapas[$wp_id_mapa]['areas'][$wpim["id_zona"]] =  array (	  "area_init" => $default_map[0]['areas'][$wpim["id_zona"]]['area_init']
																				, "area" => $default_map[0]['areas'][$wpim["id_zona"]]['area_init']
																				, "desc_area" =>  $wpim["desc_zona"]
																				, "href" => $wpim["href"]
																				, "target" => "_blank"
																				, "title" => $wpim["desc_zona"]
																				, "alt" => $wpim["href"]);
			
				}
				
				
				
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpmps");
					
			}
						
		}
	
		return $wpmapas;
	}
	
	public static function get_default_map () {
		$wp_id_mapa = 0;
		
		// Información del mapa
		$wpmapas[$wp_id_mapa]['mapa'] = array('title'=>'España', 'description'=>'Mapa político básico');
		
		
		// 15 A coruña
		$wpmapas[$wp_id_mapa]['areas']['15'] =  array (	  "area_init" =>  array (184,83,186,64,196,43,172,52,143,63,151,91,168,84)
				, "area" =>  array (184,83,186,64,196,43,172,52,143,63,151,91,168,84)
				, "desc_area" => "A Coruña"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "A Coruña"
				, "alt" => "A Coruña");
		
		// 01 Araba - Alava
		$wpmapas[$wp_id_mapa]['areas']['01'] =  array (	  "area_init" =>  array (324,81,335,76,343,84,344,91,348,91,361,98,356,113,340,106,333,97,336,92,334,85)
				, "area" =>  array (324,81,335,76,343,84,344,91,348,91,361,98,356,113,340,106,333,97,336,92,334,85)
				, "desc_area" => "Araba - Alava"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Araba - Alava"
				, "alt" => "Araba - Alava");
		
		// 02 Albacete
		$wpmapas[$wp_id_mapa]['areas']['02'] =  array (	  "area_init" =>  array (375,252,381,270,392,282,372,284,369,296,343,311,334,307,341,298,330,270,335,252,357,256)
				, "area" =>  array (375,252,381,270,392,282,372,284,369,296,343,311,334,307,341,298,330,270,335,252,357,256)
				, "desc_area" => "Albacete"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Albacete"
				, "alt" => "Albacete");
		
		// 03 Alicante
		$wpmapas[$wp_id_mapa]['areas']['03'] =  array (	  "area_init" =>  array (394,322,430,285,421,277,402,285,390,283,386,302,386,312)
				, "area" =>  array (394,322,430,285,421,277,402,285,390,283,386,302,386,312)
				, "desc_area" => "Alicante"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Alicante"
				, "alt" => "Alicante");
		
		// 04 Almeria
		$wpmapas[$wp_id_mapa]['areas']['04'] =  array (	  "area_init" =>  array (317,366,324,342,334,342,346,317,363,342,349,366)
				, "area" =>  array (317,366,324,342,334,342,346,317,363,342,349,366)
				, "desc_area" => "Almería"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Almería"
				, "alt" => "Almería");
		
		
		// 33 Asturias
		$wpmapas[$wp_id_mapa]['areas']['33'] =  array (	  "area_init" =>  array (217,54,295,68,279,79,221,83,214,68)
				, "area" =>  array (217,54,295,68,279,79,221,83,214,68)
				, "desc_area" => "Asturias"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Asturias"
				, "alt" => "Asturias");
		
		// 05 Avila
		$wpmapas[$wp_id_mapa]['areas']['05'] = array (	  "area_init" =>  array (243,198,259,210,289,194,279,168,264,165,263,178)
														, "area" =>  array (243,198,259,210,289,194,279,168,264,165,263,178)
														, "desc_area" => "Ávila"
														, "href" => "#"
														, "target" => "_blank"
														, "title" => "Ávila"
														, "alt" => "Ávila");
												
		// 06 Badajoz
		$wpmapas[$wp_id_mapa]['areas']['06'] =  array (	  "area_init" =>  array (187,237,201,237,229,253,261,239,270,248,257,271,219,300,179,276,194,251)
				, "area" =>  array (187,237,201,237,229,253,261,239,270,248,257,271,219,300,179,276,194,251)
				, "desc_area" => "Badajoz"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Badajoz"
				, "alt" => "Badajoz");
		
		// 08 Barcelona
		$wpmapas[$wp_id_mapa]['areas']['08'] =  array (	  "area_init" => array (481,181,520,160,490,131,481,153,474,153,473,169)
				, "area" => array (481,181,520,160,490,131,481,153,474,153,473,169)
				, "desc_area" => "Barcelona"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Barcelona"
				, "alt" => "Barcelona");
		
		// 48 Bizkaia
		$wpmapas[$wp_id_mapa]['areas']['48'] =  array (	  "area_init" => array (335,74,349,68,362,74,357,81,357,87,345,88,345,82)
				, "area" => array (335,74,349,68,362,74,357,81,357,87,345,88,345,82)
				, "desc_area" => "Bizkaia"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Bizkaia"
				, "alt" => "Bizkaia");
		
		// 09 Burgos
		$wpmapas[$wp_id_mapa]['areas']['09'] =  array (	  "area_init" => array (299,97,301,145,311,153,338,131,331,127,333,84,314,85,309,94,306,98)
				, "area" => array (299,97,301,145,311,153,338,131,331,127,333,84,314,85,309,94,306,98)
				, "desc_area" => "Burgos"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Burgos"
				, "alt" => "Burgos");
		
		
		// 10 Cáceres
		$wpmapas[$wp_id_mapa]['areas']['10'] =  array (	  "area_init" => array (201,196,216,199,226,191,253,209,261,238,230,251,201,236,187,236,181,219,197,218)
				, "area" => array (201,196,216,199,226,191,253,209,261,238,230,251,201,236,187,236,181,219,197,218)
				, "desc_area" => "Cáceres"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Cáceres"
				, "alt" => "Cáceres");
		
		
		// 11 Cádiz
		$wpmapas[$wp_id_mapa]['areas']['11'] =  array (	  "area_init" => array (205,348,244,347,244,353,230,364,239,380,226,386,207,371)
				, "area" => array (205,348,244,347,244,353,230,364,239,380,226,386,207,371)
				, "desc_area" => "Cádiz"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Cádiz"
				, "alt" => "Cádiz");
		
		
		// 39 Cantabria
		$wpmapas[$wp_id_mapa]['areas']['39'] =  array (	  "area_init" => array (281,78,297,67,323,66,333,75,307,88,307,96,299,96,297,88,286,85)
				, "area" => array (281,78,297,67,323,66,333,75,307,88,307,96,299,96,297,88,286,85)
				, "desc_area" => "Cantabria"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Cantabria"
				, "alt" => "Cantabria");
		
		// 12 Castellón
		$wpmapas[$wp_id_mapa]['areas']['12'] =  array (	  "area_init" => array (420,242,443,211,433,200,419,199,417,216,399,229,404,237)
				, "area" => array (420,242,443,211,433,200,419,199,417,216,399,229,404,237)
				, "desc_area" => "Castellón"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Castellón"
				, "alt" => "Castellón");
		
		
		// 13 Ciudad Real
		$wpmapas[$wp_id_mapa]['areas']['13'] =  array (	  "area_init" => array (258,272,273,243,286,241,289,247,308,251,327,245,333,251,328,271,332,286,281,289)
				, "area" => array (258,272,273,243,286,241,289,247,308,251,327,245,333,251,328,271,332,286,281,289)
				, "desc_area" => "Ciudad Real"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Ciudad Real"
				, "alt" => "Ciudad Real");
		
		
		// 51 Ceuta
		$wpmapas[$wp_id_mapa]['areas']['51'] =  array (	  "area_init" =>  array (221,394,235,388,233,408)
				, "area" =>  array (221,394,235,388,233,408)
				, "desc_area" => "Ceuta"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Ceuta"
				, "alt" => "Ceuta");
		
		
		// 14 Córdoba
		$wpmapas[$wp_id_mapa]['areas']['14'] =  array (	  "area_init" =>  array (233,293,257,273,281,292,277,313,286,329,272,339,251,317)
				, "area" =>  array (233,293,257,273,281,292,277,313,286,329,272,339,251,317)
				, "desc_area" => "Córdoba"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Córdoba"
				, "alt" => "Córdoba");
		
		// 16 Cuenca
		$wpmapas[$wp_id_mapa]['areas']['16'] =  array (	  "area_init" =>  array (326,219,331,247,355,255,375,251,388,226,356,198)
				, "area" =>  array (326,219,331,247,355,255,375,251,388,226,356,198)
				, "desc_area" => "Cuenca"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Cuenca"
				, "alt" => "Cuenca");
		
		// 20 Gipuzkoa
		$wpmapas[$wp_id_mapa]['areas']['20'] =  array (	  "area_init" =>  array (349,92,364,96,382,76,377,73,370,77,361,76,358,86)
				, "area" =>  array (349,92,364,96,382,76,377,73,370,77,361,76,358,86)
				, "desc_area" => "Gipuzkoa"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Gipuzkoa"
				, "alt" => "Gipuzkoa");
		
		// 17 Girona
		$wpmapas[$wp_id_mapa]['areas']['17'] =  array (	  "area_init" =>  array (490,129,523,162,540,149,540,134,530,126)
				, "area" =>  array (490,129,523,162,540,149,540,134,530,126)
				, "desc_area" => "Girona"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Girona"
				, "alt" => "Girona");
		
		// 18 Granada
		$wpmapas[$wp_id_mapa]['areas']['18'] =  array (	  "area_init" =>  array (274,339,280,354,292,361,317,361,323,341,331,341,346,315,331,313,323,325,284,332)
				, "area" =>  array (274,339,280,354,292,361,317,361,323,341,331,341,346,315,331,313,323,325,284,332)
				, "desc_area" => "Granada"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Granada"
				, "alt" => "Granada");
		
		// 19 Guadalajara
		$wpmapas[$wp_id_mapa]['areas']['19'] =  array (	  "area_init" =>  array (317,185,331,214,349,198,359,198,366,208,377,198,376,185,362,173,353,178,341,168,321,168)
				, "area" =>  array (317,185,331,214,349,198,359,198,366,208,377,198,376,185,362,173,353,178,341,168,321,168)
				, "desc_area" => "Guadalajara"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Guadalajara"
				, "alt" => "Guadalajara");
		
		// 21 Huelva
		$wpmapas[$wp_id_mapa]['areas']['21'] =  array (	  "area_init" =>  array (188,286,217,302,206,312,204,351,166,311)
				, "area" =>  array (188,286,217,302,206,312,204,351,166,311)
				, "desc_area" => "Huelva"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Huelva"
				, "alt" => "Huelva");
		
		// 22 Huesca
		$wpmapas[$wp_id_mapa]['areas']['22'] =  array (	  "area_init" =>  array (412,100,453,109,450,146,441,152,443,162,426,168,403,142,402,109)
				, "area" =>  array (412,100,453,109,450,146,441,152,443,162,426,168,403,142,402,109)
				, "desc_area" => "Huesca"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Huesca"
				, "alt" => "Huesca");
		
		// 07 Illes Balears
		$wpmapas[$wp_id_mapa]['areas']['07'] =  array (	  "area_init" =>  array (459,297,482,328,578,263,556,240)
				, "area" =>  array (459,297,482,328,578,263,556,240)
				, "desc_area" => "Illes Balears"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Illes Balears"
				, "alt" => "Illes Balears");
		
		// 23 Jaén
		$wpmapas[$wp_id_mapa]['areas']['23'] =  array (	  "area_init" =>  array (279,312,283,291,333,288,339,300,323,322,288,329)
				, "area" => array (279,312,283,291,333,288,339,300,323,322,288,329)
				, "desc_area" => "Jaén"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Jaén"
				, "alt" => "Jaén");
		
		
		// 26 La Rioja
		$wpmapas[$wp_id_mapa]['areas']['26'] =  array (	  "area_init" =>  array (334,103,333,127,348,134,354,129,368,138,370,129,357,114,343,114)
				, "area" => array (334,103,333,127,348,134,354,129,368,138,370,129,357,114,343,114)
				, "desc_area" => "La Rioja"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "La Rioja"
				, "alt" => "La Rioja");
		
		// 35 Las Palmas
		$wpmapas[$wp_id_mapa]['areas']['35'] =  array (	  "area_init" =>  array (111,384,167,346,174,353,160,385,124,404,107,397)
				, "area" => array (111,384,167,346,174,353,160,385,124,404,107,397)
				, "desc_area" => "Las Palmas"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Las Palmas"
				, "alt" => "Las Palmas");
		
		// 27 Lugo
		$wpmapas[$wp_id_mapa]['areas']['27'] =  array (	  "area_init" =>  array (185,93,196,101,209,100,217,80,211,63,216,53,198,43,187,67)
				, "area" => array (185,93,196,101,209,100,217,80,211,63,216,53,198,43,187,67)
				, "desc_area" => "Lugo"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Lugo"
				, "alt" => "Lugo");
		
		
		// 24 León
		$wpmapas[$wp_id_mapa]['areas']['24'] =  array (	  "area_init" =>   array (218,113,258,122,272,113,282,85,275,80,221,85,214,90,214,97,219,104)
				, "area" =>  array (218,113,258,122,272,113,282,85,275,80,221,85,214,90,214,97,219,104)
				, "desc_area" => "León"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "León"
				, "alt" => "León");
		
		// 25 Lleida
		$wpmapas[$wp_id_mapa]['areas']['25'] =  array (	  "area_init" => array (442,173,444,152,452,145,454,106,489,127,480,150,472,149,471,163)
				, "area" =>  array (442,173,444,152,452,145,454,106,489,127,480,150,472,149,471,163)
				, "desc_area" => "Lleida"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Lleida"
				, "alt" => "Lleida");
		
		// 28 Madrid
		$wpmapas[$wp_id_mapa]['areas']['28'] =  array (	  "area_init" => array (279,204,318,169,322,177,317,188,329,215,305,223,307,214,292,205)
				, "area" => array (279,204,318,169,322,177,317,188,329,215,305,223,307,214,292,205)
				, "desc_area" => "Madrid"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Madrid"
				, "alt" => "Madrid");
		
		// 29 Málaga
		$wpmapas[$wp_id_mapa]['areas']['29'] =  array (	  "area_init" => array (234,363,247,351,247,345,262,336,274,341,278,354,290,361,239,377)
				, "area" => array (234,363,247,351,247,345,262,336,274,341,278,354,290,361,239,377)
				, "desc_area" => "Málaga"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Málaga"
				, "alt" => "Málaga");
		
		// 52 Melilla
		$wpmapas[$wp_id_mapa]['areas']['52'] =  array (	  "area_init" => array (300,430,306,422,314,430,309,436)
				, "area" => array (300,430,306,422,314,430,309,436)
				, "desc_area" => "Melilla"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Melilla"
				, "alt" => "Melilla");
		
		
		// 30 Murcia
		$wpmapas[$wp_id_mapa]['areas']['30'] =  array (	  "area_init" => array (363,340,395,327,385,311,387,287,373,284,370,297,344,309)
				, "area" => array (363,340,395,327,385,311,387,287,373,284,370,297,344,309)
				, "desc_area" => "Murcia"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Murcia"
				, "alt" => "Murcia");
		
		// 31 Navarra
		$wpmapas[$wp_id_mapa]['areas']['31'] =  array (	  "area_init" => array (357,113,379,136,390,136,392,114,415,97,381,78)
														, "area" => array (357,113,379,136,390,136,392,114,415,97,381,78)
														, "desc_area" => "Navarra"
														, "href" => "#"
														, "target" => "_blank"
														, "title" => "Navarra"
														, "alt" => "Navarra");
		
		// 32 Ourense
		$wpmapas[$wp_id_mapa]['areas']['32'] =  array (	  "area_init" => array (174,126,206,127,219,107,211,99,204,103,192,103,185,96,170,99,179,114)
														, "area" => array (174,126,206,127,219,107,211,99,204,103,192,103,185,96,170,99,179,114)
														, "desc_area" => "Ourense"
														, "href" => "#"
														, "target" => "_blank"
														, "title" => "Ourense"
														, "alt" => "Ourense");
		
		// 34 Palencia
		$wpmapas[$wp_id_mapa]['areas']['34'] =  array (	  "area_init" => array (278,88,297,88,299,139,288,139,273,130)
				, "area" => array (278,88,297,88,299,139,288,139,273,130)
				, "desc_area" => "Palencia"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Palencia"
				, "alt" => "Palencia");
		
		// 36 Pontevedra
		$wpmapas[$wp_id_mapa]['areas']['36'] =  array (	  "area_init" => array (151,121,173,110,168,97,184,95,184,84,152,92)
				, "area" => array (151,121,173,110,168,97,184,95,184,84,152,92)
				, "desc_area" => "Pontevedra"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Pontevedra"
				, "alt" => "Pontevedra");
		
		// 37 Salamanca
		$wpmapas[$wp_id_mapa]['areas']['37'] =  array (	  "area_init" => array (241,199,262,178,262,166,219,154,208,164,204,196,216,197,226,189)
				, "area" => array (241,199,262,178,262,166,219,154,208,164,204,196,216,197,226,189)
				, "desc_area" => "Salamanca"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Salamanca"
				, "alt" => "Salamanca");
		
		// 38 Santa Cruz de Tenerife
		$wpmapas[$wp_id_mapa]['areas']['38'] =  array (	  "area_init" => array (101,395,114,370,59,362,48,408)
				, "area" => array (101,395,114,370,59,362,48,408)
				, "desc_area" => "Santa Cruz de Tenerife"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Santa Cruz de Tenerife"
				, "alt" => "Santa Cruz de Tenerife");
		
		// 40 Segovia
		$wpmapas[$wp_id_mapa]['areas']['40'] =  array (	  "area_init" => array (280,167,290,192,327,164,318,153,310,156,304,151,287,156)
				, "area" => array (280,167,290,192,327,164,318,153,310,156,304,151,287,156)
				, "desc_area" => "Segovia"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Segovia"
				, "alt" => "Segovia");
		
		// 41 Sevilla
		$wpmapas[$wp_id_mapa]['areas']['41'] =  array (	  "area_init" => array (206,346,207,313,229,293,260,335,244,346)
				, "area" => array (206,346,207,313,229,293,260,335,244,346)
				, "desc_area" => "Sevilla"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Sevilla"
				, "alt" => "Sevilla");
		
		// 42 Soria
		$wpmapas[$wp_id_mapa]['areas']['42'] =  array (	  "area_init" => array (318,152,330,165,342,166,350,176,360,174,370,142,355,130,346,137,339,133)
				, "area" => array (318,152,330,165,342,166,350,176,360,174,370,142,355,130,346,137,339,133)
				, "desc_area" => "Soria"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Soria"
				, "alt" => "Soria");
		
		// 43 Tarragona
		$wpmapas[$wp_id_mapa]['areas']['43'] =  array (	  "area_init" => array (479,181,470,164,434,182,435,202,443,209)
				, "area" => array (479,181,470,164,434,182,435,202,443,209)
				, "desc_area" => "Tarragona"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Tarragona"
				, "alt" => "Tarragona");
		
		// 44 Teruel
		$wpmapas[$wp_id_mapa]['areas']['44'] =  array (	  "area_init" => array (379,185,411,172,434,190,430,200,420,195,414,202,417,214,394,230,371,205,379,198)
				, "area" => array (379,185,411,172,434,190,430,200,420,195,414,202,417,214,394,230,371,205,379,198)
				, "desc_area" => "Teruel"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Teruel"
				, "alt" => "Teruel");
		
		// 45 Toledo
		$wpmapas[$wp_id_mapa]['areas']['45'] =  array (	  "area_init" => array (255,213,267,243,291,238,289,245,307,250,329,242,325,219,304,226,304,215,276,205)
				, "area" => array (255,213,267,243,291,238,289,245,307,250,329,242,325,219,304,226,304,215,276,205)
				, "desc_area" => "Toledo"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Toledo"
				, "alt" => "Toledo");
		
		
		// 46 Valencia
		$wpmapas[$wp_id_mapa]['areas']['46'] =  array (	  "area_init" => array (418,242,421,276,397,283,383,271,376,245,389,229)
				, "area" => array (418,242,421,276,397,283,383,271,376,245,389,229)
				, "desc_area" => "Valencia"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Valencia"
				, "alt" => "Valencia");
		
		
		// 47 Valladolid
		$wpmapas[$wp_id_mapa]['areas']['47'] =  array (	  "area_init" => array (260,123,259,162,278,167,303,150,299,141,277,137,271,117)
				, "area" => array (260,123,259,162,278,167,303,150,299,141,277,137,271,117)
				, "desc_area" => "Valladolid"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Valladolid"
				, "alt" => "Valladolid");
		
		// 49 Zamora
		$wpmapas[$wp_id_mapa]['areas']['49'] =  array (	  "area_init" => array (209,124,216,114,258,124,257,163,221,153,227,139)
				, "area" => array (209,124,216,114,258,124,257,163,221,153,227,139)
				, "desc_area" => "Zamora"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Zamora"
				, "alt" => "Zamora");
		
		// 50 Zaragoza
		$wpmapas[$wp_id_mapa]['areas']['50'] =  array (	  "area_init" => array (401,110,402,142,425,170,437,166,440,173,430,183,412,170,377,185,361,167,371,148,369,136,377,136,388,139,394,117)
				, "area" => array (401,110,402,142,425,170,437,166,440,173,430,183,412,170,377,185,361,167,371,148,369,136,377,136,388,139,394,117)
				, "desc_area" => "Zaragoza"
				, "href" => "#"
				, "target" => "_blank"
				, "title" => "Zaragoza"
				, "alt" => "Zaragoza");
		
		return $wpmapas;
		
	}
	
	
	public static function set_default_map ( $id_mapa = '0' ) {
				
		$mapas =  get_option( 'wpmps_plugin_mapas' );
				
		if (!$mapas) {
			// No existe ningún mapa, vamos a comprobar si existe
			// una versión previa del plugin y sino se recuperan
			// un mapa por defecto.
			
			// Comprobamos si existe un mapa de la versión previa
			// que tiene la información guardada en la tabla wp_wpmps
			$mapas =  WP_Mapa_Politico_Coordenadas::get_map_older_version();
			
			if (!$mapas) {		
				$mapas = WP_Mapa_Politico_Coordenadas::get_default_map();
				
			}
			
			update_option( 'wpmps_plugin_mapas', $mapas );
			
		}
			
		
		
	}
	
	
	public static function generar_mapa_coordenadas( $id_mapa = '0' ) {
		
		
		$wpmps_mapas =  get_option( 'wpmps_plugin_mapas' );
		$mapa = $wpmps_mapas[$id_mapa];
		
		//echo '<pre>'; print_r($mapa); echo '</pre>'; die;
		
		$nombre_mapa = 'wp-img-mapa'.'601'.'x';
		$mapa_coordenadas = '';
		$mapa_coordenadas .= '<map name="'.$nombre_mapa.'" id="'.$nombre_mapa.'">';
	
		foreach ($mapa['areas'] as $cod_area => $value){
	
			//echo '<pre>';echo $cod_area;print_r($value);echo '</pre>';	die;
			
			$mapa_coordenadas .= '<area shape="poly" '
								.' id ="prv-'.$cod_area.'"'
								.' class="provincia" '
								.' coords="'.implode(",", $value['area']).'"'
								.' href="'.$value['href'].'"'
								.' alt="'.$value['alt'].'"'
								.' title="'.$value['title'].'"'
								.' target="'.$value['target'].'"'
								.'/>';
		}
		$mapa_coordenadas .= '</map>';
		return $mapa_coordenadas;
	
	}
	
	
	
}