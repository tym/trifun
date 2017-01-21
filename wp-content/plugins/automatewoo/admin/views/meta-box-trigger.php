<?php
/**
 * @var $workflow AW_Model_Workflow
 * @var $selected_trigger
 */


// Group triggers
$grouped_triggers = [];

foreach ( AW()->registered_triggers as $trigger ) {
	$grouped_triggers[$trigger->group][] = $trigger;
}


$trigger = false;

if ( $workflow ) {
	$trigger = $workflow->get_trigger();
}


?>

	<table class="aw-table two-column">
		<tbody>

			<tr class="aw-field-row" data-name="trigger_name" data-type="select" data-required="1">
				<td class="aw-label">

					<label><?php _e( 'Trigger Type', 'automatewoo' ) ?><span class="required">*</span></label>

					<img class="tips" data-tip="<?php _e( 'Select a trigger type to view the options fields.', 'automatewoo' ) ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">

				</td>
				<td class="aw-input">

					<select name="aw_workflow_data[trigger_name]" class="aw-field js-trigger-select">
						<option value=""> - Select - </option>
						<?php foreach ($grouped_triggers as $trigger_group => $triggers ): ?>
							<optgroup label="<?php echo $trigger_group; ?>">
								<?php foreach ($triggers as $_trigger ): ?>
									<option value="<?php echo $_trigger->name; ?>" <?php echo ( $selected_trigger && $selected_trigger->name == $_trigger->name ? 'selected="selected"' : '' ); ?>><?php echo $_trigger->title; ?></option>
								<?php endforeach; ?>
							</optgroup>
						<?php endforeach; ?>
					</select>

					<?php if ( $trigger && $trigger->get_description() ): ?>
						<div class="js-trigger-description"><?php echo $trigger->get_description_html() ?></div>
					<?php else: ?>
						<div class="js-trigger-description"></div>
					<?php endif; ?>

				</td>
			</tr>

		<?php

		if ( $workflow ) {
			AW()->admin->get_view( 'trigger-fields', [
				'trigger' => $trigger,
				'workflow' => $workflow,
				'fill_fields' => true
			]);
		}

		?>

		</tbody>
	</table>
