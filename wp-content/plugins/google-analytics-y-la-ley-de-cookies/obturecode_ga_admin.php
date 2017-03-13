<?php
    global $wpdb;
      // Collect our widget options.
      if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
      }
          
      // If original widget options do not match control form
      // submission options, update them.      
      require dirname(__FILE__).'/obturecode_ga_admin.phtml';