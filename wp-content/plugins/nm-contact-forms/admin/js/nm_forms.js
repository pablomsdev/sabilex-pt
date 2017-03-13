function addField(element){

		if(jQuery(element).closest('.nm_form').find('.nm_field_slug').length){

			ids = [];	
			jQuery(element).closest('.nm_form').find('.nm_field_slug').each(function(){
				ids.push(jQuery(this).val().match(/\d+/));
			});
			largest = Math.max.apply(Math, ids);
		
		}else{
		
			largest = 0;
		
		}
		
		jQuery(element).closest('.nm_form').find('.nm_left_empty').hide();
		nm_form_id = jQuery(element).closest('.nm_form').data('nm_form_id');
		fields_counter = largest + 1;

		jQuery(element).closest('.nm_form').find('.nm_form_fields').prepend('<li class="nm_item menu-item menu-item-page">'
			+'<dl class="menu-item-bar">'
			+'<dt class="menu-item-handle handle_nm">'
			+'<span class="item-title">'
			+'<span class="menu-item-title nm_field_heading">New Field '+fields_counter+'</span>'
			+'</span>'
			+'<span class="nm_toggle"></span>'
			+'</dt>'
			+'</dl>'
			+'<div class="nm_field_settings">'
			+'<table class="nm_table">'
			+'<tr><th>'
			+'Field title:'
			+'</th><td>'
			+'<input type="text" class="nm_field_title" name="nm_f['+nm_form_id+'][fields][nm_field_'+fields_counter+'][title]" value="New Field '+fields_counter+'"/>'
			+'</td></tr><tr class="nm_placeholder"><th>'
			+'Field placeholder:'
			+'</th><td>'
			+'<input type="text" name="nm_f['+nm_form_id+'][fields][nm_field_'+fields_counter+'][placeholder]" value=""/>'
			+'</td></tr><tr><th>'
			+'Field type:'
			+'</th><td>'
			+'<select type="text" class="nm_field_type" name="nm_f['+nm_form_id+'][fields][nm_field_'+fields_counter+'][type]">'
				+'<option value="text">Text</option>'
				+'<option value="textarea">Textarea</option>'
				+'<option value="email">Email</option>'
				+'<option value="html">HTML</option>'
				+'<option value="select">Select</option>'
				+'<option value="checkbox">Checkboxes</option>'
				+'<option value="radio">Radio Buttons</option>'
				+'<option value="file_upload">Single file upload</option>'
				+'<option value="get_hidden">GET variable (hidden)</option>'
				+'<option value="submit">Submit</option>'
				+'<option value="recaptcha">reCaptcha</option>'
				+'<option value="honeypot">Honey Pot</option>'
			+'</select>'
			+'</td></tr>'
			+'<tr class="nm_select_options">'
			+'<th>'
			+'Options:'
			+'</th>'
			+'<td>'
			+'<textarea style="min-height:200px;" placeholder="One option per line" name="nm_f['+nm_form_id+'][fields][nm_field_'+fields_counter+'][select_options]"></textarea>'
			+'</td>'
			+'</tr>'
			+'<tr class="nm_html nm_hide"><th>'
			+'HTML:'
			+'</th><td>'
			+'<textarea style="min-height:200px;" placeholder="HTML / Free text" name="nm_f['+nm_form_id+'][fields][nm_field_'+fields_counter+'][html]"></textarea>'
			+'</td></tr>'
			+'<tr class="nm_get"><th>'
			+'GET variable:'
			+'</th><td>'
			+'<input placeholder="e.g.: affiliate_id" type="text" name="nm_f['+nm_form_id+'][fields][nm_field_'+fields_counter+'][get]" value="" />'
			+'</td></tr>'
			+'<tr class="nm_exts nm_hide"><th>'
			+'Allowed extensions:'  
			+'</th><td>'
			+'<input placeholder="e.g.:jpg,png,gif" type="text" name="nm_f['+nm_form_id+'][fields][nm_field_'+fields_counter+'][extensions]" value=""/>'
			+'<p class="description">Separated by coma</p>'
			+'</td></tr>'
			+'<tr class="nm_size nm_hide"><th>'
			+'Max file size:'
			+'</th><td>'
			+'<input placeholder="e.g.: 10000" type="text" name="nm_f['+nm_form_id+'][fields][nm_field_'+fields_counter+'][size]" value="" />'
			+'<p class="description">Bytes, 1 000 000 ~ 1MB</p>'
			+'</td></tr>'
			+'<tr class="nm_required"><th>'
			+'Required:'
			+'</th><td>'
			+'<input type="checkbox" name="nm_f['+nm_form_id+'][fields][nm_field_'+fields_counter+'][required]"/>'
			+'</td></tr><tr><th>'
			+'Extra classes:'
			+'</th><td>'
			+'<input type="text" name="nm_f['+nm_form_id+'][fields][nm_field_'+fields_counter+'][classes]" value=""/>'
			+'</td></tr>'
			+'<tr><th>'
			+'</th><td>'
			+'<a href="" class="nm_delete_field">Delete field</a>'
			+'</td></tr>'
			+'</table>'
			+'<input type="hidden" class="nm_field_slug" name="nm_f['+nm_form_id+'][fields][nm_field_'+fields_counter+'][slug]" value="nm_field_'+fields_counter+'"/>'
			+'<div style="clear:both;"></div>'
			+'</div>'
			+'</li>'); 

}


