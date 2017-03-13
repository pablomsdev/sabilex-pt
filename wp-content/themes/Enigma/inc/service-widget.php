<?php /**
 */
class highlight_widget extends WP_Widget {

    /** constructor */
    function highlight_widget() {
        parent::WP_Widget(false, $name = 'Highlight Widget');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
		global $wpdb;

        $title = apply_filters('widget_title', $instance['title']);
        //$link = $instance['link'];
		$highlight = $instance['highlight'];
		$icon = $instance['icon'];
		
		   ?>
            <?php echo $before_widget; ?>
              	<div class="highcon"><i class="icon-<?php echo $icon; ?>"></i>  </div>	     
            <?php if ( $title )
                echo $before_title . $title . $after_title; ?>
			<div class="clear"></div>
			<p class="high-text"> <?php echo $highlight; ?> </p>			
            <?php echo $after_widget; ?>
             
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['highlight'] = strip_tags($new_instance['highlight']);
		$instance['icon'] = strip_tags($new_instance['icon']);
		//$instance['link'] = strip_tags($new_instance['link']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {	
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Highlight', 'highlight' => 'This is a highlight point.','link'=>'', 'icon'=> 'ok-sign'));			

        $title = esc_attr($instance['title']);
		$highlight = esc_attr($instance['highlight']);
		$icon = esc_attr($instance['icon']);
		//$link = esc_attr($instance['link']);		
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
        </p>
        
<!--
         <p>
          <label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('Link:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo $instance['link']; ?>" />
        </p>
-->

        <p>
		<label for="<?php echo $this->get_field_id('icon'); ?>"><?php _e('Select an icon name'); ?></label>
		<select name="<?php echo $this->get_field_name('icon'); ?>" id="<?php echo $this->get_field_id('icon'); ?>" class="widefat">
			<?php
			$options = array('adjust', 'asterisk', 'bar-chart','beaker','bell','bolt','bookmark-empty','briefcase','bullhorn','caret-down','caret-left','caret-right','caret-up','certificate','check-empty','circle-arrow-down','circle-arrow-left','circle-arrow-right','circle-arrow-up','cloud','columns','comment-alt','comments-alt','copy','credit-card','cut','dashboard','envelope-alt','eye-open','facebook','fiter','fullscreen','github','globe','google-plus-sign','group','hand-down','hand-left','had-right','hand-up','hdd','legal','link','linkedin','list-ol','list-ul','magic','money','paper-clip','paste','phone-sign','phone','pinterest-sign','pinterest','reorder','rss','save','sign-blank','sitemap','sort-down','sort-up','sort','strikethough','table','tasks','truck','twitter','umbrella','underline','undo','unlock','user-md','wrench','music','search','envelope','heart', 'star','user','film','ok','remove','zoom-in','zoom-out','off','signal','trash', 'home','file','time', 'download','inbox', 'repeat','refresh','flag','headphones','qrcode','tag','tags','book','bookmark','print','camera','list','facetime-video','picture','pencil','map-marker', 'tint','edit', 'share','check','move','play','plus-sign', 'minus-sign','ok-sign','question-sign','info-sign', 'screenshot','remove-circle','ok-circle','ban-circle','plus','minus','asterisk','exclamation-sign','gift','leaf','fire','warning-sign','plane','calendar','random','comment','magnet','shopping-cart','folder-open','folder-close','bar-chart','cogs','external-link','pushpin','key','thumbs-up','comments','trophy','upload-alt','upload','fire'   );
			foreach ($options as $option) {
				echo '<option value="' . $option . '" id="' . $option . '"', $icon == $option ? ' selected="selected"' : '', '>', $option, '</option>';
			}
			?>
		</select>
		</p>

	<p>
			<label for="<?php echo $this->get_field_id('highlight'); ?>"><?php _e('Highlight text','reliance'); ?></label><br />
			<textarea class="widefat" id="<?php echo $this->get_field_id('highlight'); ?>" name="<?php echo $this->get_field_name('highlight'); ?>" type="text" rows="16" cols="20" value="<?php echo $highlight; ?>"><?php echo $highlight; ?></textarea>
		</p>
		
        <?php
    }

} // class utopian_recent_posts
add_action('widgets_init', create_function('', 'return register_widget("highlight_widget");'));
