<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package web2feel
 */
?>

	</div><!-- #content -->
	
	<div id="bottom" class="clearfix">
		<div class="footer-cover clearfix">
	
		<div class="footer-left">
		<?php if ( !function_exists('dynamic_sidebar')
		        || !dynamic_sidebar("Footer left") ) : ?>  
		<?php endif; ?>
		</div>

		<div class="footer-middle">
		<?php if ( !function_exists('dynamic_sidebar')
		        || !dynamic_sidebar("Footer middle") ) : ?>  
		<?php endif; ?>
		</div>
		
			<div class="footer-right">
		<?php if ( !function_exists('dynamic_sidebar')
		        || !dynamic_sidebar("Footer right") ) : ?>  
		        
		<?php endif; ?>
		
		<?php get_template_part( 'sponsors' ); ?>
		</div>		
		
		</div>
	</div>
		

	<footer id="colophon" class="site-footer container_12" role="contentinfo">
		<div class="site-info">
			<div class="fcred">
				Copyright &copy; <?php echo date('Y');?> <a href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>"><?php bloginfo('name'); ?></a> - <?php bloginfo('description'); ?>.<br />
				<?php fflink(); ?> | <a href="http://topwpthemes.com/<?php echo wp_get_theme(); ?>/" ><?php echo wp_get_theme(); ?> Theme</a> 	
			</div>		

		</div><!-- .site-info -->
	</footer><!-- #colophon .site-footer -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>