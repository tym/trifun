<?php
/**
 * @package     AutomateWoo/Admin/Views
 * @since       2.1.0
 */

$plugin_slug = aw_clean( aw_request('plugin_slug') );


if ( $plugin_slug == AW()->plugin_slug ) {
	// updating the primary plugin
	$plugin_name = 'AutomateWoo';
	$version = AW()->version;
	$update_available = AW_Install::database_upgrade_available();
}
elseif ( $plugin_slug ) {
	// updating an addon
	$addon = AW()->addons()->get( $plugin_slug );

	if ( ! $addon ) {
		wp_die( __( 'Add-on could not be updated', 'automatewoo' ) );
	}

	$plugin_name = $addon->name;
	$version = $addon->version;
	$update_available = $addon->is_database_upgrade_available();
}
else {
	wp_die( 'Missing parameter.' );
}



?>

<div id="automatewoo-upgrade-wrap" class="wrap automatewoo-page automatewoo-page--data-upgrade">
	
	<h2><?php printf( __( "%s - Database Upgrade" ,'automatewoo' ), $plugin_name ); ?></h2>
	
	<?php if ( $update_available ): ?>

		<p><?php _e('Reading upgrade tasks...', 'automatewoo'); ?></p>

		<p class="show-on-ajax"><?php printf(__('Upgrading data to version %s', 'automatewoo'), $version ); ?> <i class="automatewoo-upgrade-loader"></i> </p>

		<p class="show-on-complete"><?php _e('Database Upgrade complete', 'automatewoo'); ?>.</p>

		<style type="text/css">

			/* hide show */
			.show-on-ajax,
			.show-on-complete {
				display: none;
			}

		</style>

		<script type="text/javascript">
		(function($) {

			var $wrap = $('#automatewoo-upgrade-wrap');

			var upgrader = {

				init: function(){

					// reference
					var self = this;


					// allow user to read message for 1 second
					setTimeout(function(){
						self.upgrade();
					}, 1000);


					// return
					return this;
				},

				upgrade: function(){

					// show message
					$('.show-on-ajax').show();


					$.ajax({
						method: 'POST',
						url: ajaxurl,
						data: {
							action: 'aw_database_update',
							nonce: '<?php echo wp_create_nonce('automatewoo_database_upgrade'); ?>',
							plugin_slug: '<?php echo $plugin_slug ?>'
						},
						success: function (response) {

							if ( response.success ) {

								$('.show-on-complete').show();
							}
							else {
								if ( response.data ) {
									$wrap.append('<p><strong>' + response.data + '</strong></p>');
								}
							}

							// remove spinner
							$('.automatewoo-upgrade-loader').hide();

						}
					});

				}

			}.init();

		})(jQuery);
		</script>

	<?php else: ?>

		<p><?php _e('No updates available', 'automatewoo'); ?>.</p>

	<?php endif; ?>

</div>
