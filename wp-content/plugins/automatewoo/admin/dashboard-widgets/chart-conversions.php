<?php
/**
 * @class AW_Dashboard_Widget_Chart_Conversions
 */

class AW_Dashboard_Widget_Chart_Conversions extends AW_Dashboard_Widget_Chart_Abstract {

	public $id = 'chart-conversions';

	public $is_currency = true;

	public $conversion_count = 0;

	public $conversion_total = 0;


	function __construct() {

		$query = ( new AW_Query_Logs() )
			->set_limit(1)
			->where( 'conversion_tracking_enabled', true );

		if ( ! $query->get_results() ) {
			$this->display = false;
		}
	}


	/**
	 * @return array
	 */
	function load_data() {

		$conversions = AW_Admin_Controller_Dashboard::get_conversions();
		$conversions_clean = [];

		foreach ( $conversions as $order ) {
			/** @var $order WC_Order */
			$conversions_clean[] = (object) [
				'date' => $order->order_date,
				'total' => $order->get_total()
			];

			$this->conversion_count++;
			$this->conversion_total += $order->get_total();
		}

		return [ array_values( $this->prepare_chart_data( $conversions_clean, 'date', 'total', $this->get_interval(), 'day' ) ) ];
	}


	function output_content() {

		if ( ! $this->date_to || ! $this->date_from )
			return;

		$this->render_js();

		?>

		<div class="automatewoo-dashboard-chart">

			<div class="automatewoo-dashboard-chart__header">

				<div class="automatewoo-dashboard-chart__header-group">
					<div class="automatewoo-dashboard-chart__header-figure"><?php echo wc_price( $this->conversion_total ) ?></div>
					<div class="automatewoo-dashboard-chart__header-text">
						<span class="automatewoo-dashboard-chart__legend automatewoo-dashboard-chart__legend--blue"></span>
						<?php _e( 'conversion revenue', 'automatewoo' ) ?>
					</div>
				</div>

				<div class="automatewoo-dashboard-chart__header-group">
					<div class="automatewoo-dashboard-chart__header-figure"><?php echo $this->conversion_count ?></div>
					<div class="automatewoo-dashboard-chart__header-text"><?php _e( 'conversions', 'automatewoo' ) ?></div>
				</div>

				<a href="<?php echo AW()->admin->page_url( 'conversions' ) ?>" class="automatewoo-arrow-link"></a>
			</div>

			<div class="automatewoo-dashboard-chart__tooltip"></div>

			<div id="automatewoo-dashboard-<?php echo $this->get_id() ?>" class="automatewoo-dashboard-chart__flot"></div>

		</div>

		<?php
	}

}

return new AW_Dashboard_Widget_Chart_Conversions();
