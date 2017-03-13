<?php

add_action('admin_menu', 'hrf_create_menu');

function hrf_create_menu() {

   add_menu_page('HTML5 Responsive FAQ', 'Responsive FAQ', 'administrator', 'hrf-faq-options', 'hrf_faq_options');
   add_action( 'admin_init', 'hrf_faq_register_mysettings' );
}


function hrf_faq_register_mysettings() {

   register_setting( 'hrf-settings-group', 'hrf_main_title_size' );
   register_setting( 'hrf-settings-group', 'hrf_question_text_color' );
   register_setting( 'hrf-settings-group', 'hrf_question_bgcolor' );
   register_setting( 'hrf-settings-group', 'hrf_question_text_size' );
   register_setting( 'hrf-settings-group', 'hrf_question_headingtype' );
   register_setting( 'hrf-settings-group', 'hrf_answer_text_color' );
   register_setting( 'hrf-settings-group', 'hrf_answer_bgcolor' );
   register_setting( 'hrf-settings-group', 'hrf_answer_text_size' );
   register_setting( 'hrf-settings-group', 'hrf_bullets_style' );
   register_setting( 'hrf-settings-group', 'hrf_bullets_bgcolor' );
   register_setting( 'hrf-settings-group', 'hrf_faqs_bottom_gap' );

}

function hrf_faq_options() {
?>
<div class="wrap">
<h2>HTML5 Responsive FAQ Settings</h2>

<?php
   
if( isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true'):
   echo '<div id="setting-error-settings_updated" class="updated settings-error"> 
            <p><strong>Settings saved.</strong></p>
         </div>';
endif;
?>

<form method="post" action="options.php">
    <?php settings_fields( 'hrf-settings-group' ); ?>
    <?php do_settings_sections( 'hrf-settings-group' ); ?>
    <table class="form-table">
    <tr valign="top">
         <th scope="row">Category/Main title font size</th>
         <td><input type="text" style="width:50%" name="hrf_main_title_size" value="<?php echo get_option('hrf_main_title_size', '18px'); ?>" placeholder="18px" /></td>
      </tr>
      <tr valign="top">
         <th scope="row">Text color of question</th>
         <td><input type="text" style="width:50%" name="hrf_question_text_color" value="<?php echo get_option('hrf_question_text_color', '#444444'); ?>" placeholder="#444444" data-default-color="#444444"  class="hrf-color-picker"/></td>
      </tr>

      <tr valign="top">
         <th scope="row">Background color of question</th>
         <td><input type="text" style="width:50%" name="hrf_question_bgcolor" value="<?php echo get_option('hrf_question_bgcolor', '#ffffff'); ?>" placeholder="#ffffff" data-default-color="#ffffff"  class="hrf-color-picker" /></td>
      </tr>

      <tr valign="top">
         <th scope="row">Text size of question (in pixels)</th>
         <td><input type="text" style="width:50%" name="hrf_question_text_size" value="<?php echo get_option('hrf_question_text_size', '18px'); ?>" placeholder="18px" /></td>
      </tr>
      
      <tr valign="top">
        <th scope="row">Select heading type for question</th>
        <td><select name="hrf_question_headingtype" />
            <option value="h2" <?php if( get_option('hrf_question_headingtype', 'h3') == "h2" ): echo 'selected'; endif;?> >H2</option>
            <option value="h3" <?php if( get_option('hrf_question_headingtype', 'h3') == "h3" ): echo 'selected'; endif;?> >H3</option>
            <option value="h4" <?php if( get_option('hrf_question_headingtype', 'h3') == "h4" ): echo 'selected'; endif;?> >H4</option>
            <option value="h5" <?php if( get_option('hrf_question_headingtype', 'h3') == "h5" ): echo 'selected'; endif;?> >H5</option>
            <option value="h6" <?php if( get_option('hrf_question_headingtype', 'h3') == "h6" ): echo 'selected'; endif;?> >H6</option>
         </select></td>
      </tr>
      
      <tr valign="top">
         <th scope="row">Text color of answer</th>
         <td><input type="text" style="width:50%" name="hrf_answer_text_color" value="<?php echo get_option('hrf_answer_text_color', '#444444'); ?>" placeholder="#444444" data-default-color="#444444"  class="hrf-color-picker"/></td>
      </tr>

      <tr valign="top">
         <th scope="row">Background color of answer</th>
         <td><input type="text" style="width:50%" name="hrf_answer_bgcolor" value="<?php echo get_option('hrf_answer_bgcolor', 'ffffff'); ?>" placeholder="#ffffff" data-default-color="#ffffff"  class="hrf-color-picker" /></td>
      </tr>

      <tr valign="top">
         <th scope="row">Text size of answer (in pixels)</th>
         <td><input type="text" style="width:50%" name="hrf_answer_text_size" value="<?php echo get_option('hrf_answer_text_size', '14px'); ?>" placeholder="14px" /></td>
      </tr>
      
      <tr valign="top">
        <th scope="row">Select style of bullet</th>
        <td><select name="hrf_bullets_style" />
            <option value="light" <?php if( get_option('hrf_bullets_style', 'light') == "light" ): echo 'selected'; endif;?> >Light</option>
            <option value="dark" <?php if( get_option('hrf_bullets_style', 'light') == "dark" ): echo 'selected'; endif;?> >Dark</option>
         </select></td>
      </tr>
      
      <tr valign="top">
         <th scope="row">Background color of bullet</th>
         <td><input type="text" style="width:50%" name="hrf_bullets_bgcolor" value="<?php echo get_option('hrf_bullets_bgcolor', '#444444'); ?>" data-default-color="#444444"  class="hrf-color-picker" placeholder="#444444" class="" /></td>
      </tr>
      
      <tr valign="top">
         <th scope="row">Gap between 2 FAQs (in pixels)</th>
         <td><input type="text" style="width:50%" name="hrf_faqs_bottom_gap" value="<?php echo get_option('hrf_faqs_bottom_gap', '0px'); ?>" placeholder="0px" /></td>
      </tr>
      
    </table>
    
    <?php submit_button(); ?>

</form>
<h3>How to add FAQs on Page</h3>
<ol>
  <li>Add this [hrf_faqs] shortcode to the page/post where you want to show all FAQs list.</li>
  <li>      Add this [hrf_faqs category='uncategorized'] shortcode to the page/post where you want to show specific category FAQs.</li>
  <li>      Add this [hrf_faqs category='uncategorized,wordpress,plugins'] shortcode to the page/post where you want to show multiple category FAQs.</li>
  <li>      [hrf_faqs category='uncategorized,wordpress,plugins'  title='My Awesome Title'] shortcode to the page/post where you want to show category FAQs with title/heading.</li>
</ol>
<br/>
<br/>
<a href="http://www.indatos.com/product-feedback/?ref=faq-feedback"><img src="<?php echo plugins_url( '../images/feedback.jpg', __FILE__ );?>" alt="feedback" style="max-width:100%; width:400px; margin:20px 0"></a>
</div>
<?php
}