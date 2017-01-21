<?php
/**
 * @var $variable string
 */

$variable_obj = AW()->variables()->get_variable_object( $variable );

?>

	<div class="automatewoo-modal__header">
		<h1><?php echo $variable ?></h1>
	</div>

	<div class="automatewoo-modal__body">
		<div class="automatewoo-modal__body-inner">

			<?php if ( $variable_obj && $variable_obj->get_description() ): ?>
				<p><?php echo $variable_obj->get_description() ?></p>
			<?php endif; ?>


			<table class="aw-table two-column bordered aw-workflow-variable-parameters-table">
				<tbody>

					<?php if ( $variable_obj && $variable_obj->has_parameters() ) foreach ( $variable_obj->get_parameters() as $parameter_name => $parameter ): ?>

						<tr class="aw-workflow-variables-parameter-row"
							 data-parameter-name="<?php echo $parameter_name ?>"
							<?php if ( isset ( $parameter['show'] ) ): ?>data-parameter-show="<?php echo $parameter['show'] ?>"<?php endif; ?>
							<?php echo ( $parameter['required'] ? 'data-is-required="true"' : '' ) ?>
							>

							<td>
								<strong><?php echo $parameter_name ?></strong>
								<?php if ( $parameter['required'] ): ?><span class="aw-required-asterisk"></span><?php endif; ?>
								<img class="tips" data-tip="<?php echo $parameter['description'] ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png">
							</td>
							<td class="aw-field-row">

								<?php if ( $parameter['type'] === 'text' ): ?>

									<input type="text" name="<?php echo $parameter_name ?>" placeholder="<?php echo $parameter['placeholder'] ?>" class="aw-workflow-variable-parameter">

								<?php elseif ( $parameter['type'] === 'select' ): ?>

									<select name="<?php echo $parameter_name ?>" class="aw-workflow-variable-parameter">
										<?php foreach ( $parameter['options'] as $value => $text ): ?>
											<option value="<?php echo $value ?>"><?php echo $text ?></option>
										<?php endforeach; ?>
									</select>

								<?php endif; ?>

							</td>
						</tr>
					<?php endforeach; ?>

					<?php if ( $variable_obj->use_fallback ): ?>
						<tr>
							<td>
								<strong>fallback</strong>
								<img class="tips" data-tip="<?php _e( 'Displayed when there is no value found.', 'automatewoo') ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png">
							</td>
							<td class="aw-field-row">
								<input type="text" name="fallback" class="aw-workflow-variable-parameter">
							</td>
						</tr>
					<?php endif; ?>
				</tbody>

			</table>


			<div class="aw-workflow-variable-clipboard-form">
				<div id="aw_workflow_variable_preview_field" class="aw-workflow-variable-preview-field" data-variable="<?php echo $variable ?>"></div>
				<button class="aw-clipboard-btn button button-primary button-large" data-clipboard-target="#aw_workflow_variable_preview_field"><?php _e( 'Copy to clipboard', 'automatewoo' ) ?></button>
			</div>

		</div>
	</div>
