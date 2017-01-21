<?php
/**
 * Can be loaded by ajax
 *
 * @var $workflow AW_Model_Workflow
 * @var $trigger AW_Trigger
 * @var $fill_fields (optional)
 */


// default to false
if ( ! isset( $fill_fields ) )
	$fill_fields = false;

if ( ! $trigger )
	return;


// if we're populating field values, get the trigger object from the workflow
// Otherwise just use the unattached trigger object

if ( $fill_fields ) {
	$trigger = $workflow->get_trigger();
}

$fields = $trigger->get_fields();


?>

	<?php foreach( $fields as $field ):

		if ( $fill_fields ) {
			$value = $workflow->get_trigger_option( $field->get_name() );
		}
		else {
			$value = null;
		}

		?>

		<tr class="aw-field-row field_type-text aw-trigger-option"
		    data-name="name"
		    data-type="<?php echo $field->get_type(); ?>"
		    data-required="<?php echo ( $field->get_required() ? '1' : '0' ) ?> ">

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
				<?php $field->render( $value ); ?>
			</td>
		</tr>

	<?php endforeach; ?>
