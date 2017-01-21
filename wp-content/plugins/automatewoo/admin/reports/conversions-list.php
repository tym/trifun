<?php
/**
 * @class		AW_Report_Conversions_List
 * @package		AutomateWoo/Admin/Reports
 */

class AW_Report_Conversions_List extends AW_Report_List_Table
{
	public $_column_headers;

	public $max_items;


	/**
	 * __construct function.
	 */
	function __construct()
	{
		parent::__construct( array(
			'singular'  => __( 'Conversion', 'automatewoo' ),
			'plural'    => __( 'Conversions', 'automatewoo' ),
			'ajax'      => false
		) );
	}


	/**
	 * No items found text
	 */
	function no_items()
	{
		_e( 'No conversions found.', 'automatewoo' );
	}


	/**
	 * Retrieve the bulk actions
	 */
	function get_bulk_actions()
	{
		$actions = [
			'bulk_unmark_conversion' => __( 'Unmark As Conversion', 'automatewoo' ),
		];

		return $actions;
	}


	/**
	 * @param WC_Order $order
	 * @return string
	 */
	function column_cb( $order )
	{
		return '<input type="checkbox" name="order_ids[]" value="' . absint( $order->id ) . '" />';
	}


	/**
	 * @param $order
	 */
	function column_interacted( $order )
	{
		$log_id = get_post_meta( $order->id, '_aw_conversion_log', true );

		if ( $log = AW()->get_log( $log_id ) )
		{
			$tracking = $log->get_meta('tracking_data');

			if ( is_array( $tracking ) )
			{
				return $this->format_date( $tracking[0]['date'] );
			}
		}

		$this->format_blank();
	}


	/**
	 * @param $order WC_Order
	 */
	function column_workflow( $order )
	{
		if ( $workflow_id = get_post_meta( $order->id, '_aw_conversion', true ) )
		{
			if ( $workflow = AW()->get_workflow( $workflow_id ) )
			{
				return $this->format_workflow_title( $workflow );
			}
		}

		$this->format_blank();
	}


	/**
	 * @param WC_Order $order
	 * @param mixed $column_name
	 */
	function column_default( $order, $column_name )
	{
		switch( $column_name )
		{
			case 'order':
				echo '<a href="' . get_edit_post_link( $order->id ) . '"><strong>#' . $order->get_order_number() . '</strong></a>';

				break;

			case 'customer':

				if ( $user = $order->get_user() )
					echo '<a href="' . get_edit_user_link( $user->ID ) . '">' . $user->first_name . ' ' . $user->last_name . '</a>';
				else
					echo $order->billing_first_name . ' ' . $order->billing_last_name;

				break;


			case 'order_placed':

				$this->format_date( $order->order_date, false );

				break;


			case 'log':

				if ( $log_id = get_post_meta( $order->id, '_aw_conversion_log', true ) )
				{
					$url = add_query_arg(array(
						'action' => 'aw_modal_log_info',
						'log_id' => $log_id
					), admin_url('admin-ajax.php') );

					echo '<a class="js-open-automatewoo-modal" data-modal-type="ajax" href="' . $url . '">#'.$log_id.'</a>';
				}
				else
				{
					$this->format_blank();
				}

				break;

			case 'total':
				echo wc_price( $order->get_total() );
				break;

		}
	}

	/**
	 * get_columns function.
	 */
	function get_columns()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'order'  => __( 'Order', 'automatewoo' ),
			'customer'  => __( 'Customer', 'automatewoo' ),
			'workflow' => __( 'Workflow', 'automatewoo' ),
			'log' => __( 'Log', 'automatewoo' ),
			'interacted' => __( 'First Interacted', 'automatewoo' ),
			'order_placed' => __( 'Order Placed', 'automatewoo' ),
			'total' => __( 'Order Total', 'automatewoo' ),
		);

		return $columns;
	}

	/**
	 * prepare_items function.
	 */
	function prepare_items()
	{
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$current_page = absint( $this->get_pagenum() );
		$per_page = apply_filters( 'automatewoo_report_items_per_page', 20 );

		$this->get_items( $current_page, $per_page );

		/**
		 * Pagination
		 */
		$this->set_pagination_args( array(
			'total_items' => $this->max_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		) );
	}



	/**
	 * Get Products matching stock criteria
	 */
	function get_items( $current_page, $per_page )
	{
		$query = new WP_Query(array(
			'post_type' => 'shop_order',
			'post_status' => array( 'wc-processing', 'wc-completed' ),
			'posts_per_page' => $per_page,
			'offset' => ( $current_page - 1 ) * $per_page,
			'meta_query' => array(
				array(
					'key' => '_aw_conversion',
					'compare' => 'EXISTS',
				)
			)
		));

		foreach ( $query->posts as $order )
		{
			$this->items[] = wc_get_order( $order );
		}

		$this->max_items = $query->found_posts;
	}

}
