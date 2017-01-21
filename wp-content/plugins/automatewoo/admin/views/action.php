<?php
/**
 * Renders an action and the template for new actions via ajax
 *
 * @var $action_number
 * @var $action
 * @var $action_select_box_values
 * @var $workflow AW_Model_Workflow
 */

if ( $workflow )
	$editing = isset( $_COOKIE['aw_editing_action_' . $workflow->id . '_' . $action_number ] );
else
	$editing = false;


?>
	<div class="<?php echo ( $action ? 'aw-action' : 'aw-action-template' ) ?> <?php echo ( $editing ? 'js-open' : '' ) ?>"
	     data-action-number="<?php echo $action ? $action_number : '' ?>">

		<div class="aw-action-header">
			<div class="row-options">
				<a class="js-preview-action <?php echo ( $action && $action->can_be_previewed ? '' : 'hidden' ); ?>" href="#"><?php echo __( 'Preview', 'automatewoo' ) ?></a>
				<a class="js-edit-action" href="#"><?php echo __( 'Edit', 'automatewoo' ) ?></a>
				<a class="js-delete-action" href="#"><?php echo __( 'Delete', 'automatewoo' ) ?></a>
			</div>

			<h4 class="action-title"><?php echo ( $action ? $action->get_title( true ) : __( 'New Action', 'automatewoo' ) ); ?></h4>
		</div>

		<div class="aw-action-fields-container">
			<table class="aw-table two-column">
				<tbody>


				<tr class="aw-field-row" data-name="action_name" data-type="select" data-required="1">
					<td class="aw-label">
						<label><?php echo __( 'Action', 'automatewoo' ) ?><span class="required">*</span></label>

						<img class="<?php echo $action ? '' : '_' //hack to get tips working ?>tips" data-tip="<?php echo __( "NOTE: If you see some actions are disabled that is because your available actions will depend the selected trigger.", 'automatewoo' ) ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png">

					</td>
					<td class="aw-input">

						<?php

						$action_field = new AW_Field_Select();

						if ( $action ) {
							$action_field->set_name_base( "aw_workflow_data[actions][{$action_number}]" );
							$action_field->set_name('action_name');
						}
						else {
							$action_field->set_name('');
						}

						$action_field->set_options( $action_select_box_values );
						$action_field->set_classes('js-action-select');
						$action_field->render( $action ? $action->get_name() : false );

						?>

						<?php if ( $action && $action->get_description() ): ?>
							<div class="js-action-description"><?php echo $action->get_description_html() ?></div>
						<?php else: ?>
							<div class="js-action-description"></div>
						<?php endif; ?>

					</td>

				</tr>

				<?php
					if ( $action )
						AW()->admin->get_view('action-fields', array(
							'action' => $action,
							'action_number' => $action_number,
							'workflow' => $workflow,
							'fill_fields' => true
						));
				?>

				</tbody>

			</table>
		</div>

	</div>