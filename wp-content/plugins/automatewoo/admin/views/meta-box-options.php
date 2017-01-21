<?php
/**
 * @var AW_Model_Workflow $workflow
 */

?>

	<table class="aw-table">
		<tbody>

			<tr class="aw-field-row">
				<td class="aw-input">

					<label><?php _e( 'Status', 'automatewoo' ) ?></label>

					<?php
					if ( $workflow ) {
						$status = $workflow->is_active() ? 'active': 'disabled';
					}
					else {
						$status = 'active';
					}

					( new AW_Field_Select( false ) )
						->set_name('workflow_status')
						->set_options([
							'active' => __('Active', 'automatewoo'),
							'disabled' => __('Disabled', 'automatewoo')
						])
						->render( $status );
					?>
				</td>
			</tr>

			<tr class="aw-field-row js-trigger-options-queueing">
				<td class="aw-input">

					<label><?php _e( 'Run Actions', 'automatewoo' ) ?> <img class="tips" data-tip="<?php _e( "Use this to delay actions being run by a set period of time after the initial trigger event.", 'automatewoo' ) ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png"></label>

					<?php
					$field = new AW_Field_Select( false );
					$field
						->set_name_base('aw_workflow_data[workflow_options]')
						->set_name('when_to_run')
						->set_options([
							'immediately' => __('Immediately', 'automatewoo'),
							'delayed' => __('After a Set Period', 'automatewoo'),
							'datetime' => __( 'At a Variable DateTime', 'automatewoo')
						])
						->set_classes('js-when-to-run-select')
						->render( $workflow ? $workflow->get_option('when_to_run') : '' );
					?>

					<div class="field-cols field-gap js-when-to-run-delayed hidden">
						<div class="col-1">
							<?php
							$field = new AW_Field_Number_Input();
							$field
								->set_name_base('aw_workflow_data[workflow_options]')
								->set_name('run_delay_value')
								->set_name('run_delay_value')
								->set_placeholder( __('Number of', 'automatewoo') )
								->set_min( '0' )
								->render( $workflow ? $workflow->get_option('run_delay_value') : '' );
							?>
						</div>

						<div class="col-2">
							<?php
							$field = new AW_Field_Select( false );
							$field->set_name_base('aw_workflow_data[workflow_options]');
							$field->set_name('run_delay_unit');
							$field->set_options(array(
								'h' => __('Hours', 'automatewoo'),
								'm' => __('Minutes', 'automatewoo'),
								'd' => __('Days', 'automatewoo'),
								'w' => __('Weeks', 'automatewoo')
							));
							$field->render( $workflow ? $workflow->get_option('run_delay_unit') : '' );
							?>
						</div>
					</div>

					<div class="js-when-to-run-datetime field-gap hidden">
						<?php
						$field = new AW_Field_Text_Area();
						$field
							->set_rows(3)
							->set_name_base('aw_workflow_data[workflow_options]')
							->set_name('queue_datetime')
							->set_classes('aw-input-monospaced')
							->add_extra_attr('spellcheck', 'false')
							->set_placeholder( __('e.g. {{ subscription.next_payent_date | modify: -1 day }}', 'automatewoo') )
							->render( $workflow ? $workflow->get_option('queue_datetime') : '' );
						?>
					</div>


				</td>
			</tr>


			<tr class="aw-field-row">
				<td class="aw-input">

					<label><?php _e( 'Enable Email Tracking', 'automatewoo' ) ?> <img class="tips" data-tip="<?php _e( "If checked clicks and opens will be tracked on any email sent to an email address that belongs to one of the site's users. Reports will be visible on the WooCommerce Reports page.", 'automatewoo' ) ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png"></label>

					<?php
					$field = new AW_Field_Checkbox();
					$field
						->set_name_base('aw_workflow_data[workflow_options]')
						->set_name('click_tracking')
						->set_classes('aw-checkbox-enable-click-tracking')
						->render( $workflow ? $workflow->get_option('click_tracking') : '' );
					?>
				</td>
			</tr>


			<tr class="aw-field-row js-require-email-tracking">
				<td class="aw-input">

					<label><?php _e( 'Enable Conversion Tracking', 'automatewoo' ) ?> <img class="tips" data-tip="<?php _e( "Check to enable conversion tracking on purchases. Reports will be visible on the WooCommerce Reports page.", 'automatewoo' ) ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png"></label>
					<?php
					$field = new AW_Field_Checkbox();
					$field
						->set_name_base('aw_workflow_data[workflow_options]')
						->set_name('conversion_tracking')
						->set_classes('aw-checkbox-enable-conversion-tracking')
						->render( $workflow ? $workflow->get_option('conversion_tracking') : '' );
					?>

				</td>
			</tr>


			<tr class="aw-field-row js-require-email-tracking">
				<td class="aw-input">

					<label><?php _e( 'Google Analytics Link Tracking', 'automatewoo' ) ?> <img class="tips" data-tip="<?php _e('This will be appended to every URL in the email content or SMS body.', 'automatewoo' ) ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png"></label>

					<?php
					$field = new AW_Field_Text_Area();
					$field
						->set_rows(3)
						->set_name_base('aw_workflow_data[workflow_options]')
						->set_name('ga_link_tracking')
						->set_classes('aw-input-monospaced')
						->add_extra_attr('spellcheck', 'false')
						->set_placeholder( 'e.g. utm_source=automatewoo&utm_medium=email&utm_campaign=example' )
						->render( $workflow ? $workflow->get_option('ga_link_tracking') : '' )
					?>
				</td>
			</tr>


			<tr class="aw-field-row">
				<td class="aw-input">

					<label><?php _e( 'Order', 'automatewoo' ) ?> <img class="tips" data-tip="<?php _e('The order that workflows get run.', 'automatewoo' ) ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png"></label>

					<?php
					global $post;

					$field = new AW_Field_Number_Input();
					$field
						->set_name('menu_order')
						->render( $post ? $post->menu_order : '' )
					?>
				</td>
			</tr>

		</tbody>
	</table>
