<?php
/**
 * @var $workflow
 */

?>

	<table class="aw-table">
		<tbody>

			<tr class="aw-field-row">
				<td class="aw-input">

					<label>Available Variables <img class="tips" data-tip="<?php _e( 'Click on a variable to see more info and copy it to the clipboard. Variables can be used in any action text field to add variable data. The available variables are filtered based on the selected trigger for this workflow.', 'automatewoo' ) ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png"></label>

					<div class="aw-workflow-variables-container">

						<?php foreach(AW()->variables()->get_list() as $data_type => $vars ): ?>
							<div class="aw-variables-group" data-type="<?php echo $data_type; ?>">
								<?php foreach ( $vars as $variable => $file_path ): ?>
									<div class="aw-workflow-variable-outer"><span class="aw-workflow-variable"><?php echo $data_type.'.'.$variable ?></span></div>
								<?php endforeach; ?>
							</div>
						<?php endforeach; ?>

					</div>

				</td>
			</tr>

		</tbody>
	</table>
