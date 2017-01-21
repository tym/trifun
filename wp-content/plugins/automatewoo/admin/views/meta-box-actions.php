<?php
/**
 * @var $workflow
 * @var $actions
 * @var $action_select_box_values
 */
?>

	<div class="aw-actions-container">

		<?php if ( is_array( $actions ) ): ?>
			<?php $n = 1 ?>
			<?php foreach ( $actions as $action ): ?>

				<?php

					AW()->admin->get_view( 'action', [
						'workflow' => $workflow,
						'action' => $action,
						'action_number' => $n,
						'action_select_box_values' => $action_select_box_values
					]);

				?>

				<?php $n++; ?>
			<?php endforeach; ?>
		<?php endif; ?>


		<?php

			// Render blank action template
			AW()->admin->get_view( 'action', [
				'workflow' => $workflow,
				'action' => false,
				'action_number' => false,
				'action_select_box_values' => $action_select_box_values
			]);

		?>

		<?php if ( empty( $actions) ): ?>

			<div class="js-aw-no-actions-message">
				<p class="text-center"><?php _e( 'No actions. Click the <strong>+ Add Action</strong> to create an action.', 'automatewoo' ) ?></p>
			</div>

		<?php endif; ?>

	</div>



	<div class="automatewoo-metabox-footer">
		<a href="#" class="js-aw-add-action button button-primary button-large"><?php _e( '+ Add Action', 'automatewoo' ) ?></a>
	</div>


