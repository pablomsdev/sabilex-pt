<div class="slider-section">

	<div id="slider" class="container_12 cbp-contentslider">
     	
	    <ul class="cf">
		<?php
		global $post;
		$count = of_get_option('w2f_slide_number');
		$slidecat =of_get_option('w2f_slide_categories');
		$args = array( 'numberposts' => $count, 'cat' => $slidecat );
		$myposts = get_posts( $args );
		foreach( $myposts as $post ) :	setup_postdata($post); ?>
			<li id="slide-<?php the_ID(); ?>">
			<?php
					$thumb = get_post_thumbnail_id();
					$img_url = wp_get_attachment_url( $thumb,'full' ); //get full URL to image (use "large" or "medium" if the images too big)
					$image = aq_resize( $img_url, 940, 400, true ); //resize & crop the image
				?>
				
				<?php if($image) : ?>
				<a href="<?php the_permalink(); ?>"> <img  src="<?php echo $image ?>"/></a>
				<?php endif; ?>

			</li>
		<?php endforeach; ?>
		</ul>       	
	     <div class="clear"></div>    	
	    <nav class="slidenav">
	    
		<?php
		global $post;
		$count = of_get_option('w2f_slide_number');
		$slidecat =of_get_option('w2f_slide_categories');
		$args = array( 'numberposts' => $count, 'cat' => $slidecat );
		$myposts = get_posts( $args );
		foreach( $myposts as $post ) :	setup_postdata($post); ?>
			<a href="#slide-<?php the_ID(); ?>">
			<span>
			<?php
					$thumb = get_post_thumbnail_id();
					$img_url = wp_get_attachment_url( $thumb,'full' ); //get full URL to image (use "large" or "medium" if the images too big)
					$image = aq_resize( $img_url, 50, 50, true ); //resize & crop the image
				?>
				
				<?php if($image) : ?>
				 <img class="slthumb" src="<?php echo $image ?>"/>
				<?php endif; ?>

			<?php the_title(); ?>
			</span>
			</a>
		<?php endforeach; ?>

	    </nav>     	

</div>	

</div>


<div class="cta-section container_12 cf">

		<div class="grid_12">
		
			<p>  <?php echo of_get_option('w2f_intro_text' ); ?>	</p>
		</div>

</div>