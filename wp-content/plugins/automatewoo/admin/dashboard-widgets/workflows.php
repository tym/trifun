<?php
/**
 * @class AW_Dashboard_Widget_Workflows
 */

class AW_Dashboard_Widget_Workflows extends AW_Dashboard_Widget_Abstract {

	function __construct() {
		$this->id = 'workflows';
	}


	/**
	 * @return array
	 */
	function get_featured() {

		$featured = [];

		if ( ! $this->date_to || ! $this->date_from )
			return [];

		$logs = AW_Admin_Controller_Dashboard::get_logs();
		$unsubscribes = AW_Admin_Controller_Dashboard::get_unsubscribes();
		$conversions = AW_Admin_Controller_Dashboard::get_conversions();


		$counts = [];

		foreach ( $logs as $log ) {
			/** @var $log AW_Model_Log */
			$counts[] = $log->workflow_id;
		}

		$counts = array_count_values( $counts );
		arsort( $counts, SORT_NUMERIC );
		$workflow = AW()->get_workflow( key( $counts ) );

		if ( $workflow ) {
			$featured[] = [
				'workflow' => $workflow,
				'description' => __( 'most run workflow', 'automatewoo' ),
			];
		}


		if ( $conversions ) {

			$totals = [];

			foreach ( $conversions as $order ) {
				/** @var $order WC_Order */
				$workflow_id = absint( get_post_meta( $order->id, '_aw_conversion', true ) );

				if ( isset( $totals[ $workflow_id ] ) ) {
					$totals[ $workflow_id ] += $order->get_total();
				}
				else {
					$totals[ $workflow_id ] = $order->get_total();
				}
			}

			arsort( $totals, SORT_NUMERIC );
			$workflow = AW()->get_workflow( key( $totals ) );

			if ( $workflow ) {
				$featured[] = [
					'workflow' => $workflow,
					'description' => __( 'highest converting workflow', 'automatewoo' ),
				];
			}
		}



		if ( $unsubscribes ) {

			$counts = [];

			foreach ( $unsubscribes as $unsubscribe ) {
				/** @var $unsubscribe AW_Model_Unsubscribe */
				$counts[] = $unsubscribe->workflow_id;
			}

			$counts = array_count_values( $counts );
			arsort( $counts, SORT_NUMERIC );
			$workflow = AW()->get_workflow( key( $counts ) );

			if ( $workflow ) {
				$featured[] = [
					'workflow' => $workflow,
					'description' => __( 'most unsubscribed workflow', 'automatewoo' ),
				];
			}
		}

		return $featured;
	}


	function output_content() {

		$features = $this->get_featured();

		?>

		<div class="automatewoo-dashboard__workflows">
			<?php foreach ( $features as $feature ): ?>

				<a class="automatewoo-dashboard__workflow" href="<?php echo esc_url( get_edit_post_link( $feature['workflow']->id ) ) ?>">

					<div class="automatewoo-dashboard__workflow-title"><?php echo esc_attr( $feature['workflow']->title ) ?></div>
					<div class="automatewoo-dashboard__workflow-description"><?php echo esc_attr( $feature['description'] ) ?></div>

				</a>

			<?php endforeach; ?>
		</div>

		<?php
	}

}

return new AW_Dashboard_Widget_Workflows();
