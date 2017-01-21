<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * AW_Report_Unsubscribes
 */
class AW_Report_Unsubscribes extends AW_Report_List_Table {

	public $_column_headers;
	public $max_items;


	function __construct() {
		parent::__construct([
			'singular'  => __( 'Unsubscribe', 'automatewoo' ),
			'plural'    => __( 'Unsubscribes', 'automatewoo' ),
			'ajax'      => false
		]);
	}


	function no_items() {
		_e( 'No unsubscribes found.', 'automatewoo' );
	}


	function filters() {
		$user_string = '';
		$user_id = '';
		$workflow_id = '';
		$workflow_name = '';

		if ( ! empty( $_GET['_workflow'] ) ) {
			$workflow_id = absint( $_GET['_workflow'] );
			$workflow_name = get_the_title( $workflow_id );
		}

		if ( ! empty( $_GET['_customer_user'] ) ) {
			$user_id     = absint( $_GET['_customer_user'] );
			$user        = get_user_by( 'id', $user_id );
			$user_string = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email );
		}

		?>

		<input type="hidden" class="wc-product-search" style="width:203px;" name="_workflow"
				 data-placeholder="<?php _e( 'Search for a workflow&hellip;', 'automatewoo' ); ?>"
				 data-selected="<?php echo htmlspecialchars( $workflow_name ); ?>" value="<?php echo $workflow_id; ?>"
				 data-action="aw_json_search_workflows" data-allow_clear="true">

		<input type="hidden" class="wc-customer-search" name="_customer_user"
				 data-placeholder="<?php esc_attr_e( 'Search for a customer&hellip;', 'automatewoo' ); ?>"
				 data-selected="<?php echo htmlspecialchars( $user_string ); ?>" value="<?php echo $user_id; ?>"
				 data-allow_clear="true">
		<?php
	}


	/**
	 * @param $unsubscribe AW_Model_Unsubscribe
	 * @return string
	 */
	function column_cb( $unsubscribe ) {
		return '<input type="checkbox" name="unsubscribe_ids[]" value="' . absint( $unsubscribe->id ) . '" />';
	}


	/**
	 * @param $unsubscribe AW_Model_Unsubscribe
	 * @param mixed $column_name
	 */
	function column_default( $unsubscribe, $column_name ) {

		switch( $column_name ) {

			case 'workflow':
				$this->format_workflow_title( AW()->get_workflow( $unsubscribe->get_workflow_id() ) );
				break;

			case 'email':

				$user = false;
				$email = false;

				if ( $user_id = $unsubscribe->get_user_id() ) {
					$user = get_user_by( 'id', $user_id );
				}
				else {
					$email = $unsubscribe->get_email();
				}

				if ( $user ) {
					$this->format_user( $user );
				}
				else {
					$this->format_guest( $email );
				}

				break;

			case 'time':
				$this->format_date( $unsubscribe->date );
				break;
		}
	}


	function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'workflow'  => __( 'Workflow', 'automatewoo' ),
			'email' => __( 'Email', 'automatewoo' ),
			'time' => __( 'Date', 'automatewoo' ),
		];

		return $columns;
	}


	/**
	 * Retrieve the bulk actions
	 */
	function get_bulk_actions() {
		$actions = [
			'bulk_delete' => __( 'Delete', 'automatewoo' ),
		];

		return $actions;
	}


	/**
	 * prepare_items function.
	 */
	function prepare_items() {

		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];
		$current_page = absint( $this->get_pagenum() );
		$per_page = apply_filters( 'automatewoo_report_items_per_page', 20 );

		$this->get_items( $current_page, $per_page );

		$this->set_pagination_args([
			'total_items' => $this->max_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		]);
	}


	/**
	 * Get Products matching stock criteria
	 */
	function get_items( $current_page, $per_page ) {

		$query = new AW_Query_Unsubscribes();
		$query->set_limit( $per_page );
		$query->set_offset( ($current_page - 1 ) * $per_page );
		$query->set_ordering('date', 'DESC');

		if ( ! empty( $_GET['_workflow'] ) ) {
			$query->where('workflow_id', absint( $_GET['_workflow'] ) );
		}

		if ( ! empty( $_GET['_customer_user'] ) ) {
			$query->where('user_id', absint( $_GET['_customer_user'] ) );
		}

		$res = $query->get_results();

		$this->items = $res;

		$this->max_items = $query->found_rows;
	}

}
