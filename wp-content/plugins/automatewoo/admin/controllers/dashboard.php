<?php
/**
 * @class AW_Admin_Controller_Dashboard
 * @since 2.8
 */

class AW_Admin_Controller_Dashboard extends AW_Admin_Controller_Abstract {

	/** @var array */
	private static $widgets;

	/** @var array */
	private static $logs;

	/** @var array */
	private static $queued;

	/** @var array */
	private static $carts;

	/** @var array */
	private static $guests;

	/** @var array */
	private static $unsubscribes;

	/** @var array */
	private static $conversions;


	static function output() {

		wp_enqueue_script( 'automatewoo-dashboard' );

		self::maybe_set_date_cookie();

		$widgets = self::get_widgets();
		$date_arg = self::get_date_arg();
		$date_range = self::get_date_range();
		$date_tabs = [
			'30days' => __( '30 days', 'automatewoo' ),
			'14days' => __( '14 days', 'automatewoo' ),
			'7days' => __( '7 days', 'automatewoo' )
		];

		foreach ( $widgets as $i => $widget ) {
			$widget->set_date_range( $date_range['from'], $date_range['to'] );
			if ( ! $widget->display ) {
				unset( $widgets[$i] );
			}
		}

		AW()->admin->get_view( 'page-dashboard', [
			'widgets' => $widgets,
			'date_text' => $date_tabs[$date_arg],
			'date_current' => self::get_date_arg(),
			'date_tabs' => $date_tabs
		]);
	}


	/**
	 * @return AW_Dashboard_Widget_Abstract[]
	 */
	static function get_widgets() {

		if ( ! isset( self::$widgets ) ) {

			$path = AW()->path( '/admin/dashboard-widgets/' );

			$includes = [];

			$includes[] = $path . 'chart-workflows-run.php';
			$includes[] = $path . 'chart-conversions.php';
			$includes[] = $path . 'chart-email.php';
			$includes[] = $path . 'key-figures.php';
			$includes[] = $path . 'workflows.php';
			$includes[] = $path . 'logs.php';
			$includes[] = $path . 'queue.php';

			$includes = apply_filters( 'automatewoo/dashboard/widgets', $includes );

			include_once $path . 'abstract.php';
			include_once $path . 'chart-abstract.php';

			foreach ( $includes as $include ) {
				$class = include_once $include;
				self::$widgets[ $class->id ] = $class;
			}
		}

		return self::$widgets;
	}


	/**
	 * @return string
	 */
	static function get_date_arg() {

		$cookie_name = 'automatewoo_dashboard_date';

		if ( ! aw_request( 'date' ) && isset( $_COOKIE[ $cookie_name ] ) ) {
			return (string) aw_clean( $_COOKIE[ $cookie_name ] );
		}

		if ( aw_request( 'date' ) ) {
			$date = (string) aw_clean( aw_request( 'date' ) );
			return $date;
		}

		return '30days';
	}


	static function maybe_set_date_cookie() {
		if ( aw_request( 'date' ) ) {
			$date = (string) aw_clean( aw_request( 'date' ) );
			if ( ! headers_sent() ) wc_setcookie( 'automatewoo_dashboard_date', $date, time() + MONTH_IN_SECONDS * 2 );
		}
	}


	/**
	 * @return array
	 */
	static function get_date_range() {

		$range = self::get_date_arg();

		$from = new DateTime();
		$to = new DateTime();

		switch ( $range ) {
			case '14days':
				$from->modify( "-14 days" );
				break;
			case '7days':
				$from->modify( "-7 days" );
				break;
			case '30days':
				$from->modify( "-30 days" );
				break;
		}

		return [
			'from' => $from,
			'to' => $to
		];
	}


	static function get_logs() {
		if ( ! isset( self::$logs ) ) {

			$date = self::get_date_range();

			$query = new AW_Query_Logs();
			$query->where( 'date', $date['from'], '>' );
			$query->where( 'date', $date['to'], '<' );

			self::$logs = $query->get_results();
		}

		return self::$logs;
	}


	static function get_carts() {
		if ( ! isset( self::$carts ) ) {

			$date = self::get_date_range();

			$query = new AW_Query_Abandoned_Carts();
			$query->where( 'created', $date['from'], '>' );
			$query->where( 'created', $date['to'], '<' );

			self::$carts = $query->get_results();
		}

		return self::$carts;
	}


	static function get_guests() {
		if ( ! isset( self::$guests ) ) {

			$date = self::get_date_range();

			$query = new AW_Query_Guests();
			$query->where( 'created', $date['from'], '>' );
			$query->where( 'created', $date['to'], '<' );

			self::$guests = $query->get_results();
		}

		return self::$guests;
	}


	static function get_queued() {
		if ( ! isset( self::$queued ) ) {

			$date = self::get_date_range();

			$query = new AW_Query_Queue();
			$query->where( 'created', $date['from'], '>' );
			$query->where( 'created', $date['to'], '<' );

			self::$queued = $query->get_results();
		}

		return self::$queued;
	}


	static function get_unsubscribes() {
		if ( ! isset( self::$unsubscribes ) ) {

			$date = self::get_date_range();

			$query = new AW_Query_Unsubscribes();
			$query->where( 'date', $date['from'], '>' );
			$query->where( 'date', $date['to'], '<' );

			self::$unsubscribes = $query->get_results();
		}

		return self::$unsubscribes;
	}


	static function get_conversions() {
		if ( ! isset( self::$conversions ) ) {

			$date = self::get_date_range();

			$query = get_posts([
				'post_type' => 'shop_order',
				'post_status' => [ 'wc-processing', 'wc-completed' ],
				'posts_per_page' => -1,
				'fields' => 'ids',
				'meta_query' => [
					[
						'key' => '_aw_conversion',
						'compare' => 'EXISTS',
					]
				],
				'date_query' => [
					[
						'column' => 'post_date',
						'after' => $date['from']->format('Y-m-d H:i:s')
					],
					[
						'column' => 'post_date',
						'before' => $date['to']->format('Y-m-d H:i:s')
					]
				]
			]);

			self::$conversions = array_map( 'wc_get_order', $query );
		}

		return self::$conversions;
	}

}
