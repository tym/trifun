<?php
/**
 * @package		AutomateWoo/Admin/Views
 *
 * @var $tool
 * @var $content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="wrap automatewoo-page automatewoo-page--tools">

	<?php AW()->admin->get_view( 'tool-header', ['tool' => $tool ] ); ?>

	<div id="poststuff">

		<form id="automatewoo_process_tool_form" method="post" action="<?php echo AW_Admin_Controller_Tools::get_route_url( 'validate', $tool ) ?>">

			<div class="automatewoo-metabox postbox">
				<div class="aw-action-fields-container">
					<table class="aw-table two-column">

						<?php foreach ( $tool->get_form_fields() as $field ): /** @var $field AW_Field */ ?>

							<tr class="aw-field-row">

								<td class="aw-label">
									<?php if ( $field->get_description() ): ?>
										<img class="tips" data-tip="<?php echo $field->get_description(); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png">
									<?php endif; ?>

									<label><?php echo $field->get_title(); ?>
										<?php if ( $field->get_required() ): ?>
											<span class="required">*</span>
										<?php endif; ?>
									</label>
								</td>

								<td class="aw-input">
									<?php

									$value = isset( $_POST['args'][ $field->get_name() ] ) ? $field->esc_value( $_POST['args'][ $field->get_name() ] ) : false;
									$field->render( $value );

									?>
								</td>
							</tr>

						<?php endforeach; ?>

					</table>
				</div>

				<div class="automatewoo-metabox-footer">
					<button type="submit" class="button button-primary button-large"><?php _e('Next', 'automatewoo') ?></button>
				</div>
			</div>

		</form>





	</div>

</div>


