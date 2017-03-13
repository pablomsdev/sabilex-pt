<?php
/**
 * @package web2feel
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>
	<header class="entry-header">
		<?php if ( 'post' == get_post_type() ) : ?>
		<div class="entry-meta">
			<?php the_time('F j, Y'); ?>
		</div><!-- .entry-meta -->
		<?php endif; ?>
		<h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>


	</header><!-- .entry-header -->
	

	<div class="entry-summary">
	
	<?php
		$thumb = get_post_thumbnail_id();
		$img_url = wp_get_attachment_url( $thumb,'full' ); //get full URL to image (use "large" or "medium" if the images too big)
		$image = aq_resize( $img_url, 800, 400, true ); //resize & crop the image
	?>
	
	<?php if($image) : ?>
		<a href="<?php the_permalink(); ?>"><img class="postim"  src="<?php echo $image ?>"/></a>
	<?php endif; ?>
	
		<?php the_excerpt(); ?>
	</div><!-- .entry-summary -->
	
	<footer class="entry-meta">

		<?php edit_post_link( __( 'Edit', 'web2feel' ), '<span class="edit-link">', '</span>' ); ?>
		
		<a href="<?php the_permalink(); ?>" class="readmore"> Read More </a>
	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
