<?php
/**
 * Can be loaded by ajax
 *
 * @param $fill_fields (optional)
 */


if ( ! $action || ! $action_number )
	return;


// default to false
if ( ! isset( $fill_fields ) )
	$fill_fields = false;


if ( $fill_fields ) {
	$action = $workflow->get_action( $action_number );
}

$fields = $action->get_fields();

?>

	<?php $first = true; foreach( $fields as $field ):

		/** @var $field AW_Field */

		// add action number to name base
		$field->set_name_base( $field->get_name_base() . "[$action_number]" );

		$value = $fill_fields ? $action->get_option( $field->get_name() ) : '';


		?>

		<tr class="field_type-text aw-field-row"
		    data-name="<?php echo $field->get_name(); ?>"
		    data-type="<?php echo $field->get_type(); ?>"
		    data-required="<?php echo ( $field->get_required() ? '1' : '0' ) ?> ">

			<td class="aw-label">

				<?php if ( $first ): ?>
					<?php $action->check_requirements(); ?>
				<?php endif ?>

				<?php if ( $field->get_description() ): ?>
					<img class="tips" data-tip="<?php echo $field->get_description(); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png">
				<?php endif; ?>

				<label><?php echo $field->get_title(); ?>
					<?php if ( $field->get_required() ): ?>
						<span class="required">*</span>
					<?php endif; ?>
				</label>

			</td>

			<td class="aw-input automatewoo-field-wrap">
				<?php $field->render( $value ); ?>
			</td>
		</tr>

	<?php $first = false; endforeach; ?>