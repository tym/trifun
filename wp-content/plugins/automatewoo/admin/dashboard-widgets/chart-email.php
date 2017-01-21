<?php
/**
 * @class AW_Dashboard_Widget_Chart_Email
 */

class AW_Dashboard_Widget_Chart_Email extends AW_Dashboard_Widget_Chart_Abstract {

	public $id = 'chart-email';

	public $email_count = 0;

	public $open_count = 0;

	public $click_count = 0;


	/**
	 * @return array
	 */
	function load_data() {

		$logs = AW_Admin_Controller_Dashboard::get_logs();
		$emails = [];
		$opens = [];
		$clicks = [];
		$series = [];

		foreach ( $logs as $log ) {

			/** @var $log AW_Model_Log */
			if ( ! $log->tracking_enabled ) {
				continue;
			}

			$this->email_count++;

			$emails[] = (object) [
				'date' => $log->date,
			];

			if ( $log->has_open_recorded() ) {
				$this->open_count++;
				$opens[] = (object) [
					'date' => $log->get_date_opened()
				];
			}

			if ( $log->has_click_recorded() ) {
				$this->click_count++;
				$clicks[] = (object) [
					'date' => $log->get_date_clicked()
				];
			}
		}


		$series['emails'] = array_values( $this->prepare_chart_data( $emails, 'date', '', $this->get_interval(), 'day' ) );
		$series['opens'] = array_values( $this->prepare_chart_data( $opens, 'date', '', $this->get_interval(), 'day' ) );
		$series['clicks'] = array_values( $this->prepare_chart_data( $clicks, 'date', '', $this->get_interval(), 'day' ) );

		return $series;
	}


	function output_content() {

		if ( ! $this->date_to || ! $this->date_from )
			return;

		$this->render_js();

		?>

		<div class="automatewoo-dashboard-chart">

			<div class="automatewoo-dashboard-chart__header">

				<div class="automatewoo-dashboard-chart__header-group">
					<div class="automatewoo-dashboard-chart__header-figure"><?php echo $this->email_count ?></div>
					<div class="automatewoo-dashboard-chart__header-text">
						<span class="automatewoo-dashboard-chart__legend automatewoo-dashboard-chart__legend--blue"></span>
						<?php _e( 'emails sent', 'automatewoo' ) ?>
					</div>
				</div>

				<div class="automatewoo-dashboard-chart__header-group">
					<div class="automatewoo-dashboard-chart__header-figure"><?php echo $this->open_count ?></div>
					<div class="automatewoo-dashboard-chart__header-text">
						<span class="automatewoo-dashboard-chart__legend automatewoo-dashboard-chart__legend--purple"></span>
						<?php _e( 'opens', 'automatewoo' ) ?>
					</div>
				</div>

				<div class="automatewoo-dashboard-chart__header-group">
					<div class="automatewoo-dashboard-chart__header-figure"><?php echo $this->click_count ?></div>
					<div class="automatewoo-dashboard-chart__header-text">
						<span class="automatewoo-dashboard-chart__legend automatewoo-dashboard-chart__legend--green"></span>
						<?php _e( 'clicks', 'automatewoo' ) ?>
					</div>
				</div>

				<a href="<?php echo AW()->admin->page_url( 'email-tracking' ) ?>" class="automatewoo-arrow-link"></a>

			</div>

			<div class="automatewoo-dashboard-chart__tooltip"></div>

			<div id="automatewoo-dashboard-<?php echo $this->get_id() ?>" class="automatewoo-dashboard-chart__flot"></div>

		</div>

		<?php
	}

}

return new AW_Dashboard_Widget_Chart_Email();
