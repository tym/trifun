<?php
/**
 * @class AW_Dashboard_Widget_Key_Figures
 */

class AW_Dashboard_Widget_Key_Figures extends AW_Dashboard_Widget_Abstract {

	function __construct() {
		$this->id = 'key-figures';
	}


	/**
	 * @return array
	 */
	function get_figures() {

		$figures = [];

		if ( ! $this->date_to || ! $this->date_from )
			return [];

		$queued = AW_Admin_Controller_Dashboard::get_queued();
		$carts = AW_Admin_Controller_Dashboard::get_carts();
		$unsubscribes = AW_Admin_Controller_Dashboard::get_unsubscribes();
		$guests = AW_Admin_Controller_Dashboard::get_guests();

		$figures[] = [
			'name' => __( 'workflows queued', 'automatewoo' ),
			'value' => $queued ? count( $queued ) : 0,
			'link' => AW()->admin->page_url( 'queue' )
		];

		$figures[] = [
			'name' => __( 'active carts', 'automatewoo' ),
			'value' => $carts ? count( $carts ) : 0,
			'link' => AW()->admin->page_url( 'carts' )
		];

		$figures[] = [
			'name' => __( 'guests captured', 'automatewoo' ),
			'value' => $guests ? count( $guests ) : 0,
			'link' => AW()->admin->page_url( 'guests' )
		];

		$figures[] = [
			'name' => __( 'unsubscribes', 'automatewoo' ),
			'value' => $unsubscribes ? count( $unsubscribes ) : 0,
			'link' => AW()->admin->page_url( 'unsubscribes' )
		];


		return apply_filters('automatewoo/dashboard/key_figures', $figures );
	}


	function output_content() {

		$figures = $this->get_figures();

		?>

		<div class="automatewoo-dashboard__figures">
			<?php foreach ( $figures as $figure ): ?>

				<a href="<?php echo esc_url( $figure['link'] ) ?>" class="automatewoo-dashboard__figure">
					<div class="automatewoo-dashboard__figure-value"><?php echo $figure['value'] ?></div>
					<div class="automatewoo-dashboard__figure-name"><?php echo esc_attr( $figure['name'] ) ?></div>
				</a>

			<?php endforeach; ?>
		</div>

		<?php
	}

}

return new AW_Dashboard_Widget_Key_Figures();
