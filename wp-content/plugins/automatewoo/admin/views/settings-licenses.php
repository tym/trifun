<?php
/**
 * @package		AutomateWoo/Admin/Views
 */

$dev_check = AW()->licenses->is_valid_dev_domain();

?>


<p><?php printf(
		__( 'In order to use AutomateWoo you must enter a license key and activate this domain. If you do not have a license key please see <a href="%s" target="_blank">details & pricing</a>. If your license has expired you may still use it to activate this domain but you will not be able to receive updates.', 'automatewoo' ),
		AW()->website_url
	); ?></p>


<?php if ( is_wp_error( $dev_check ) ): ?>

	<?php AW()->admin->notice( 'error', $dev_check->get_error_message() )?>

<?php elseif ( $dev_check ): ?>

	<div class="automatewoo-info-box">
		<span class="dashicons dashicons-info"></span> <strong><?php _e( 'Development Install Detected', 'automatewoo' ) ?></strong> -
		<?php printf(
			__( 'Activating this domain will not count against the activation limit of your license. For more info please see <a href="%s" target="_blank">our documentation</a>.', 'automatewoo' ),
			AW()->url_license_docs
		); ?>
	</div>

<?php endif; ?>


<form method="post" id="mainform" action="" enctype="multipart/form-data">

	<input type="hidden" name="action" value="automatewoo-settings">

	<?php

	$list_table = new AW_Admin_Licenses_Table();
	$list_table->prepare_items();
	$list_table->display();
	wp_nonce_field( 'automatewoo-settings' );

	if ( AW()->licenses->has_unactivated_products() ) {
		submit_button( __( 'Activate Licenses', 'automatewoo' ), 'button-primary' );
	}

	?>

</form>





