<?php
/**
 * @package		AutomateWoo/Admin/Views
 *
 * @var $tool
 * @var $args array
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="wrap automatewoo-page automatewoo-page--tools">

	<?php AW()->admin->get_view('tool-header', [ 'tool' => $tool ] ); ?>

	<div id="poststuff">

		<form id="automatewoo_process_tool_form" method="post" action="<?php echo AW_Admin_Controller_Tools::get_route_url( 'confirm', $tool ) ?>">

			<?php wp_nonce_field( $tool->id ) ?>

			<?php foreach ( $args as $key => $value ): ?>
				<input type="hidden" name="args[<?php echo $key ?>]" value="<?php echo $value ?>">
			<?php endforeach ?>

			<div class="automatewoo-metabox postbox">
				<div class="automatewoo-metabox-pad">
					<p><?php $tool->display_confirmation_screen( $args ) ?></p>
				</div>

				<div class="automatewoo-metabox-footer">
					<button type="submit" class="button button-primary button-large"><?php _e( 'Confirm', 'automatewoo' ) ?></button>
				</div>
			</div>

		</form>

	</div>

</div>