function chkDuplicates(arr,justCheck){
  var len = arr.length, tmp = {}, arrtmp = arr.slice(), dupes = [];
  arrtmp.sort();
  while(len--){
   var val = arrtmp[len];
   if (/nul|nan|infini/i.test(String(val))){
     val = String(val);
    }
    if (tmp[JSON.stringify(val)]){
       if (justCheck) {return true;}
       dupes.push(val);
    }
    tmp[JSON.stringify(val)] = true;
  }
  return justCheck ? false : dupes.length ? dupes : null;
}

jQuery(document).ready(function(){

	/*var container = document.querySelector('#inner_forms');
	var msnry = new Masonry( container, {
		itemSelector: '.nm_form',
	});*/
	
	jQuery( "#nm_radio" ).buttonset();

	jQuery(document).on( 'change', '#nm_radio',function(event){
	
		amount = jQuery("#nm_radio :radio:checked").data('amount'); 
		
		jQuery('#nm_donation_value').val(amount);
	
	});
	
	jQuery(document).on( 'click', '.nm_toggle',function(event){
	
		if(jQuery(this).closest('.nm_item').hasClass('active')){
			jQuery(this).closest('.nm_item').removeClass('active');
			jQuery(this).closest('.nm_item').find('.nm_field_settings').slideUp(500,function() {
			
				//msnry.layout();
			});
			
		}else{
			jQuery(this).closest('.nm_item').addClass('active');
			jQuery(this).closest('.nm_item').find('.nm_field_settings').slideDown(500,function() {
			
				//msnry.layout();
				
			});
		
		}
	
	});
	
	jQuery(document).on( 'click', '.nm_form_delete',function(event){
		jQuery(this).closest('.nm_form').remove();
		//msnry.layout();
	});
	
	jQuery( ".nm_sortable" ).sortable();

	
	jQuery(document).on( 'click', '.nm_save_forms',function(event){

		event.preventDefault();
		
		if (jQuery('.nm_form').length){
		
			jQuery('.nm_form').each(function(){
			
				empty_id = false;
				has_duplicate = false;
				
				jQuery(this).find('.nm_field_slug').each(function(){
					if(jQuery(this).val() == ''){
						empty_id = true;
						return false;
					}
				});
				
				all_ids = [];
				
				jQuery(this).find('.nm_field_slug').each(function(){
					all_ids.push(jQuery(this).val());
				});
				jQuery(this).find('.nm_errors').html('');
				if(chkDuplicates(all_ids,true)){
					jQuery(this).find('.nm_errors').show().append('<li>Some of fields titles are duplicated</li>');
				}
				
				if(empty_id) jQuery(this).find('.nm_errors').show().append('<li>Some of fields missing their title</li>');
				
				console.log(all_ids);
				
			});

			
			if(!chkDuplicates(all_ids,true) && !empty_id){
				jQuery('#nm_all_forms').submit();
				jQuery('.nm_errors').html('').hide();
			}else{
				return false;
			}
		
		}else{
		
			jQuery('#nm_all_forms').submit();
			jQuery('.nm_errors').html('').hide();
			
		}
		
		
	});	
		
	jQuery(document).on( 'click', '#add_new_form',function(event){

		event.preventDefault();
		
		if(jQuery('#add_new_form_block').hasClass('active')){
			jQuery('#add_new_form_block').removeClass('active');
			jQuery('#add_new_form_block').slideUp(500);
		}else{
			jQuery('#add_new_form_block').addClass('active');
			jQuery('#add_new_form_block').slideDown(500);
		}
		
		
	});	
		
	jQuery(document).on( 'click', '.nm_delete_field',function(event){

		event.preventDefault();
		jQuery(this).closest('.nm_item').remove();
		
	});	
		
	jQuery(document).on( 'click', '#add_form',function(event){

		event.preventDefault();
			
		var nm_form_title = jQuery('#new_form_title').val();	
		nm_form_id = nm_form_title.toLowerCase();
        nm_form_id = nm_form_id.replace(/[^a-zA-Z0-9]+/g,'_');
	
		if(nm_form_title == ''){
			
			alert('You must enter form name');
			return false;
			
		}else{
		
			no_id_match = true;
			
			jQuery('.nm_form_id').each(function(){
				if(jQuery(this).val() == nm_form_id){
					no_id_match = false;
					return false;
				}
			});
			
			if(!no_id_match){
				alert('Form name has to be unique');
				return false;
			}
			
		}
		
		jQuery('#inner_forms').prepend('<div class="nm_form" data-nm_form_id="'+ nm_form_id +'">'
		+'<input type="hidden" name="nm_f['+ nm_form_id +'][nm_form_title]" value="'+nm_form_title+'">'
		+'<input type="hidden" class="nm_form_id" name="nm_f['+ nm_form_id +'][nm_form_id]" value="'+ nm_form_id +'">'
		+'<div class="nm_form_heading">'
		+'<h3>'+nm_form_title+'<span class="nm_form_delete"><span class="dashicons dashicons-no"></span></span><span class="nm_form_add">Add field</span></h3>'
		+'</div>'
		+'<div class="nm_left_side">'
		+'<div class="nm_left_empty" style="display:block;">'
		+'<span class="dashicons dashicons-index-card"></span>'
		+'<span>Form is empty, please add your first field.</span>'
		+'</div>'
		+'<ul class="menu nm_menu nm_form_fields nm_sortable  ui-sortable">'
		+'</ul>'
		+'</div>'
		+'<div class="nm_right_side">'
		+'<ul class="menu nm_menu nm_sortable  ui-sortable">'
			+'<li class="nm_item menu-item menu-item-page">'
				+'<dl class="menu-item-bar">'
				+'<dt class="menu-item-handle handle_nm">'
				+'<span class="item-title">'
					+'<span class="menu-item-title">Form Settings</span>'
				+'</span>'
				+'<!--<span class="nm_toggle"></span>-->'
				+'</dt>'
				+'</dl>'
				+'<div class="nm_field_settings" style="display:block;">'
							
								+'<table class="nm_table">'
									+'<tr><th>'
									+'Email subject: '
									+'</th><td>'
										+'<input type="text" name="nm_f['+ nm_form_id +'][subject]" value="">'
									+'</td></tr>'
									+'<tr><th><tr><th>'
									+'Receivers:'
									+'</th><td>'
										+'<input type="text" name="nm_f['+ nm_form_id +'][receivers]" value="">'
										+'<p class="description">Separated by coma.</p>'
									+'</td></tr>'
									+'<tr><th>'
									+'From email:'
									+'</th><td>'
										+'<input type="text" name="nm_f['+ nm_form_id +'][sender]" value="">'
										+'<p class="description">Email or field ID, fallback to admin email.</p>'
									+'</td></tr><tr><th>'
									+'From title:'
									+'</th><td>'
										+'<input type="text" name="nm_f['+ nm_form_id +'][sender_title]" value="">'
										+'<p class="description">You can construct form title using field IDs. e.g.: From {{nm_field_1}}</p>'
									+'</td></tr>'
									+'<tr><th>'
									+'Show labels:'
									+'</th><td>'
										+'<input type="checkbox" name="nm_f['+ nm_form_id +'][show_labels]">'
									+'</td></tr>'
									+'<tr><th>'
									+'Before FORM:' 
									+'</th><td>'
										+'<input type="text" name="nm_f['+ nm_form_id +'][before_form]" value="">'
									+'</td></tr>'
									+'</tr>'
									+'<tr><th>'
									+'After FORM:' 
									+'</th><td>'
										+'<input type="text" name="nm_f['+ nm_form_id +'][after_form]" value="">'
									+'</td></tr>'
									+'<tr>'
										+'<th>'
											+'Redirect url:'
										+'</th>'
										+'<td>'
											+'<input type="text" name="nm_f['+ nm_form_id +'][redirect]" value="">'
											+'<p class="description">Redirect after successful submission.</p>'
										+'</td>'
									+'</tr>'
									+'<tr>'
										+'<th>'
											+'Javascript redirect:'
										+'</th>'
										+'<td>'
											+'<label>'
											+'<input type="checkbox" name="nm_f['+ nm_form_id +'][js_redirect]"/>'
											+'Yes'
											+'</label>'
											+'<p class="description">Incase PHP redirect breaks the page.</p>'
										+'</td>'
									+'</tr>'
								+'</table>'
							+'</div>'
			+'</li>'
		+'</ul>'
		
		+'<div class="nm_shortcode">'
				+'Form shortcode:</br>'
				+'<span class="nm_bold">[nm_forms id="'+ nm_form_id +'"]</span>'
				+'<div class="nm_sep"></div>'
				+'Template integration:</br>'
				+'<span class="nm_bold"><&#63;php echo do_shortcode(\'[nm_forms id="'+ nm_form_id +'"]\');&#63;></span>'
		+'</div>'
		+'<input type="submit" class="nm_save_forms button-primary" value="Save Forms" />'
		+'</div>'

		+'</div>');
		
		jQuery( ".nm_sortable" ).sortable();
		jQuery('#new_form_title').val('');	
		
		//msnry.reloadItems()
	
	});
	
	jQuery(document).on( 'click', '.nm_form .nm_form_add',function(event){
		addField(this);
	//	msnry.layout();
	});

	jQuery(document).on( 'keyup', '.nm_field_title',function(event){
	
	
		value = jQuery(this).val();
		jQuery(this).closest('.nm_item').find('.nm_field_heading').html(value);
		

		
        //nm_form_id = value.toLowerCase();
		//nm_form_id = nm_form_id.replace(/[^a-zA-Z0-9]+/g,'_');
		//jQuery(this).closest('.nm_item').find('.nm_field_slug').val('nm_'+nm_form_id);
		
	
	});
	
	jQuery(document).on( 'change', '.nm_field_type',function(event){
	
	
		option = jQuery(this).val();
		this_option = jQuery(this);
		
		unlock_rechaptcha = true;
		unlock_submit = true;
		unlock_honeypot = true;
		
		jQuery(this).closest('.nm_form').find('.field_type').each(function(){
		
			val = jQuery(this).val();

			if(val == 'recaptcha'){
				unlock_rechaptcha = false;
			}
		
			if(val == 'submit'){
				unlock_submit = false;
			}
			
			if(val == 'honeypot'){
				unlock_honeypot = false;
			}
			
		});
		
		jQuery(this).closest('.nm_form').find('.field_type').promise().done(function() {
			if(unlock_rechaptcha){
				jQuery(this).closest('.nm_form').find(".field_type option[value=recaptcha]").prop('disabled',false);
			}
			if(unlock_submit){
				jQuery(this).closest('.nm_form').find(".field_type option[value=submit]").prop('disabled',false);
			}
			if(unlock_honeypot){
				jQuery(this).closest('.nm_form').find(".field_type option[value=honeypot]").prop('disabled',false);
			}
		});
		
		
		if(option == 'select' || option == 'radio' || option == 'checkbox'){
		
			jQuery(this).closest('.nm_table').find('.nm_select_options,.nm_required,.nm_placeholder,.nm_get').fadeIn(500);
			jQuery(this).closest('.nm_table').find('.nm_exts,.nm_size,.nm_placeholder,.nm_html').hide();
			if(option == 'select') jQuery(this).closest('.nm_table').find('.nm_placeholder').fadeIn(500);
			
		}else if(option == 'submit'){
		
			jQuery(this).closest('.nm_form').find(".field_type option[value=submit]").prop('disabled',true);
			jQuery(this_option).find('option[value=submit]').prop('disabled',false);
			
			jQuery(this).closest('.nm_table').find('.nm_select_options,.nm_required,.nm_get,.nm_placeholder,.nm_exts,.nm_size,.nm_html').hide();
		}else if(option == 'file_upload'){	
		
			jQuery(this).closest('.nm_table').find('.nm_exts,.nm_size').show();
			jQuery(this).closest('.nm_table').find('.nm_select_options,.nm_get,.nm_placeholder,.nm_html').hide();
		
		}else if(option == 'honeypot'){
		
			jQuery(this).closest('.nm_form').find(".field_type option[value=honeypot]").prop('disabled',true);
			jQuery(this_option).find('option[value=honeypot]').prop('disabled',false);
			
			jQuery(this).closest('.nm_table').find('.nm_select_options,.nm_required,.nm_get,.nm_placeholder,.nm_exts,.nm_size,.nm_html').hide();
		
		}else if(option == 'get_hidden'){
		
			jQuery(this).closest('.nm_table').find('.nm_get').fadeIn(500);
			jQuery(this).closest('.nm_table').find('.nm_select_options,.nm_required,.nm_placeholder,.nm_exts,.nm_size,.nm_html').hide();
		
		}else if(option == 'text' || option == 'textarea' || option == 'email'){
		
			jQuery(this).closest('.nm_table').find('.nm_required,.nm_placeholder,.nm_get').fadeIn(500);
			jQuery(this).closest('.nm_table').find('.nm_select_options,.nm_exts,.nm_size,.nm_html').hide();
			
		}else if(option == 'html'){
		
			jQuery(this).closest('.nm_table').find('.nm_html').fadeIn(500);
			jQuery(this).closest('.nm_table').find('.nm_select_options,.nm_exts,.nm_size,.nm_get,.nm_required,.nm_placeholder').hide();
			
		}else if(option == 'recaptcha'){
		
			jQuery(this).closest('.nm_form').find(".field_type option[value=recaptcha]").prop('disabled',true);
			jQuery(this_option).find('option[value=recaptcha]').prop('disabled',false);

			jQuery(this).closest('.nm_table').find('.nm_select_options,.nm_get,.nm_placeholder,.nm_required,.nm_exts,.nm_size,.nm_html').hide();
		
		}
	
	});
	
});