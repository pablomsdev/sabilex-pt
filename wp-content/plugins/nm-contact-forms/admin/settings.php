<?php 
global $nm_forms_c;
?>

<div class="wrap">


	<h2>NM contact forms - Settings</h2> 
	
	<?php if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true'){?>
	<div class="updated">
		<p>All forms has been saved.</p>
	</div>
	<?php } ?>
	
	<?php $nm_forms_c->nm_donate();?>
	
	<form method="post" id="nm_all_forms" action="options.php">
	<div class="nm_settings_container">
	

		<?php settings_fields( 'nm_forms_settings' );
		$nm_form_s = get_option( 'nm_f_s' ); 
		?>
		
		<table class="form-table">
			<tr> 	
				<th>
					ReCaptcha site key:
				</th>
				<td>
					<input type="text" class="regular-text" name="nm_f_s[recaptcha]" value="<?=$nm_form_s['recaptcha'];?>">
					<p class="description">Get your key <a href="https://www.google.com/recaptcha/" target="_blank">Google reCaptcha</a></p>
				</td>
			</tr>
			<tr> 	
				<th>
					ReCaptcha secret:
				</th>
				<td>
					<input type="text" class="regular-text" name="nm_f_s[secret]" value="<?=$nm_form_s['secret'];?>">
					<p class="description">Get your secret <a href="https://www.google.com/recaptcha/" target="_blank">Google reCaptcha</a></p>
				</td>
			</tr>
			<tr> 	
				<th>
					ReCaptcha Language:
				</th>
				<td>
					<select name="nm_f_s[recaptcha_lang]">
						<option <?php if($nm_form_s['recaptcha_lang'] == 'en'){?>selected<?php } ?> value="en">English (US)</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'en-GB'){?>selected<?php } ?> value="en-GB">English (UK)</option>			
						<option <?php if($nm_form_s['recaptcha_lang'] == 'ar'){?>selected<?php } ?> value="ar">Arabic</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'bg'){?>selected<?php } ?> value="bg">Bulgarian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'ca'){?>selected<?php } ?> value="ca">Catalan</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'zh-CN'){?>selected<?php } ?> value="zh-CN">Chinese (Simplified)</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'zh-TW'){?>selected<?php } ?> value="zh-TW">Chinese (Traditional)</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'hr'){?>selected<?php } ?> value="hr">Croatian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'cs'){?>selected<?php } ?> value="cs">Czech</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'da'){?>selected<?php } ?> value="da">Danish</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'nl'){?>selected<?php } ?> value="nl">Dutch</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'fil'){?>selected<?php } ?> value="fil">Filipino</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'fi'){?>selected<?php } ?> value="fi">Finnish</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'fr'){?>selected<?php } ?> value="fr">French</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'fr-CA'){?>selected<?php } ?> value="fr-CA">French (Canadian)</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'de'){?>selected<?php } ?> value="de">German</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'de-AT'){?>selected<?php } ?> value="de-AT">German (Austria)</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'de-CH'){?>selected<?php } ?> value="de-CH">German (Switzerland)</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'el'){?>selected<?php } ?> value="el">Greek</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'iw'){?>selected<?php } ?> value="iw">Hebrew</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'hi'){?>selected<?php } ?> value="hi">Hindi</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'hu'){?>selected<?php } ?> value="hu">Hungarain</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'id'){?>selected<?php } ?> value="id">Indonesian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'it'){?>selected<?php } ?> value="it">Italian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'ja'){?>selected<?php } ?> value="ja">Japanese</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'ko'){?>selected<?php } ?> value="ko">Korean</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'lv'){?>selected<?php } ?> value="lv">Latvian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'lt'){?>selected<?php } ?> value="lt">Lithuanian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'no'){?>selected<?php } ?> value="no">Norwegian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'fa'){?>selected<?php } ?> value="fa">Persian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'pl'){?>selected<?php } ?> value="pl">Polish</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'pt'){?>selected<?php } ?> value="pt">Portuguese</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'pt-BR'){?>selected<?php } ?> value="pt-BR">Portuguese (Brazil)</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'pt-PT'){?>selected<?php } ?> value="pt-PT">Portuguese (Portugal)</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'ro'){?>selected<?php } ?> value="ro">Romanian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'ru'){?>selected<?php } ?> value="ru">Russian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'sr'){?>selected<?php } ?> value="sr">Serbian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'sk'){?>selected<?php } ?> value="sk">Slovak</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'sl'){?>selected<?php } ?> value="sl">Slovenian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'es'){?>selected<?php } ?> value="es">Spanish</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'es-419'){?>selected<?php } ?> value="es-419">Spanish (Latin America)</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'sv'){?>selected<?php } ?> value="sv">Swedish</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'th'){?>selected<?php } ?> value="th">Thai</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'tr'){?>selected<?php } ?> value="tr">Turkish</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'uk'){?>selected<?php } ?> value="uk">Ukrainian</option>
						<option <?php if($nm_form_s['recaptcha_lang'] == 'vi'){?>selected<?php } ?> value="vi">Vietnamese</option>
					</select>
				</td>
			</tr>
			<tr> 	
				<th>
					Enable default styles:
				</th>
				<td>
					<fieldset>
					<legend class="screen-reader-text"><span>Enabled</span></legend><label for="users_can_register">
					<input name="nm_f_s[default_css]" type="checkbox" <?php if($nm_form_s['default_css'] == 'on'){?>checked<?php }?>>
					This will load plugins default CSS</label>
					</fieldset>
				</td>
			</tr>
			<tr> 	
				<th>
					Hide donation message
				</th>
				<td>
					<fieldset>
					<legend class="screen-reader-text"><span>Enabled</span></legend><label for="users_can_register">
					<input name="nm_f_s[hide_donation]" type="checkbox" <?php if(isset($nm_form_s['hide_donation'])){?>checked<?php }?>>
					Hide (Thanks if you have donated)</label>
					</fieldset>
				</td>
			</tr>
			<tr> 	
				<th>
					Default "FROM" email address
				</th>
				<td>
					<input type="text" class="regular-text" name="nm_f_s[default_sender]" value="<?=$nm_form_s['default_sender'];?>">
					<p class="description">Fallback to admin email</a></p>
				</td>
			</tr>
			<tr> 	
				<th>
					Default "FROM" title
				</th>
				<td>
					<input type="text" class="regular-text" name="nm_f_s[default_sender_title]" value="<?=$nm_form_s['default_sender_title'];?>">
					<p class="description">Fallback to site name</a></p>
				</td>
			</tr>
		</table>
	
	</div>
	
	<input type="submit" class="button-primary" value="<?php _e( 'Save Forms', 'nm_forms' ); ?>" />
	
	</form>
</div>