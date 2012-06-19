<?php get_header('buddypress'); ?>
<div id="content">
	<div class="padder">
		<?php global $view; ?>
		<h3 class="pagetitle"><?php _e( 'Polls', "bp_polls" ); ?></h3>
		<hr/>
		<?php $view->avaible_polls();?>
		
	</div><!-- .padder -->
</div><!-- #content -->

<?php get_sidebar( 'buddypress' ); ?>

<?php get_footer( 'buddypress' ); ?>
