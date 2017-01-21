<?php
/**
 * @package     AutomateWoo/Admin/Views
 * @since       2.1.0
 *
 * @var string $plugin_name
 * @var string $plugin_slug
 */

if ( AW_Install::is_data_update_screen() ) return;

AW()->admin->notice(
	'info',

	sprintf( __('%s Database Upgrade Required', 'automatewoo' ), $plugin_name ),
	__('- Please backup your database and then run the updater. It is normal for this to take some time to complete.', 'automatewoo' ), '',
	__('Run the updater', 'automatewoo' ),

	add_query_arg([
		'page' => 'automatewoo-data-upgrade',
		'plugin_slug' => $plugin_slug
	], admin_url( 'admin.php' ) ),

	'js-automatewoo-do-database-update'
);

?>

<script type="text/javascript">
	(function($) {
		$('.js-automatewoo-do-database-update').on('click', function(){
			return confirm("<?php _e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'automatewoo' ); ?>");
		});
	})(jQuery);
</script>