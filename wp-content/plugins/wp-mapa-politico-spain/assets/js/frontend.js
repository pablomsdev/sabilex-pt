jQuery(function($){
	$(document).ready(function(){
	
		/* Creamos el mapa y lo asignamos a la imagen */
		var imagen = $('#wp-img-mapa');
		var ancho = imagen.width();
		var alto =  imagen.height();
			
		var nombre_mapa = "#wp-img-mapa"+ancho+"x";
		
		if ($(nombre_mapa).length == 0) {
			
			imagen.attr("usemap",nombre_mapa);
			
			// No existe el mapa, lo generamos
			var data = {
				'action': 'generar_mapa_coordenadas',
				'wpms_ancho_imagen': ancho,
				'wpms_alto_imagen': alto
				
			};
		
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(bloginfo.ajaxurl, data, function(response) {
				
				$( ".wpim-wrap-mapa" ).append( response )
				
				$('#wp-img-map').attr("usemap",nombre_mapa);
								
				$("area.provincia").on("mouseover", function () {
						
					var imagen_actual = $("img#wp-img-mapa").attr("src");
					var imagen_nueva ;
					
					var siteurl =  bloginfo.siteurl;
			
					var ruta = siteurl+"/wp-content/plugins/wp-mapa-politico-spain/images/";
					var id_area = $(this).attr('id');
					
					
					if (
					   (id_area == 'prv-15') // A coruna 
					|| (id_area == 'prv-36') // Pontevedra
					|| (id_area == 'prv-27') // Lugo
					|| (id_area == 'prv-32') // Ourense
					|| (id_area == 'prv-33') // Asturias
					|| (id_area == 'prv-39') // Cantabria
					|| (id_area == 'prv-48') // Bizkaia
					|| (id_area == 'prv-20') // Guipuzkoa
					|| (id_area == 'prv-01') // Alava
					|| (id_area == 'prv-31') // Navarra
					|| (id_area == 'prv-24') // Leon		
					|| (id_area == 'prv-34') // Palencia
					|| (id_area == 'prv-09') // Burgos
					|| (id_area == 'prv-49') // Zamora
					|| (id_area == 'prv-47') // Valladolid
					|| (id_area == 'prv-37') // Salamanca
					|| (id_area == 'prv-05') // Avila
					|| (id_area == 'prv-40') // Segovia
					|| (id_area == 'prv-42') // Soria
					|| (id_area == 'prv-26') // La rioja		
					|| (id_area == 'prv-22') // Huesca
					|| (id_area == 'prv-50') // Zaragoza
					|| (id_area == 'prv-44') // Teruel		
					|| (id_area == 'prv-25') // Lleida
					|| (id_area == 'prv-17') // Girona
					|| (id_area == 'prv-08') // Barcelona
					|| (id_area == 'prv-43') // Tarragona		
					|| (id_area == 'prv-12') // Castellon
					|| (id_area == 'prv-46') // Valencia
					|| (id_area == 'prv-03') // Alicante		
					|| (id_area == 'prv-30') // Murcia		
					|| (id_area == 'prv-19') // Guadalajara
					|| (id_area == 'prv-45') // Toledo
					|| (id_area == 'prv-16') // Cuenca
					|| (id_area == 'prv-13') // Ciudad REal
					|| (id_area == 'prv-02') // Albacete		
					|| (id_area == 'prv-10') // Caceres
					|| (id_area == 'prv-06') // Badajoz		
					|| (id_area == 'prv-21') // Huelva
					|| (id_area == 'prv-41') // Sevilla
					|| (id_area == 'prv-14') // Cordoba
					|| (id_area == 'prv-23') // Jaen
					|| (id_area == 'prv-11') // Cadiz
					|| (id_area == 'prv-29') // Malaga
					|| (id_area == 'prv-18') // Granada
					|| (id_area == 'prv-04') // Almeria	
					|| (id_area == 'prv-38') // Santa cruz de tenerife
					|| (id_area == 'prv-35') // Las Palmas		
					|| (id_area == 'prv-07') // Islas BAleares		
					|| (id_area == 'prv-51') // ceuta		
					|| (id_area == 'prv-52') // Melilla		
					|| (id_area == 'prv-28') // Madrid
					){
						imagen_nueva = ruta+"mapa_base_azul_"+id_area+".png";
						
					} else {						
						imagen_nueva = ruta+"mapa_base_azul_claro.png";
						
					}
							
					if (imagen_actual != imagen_nueva) {
						$("img#wp-img-mapa").attr("src", imagen_nueva);
								
					}
					
					return false;
						
				});				 
				
			});
						
		}
		
	
	});
	
});