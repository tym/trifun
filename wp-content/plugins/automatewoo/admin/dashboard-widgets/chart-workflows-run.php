<?php
/**
 * @class AW_Dashboard_Widget_Graph_Workflows_Run
 */

class AW_Dashboard_Widget_Chart_Workflows_Run extends AW_Dashboard_Widget_Chart_Abstract {

	public $id = 'chart-workflows-run';

	function load_data() {

		$logs = AW_Admin_Controller_Dashboard::get_logs();

		foreach ( $logs as $log ) {
			$log->_local_date = get_date_from_gmt( $log->date );
		}

		return [ array_values( $this->prepare_chart_data( $logs, '_local_date', false, $this->get_interval(), 'day' ) ) ];
	}


	function output_content() {

		if ( ! $this->date_to || ! $this->date_from )
			return;

		$logs = AW_Admin_Controller_Dashboard::get_logs();
		$this->render_js();

		?>

		<div class="automatewoo-dashboard-chart">

			<div class="automatewoo-dashboard-chart__header">

				<div class="automatewoo-dashboard-chart__header-group">
					<div class="automatewoo-dashboard-chart__header-figure"><?php echo count( $logs ) ?></div>
					<div class="automatewoo-dashboard-chart__header-text">
						<span class="automatewoo-dashboard-chart__legend automatewoo-dashboard-chart__legend--blue"></span>
						<?php _e( 'workflows run', 'automatewoo' ) ?>
					</div>
				</div>

				<a href="<?php AW()->admin->page_url( 'workflows-report' ) ?>" class="automatewoo-arrow-link"></a>
			</div>

			<div class="automatewoo-dashboard-chart__tooltip"></div>

			<div id="automatewoo-dashboard-<?php echo $this->get_id() ?>" class="automatewoo-dashboard-chart__flot"></div>

		</div>

		<?php
	}

}

return new AW_Dashboard_Widget_Chart_Workflows_Run();
